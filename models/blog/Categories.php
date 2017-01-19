<?php

namespace app\models\blog;

use Yii;
use yii\db\ActiveRecord;

class Categories extends ActiveRecord
{

    public $parent_name;
	
    public static function tableName()
    {
        return static::getDb()->tablePrefix . "categories";
    }

    public function rules(){
        return [
            
        ];
    }

    public function attributeLabels()
    {
        return [
        ];
    }

    public function beforeSave($insert)
    {
        if ($this->isNewRecord) {
            $this->created_by = Yii::$app->user->id;
        } else {
            $this->updated_at = date('Y-m-d H:i:s');
            $this->updated_by = Yii::$app->user->id;
        }
        return parent::beforeSave($insert);
    }

    public function getBlog()
    {
        return $this->hasMany(Blog::className(), ['category_id' => 'id']);
    }

    public function getParent()
    {
        return $this->hasOne(self::className(), ['id' => 'parent_id']);
    }
}