<?php

namespace app\models\employer;

use Yii;
use yii\db\ActiveRecord;
use amnah\yii2\user\models\User;
use app\helper\Functions;

class Services extends ActiveRecord
{
    public $module;
	public $time_range;
	public $parent_list;
    public $imageThumb;
    public $old_image;
    private static $service_type = 'service';
    public $imageFiles;
	
    public static function tableName()
    {
        return static::getDb()->tablePrefix . "services";
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public static function find()
    {
        return parent::find()->where(['type' => self::$service_type]);
    }

    public function rules(){
        return [
            [['title'], 'required'],
            [['description', 'name', 'title', 'color'], 'string'],
            [['employer_id', 'parent_id', 'price'], 'number'],
            [['status', 'hot'], 'boolean'],
			[['imageThumb', 'status', 'hot'], 'safe']
        ];
    }

    public function attributeLabels()
    {
        return [
        ];
    }

    public function beforeSave($insert)
    {
        $this->type = self::$service_type;
        if ($this->isNewRecord) {
            $this->created_by = Yii::$app->user->id;
        } else {
            $this->updated_at = date('Y-m-d H:i:s');
            $this->updated_by = Yii::$app->user->id;
        }
        return parent::beforeSave($insert);
    }

    public function afterFind()
    {
        if($this->image) {
            $this->imageThumb = Functions::url() . '/' . $this->image;
        }
        parent::afterFind();
    }

    public function getAuth()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    public function getMetas()
    {
        return $this->hasMany(ServiceMeta::className(), ['service_id' => 'id']);
    }

    public function getGallery()
    {
        $path  = 'uploads/' . \yii\helpers\StringHelper::basename(get_class($this)) . '/' . $this->id . '/gallery/';
        $gallery = glob($path . "*");
        $result = [];

        foreach ($gallery as $key => $value) {
            $result[$key]['url'] = Functions::url() . '/' . $value;
            $path_parts = pathinfo($path . '/' . $value);
            $result[$key]['name'] = $path_parts['filename'];
            $result[$key]['mime'] = $path_parts['extension'];
        }

        return $result;
    }

    public function getBaseServices()
    {
        $base_services = AdminServices::find()->with('metas')->where(['status' => 1])->orderBy('title')->all();
        return $base_services;
    }

    public function getBaseService()
    {
        return $this->hasOne(AdminServices::className(), ['id' => 'parent_id']);
    }

    public function upload()
    {
        if ($this->validate()) {
            $path  = 'uploads/' . \yii\helpers\StringHelper::basename(get_class($this)) . '/' . $this->id;
            \yii\helpers\FileHelper::createDirectory($path , $mode = 0775, $recursive = true);
            $image_name = Functions::toslug($this->image->baseName) . '.' . $this->image->extension;
            $this->image->saveAs($path  . '/' . $image_name);
            $this->image = $path  . '/' . $image_name;
            if($this->old_image && $this->old_image != $this->image)
                @unlink($this->old_image);
            return true;
        } else {
            return false;
        }
    }

    public function uploadGallery()
    {
        $path  = 'uploads/' . \yii\helpers\StringHelper::basename(get_class($this)) . '/' . $this->id . '/gallery';
        \yii\helpers\FileHelper::createDirectory($path , $mode = 0775, $recursive = true);

        foreach ($this->imageFiles as $file) {
            $file->saveAs($path  . '/' . $file->baseName . '.' . $file->extension);
        }
        return true;
    }

    

}