<?php

namespace app\models\employer;

use Yii;
use yii\db\ActiveRecord;
use amnah\yii2\user\models\User;
use app\models\blog\Blog;
use app\models\blog\Comment;
use app\helper\Functions;

class Employer extends ActiveRecord
{
    public $module;
	public $time_range;
	public $parent_list;
    public $imageThumb;
    public $old_logo;
    public $imageFiles;
    public $coverFile;
    public $price;
	
    public static function tableName()
    {
        return static::getDb()->tablePrefix . "employer";
    }


    public function rules(){
        return [
            [['title', 'employer_type'], 'required'],
            [['email'], 'email'],
            [['website'], 'validateUrl'],
            [['description', 'name', 'city_id', 'district_id'], 'string'],
            [['logo'], 'file'],
            [['website', 'phone', 'address', 'contract_no', 'contract_value', 'contract_sale', 'time_range'], 'string'],
            [['vip_start_date', 'vip_end_date', 'latitude', 'longitude', 'parent_id'], 'number'],
			[['imageThumb'], 'safe']
        ];
    }

    public function attributeLabels()
    {
        return [
            "title" => \Yii::t('app',"Tên gian TD"),
            "name" => \Yii::t('app',"Tên công ty"),
            "logo" => \Yii::t('app',"Logo"),
            "description" => \Yii::t('app',"Giới thiệu chung"),
            "address" => \Yii::t('app',"Địa chỉ"),
            "website" => \Yii::t('app',"Website"),
            "phone" => \Yii::t('app',"Điên thoại"),
            "email" => \Yii::t('app',"Email"),
            "scale_company" => \Yii::t('app',"Quy mô công ty"),
            "address_drag" => \Yii::t('app',"Chọn địa chỉ trên bản đồ"),
			"contract_no" => \Yii::t('app',"Số hợp đồng"),
            "contract_value" => \Yii::t('app',"Giá trị hợp đồng"),
            "contract_sale" => \Yii::t('app',"NV Sale"),
			"parent_list" => 'Danh sách gian',
        ];
    }

    public function beforeSave($insert)
    {
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
        if($this->logo) {
            $this->imageThumb = Functions::url() . '/' . $this->logo;
        }
        parent::afterFind();
    }

    public function getAuth()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    public function validateUrl(){
        $pattern = '/(?:https?:\/\/)?(?:[a-zA-Z0-9.-]+?\.(?:[a-zA-Z])|\d+\.\d+\.\d+\.\d+)/';

        if(!preg_match($pattern, $this->website))
        {
            $this->addError('website', \Yii::t('app','Website không đúng định dạng'));
        }
    }

    public static function addActivityCount($employer){
        if($employer->vip == 0){
            if($employer->activity_count < 5){
                $employer->activity_count = $employer->activity_count + 1;
                $employer->save(true);
                return true;
            }else{
                return false;
            }
        }
        return true;
    }

    public function getEmployerMetaBlog()
    {
        return $this->hasMany(EmployerMeta::className(), ['employer_id' => 'id'])->andWhere(['meta_key' => 'introduction']);
    }

    public function getBlogs()
    {
        return $this->hasMany(Blog::className(), ['id' => 'meta_value'])->via('employerMetaBlog')->orderBy('created_at DESC');
    }

    public function getMetaNews()
    {
        return $this->hasMany(EmployerMeta::className(), ['employer_id' => 'id'])->andWhere(['meta_key' => 'news']);
    }

    public function getNews()
    {
        return $this->hasMany(Blog::className(), ['id' => 'meta_value'])->via('metaNews')->orderBy('created_at DESC');
    }

    public function getMetaUtility()
    {
        return $this->hasMany(EmployerMeta::className(), ['employer_id' => 'id'])->andWhere(['meta_key' => 'utility']);
    }

    public function getUtilities()
    {
        return $this->hasMany(Utility::className(), ['id' => 'meta_value'])->via('metaUtility');
    }

    public function getServices()
    {
        $array_services = [];
        $adminServices = AdminServices::adminServices();
        $services = Services::find()->where(['employer_id' => $this->id])->all();

        foreach ($services as $key => $value) {

            if($adminServices[$value->parent_id]) {
                $metas = [];
                foreach ($value->metas as $k => $val) {
                    if(isset($adminServices[$value->parent_id]['metas'][$val->meta_key])) {
                        $metas[$val->meta_key] = array_merge($val->attributes, [
                            'base_meta' => $adminServices[$value->parent_id]['metas'][$val->meta_key]]);
                    }
                }

                $array_services[$value->id] = array_merge($value->attributes, [
                    'imageThumb' => $value->imageThumb, 
                    'metas' => $metas,
                    'base_service' => $adminServices[$value->parent_id],
                    'gallery' =>$value->gallery]);
            }
        }

        return $array_services;
    }

    public function getCover()
    {
        return $this->hasOne(EmployerMeta::className(), ['employer_id' => 'id'])->where(['meta_key' => 'cover']);
    }

    public function updateContent($blog, $type)
    {
        $meta = EmployerMeta::find()->where(['employer_id' => $this->id, 
            'meta_value' => $blog->id,
            'meta_key' => $type])->one();
        if(!$meta) {
            $meta = new EmployerMeta();
            $meta->employer_id = $this->id;
            $meta->meta_value = $blog->id;
            $meta->meta_key = $type;
            return $meta->save();
        }

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

    public function uploadGallery()
    {
        $path  = 'uploads/' . \yii\helpers\StringHelper::basename(get_class($this)) . '/' . $this->id . '/gallery';
        \yii\helpers\FileHelper::createDirectory($path , $mode = 0775, $recursive = true);

        foreach ($this->imageFiles as $file) {
            $file->saveAs($path  . '/' . $file->baseName . '.' . $file->extension);
        }
        return true;
    }

    public function uploadCover()
    {
        $path  = 'uploads/' . \yii\helpers\StringHelper::basename(get_class($this)) . '/' . $this->id;
        \yii\helpers\FileHelper::createDirectory($path , $mode = 0775, $recursive = true);
        $image_name = Functions::toslug($this->coverFile->baseName) . '.' . $this->coverFile->extension;
        $old_cover = $this->cover;

        if($old_cover) {
            @unlink($old_cover->meta_value);
            $old_cover->delete();
        }

        $this->coverFile->saveAs($path  . '/' . $image_name);

        $new_cover = new EmployerMeta();
        $new_cover->meta_key = 'cover';
        $new_cover->meta_value = $path  . '/' . $image_name;
        $new_cover->employer_id =  $this->id;

        return $new_cover->save();
    }

    public function upload()
    {
        $path  = 'uploads/' . \yii\helpers\StringHelper::basename(get_class($this)) . '/' . $this->id;
        \yii\helpers\FileHelper::createDirectory($path , $mode = 0775, $recursive = true);
        
        $image_name = Functions::toslug($this->logo->baseName) . '.' . $this->logo->extension;
        $this->logo->saveAs($path  . '/' . $image_name);
        $this->logo = $path  . '/' . $image_name;
        if($this->old_logo && $this->old_logo != $this->logo)
            @unlink($this->old_logo);
        return true;
    }

    public function getEmployerMetaComment()
    {
        return $this->hasMany(EmployerMeta::className(), ['employer_id' => 'id'])->andWhere(['meta_key' => 'comment']);
    }

    public function getComments()
    {
        return $this->hasMany(Comment::className(), ['id' => 'meta_value'])->via('employerMetaComment')->orderBy('created_at DESC');
    }

}