<?php

namespace app\models\tour;

use Yii;
use yii\db\ActiveRecord;
use app\helper\Functions;
use amnah\yii2\user\models\User;

class TourMeta extends ActiveRecord
{
	
    public static function tableName()
    {
        return static::getDb()->tablePrefix . "tour_meta";
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
            [['meta_key', 'meta_value', 'tour_id'], 'required'],
            [['meta_key', 'meta_value', 'tour_id'], 'unique', 'targetAttribute' => ['meta_key', 'meta_value', 'tour_id']],
            [['alt_text', 'caption', 'description'], 'safe']
        ];
    }

    public function beforeSave($insert)
    {
        return parent::beforeSave($insert);
    }

    public function getTour()
    {
        return $this->hasOne(Tour::className(), ['id' => 'tour_id']);
    }
}