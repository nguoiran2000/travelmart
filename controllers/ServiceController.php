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
use app\models\employer\AdminServices;
use app\models\employer\ServiceMeta;

/**
 * Site controller
 */
class ServiceController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::className(),
            'only' => ['dashboard', 'insert-service', 'admin-services'],
        ];
        $behaviors['contentNegotiator'] = [
            'class' => ContentNegotiator::className(),
            'formats' => [
                'application/json' => Response::FORMAT_JSON,
            ],
        ];
        $behaviors['access'] = [
            'class' => AccessControl::className(),
            'only' => ['dashboard', 'insert-service', 'admin-services'],
            'rules' => [
                [
                    'actions' => ['dashboard', 'insert-service', 'admin-services'],
                    'allow' => true,
                    'roles' => ['admin'],
                ],
            ],
        ];
        return $behaviors;
    }

    public function actionServices() {
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
            $order = 'created_at DESC';
        }

        $query = AdminServices::find();

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

        foreach ($response as $key => $value) {
            $response[$key] = array_merge($value->attributes,
                    ['imageThumb' => $value->imageThumb,
                    'metas' => $value->metas]);
        }

        return ['totalCount' => $totalCount, 'response' => $response];
    }

    public function actionService() {
        $id = (int)$_GET['id'];
        if($id > 0) {
            $models = AdminServices::findOne($id);
            if($models)
                return array_merge(
                    $models->attributes,
                    ['imageThumb' => $models->imageThumb]
                );
        }
        return ['data' => new AdminServices()];
    }

    public function actionInsertService() {
        $model = new AdminServices();
        $post = Yii::$app->getRequest()->getBodyParams();
        if ($model->load($post, '') && $model->save()) {
            if ($_FILES) {
                $model->image = UploadedFile::getInstanceByName('image');
                $model->upload();
                $model->save(false);
            }

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
            $success = array('status' => "Success", "msg" => "Service Created Successfully.", "data" => array_merge(
                    $model->attributes,
                    ['imageThumb' => $model->imageThumb,
                    'metas' => $model->metas]
                ));
            return $success;
        }
        $model->validate();
        return $model;
    }

    public function actionUpdateService() {
        $post = Yii::$app->getRequest()->getBodyParams();
        $id = (int)$post['id'];
        $model = AdminServices::findOne($id);
        $model->old_image = $model->image;
        if($model->load($post, '')) {

            if ($_FILES) {
                $model->image = UploadedFile::getInstanceByName('image');
                $model->upload();
            } else
                $model->image = $model->old_image;

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
            }

            $model->imageThumb = Functions::url() . '/' . $model->image;
            $success = array('status' => "Success", "msg" => "Service ".$id." Updated Successfully.", "data" => array_merge(
                    $model->attributes,
                    ['imageThumb' => $model->imageThumb,
                    'metas' => $model->metas]
                ));
            return $success;
            
        }

        $model->validate();
        return $model;
    }

    public function actionStatusService() {
        $post = Yii::$app->getRequest()->getBodyParams();
        $id = (int)$post['id'];
        $status = $post['status'];
        $model = AdminServices::findOne($id);
        if ($model) {
            $model->status = $status;
            $model->save();
            $success = array('status' => "Success", "msg" => "Service ".$id." Updated Successfully.", "data" => $model);
            return $success;
        }

        return [];
    }

    public function actionDeleteService() {
        $id = (int)$_GET['id'];
        $model = AdminServices::findOne($id);
        if ($model->delete()) {
            $path  = 'uploads/' . \yii\helpers\StringHelper::basename(get_class($model)) . '/' . $model->id;
            \yii\helpers\FileHelper::removeDirectory($path);
            $success = array('status' => "Success", "msg" => "Successfully deleted one record.");
            return $success;
        }
        $model->validate();
        return $model;
    }

    public function actionDeleteMeta() {
        $id = (int)$_GET['id'];
        $model = ServiceMeta::findOne($id);
        if ($model->delete()) {
            $success = array('status' => "Success", "msg" => "Successfully deleted one record.");
            return $success;
        }
        $model->validate();
        return $model;
    }
}
