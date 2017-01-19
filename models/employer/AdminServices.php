<?php

namespace app\models\employer;

use Yii;
use yii\db\ActiveRecord;
use amnah\yii2\user\models\User;
use app\helper\Functions;

class AdminServices extends ActiveRecord
{
    public $module;
	public $time_range;
	public $parent_list;
    public $imageThumb;
    public $old_image;
    private static $service_type = 'admin-service';
	
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
            [['title', 'name'], 'required'],
            [['description', 'name', 'title', 'color'], 'string'],
            [['image'], 'file'],
			[['imageThumb', 'status'], 'safe']
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

    public function upload()
    {
        if ($this->validate()) {
            $path  = 'uploads/' . \yii\helpers\StringHelper::basename(get_class($this)) . '/' . $this->id;
            \yii\helpers\FileHelper::createDirectory($path , $mode = 0775, $recursive = true);
            $this->image->saveAs($path  . '/' . Functions::toslug($this->image->baseName) . '.' . $this->image->extension);
            $this->image = $path  . '/' . Functions::toslug($this->image->baseName) . '.' . $this->image->extension;
            if($this->old_image && $this->old_image != $this->image)
                @unlink($path  . '/' . $this->old_image);
            return true;
        } else {
            return false;
        }
    }

    public function adminServices()
    {
        $result = [];
        $base_services = AdminServices::find()->with('metas')->where(['type' => self::$service_type, 'status' => 1])->all();

        foreach ($base_services as $key => $value) {
            $metas = [];
            foreach ($value->metas as $k => $val) {
                if($val->meta_key == 'select')
                    $val->description = explode(',', $val->description);
                $metas[$val->id] = $val;
            }
            $result[$value->id] = array_merge($value->attributes, ['metas' => $metas,
                'imageThumb' => $value->imageThumb]);
        }

        return $result;
    }

    public function baseServices()
    {
        $base_services = AdminServices::find()->with('metas')->where(['type' => self::$service_type, 'status' => 1])->all();

        foreach ($base_services as $key => $value) {
            $metas = [];
            foreach ($value->metas as $k => $val) {
                if($val->meta_key == 'select')
                    $val->description = explode(',', $val->description);
                $metas[$val->id] = $val;
            }
            $base_services[$key] = array_merge($value->attributes, ['metas' => $metas,
                'imageThumb' => $value->imageThumb]);
        }

        return $base_services;
    }

}