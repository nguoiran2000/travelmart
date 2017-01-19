<?php

namespace app\models\blog;

use Yii;
use yii\db\ActiveRecord;
use app\helper\Functions;
use amnah\yii2\user\models\User;

class Blog extends ActiveRecord
{

    public $old_image;
    public $imageThumb;
    private static $post_type = 'blog';
    public $categories;
    public $tag;
    public $imageFiles;
	
    public static function tableName()
    {
        return static::getDb()->tablePrefix . "posts";
    }

    public function init() {
        $categories = Categories::find()->joinWith('parent as t')->select('categories.id, categories.name, categories.parent_id, t.name as parent_name')->all();
        $this->categories = [];
        foreach ($categories as $key => $value) {
            $this->categories[] = ['id' => $value->id, 'name' => $value->name, 'parent_name' => ($value->parent_id)?$value->parent_name:'Root Category'];
        }
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
            [['slug', 'content'], 'required'],
            [['slug'], 'unique'],
            //[['user_id'], 'number', 'min'=>1],
            [['image'], 'file', 'skipOnEmpty' => true, 'extensions' => 'png, jpg'],
            [['description', 'meta_title', 'meta_description', 'meta_keywords', 'status', 'hot', 'category_id', 'parent_id', 'imageThumb', 'tag', 'title'], 'safe']
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

    public function afterFind()
    {
        if($this->image) {
            $this->imageThumb = Functions::url() . '/' . $this->image;
        }
        if($this->created_at) {
            $this->created_at = Yii::$app->formatter->asDate($this->created_at, 'dd/MM/yyyy H:m');
        }
        return parent::afterFind();
    }

    public function getCategory()
    {
        return $this->hasOne(Categories::className(), ['id' => 'category_id']);
    }

    public function getAuth()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    public function getBlogMetaTag()
    {
        return $this->hasMany(BlogMeta::className(), ['post_id' => 'id'])->andWhere(['meta_key' => 'tag']);
    }

    public function getTags()
    {
        return $this->hasMany(Tag::className(), ['id' => 'meta_value'])->via('blogMetaTag');
    }

    public function getBlogMetaComment()
    {
        return $this->hasMany(BlogMeta::className(), ['post_id' => 'id'])->andWhere(['meta_key' => 'comment']);
    }

    public function getComments()
    {
        return $this->hasMany(Comment::className(), ['id' => 'meta_value'])->via('blogMetaComment')->orderBy('created_at DESC');
    }

    public function getLikes()
    {
        return $this->hasMany(BlogMeta::className(), ['post_id' => 'id'])->andWhere(['meta_key' => 'like']);
    }

    public function upload()
    {
        if ($this->validate()) {
            $path  = 'uploads/' . \yii\helpers\StringHelper::basename(get_class($this)) . '/' . $this->id;
            \yii\helpers\FileHelper::createDirectory($path , $mode = 0775, $recursive = true);
            $this->image->saveAs($path  . '/' . Functions::toslug($this->image->baseName) . '.' . $this->image->extension);
            $this->image = $path  . '/' . Functions::toslug($this->image->baseName) . '.' . $this->image->extension;
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