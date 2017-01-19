<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use app\helper\Functions;

class Customers extends ActiveRecord
{
    public $old_image;
    public $imageThumb;

    // public function attributes()
    // {
    //     return array_merge(
    //         parent::attributes(),
    //         ['imageThumb']
    //     );
    // }
    
    public static function tableName()
    {
        return static::getDb()->tablePrefix . "angularcode_customers";
    }


    public function rules(){
        return [
            [['customerName', 'email', 'slug'], 'required'],
            [['image'], 'file', 'skipOnEmpty' => true, 'extensions' => 'png, jpg'],
            [['address', 'city', 'state', 'postalCode', 'country', 'status', 'content', 'image', 'imageThumb'], 'safe']
        ];  
    }

    public function attributeLabels()
    {
        return [];
    }

    public function afterFind()
    {
        if($this->image) {
            $path  = 'uploads/' . \yii\helpers\StringHelper::basename(get_class($this)) . '/' . $this->customerNumber;
            $this->imageThumb = Functions::url() . '/' . $path . '/' . $this->image;
        }
        parent::afterFind();
    }

    public function upload()
    {
        if ($this->validate()) {
            $path  = 'uploads/' . \yii\helpers\StringHelper::basename(get_class($this)) . '/' . $this->customerNumber;
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