<?php

namespace app\models\employer;

use Yii;
use yii\db\ActiveRecord;
use app\helper\Functions;
use amnah\yii2\user\models\User;

class EmployerMeta extends ActiveRecord
{
	
    public static function tableName()
    {
        return static::getDb()->tablePrefix . "employer_meta";
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
            [['meta_key', 'meta_value', 'employer_id'], 'required'],
            [['meta_key', 'meta_value', 'employer_id'], 'unique', 'targetAttribute' => ['meta_key', 'meta_value', 'employer_id']],
            [['alt_text', 'caption', 'description'], 'safe']
        ];
    }

    public function beforeSave($insert)
    {
        return parent::beforeSave($insert);
    }

    public function getEmployer()
    {
        return $this->hasOne(Employer::className(), ['id' => 'employer_id']);
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