<?php
namespace app\controllers;

use Yii;
use yii\filters\ContentNegotiator;
use yii\web\Response;
use yii\filters\AccessControl;
use yii\rest\Controller;
use yii\filters\auth\HttpBearerAuth;
use yii\web\UploadedFile;
use app\models\blog\Tag;
use app\models\blog\Categories;
use app\helper\Functions;

/**
 * Site controller
 */
class TagController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::className(),
            'only' => ['insert-tag', 'admin-tags', 'admin-tag', 'update-tag', 'insert-tag', 'status-tag', 'delete-tag'],
        ];
        $behaviors['contentNegotiator'] = [
            'class' => ContentNegotiator::className(),
            'formats' => [
                'application/json' => Response::FORMAT_JSON,
            ],
        ];
        $behaviors['access'] = [
            'class' => AccessControl::className(),
            'only' => ['insert-tag', 'admin-tags', 'admin-tag', 'update-tag', 'insert-tag', 'status-tag', 'delete-tag'],
            'rules' => [
                [
                    'actions' => ['insert-tag', 'admin-tags', 'admin-tag', 'update-tag', 'insert-tag', 'delete-tag'],
                    'allow' => true,
                    'roles' => ['writer'],
                ],
                [
                    'actions' => ['status-tag'],
                    'allow' => true,
                    'roles' => ['editor'],
                ],
            ],
        ];
        return $behaviors;
    }

    // Tag controller Yii2

    public function actionAdminTags() {
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

        $query = Tag::find()->select('posts.*, t.name')->joinWith('category t');

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

        if(!Yii::$app->user->can('editor')) {
            $query = $query->andWhere(['user_id' => Yii::$app->user->id]);
        }

        $totalCount = $query->count();

        $response = $query->limit($pagesize)->offset($offset)->orderBy($order)->all();

        foreach ($response as $key => $value) {
            $response[$key] = array_merge($value->attributes, [
                'imageThumb' => $value->imageThumb, 
                'category' => $value->category,
                'auth' => $value->auth->displayName]);
        }

        $categories = Categories::find()->select('id, name, parent_id')->all();

        return ['totalCount' => $totalCount, 'response' => $response, 'categories' => $categories];
    }

    public function actionAdminTag() {
        $id = (int)$_GET['id'];
        $categories = Categories::find()->select('id, name, parent_id')->all();
        if($id > 0) {
            $model = Tag::findOne($id);
            if($model)
                return array_merge(
                    $model->attributes,
                    ['imageThumb' => $model->imageThumb, 'categories' => $categories]
                );
        }
        $model = new Tag();
        return array_merge($model->attributes, ['imageThumb' => $model->imageThumb, 'categories' => $categories]) ;
    }

    public function actionInsertTag() {
        $model = new Tag();
        // $model->user_id = Yii::$app->user->id;
        if ($model->load(Yii::$app->getRequest()->getBodyParams(), '') && $model->save()) {
            if ($_FILES) {
                $model->image = UploadedFile::getInstanceByName('image');
                $model->upload();

                if(!Yii::$app->user->can('editor'))
                    $model->status = 0;

                $model->save(false);
            }
            $success = array('status' => "Success", "msg" => "Tag Created Successfully.", "data" => $model);
            return $success;
        }
        $model->validate();
        return $model;
    }

    public function actionUpdateTag() {
        $post = Yii::$app->getRequest()->getBodyParams();
        $id = (int)$post['id'];
        $model = Tag::findOne($id);

        if(!$model)
            throw new Exception("Không có dữ liệu", 404);

        if(!Yii::$app->user->can('editor') && Yii::$app->user->id != $model->user_id)
            throw new Exception("Không có quyền", 403);

        $model->old_image = $model->image;
        if($model->load($post, '')) {
            if ($_FILES) {
                $model->image = UploadedFile::getInstanceByName('image');
                $model->upload();
            }
            $model->save(false);
            $success = array('status' => "Success", "msg" => "Tag ".$id." Updated Successfully.", "data" => $model);
            return $success;
            
        }

        $model->validate();
        return $model;
    }

    public function actionStatusTag() {
        $post = Yii::$app->getRequest()->getBodyParams();
        $id = (int)$post['id'];
        $status = $post['status'];
        $model = Tag::findOne($id);
        if ($model) {
            $model->status = $status;
            if($model->save(false)) {
                $success = array('status' => "Success", "msg" => "Tag ".$id." Updated Successfully." . $model->status, "data" => $model);
                return $success;
            }
        }

        return [];
    }

    public function actionDeleteTag() {
        $id = (int)$_GET['id'];
        $model = Tag::findOne($id);

        if(!Yii::$app->user->can('editor') && Yii::$app->user->id != $model->user_id)
            throw new Exception("Không có quyền", 403);

        if ($model->delete()) {
            $path  = 'uploads/' . \yii\helpers\StringHelper::basename(get_class($model)) . '/' . $model->id;
            \yii\helpers\FileHelper::removeDirectory($path);
            $success = array('status' => "Success", "msg" => "Successfully deleted one record.");
            return $success;
        }
        $model->validate();
        return $model;
    }
}
