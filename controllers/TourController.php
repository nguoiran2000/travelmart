<?php
namespace app\controllers;

use Yii;
use yii\filters\ContentNegotiator;
use yii\web\Response;
use yii\filters\AccessControl;
use yii\rest\Controller;
use yii\filters\auth\HttpBearerAuth;
use yii\web\UploadedFile;
use app\helper\Functions;
use app\models\tour\Tour;
use app\models\tour\TourMeta;

/**
 * Site controller
 */
class TourController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $allows = ['admin-tours', 'insert-tour'];

        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::className(),
            'only' => $allows,
        ];
        $behaviors['contentNegotiator'] = [
            'class' => ContentNegotiator::className(),
            'formats' => [
                'application/json' => Response::FORMAT_JSON,
            ],
        ];
        $behaviors['access'] = [
            'class' => AccessControl::className(),
            'only' => $allows,
            'rules' => [
                [
                    'actions' => $allows,
                    'allow' => true,
                    'roles' => ['@'],
                ],
            ],
        ];
        return $behaviors;
    }

    public function actionAdminTours()
    {
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

        $query = Tour::find();

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

    public function actionInsertTour()
    {
        $model = new Tour();
        $post = Yii::$app->getRequest()->getBodyParams();
        $model->load($post, '');
        
        if ($model->save()) {
            if(isset($post['makers']))
                foreach ($post['makers'] as $key => $value) {
                    $maker = new TourMeta();
                    $maker->meta_key = 'maker';
                    $maker->meta_value = $value['city'];
                    $maker->tour_id = $model->id;
                    if(isset($value['district']))
                        $maker->alt_text = $value['district'];
                    $maker->caption = $key+1;
                    $maker->save();
                }
            $success = array('status' => "Success", "msg" => "Blog Created Successfully.", "data" => $model);
            return $success;
        }
        $model->validate();
        return $model;
    }

    public function actionTour()
    {
        $request = Yii::$app->request;
        $id = $request->get('id', '');
        $slug = $request->get('slug', '');
        $model = Tour::findOne($id);
        if($model) {
            $metas = TourMeta::find()->where(['tour_id' => $model->id])->all();
            // foreach ($metas as $key => $value) {
            //     $metas[$key] = 
            // }
            return array_merge($model->attributes, [
                    'metas' => $metas,
                    'gallery' => $model->gallery,
                    'city' => Functions::cityAll()]);
        } 

        return false;
    }

    public function actionInsertImage() {

        $tour_id = $_POST['tour_id'];

        $model = Tour::findOne($tour_id);

        if($model) {
            if ($_FILES) {
                if($model->imageFiles = UploadedFile::getInstancesByName('gallery'))
                    $model->uploadGallery();
            }
        }

        $success = array('status' => "Success", "msg" => "Tour ".$tour_id." Updated Successfully.", "data" => ['gallery' => $model->gallery]);
        return $success;
    }

    public function actionDeleteImage() {
        $image_url = $_GET['image_url'];
        $image_url = str_replace(Functions::url() . '/', "", $image_url);
        @unlink($image_url);
        
        $success = array('status' => "Success", "msg" => "Successfully deleted one record.");
        return $success;
    }

}
