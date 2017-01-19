<?php
namespace app\controllers;

use Yii;
use amnah\yii2\user\models\forms\LoginForm;
use app\models\ContactForm;
use app\models\SignupForm;
use yii\filters\ContentNegotiator;
use yii\web\Response;
use yii\filters\AccessControl;
use yii\rest\Controller;
use yii\filters\auth\HttpBearerAuth;
use yii\web\UploadedFile;
use app\models\blog\Blog;
use app\helper\Functions;
use app\models\employer\Employer;

/**
 * Site controller
 */
class ApiController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::className(),
            'only' => ['dashboard', 'insert-blog', 'admin-blogs'],
        ];
        $behaviors['contentNegotiator'] = [
            'class' => ContentNegotiator::className(),
            'formats' => [
                'application/json' => Response::FORMAT_JSON,
            ],
        ];
        $behaviors['access'] = [
            'class' => AccessControl::className(),
            'only' => ['dashboard', 'insert-blog', 'admin-blogs'],
            'rules' => [
                [
                    'actions' => ['dashboard'],
                    'allow' => true,
                    'roles' => ['@'],
                ],
                [
                    'actions' => ['dashboard', 'insert-blog', 'admin-blogs'],
                    'allow' => true,
                    'roles' => ['admin'],
                ],
            ],
        ];
        return $behaviors;
    }

    public function actionLogin()
    {
        $model = new LoginForm();

        if ($model->load(Yii::$app->getRequest()->getBodyParams(), '') && $model->login()) {
            return ['access_token' => Yii::$app->user->identity->getAuthKey()];
        } else {
            $model->validate();
            return $model;
        }
    }

    public function actionDashboard()
    {
        $response = [
            'username' => Yii::$app->user->identity->username,
            'access_token' => Yii::$app->user->identity->getAuthKey(),
        ];

        return $response;
    }

    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->getRequest()->getBodyParams(), '') && $model->validate()) {
            if ($model->sendEmail(Yii::$app->params['adminEmail'])) {
                $response = [
                    'flash' => [
                        'class' => 'success',
                        'message' => 'Thank you for contacting us. We will respond to you as soon as possible.',
                    ]
                ];
            } else {
                $response = [
                    'flash' => [
                        'class' => 'error',
                        'message' => 'There was an error sending email.',
                    ]
                ];
            }
            return $response;
        } else {
            $model->validate();
            return $model;
        }
    }

    public function actionSignup()
    {
        $model = new SignupForm();

        if ($model->load(Yii::$app->getRequest()->getBodyParams(), '') && $user = $model->signup()) {
            if(Yii::$app->getUser()->login($user))
                return ['access_token' => Yii::$app->user->identity->getAuthKey()];
        }
        $model->validate();
        return $model;
    }

    public function actionCustomers() {
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
            $order = 'customerNumber DESC';
        }

        $query = \app\models\Customers::find();

        if ($search != "") {
            $search = json_decode($search, true);
            foreach ($search as $key => $value) {
                $where = ['like', $key, $value];
                $query = $query->where($where);
            }            
        } else {
            $where = [];
        }

        $totalCount = $query->count();

        $response = $query->limit($pagesize)->offset($offset)->orderBy($order)->all();

        return ['totalCount' => $totalCount, 'response' => $response];
    }

    public function actionCustomer() {
        $id = (int)$_GET['id'];
        if($id > 0) {
            $models = \app\models\Customers::find()->where(['customerNumber' => $id])->one();
            if($models)
                return array_merge(
                    $models->attributes,
                    ['imageThumb' => $models->imageThumb]
                );
        }
        return ['data' => new \app\models\Customers()];
    }

    public function actionInsertCustomer() {
        $model = new \app\models\Customers();
        if ($model->load(Yii::$app->getRequest()->getBodyParams(), '') && $model->save()) {
            if ($_FILES) {
                $model->image = UploadedFile::getInstanceByName('image');
                $model->upload();
                $model->save(false);
            }
            $success = array('status' => "Success", "msg" => "Customer Created Successfully.", "data" => $model);
            return $success;
        }
        $model->validate();
        return $model;
    }

    public function actionUpdateCustomer() {
        $post = Yii::$app->getRequest()->getBodyParams();
        $id = (int)$post['id'];
        $model = \app\models\Customers::findOne($id);
        $model->old_image = $model->image;
        if($model->load($post, '')) {
            if ($_FILES) {
                
                $model->image = UploadedFile::getInstanceByName('image');
                $model->upload();
            }
            $model->save(false);
            $success = array('status' => "Success", "msg" => "Customer ".$id." Updated Successfully.", "data" => $model);
            return $success;
            
        }

        $model->validate();
        return $model;
    }

    public function actionStatusCustomer() {
        $post = Yii::$app->getRequest()->getBodyParams();
        $id = (int)$post['id'];
        $status = $post['status'];
        $model = \app\models\Customers::findOne($id);
        if ($model) {
            $model->status = $status;
            $model->save();
            $success = array('status' => "Success", "msg" => "Customer ".$id." Updated Successfully.", "data" => $model);
            return $success;
        }

        return [];
    }

    public function actionDeleteCustomer() {
        $id = (int)$_GET['id'];
        $model = \app\models\Customers::findOne($id);
        if ($model->delete()) {
            $path  = 'uploads/' . \yii\helpers\StringHelper::basename(get_class($model)) . '/' . $model->customerNumber;
            \yii\helpers\FileHelper::removeDirectory($path);
            $success = array('status' => "Success", "msg" => "Successfully deleted one record.");
            return $success;
        }
        $model->validate();
        return $model;
    }

    public function actionCity()
    {
        return Functions::cityAll();
    }

    public function actionHome()
    {

        $provinces = Functions::cityAll();

        // Slideshow
        $slideshow = Blog::find()->select('id, slug, title, image, description, view_count')->where(['status' => 1, 'post_type' => 'slideshow'])->orderBy('updated_at DESC')->limit(10)->all();
        foreach ($slideshow as $key => $value) {
            $slideshow[$key] = array_merge($value->attributes, [
                'imageThumb' => $value->imageThumb
                ]);
        }

        // Blogs 
        $connection = Yii::$app->getDb();
        $blogs_sql = 'SELECT * FROM (
                SELECT @row_number := IF(@category_id = category_id, @row_number + 1, 1) AS row_number, @category_id:=category_id as row_category_id, t.* 
                FROM (
                    SELECT posts.id, posts.slug, posts.title, posts.image, posts.description, posts.updated_at, posts.view_count, categories.name, categories.slug as category_slug, category_id, categories.description as category_description, categories.name as category_name
                    FROM posts INNER JOIN categories ON posts.category_id = categories.id
                    WHERE posts.status = 1 AND posts.post_type = "blog" AND categories.hot = 1 AND categories.status = 1
                    ORDER BY posts.category_id DESC, posts.updated_at DESC LIMIT 10000000
                ) t 
                CROSS JOIN (SELECT @category_id:=0, @row_number :=0)  AS dummy
            ) AS V 
            WHERE row_number <= 10';
        $command = $connection->createCommand($blogs_sql);
        $blogs = $command->queryAll();

        $category_blog = [];
        foreach ($blogs as $key => $value) {
            if(!isset($category_blog[$value['category_id']])) {
                $category_blog[$value['category_id']] = [
                    'slug' => $value['category_slug'],
                    'name' => $value['category_name'],
                    'description' => $value['category_description']];
            }
            $category_blog[$value['category_id']]['blogs'][] =  array_merge($value, 
                    ['imageThumb' => Functions::url() . '/' . $value['image']]);
        }

        // Employer New
        $employers = Employer::find()->select('employer.id, employer.slug, employer.title, employer.logo, employer.description, employer.address, view_count')
            //->leftJoin('services s', '`s`.`employer_id` = `employer`.`id`')
            ->where(['employer.status' => 1, 'employer_type' => 'employer'])
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

        // Employer Vip
        $employer_vip = Employer::find()->select('employer.id, employer.slug, employer.title, employer.logo, employer.description, employer.address, view_count')
            //->leftJoin('services s', '`s`.`employer_id` = `employer`.`id`')
            ->where(['employer.status' => 1, 'employer_type' => 'employer'])
            ->orderBy('employer.vip DESC, employer.updated_at DESC')->limit(18)->all();

        foreach ($employer_vip as $key => $value) {
            $services = [];
            $color = '';
            $price = 0;
            $city = isset($provinces[$value->city_id])?$provinces[$value->city_id]['name']:'Toàn quốc';
            foreach ($value->services as $k => $val) {
                if($price > $val['price'] || $price == 0) {
                    $price = $val['price'];
                    $color = $val['base_service']['color'];
                }
                $services[] = $val;
            }
            $employer_vip[$key] =  array_merge($value->attributes, 
                ['imageThumb' => $value->imageThumb,
                'services' => $value->services,
                'price' => number_format($price),
                'color' => $color,
                'city' => $city ]);
        }

        // Location
        $locations = Employer::find()->select('employer.id, employer.slug, employer.title, employer.logo, employer.description, employer.address, view_count')
            //->leftJoin('services s', '`s`.`employer_id` = `employer`.`id`')
            ->where(['employer.status' => 1, 'employer_type' => 'location'])
            ->orderBy('employer.updated_at DESC')->limit(12)->all();

        foreach ($locations as $key => $value) {
            $locations[$key] =  array_merge($value->attributes, 
                ['imageThumb' => $value->imageThumb]);
        }

        $city = Functions::cityAll();

        return ['slideshow' => $slideshow, 
            'category_blog' => $category_blog,
            'locations' => $locations,
            'employers' => $employers,
            'employer_vip' => $employer_vip,
            'city' => $city];
    }

    public function actionSearchEmployer()
    {
        $post = Yii::$app->getRequest()->getBodyParams();

        $request = Yii::$app->request;
        $pagenum = $request->get('page', '');
        $pagesize = $request->get('size', '');
        $offset = ($pagenum - 1) * $pagesize;
        $search = $request->get('search', '');

        if($pagesize == "")
            $pagesize = 12;

        $order = 'employer.updated_at DESC';

        $query = Employer::find()->select('employer.id, employer.slug, employer.title, employer.logo, employer.description, employer.address, s.price, view_count')
            ->innerJoin('services s', '`s`.`employer_id` = `employer`.`id`')
            ->where(['employer.status' => 1, 'employer_type' => 'employer']);

        if ($search != "") {
            $search = json_decode($search, true);
            
            foreach ($search as $key => $value) {
                switch ($key) {
                    case 'sort':
                        if($value == 'price_asc') {
                            $query = $query->andWhere('s.price = (SELECT MIN(price) FROM services WHERE employer_id = `employer`.id)');
                            $order = 's.price ASC, ' . $order;
                        }
                        elseif($value == 'price_desc') {
                            $query = $query->andWhere('s.price = (SELECT MAX(price) FROM services WHERE employer_id = `employer`.id)');
                            $order = 's.price DESC, ' . $order;
                        } elseif ($value == 'view_count') {
                            $order = 'employer.view_count DESC, ' . $order;
                        }
                        break;
                    
                    default:
                        if(is_array($value)) {
                            $flip_array = [];
                            foreach($value as $k=>$val) { $flip_array[$val] = $val; }
                            $value = $flip_array;
                        }
                        if($value) {
                            $where = ['employer.'.$key => $value];
                            $query = $query->where($where);
                        }
                        break;
                }
            }
        }

        $totalCount = $query->count();

        $query = $query->limit($pagesize)->offset($offset)->orderBy($order);
        // var_dump($query->prepare(Yii::$app->db->queryBuilder)->createCommand()->rawSql);
        $response = $query->all();

        foreach ($response as $key => $value) {
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
            $response[$key] =  array_merge($value->attributes, 
                ['imageThumb' => $value->imageThumb,
                'services' => $value->services,
                'price' => number_format($value->price?$value->price:$price),
                'color' => $color]);
        }

        return $response;
    }

}
