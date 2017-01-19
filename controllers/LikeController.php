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
use app\models\blog\BlogMeta;

/**
 * Site controller
 */
class LikeController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::className(),
            'only' => ['like'],
        ];
        $behaviors['contentNegotiator'] = [
            'class' => ContentNegotiator::className(),
            'formats' => [
                'application/json' => Response::FORMAT_JSON,
            ],
        ];
        $behaviors['access'] = [
            'class' => AccessControl::className(),
            'only' => ['like'],
            'rules' => [
                [
                    'actions' => ['like'],
                    'allow' => true,
                    'roles' => ['@'],
                ],
            ],
        ];
        return $behaviors;
    }

    public function actionLike() {
        $user_id = Yii::$app->user->id;
        $post_id = $_GET['post_id'];
        $blog_meta = BlogMeta::find()->where([
            'meta_key' => 'like', 
            'meta_value' => $user_id,
            'post_id' => $post_id
            ])->one();
        if($blog_meta) {
            // nếu đã có thì xóa
            $blog_meta->delete();
        } else {
            $blog_meta = new BlogMeta();
            $blog_meta->meta_key = 'like';
            $blog_meta->meta_value = $user_id;
            $blog_meta->post_id = $post_id;
            $blog_meta->save();
        }
        return BlogMeta::find()->where([
            'meta_key' => 'like',
            'post_id' => $post_id
            ])->all();
    }

}
