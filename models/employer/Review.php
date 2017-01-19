<?php

namespace app\models\employer;

use Yii;
use yii\db\ActiveRecord;
use app\helper\Functions;
use amnah\yii2\user\models\User;
use app\models\blog\BlogMeta;

class Review extends ActiveRecord
{

    public $old_image;
    public $imageThumb;
    private static $post_type = 'review';
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

    public function getAuth()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    public function getLikes()
    {
        return $this->hasMany(BlogMeta::className(), ['post_id' => 'id'])->andWhere(['meta_key' => 'like']);
    }

    public function getComments()
    {
        return $this->hasMany(Comment::className(), ['id' => 'meta_value'])->via('blogMetaComment')->orderBy('created_at DESC');
    }

    public function afterFind()
    {
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
}