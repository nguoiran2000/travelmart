<?php

namespace app\models\employer;

use Yii;
use yii\db\ActiveRecord;
use app\helper\Functions;
use amnah\yii2\user\models\User;

class ServiceMeta extends ActiveRecord
{
	
    public static function tableName()
    {
        return static::getDb()->tablePrefix . "service_meta";
    }

    public function init() {
        parent::init();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public static function find()
    {
        return parent::find();
    }

    public function rules(){
        return [
            [['meta_key', 'meta_value', 'service_id'], 'required'],
            [['alt_text', 'caption', 'description', 'parent_id'], 'safe'],
            [['meta_key', 'meta_value', 'service_id'], 'unique', 'targetAttribute' => ['meta_key', 'meta_value', 'service_id']],
        ];
    }

    public function beforeSave($insert)
    {
        return parent::beforeSave($insert);
    }

    public function getService()
    {
        return $this->hasOne(Services::className(), ['id' => 'service_id']);
    }

    public function getBaseMeta()
    {
        return $this->hasOne(ServiceMeta::className(), ['id' => 'meta_key']);
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