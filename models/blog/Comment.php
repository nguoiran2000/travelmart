<?php

namespace app\models\blog;

use Yii;
use yii\db\ActiveRecord;
use app\helper\Functions;
use amnah\yii2\user\models\User;
use app\models\blog\BlogMeta;

class Comment extends ActiveRecord
{

    public $old_image;
    public $imageThumb;
    private static $post_type = 'comment';
    public $categories;
    public $imageFiles;
	
    public static function tableName()
    {
        return static::getDb()->tablePrefix . "posts";
    }

    public function init() {
        parent::init();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public static function find()
    {
        return parent::find()->where(['post_type' => self::$post_type]);
    }

    public function rules(){
        return [
            [['slug'], 'required'],
            [['parent_id'], 'safe']
        ];
    }

    public function beforeSave($insert)
    {
        $this->post_type = self::$post_type;
        if ($this->isNewRecord) {
            $this->created_by = (Yii::$app->user->isGuest)?0:Yii::$app->user->id;
            $this->user_id = (Yii::$app->user->isGuest)?0:Yii::$app->user->id;
        } else {
            $this->updated_at = date('Y-m-d H:i:s');
            $this->updated_by = (Yii::$app->user->isGuest)?0:Yii::$app->user->id;
        }
        return parent::beforeSave($insert);
    }

    public function getCategory()
    {
        return $this->hasOne(Categories::className(), ['id' => 'category_id']);
    }

    public function getAuth()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    public function getLikes()
    {
        return $this->hasMany(BlogMeta::className(), ['post_id' => 'id'])->andWhere(['meta_key' => 'like']);
    }


    public function afterFind()
    {
        if($this->image) {
            $this->imageThumb = Functions::url() . '/' . $this->image;
        }
        if($this->created_at) {
            $this->created_at = Yii::$app->formatter->asDate($this->created_at, 'dd/MM/yyyy H:m');
        }
        parent::afterFind();
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

    public function uploadGallery()
    {
        $path  = 'uploads/' . \yii\helpers\StringHelper::basename(get_class($this)) . '/' . $this->id . '/gallery';
        \yii\helpers\FileHelper::createDirectory($path , $mode = 0775, $recursive = true);

        foreach ($this->imageFiles as $file) {
            $file->saveAs($path  . '/' . $file->baseName . '.' . $file->extension);
        }
        return true;
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

}