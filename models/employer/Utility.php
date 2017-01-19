<?php

namespace app\models\employer;

use Yii;
use yii\db\ActiveRecord;
use app\helper\Functions;
use amnah\yii2\user\models\User;

class Utility extends ActiveRecord
{

    public $old_image;
    public $imageThumb;
    private static $service_type = 'utility';
    public $categories;
    
    public static function tableName()
    {
        return static::getDb()->tablePrefix . "services";
    }

    public function init() {
        parent::init();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public static function find()
    {
        return parent::find()->where(['type' => self::$service_type]);
    }

    public function rules(){
        return [
            [['title'], 'required'],
            [['description', 'color'], 'safe'],
            ['image', 'file'],
        ];
    }

    public function beforeSave($insert)
    {
        $this->type = self::$service_type;
        if ($this->isNewRecord) {
            $this->created_by = (Yii::$app->user->isGuest)?0:Yii::$app->user->id;
        } else {
            $this->updated_at = date('Y-m-d H:i:s');
            $this->updated_by = (Yii::$app->user->isGuest)?0:Yii::$app->user->id;
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
        parent::afterFind();
    }

    public function upload()
    {
        if ($this->validate()) {
            $path  = 'uploads/' . \yii\helpers\StringHelper::basename(get_class($this)) . '/' . $this->id;
            \yii\helpers\FileHelper::createDirectory($path , $mode = 0775, $recursive = true);
            $this->image->saveAs($path  . '/' . $this->image->baseName . '.' . $this->image->extension);
            $this->image = $path  . '/' . $this->image->baseName . '.' . $this->image->extension;
            if($this->old_image && $this->old_image != $this->image)
                @unlink($path  . '/' . $this->old_image);
            return true;
        } else {
            return false;
        }
    }

    public static function getUtilities($s = "") {
        return self::find()->select('id, title, image')->where(['status' => 1, 'type' => self::$service_type])
        ->andWhere(['like', 'title', $s])->all();
    }

    public function getEmployerMeta()
    {
        return $this->hasOne(EmployerMeta::className(), ['id' => 'meta_value']);
    }
}