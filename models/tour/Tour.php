<?php

namespace app\models\tour;

use Yii;
use yii\db\ActiveRecord;
use amnah\yii2\user\models\User;
use app\models\blog\Blog;
use app\models\blog\Comment;
use app\helper\Functions;

class Tour extends ActiveRecord
{

    public $imageThumb;
    public $imageFiles;

    public static function tableName()
    {
        return static::getDb()->tablePrefix . "tour";
    }


    public function rules(){
        return [
            [['title'], 'required'],
			[['imageThumb', 'description', 'content', 'image', 'status', 'hot', 'start_date', 'end_date', 'max_member'], 'safe']
        ];
    }

    public function attributeLabels()
    {
        return [];
    }

    public function beforeSave($insert)
    {
        if ($this->isNewRecord) {
            $this->created_by = Yii::$app->user->id;
            $this->user_id = Yii::$app->user->id;
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

    public function uploadGallery()
    {
        $path  = 'uploads/' . \yii\helpers\StringHelper::basename(get_class($this)) . '/' . $this->id . '/gallery';
        \yii\helpers\FileHelper::createDirectory($path , $mode = 0775, $recursive = true);

        foreach ($this->imageFiles as $file) {
            $file->saveAs($path  . '/' . $file->baseName . '.' . $file->extension);
        }
        return true;
    }

    public function upload()
    {
        $path  = 'uploads/' . \yii\helpers\StringHelper::basename(get_class($this)) . '/' . $this->id;
        \yii\helpers\FileHelper::createDirectory($path , $mode = 0775, $recursive = true);
        
        $image_name = Functions::toslug($this->logo->baseName) . '.' . $this->logo->extension;
        $this->logo->saveAs($path  . '/' . $image_name);
        $this->logo = $path  . '/' . $image_name;
        if($this->old_logo && $this->old_logo != $this->logo)
            @unlink($this->old_logo);
        return true;
    }

    public function getMetas()
    {
        return $this->hasMany(TourMeta::className(), ['tour_id' => 'id']);
    }

}