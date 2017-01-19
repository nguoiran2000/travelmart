<?php

namespace app\models\blog;

use Yii;
use yii\db\ActiveRecord;
use app\helper\Functions;
use amnah\yii2\user\models\User;

class Tag extends ActiveRecord
{

    public $old_image;
    public $imageThumb;
    private static $post_type = 'tag';
    public $categories;
	
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
            [['title', 'slug', 'content'], 'required'],
            [['title', 'slug'], 'unique'],
            //[['user_id'], 'number', 'min'=>1],
            [['image'], 'file', 'skipOnEmpty' => true, 'extensions' => 'png, jpg'],
            [['description', 'meta_title', 'meta_description', 'meta_keywords', 'status', 'category_id', 'parent_id', 'imageThumb'], 'safe']
        ];
    }

    public function beforeSave($insert)
    {
        $this->post_type = self::$post_type;
        if ($this->isNewRecord) {
            $this->created_by = Yii::$app->user->id;
            $this->user_id = Yii::$app->user->id;
        } else {
            $this->updated_at = date('Y-m-d H:i:s');
            $this->updated_by = Yii::$app->user->id;
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

    public function afterFind()
    {
        if($this->image) {
            $this->imageThumb = Functions::url() . '/' . $this->image;
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
}