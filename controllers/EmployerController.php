<?php
namespace app\controllers;

use Yii;
use yii\filters\ContentNegotiator;
use yii\web\Response;
use yii\filters\AccessControl;
use yii\rest\Controller;
use yii\filters\auth\HttpBearerAuth;
use yii\web\UploadedFile;
use app\models\employer\Employer;
use app\models\employer\EmployerMeta;
use app\helper\Functions;
use app\models\blog\Blog;
use app\models\blog\Categories;
use app\models\blog\Tag;
use app\models\blog\Comment;
use app\models\employer\Utility;
use yii\base\Exception;
use app\models\employer\Services;
use app\models\employer\AdminServices;
use app\models\employer\ServiceMeta;

/**
 * Site controller
 */
class EmployerController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::className(),
            'only' => ['insert-employer', 'admin-employers', 'admin-employer', 'update-employer', 'status-employer', 'delete-employer', 'insert-introduction-employer', 'update-introduction-employer', 'delete-introduction', 'utility', 'delete-utility', 'delete-service', 'update-service', 'insert-news', 'update-news', 'delete-news'],
        ];
        $behaviors['contentNegotiator'] = [
            'class' => ContentNegotiator::className(),
            'formats' => [
                'application/json' => Response::FORMAT_JSON,
            ],
        ];
        $behaviors['access'] = [
            'class' => AccessControl::className(),
            'only' => ['insert-employer', 'admin-employers', 'admin-employer', 'update-employer', 'status-employer', 'delete-employer', 'insert-introduction-employer', 'update-introduction-employer', 'delete-introduction', 'utility', 'delete-utility', 'delete-service', 'update-service', 'insert-news', 'update-news', 'delete-news'],
            'rules' => [
                [
                    'actions' => ['insert-employer', 'admin-employers', 'admin-employer', 'update-employer', 'insert-employer', 'delete-employer', 'insert-introduction-employer', 'update-introduction-employer', 'delete-introduction', 'utility', 'delete-utility', 'delete-service', 'update-service', 'insert-news', 'update-news', 'delete-news'],
                    'allow' => true,
                    'roles' => ['writer'],
                ],
                [
                    'actions' => ['status-employer'],
                    'allow' => true,
                    'roles' => ['editor'],
                ],
            ],
        ];
        return $behaviors;
    }

    // Blog controller Yii2

    public function actionAdminEmployers() {
        $request = Yii::$app->request;
        $pagenum = $request->get('page', '');
        $pagesize = $request->get('size', '');
        $offset = ($pagenum - 1) * $pagesize;
        $search = $request->get('search', '');
        $sortKey = $request->get('sortKey', '');
        $reverse = $request->get('reverse', '');

        if($sortKey != "") {
            if($reverse == 'true')
                $order = $sortKey . ' desc';
            else
                $order = $sortKey . ' asc';
        } else {
            $order = 'id DESC';
        }

        $query = Employer::find();

        if ($search != "") {
            $search = json_decode($search, true);
            foreach ($search as $key => $value) {
                if($value && $value != 'null')
                    if($key == 'user_id') {
                        $query = $query->joinWith('auth au')->where(['like', 'username', $value])->orWhere(['like', 'email', $value]);
                    } else {
                        $query = $query->where(['like', $key, $value]);
                    }
            }            
        } else {
            $where = [];
        }

        if(!Yii::$app->user->can('mod')) {
            $query = $query->andWhere(['user_id' => Yii::$app->user->id]);
        }

        $totalCount = $query->count();

        $response = $query->limit($pagesize)->offset($offset)->orderBy($order)->all();

        foreach ($response as $key => $value) {
            $response[$key] = array_merge($value->attributes, [
                'imageThumb' => $value->imageThumb,
                'auth' => $value->auth->displayName]);
        }

        return ['totalCount' => $totalCount, 'response' => $response];
    }

    public function actionAdminEmployer() {
        $id = (int)$_GET['id'];
        $categories = Categories::find()->select('id, name, parent_id')->all();
        $tags = Tag::find()->select('id, title')->all();
        if($id > 0) {
            $model = Blog::findOne($id);
            if($model) {
                $model->tag = [];
                foreach ($model->blogMetaTag as $key => $value) {
                    $model->tag[] = (int)$value->meta_value;
                }
                return array_merge(
                    $model->attributes,
                    ['imageThumb' => $model->imageThumb, 'categories' => $categories, 'tags' => $tags, 'tag' => $model->tag]
                );
            }
        }
        $model = new Employer();
        return array_merge($model->attributes, ['imageThumb' => $model->imageThumb, 'categories' => $categories, 'tags' => $tags]) ;
    }

    public function actionEmployer() {
        $slug = $_GET['slug'];
        $model = Employer::find()->where(['slug' => $slug])->one();

        if($model) {

            // Đếm lượt xem theo IP
            $ip = !empty($_SERVER['HTTP_CLIENT_IP']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
            if($model->last_ip != $ip) {
                $model->view_count += 1;
                $model->last_ip = $ip;
                $model->save();
            }

            $introduction = $model->blogs;
            foreach ($introduction as $key => $value) {
                $introduction[$key] = array_merge($value->attributes, [
                    'imageThumb' => $value->imageThumb, 
                    'category' => $value->category,
                    'auth' => $value->auth->displayName]);
            }

            $news_array = $model->news;
            $news = [];
            foreach ($news_array as $key => $value) {
                $news[$value->id] = array_merge($value->attributes, [
                    'imageThumb' => $value->imageThumb, 
                    'category' => $value->category,
                    'auth' => $model->auth->profile]);
            }

            $cover = $model->cover;
            $image_cover = '';

            if($cover)
                $image_cover = Functions::url() . '/' . $cover->meta_value;

            $comments = $model->comments;
            foreach ($comments as $key => $value) {
                $comments[$key] = array_merge($value->attributes, [
                    'gallery' => $value->gallery]);
            }

            $result = array_merge(
                $model->attributes,
                [   
                    'imageThumb' => $model->imageThumb,
                    'auth' => $model->auth->profile,
                    'introduction' => $introduction,
                    'utilities' => $model->utilities,
                    'gallery' => $model->gallery,
                    'cover' => $image_cover,
                    'comments' => $comments,
                    'news' => $news
                ]
            );

            if($model->employer_type == 'employer') {
                $base_services = [];
                $services = $model->services;
                foreach ($services as $key => $value) {
                    if(!isset($base_services[$value['parent_id']])) {
                        $base_services[$value['parent_id']] = $value['base_service'];
                    }
                }

                $result = array_merge($result, [
                    'services' => $services,
                    'base_services' => $base_services,]);

            } else {
                $employers = Employer::find()->select('employer.id, employer.slug, employer.title, employer.logo, employer.description, employer.address, view_count')
                    ->where(['employer.status' => 1, 'employer_type' => 'employer', 'parent_id' => $model->id])
                    ->orderBy('employer.updated_at DESC')->limit(12)->all();

                foreach ($employers as $key => $value) {
                    $services = [];
                    $color = '';
                    $price = 0;
                    foreach ($value->services as $k => $val) {
                        if($price > $val['price'] || $price == 0) {
                            $price = $val['price'];
                            $color = $val['base_service']['color'];
                        }
                        $services[] = $val;
                    }
                    $employers[$key] =  array_merge($value->attributes, 
                        ['imageThumb' => $value->imageThumb,
                        'services' => $value->services,
                        'price' => number_format($price),
                        'color' => $color]);
                }

                $result = array_merge($result, [
                    'employers' => $employers]);
            }

            return $result;
        }

        return false;
    }

    public function actionInsertEmployer() {
        $model = new Employer();
        $post = Yii::$app->getRequest()->getBodyParams();
        $model->load(Yii::$app->getRequest()->getBodyParams(), '');

        if($model->employer_type == 'employer') {
            $employer = Employer::findOne($model->parent_id);
            if($employer) {
                $model->city_id = $employer->city_id;
                $model->district_id = $employer->district_id;
            }
        }
        
        if ($model->save()) {
            $model->slug = Functions::toslug($model->name);
            $duplicate = $model->find()
                ->where(['slug' => $model->slug])
                ->andWhere(['<>', 'id', $model->id])
                ->one();
            if($duplicate)
                $model->slug = $model->slug . '-' . $model->id;
            $model->save();
            $success = array('status' => "Success", "msg" => "Blog Created Successfully.", "data" => $model);
            return $success;
        }
        $model->validate();
        return $model;
    }

    public function actionUpdateEmployer() {
        $post = Yii::$app->getRequest()->getBodyParams();
        $id = (int)$post['id'];
        $model = Employer::findOne($id);

        if(!$model)
            throw new Exception("Không có dữ liệu", 404);

        if(!Yii::$app->user->can('editor') && Yii::$app->user->id != $model->user_id)
            throw new Exception("Không có quyền", 403);

        $model->old_logo = $model->logo;
        if($model->load($post, '') && $model->validate()) {
            if ($_FILES) {
                $model->logo = UploadedFile::getInstanceByName('image');
                $model->upload();
            }

            $model->save(false);
            $success = array('status' => "Success", "msg" => "Employer ".$id." Updated Successfully.", "data" => $model);
            return $success;
            
        }

        $model->validate();
        return $model;
    }

    public function actionStatusEmployer() {
        $post = Yii::$app->getRequest()->getBodyParams();
        $id = (int)$post['id'];
        $status = $post['status'];
        $model = Employer::findOne($id);
        if ($model) {
            $model->status = $status;
            if($model->save(false)) {
                $success = array('status' => "Success", "msg" => "Blog ".$id." Updated Successfully." . $model->status, "data" => $model);
                return $success;
            }
        }
        return [];
    }

    public function actionDeleteEmployer() {
        $id = (int)$_GET['id'];
        $model = Employer::findOne($id);

        if(!Yii::$app->user->can('editor') && Yii::$app->user->id != $model->user_id)
            throw new Exception("Không có quyền", 403);

        if ($model->delete()) {
            $path  = 'uploads/' . \yii\helpers\StringHelper::basename(get_class($model)) . '/' . $model->id;
            \yii\helpers\FileHelper::removeDirectory($path);
            // Xóa tất cả meta 
            EmployerMeta::deleteAll(['employer_id' => $model->id]);
            $success = array('status' => "Success", "msg" => "Successfully deleted one record.");
            return $success;
        }

        return $model;
    }

    public function actionInsertIntroductionEmployer() {

        $employer_id = (int)$_POST['employer_id'];

        $employer = Employer::findOne($employer_id);

        if($employer) {

            $model = new Blog();
            // $model->user_id = Yii::$app->user->id;
            if ($model->load(Yii::$app->getRequest()->getBodyParams(), '')) {
                $model->content = $model->description;
                $model->slug = Functions::toslug($model->title) . '-' . $employer_id;
                if($model->save()) {
                    if ($_FILES) {
                        $model->image = UploadedFile::getInstanceByName('image');
                        $model->upload();

                        $model->save(false);
                    }
                    
                    $employer->updateContent($model, 'introduction');

                    $model->imageThumb = Functions::url() . '/' . $model->image;
                    
                    $success = array('status' => "Success", "msg" => "Blog Created Successfully.", "data" => array_merge(
                        $model->attributes,
                        [   
                            'imageThumb' => $model->imageThumb,
                            'auth' => $model->auth->profile
                        ]
                    ));
                    return $success;
                }
            }
            $model->validate();
            return $model;
        }
    }

    public function actionUpdateIntroductionEmployer() {

        $post = Yii::$app->getRequest()->getBodyParams();

        $employer_id = (int)$post['employer_id'];

        $blog_id = (int)$post['id'];

        $employer = Employer::findOne($employer_id);

        if($employer) {

            $model = Blog::findOne($blog_id);
            $model->old_image = $model->image;
            if($model->load($post, '') && $model->validate()) {
                if ($_FILES) {
                    $model->image = UploadedFile::getInstanceByName('image');

                    if($model->image)
                        $model->upload();
                } else 
                    $model->image = $model->old_image;

                $model->save(false);
                $model->imageThumb = Functions::url() . '/' . $model->image;
                $success = array('status' => "Success", "msg" => "Blog ".$blog_id." Updated Successfully.", "data" => array_merge(
                        $model->attributes,
                        [   
                            'imageThumb' => $model->imageThumb,
                            'auth' => $model->auth->profile
                        ]
                    ));
                return $success;
                
            }

            $model->validate();
            return $model;
        }
    }

    public function actionIntroductionEmployer() {
        $id = (int)$_GET['id'];
        $categories = Categories::find()->select('id, name, parent_id')->all();
        $tags = Tag::find()->select('id, title')->all();
        if($id > 0) {
            $model = Blog::findOne($id);
            if($model) {
                $model->tag = [];
                foreach ($model->blogMetaTag as $key => $value) {
                    $model->tag[] = (int)$value->meta_value;
                }
                return array_merge(
                    $model->attributes,
                    ['imageThumb' => $model->imageThumb, 'categories' => $categories, 'tags' => $tags, 'tag' => $model->tag]
                );
            }
        }
        $model = new Blog();
        return array_merge($model->attributes, ['imageThumb' => $model->imageThumb, 'categories' => $categories, 'tags' => $tags]) ;
    }

    public function actionDeleteIntroduction() {
        $id = (int)$_GET['id'];
        $model = Blog::findOne($id);

        if(!Yii::$app->user->can('editor') && Yii::$app->user->id != $model->user_id)
            throw new Exception("Không có quyền", 403);

        if ($model->delete()) {
            $path  = 'uploads/' . \yii\helpers\StringHelper::basename(get_class($model)) . '/' . $model->id;
            \yii\helpers\FileHelper::removeDirectory($path);
            // Xóa tất cả meta 
            //BlogMeta::deleteAll(['post_id' => $model->id]);
            $success = array('status' => "Success", "msg" => "Successfully deleted one record.");
            return $success;
        }

        return $model;
    }

    public function actionUtility() {
        $id = (int)$_GET['id'];
        $utilities = Utility::getUtilities();

        if($id > 0)
            $model = EmployerMeta::findOne($id);
        else 
            $model = new EmployerMeta();

        return array_merge($model->attributes, ['utilities' => $utilities]);
    }

    public function actionSearchUtilities() {
        $query = $_GET['s'];

        return Utility::getUtilities($query);
    }

    public function actionInsertMeta() {

        $employer_id = (int)$_POST['employer_id'];

        $employer = Employer::findOne($employer_id);

        if($employer) {

            $model = new EmployerMeta();
            if ($model->load(Yii::$app->getRequest()->getBodyParams(), '') && $model->save()) {

                switch ($model->meta_key) {
                    case 'utility':
                        $result = Utility::findOne($model->meta_value);
                        break;
                    
                    default:
                        $result = EmployerMeta::findOne($model->meta_value);
                        break;
                }

                $success = array('status' => "Success", "msg" => "Meta Created Successfully.", "data" => $result->attributes
                );
                return $success;
            }

            $model->validate();
            return $model;
        }
    }

    public function actionUpdateUtility() {

        $employer_id = (int)$_POST['employer_id'];

        $employer = Employer::findOne($employer_id);

        if($employer) {

            $model = EmployerMeta::findOne((int)$_POST['id']);
            if ($model->load(Yii::$app->getRequest()->getBodyParams(), '') && $model->save()) {

                $success = array('status' => "Success", "msg" => "Meta Created Successfully.", "data" => $model->attributes
                );
                return $success;
            }

            $model->validate();
            return $model;
        }
    }

    public function actionDeleteUtility() {
        $id = (int)$_GET['id'];
        $employer_id = (int)$_GET['employer_id'];
        $model = EmployerMeta::find()->where(['meta_value' => $id, 'employer_id' => $employer_id])->one();
        $employer = Employer::findOne($employer_id);

        if(!Yii::$app->user->can('editor') && Yii::$app->user->id != $employer->user_id)
            throw new Exception("Không có quyền", 403);

        if ($model->delete()) {
            $success = array('status' => "Success", "msg" => "Successfully deleted one record.");
            return $success;
        }

        return $model;
    }

    public function actionService() {
        $id = (int)$_GET['id'];
        $base_services = AdminServices::baseServices();

        if($id > 0)
            $model = Services::findOne($id);
        else 
            $model = new Services();
        $metas = [];
        foreach ($model->metas as $key => $value) {
            $metas[$value->meta_key] = $value;
        }

        return array_merge($model->attributes, ['base_services' => $base_services,
                'metas' => $metas
            ]);
    }

    public function actionInsertService() {
        $model = new Services();
        $post = Yii::$app->getRequest()->getBodyParams();

        $model->name = $post['name'];
        $model->title = $post['title'];
        $model->description = $post['description'];
        $model->hot = (int)$post['hot'];
        $model->employer_id = (int)$post['employer_id'];
        $model->parent_id = (int)$post['parent_id'];

        if ($model->save()) {
            if ($_FILES) {
                if($model->image = UploadedFile::getInstanceByName('image')) {
                    $model->upload();
                    $model->save(false);
                }
                
                if($model->imageFiles = UploadedFile::getInstancesByName('gallery'))
                    $model->uploadGallery();
            }

            if(isset($post['meta_key'])) {
                foreach ($post['meta_key'] as $key => $value) {
                    $service_meta = ServiceMeta::findOne($value);
                    if($service_meta && $post['meta_value'][$key]) {
                        $new_meta = new ServiceMeta();
                        $new_meta->meta_key = $post['meta_key'][$key];
                        $new_meta->meta_value = $post['meta_value'][$key];
                        $new_meta->service_id = $model->id;
                        $new_meta->save();
                    }
                }
            }

            $model->imageThumb = Functions::url() . '/' . $model->image;


            $base_service = $model->baseService;
            $base_metas = [];
            if($base_service) {
                foreach ($base_service->metas as $k => $val) {
                    if($val->meta_key == 'select')
                        $val->description = explode(',', $val->description);
                    $base_metas[$val->id] = $val;
                }
                $base_service = array_merge($base_service->attributes, [
                    'imageThumb' => $base_service->imageThumb,
                    'metas' => $base_metas]);
            }

            if($base_service) {
                $metas = [];
                foreach ($model->metas as $k => $val) {
                    if(isset($base_metas[$val->meta_key]))
                        $metas[$val->meta_key] = array_merge($val->attributes, [
                        'base_meta' => $base_metas[$val->meta_key]]);
                }
            }

            $success = array('status' => "Success", "msg" => "Service Created Successfully.", "data" => 
                array_merge(
                    $model->attributes,
                    ['imageThumb' => $model->imageThumb,
                    'metas' => $metas,
                    'base_service' => $base_service,
                    'gallery' => $model->gallery]
                ));
            return $success;
        }
        $model->validate();
        return $model;
    }

    public function actionUpdateService() {
        $post = Yii::$app->getRequest()->getBodyParams();
        $id = (int)$post['id'];
        $model = Services::findOne($id);

        if(!$model)
            throw new Exception("Dữ liệu không tồn tại", 404);

        $employer = Employer::findOne($model->employer_id);

        if(!$employer)
            throw new Exception("Dữ liệu cha không tồn tại", 404);

        if(!Yii::$app->user->can('editor') && Yii::$app->user->id != $employer->user_id)
            throw new Exception("Không có quyền", 403);

        $model->old_image = $model->image;

        if ($_FILES) {
            
            if($model->image = UploadedFile::getInstanceByName('image'))
                $model->upload();
            else 
                $model->image = $model->old_image;

            if($model->imageFiles = UploadedFile::getInstancesByName('gallery'))
                $model->uploadGallery();
        }

        $model->name = $post['name'];
        $model->title = $post['title'];
        $model->description = $post['description'];
        $model->color = $post['color'];
        $model->hot = (int)$post['hot'];
        
        if($model->save(false)) {
            
            if(isset($post['meta_value'])) {
                foreach ($post['meta_value'] as $key => $value) {
                    $service_meta = ServiceMeta::find()->where(['service_id' => $model->id]);
                    if(isset($post['meta_id'][$key]))
                        $service_meta = $service_meta->andWhere(['id' => $post['meta_id'][$key]])->one();
                    else
                        $service_meta = $service_meta->andWhere(['meta_value' => $value])->one();
                    
                    if(!$service_meta)
                        $service_meta = new ServiceMeta();
                    $service_meta->meta_key = $post['meta_key'][$key];
                    $service_meta->meta_value = $post['meta_value'][$key];
                    $service_meta->service_id = $model->id;
                    if($service_meta->meta_key == 'select')
                        $service_meta->description = $post['meta_list'][$key];
                    else
                        $service_meta->description = $post['meta_description'][$key];
                    $service_meta->save();
                }
            }

            $model->imageThumb = Functions::url() . '/' . $model->image;

            $base_service = $model->baseService;
            $base_metas = [];
            if($base_service) {
                foreach ($base_service->metas as $k => $val) {
                    if($val->meta_key == 'select')
                        $val->description = explode(',', $val->description);
                    $base_metas[$val->id] = $val;
                }
                $base_service = array_merge($base_service->attributes, [
                    'imageThumb' => $base_service->imageThumb,
                    'metas' => $base_metas]);
            }

            if($base_service) {
                $metas = [];
                foreach ($model->metas as $k => $val) {
                    if(isset($base_metas[$val->meta_key]))
                        $metas[$val->meta_key] = array_merge($val->attributes, [
                        'base_meta' => $base_metas[$val->meta_key]]);
                }
            }

            $success = array('status' => "Success", "msg" => "Service ".$id." Updated Successfully.", "data" => 
                array_merge(
                    $model->attributes,
                    ['imageThumb' => $model->imageThumb,
                    'metas' => $metas,
                    'base_service' => $base_service,
                    'gallery' => $model->gallery]
                ));
            return $success;
            
        }

        $model->validate();
        return $model;
    }

    public function actionDeleteService() {
        $id = (int)$_GET['id'];
        
        $model = Services::findOne($id);

        if(!$model)
            throw new Exception("Dữ liệu không tồn tại", 404);

        $employer = Employer::findOne($model->employer_id);

        if(!$employer)
            throw new Exception("Dữ liệu cha không tồn tại", 404);

        if(!Yii::$app->user->can('editor') && Yii::$app->user->id != $employer->user_id)
            throw new Exception("Không có quyền", 403);

        $metas = $model->metas;

        if ($model->delete()) {
            foreach ($metas as $key => $value) {
                $value->delete();
            }
            $success = array('status' => "Success", "msg" => "Successfully deleted one record.");
            return $success;
        }

        return $model;
    }

    public function actionDeleteImage() {
        $image_url = $_GET['image_url'];
        $image_url = str_replace(Functions::url() . '/', "", $image_url);
        @unlink($image_url);
        
        $success = array('status' => "Success", "msg" => "Successfully deleted one record.");
        return $success;
    }

    public function actionInsertImage() {

        $employer_id = $_POST['employer_id'];

        $model = Employer::findOne($employer_id);

        if($model) {
            if ($_FILES) {
                if($model->imageFiles = UploadedFile::getInstancesByName('gallery'))
                    $model->uploadGallery();
            }
        }

        $success = array('status' => "Success", "msg" => "Employer ".$employer_id." Updated Successfully.", "data" => ['gallery' => $model->gallery]);
        return $success;
    }

    public function actionInsertCover() {

        $employer_id = $_POST['employer_id'];

        $model = Employer::findOne($employer_id);

        if($model) {
            if ($_FILES) {
                if($model->coverFile = UploadedFile::getInstanceByName('cover'))
                    $model->uploadCover();
            }
        }

        $cover = $model->cover;

        $image_cover = '';

        if($cover)
            $image_cover = Functions::url() . '/' . $cover->meta_value;

        $success = array('status' => "Success", "msg" => "Employer ".$employer_id." Updated Successfully.", "data" => ['cover' => $image_cover]);
        return $success;
    }

    public function actionInsertLogo() {

        $employer_id = $_POST['employer_id'];

        $model = Employer::findOne($employer_id);

        if($model) {
            $model->old_logo = $model->logo;
            if ($_FILES) {
                if($model->logo = UploadedFile::getInstanceByName('logo'))
                    $model->upload();

                $model->save();
            }
        }

        $model->imageThumb = Functions::url() . '/' . $model->logo;

        $success = array('status' => "Success", "msg" => "Employer ".$employer_id." Updated Successfully.", "data" => ['logo' => $model->imageThumb]);
        return $success;
    }

    public function actionInsertReview()
    {
        $post = Yii::$app->getRequest()->getBodyParams();
        
        if($post['employer_id']) {
            $employer = Employer::findOne($post['employer_id']);
            if($employer && $post['content']) {
                
                $model = new Comment();

                if(isset($post['author']))
                    $model->title = $post['author'];
                //else
                //    $model->title = Yii::$app->user->displayName;

                if(isset($post['parent_id']))
                    $model->parent_id = $post['parent_id'];

                if(isset($post['rating']))
                    $model->description = $post['rating'];

                $model->content = $post['content'];
                $model->slug = Functions::toSlug($post['content']) . uniqid('_cmt_');

                if ($model->save()) {
                    if ($_FILES) {
                        if($model->imageFiles = UploadedFile::getInstancesByName('gallery'))
                            $model->uploadGallery();
                    }

                    $meta_comment = new EmployerMeta();
                    $meta_comment->meta_key = 'comment';
                    $meta_comment->meta_value = $model->id;
                    $meta_comment->employer_id = $post['employer_id'];
                    $meta_comment->save();

                    if(isset($post['rating'])) {
                        $meta_rating = new EmployerMeta();
                        $meta_rating->meta_key = 'rating';
                        $meta_rating->meta_value = $post['rating'];
                        $meta_rating->employer_id = $post['employer_id'];
                        $meta_rating->save();
                    }

                    $model = array_merge($model->attributes, [
                    'gallery' => $model->gallery]);

                    $success = array('status' => "Success", "msg" => "Comment Created Successfully.", "data" => $model);
                    return $success;
                }

            }
        }
    }

    public function actionInsertNews() {

        $employer_id = (int)$_POST['employer_id'];

        $employer = Employer::findOne($employer_id);

        if($employer) {

            $model = new Blog();
            // $model->user_id = Yii::$app->user->id;
            if ($model->load(Yii::$app->getRequest()->getBodyParams(), '')) {
                $model->content = $model->description;
                $model->slug = Functions::toslug($model->title) . '-' . $employer_id;
                if($model->save()) {
                    if ($_FILES) {
                        $model->image = UploadedFile::getInstanceByName('image');
                        $model->upload();

                        $model->save(false);
                    }
                    
                    $employer->updateContent($model, 'news');

                    $model->imageThumb = Functions::url() . '/' . $model->image;
                    
                    $success = array('status' => "Success", "msg" => "Blog Created Successfully.", "data" => array_merge(
                        $model->attributes,
                        [   
                            'imageThumb' => $model->imageThumb,
                            'auth' => $model->auth->profile
                        ]
                    ));
                    return $success;
                }
            }
            $model->validate();
            return $model;
        }
    }

    public function actionUpdateNews() {

        $post = Yii::$app->getRequest()->getBodyParams();

        $employer_id = (int)$post['employer_id'];

        $blog_id = (int)$post['id'];

        $employer = Employer::findOne($employer_id);

        if($employer) {

            $model = Blog::findOne($blog_id);
            $model->old_image = $model->image;
            if($model->load($post, '') && $model->validate()) {
                if ($_FILES) {
                    $model->image = UploadedFile::getInstanceByName('image');

                    if($model->image)
                        $model->upload();
                } else 
                    $model->image = $model->old_image;

                $model->save(false);
                $model->imageThumb = Functions::url() . '/' . $model->image;
                $success = array('status' => "Success", "msg" => "Blog ".$blog_id." Updated Successfully.", "data" => array_merge(
                        $model->attributes,
                        [   
                            'imageThumb' => $model->imageThumb,
                            'auth' => $model->auth->profile
                        ]
                    ));
                return $success;
                
            }

            $model->validate();
            return $model;
        }
    }

    public function actionNews() {
        $id = (int)$_GET['id'];
        $categories = Categories::find()->select('id, name, parent_id')->all();
        $tags = Tag::find()->select('id, title')->all();
        if($id > 0) {
            $model = Blog::findOne($id);
            if($model) {
                $model->tag = [];
                foreach ($model->blogMetaTag as $key => $value) {
                    $model->tag[] = (int)$value->meta_value;
                }
                return array_merge(
                    $model->attributes,
                    ['imageThumb' => $model->imageThumb, 'categories' => $categories, 'tags' => $tags, 'tag' => $model->tag]
                );
            }
        }
        $model = new Blog();
        return array_merge($model->attributes, ['imageThumb' => $model->imageThumb, 'categories' => $categories, 'tags' => $tags]) ;
    }

    public function actionDeleteNews() {
        $id = (int)$_GET['id'];
        $model = Blog::findOne($id);

        if(!Yii::$app->user->can('editor') && Yii::$app->user->id != $model->user_id)
            throw new Exception("Không có quyền", 403);

        if ($model->delete()) {
            $path  = 'uploads/' . \yii\helpers\StringHelper::basename(get_class($model)) . '/' . $model->id;
            \yii\helpers\FileHelper::removeDirectory($path);
            // Xóa tất cả meta 
            //BlogMeta::deleteAll(['post_id' => $model->id]);
            $success = array('status' => "Success", "msg" => "Successfully deleted one record.");
            return $success;
        }

        return $model;
    }

    public function actionSearchLocation() {
        $query = $_GET['q'];
        $locations = Employer::find()
            ->select('id, name, title')
            ->where(['employer_type' => 'location'])
            ->andFilterWhere([
                'or',
                ['like', 'title', $query],
                ['like', 'name', $query],
                ['like', 'address', $query],
            ])->limit(10)->all();
        return $locations;
    }

}
