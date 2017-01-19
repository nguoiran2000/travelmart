<?php
namespace app\controllers;

use Yii;
use yii\filters\ContentNegotiator;
use yii\web\Response;
use yii\filters\AccessControl;
use yii\rest\Controller;
use yii\filters\auth\HttpBearerAuth;
use app\models\blog\Blog;
use app\models\blog\Comment;
use app\helper\Functions;
use app\models\blog\BlogMeta;

/**
 * Site controller
 */
class CommentController extends Controller
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
                    'actions' => ['dashboard', 'insert-blog', 'admin-blogs'],
                    'allow' => true,
                    'roles' => ['admin'],
                ],
            ],
        ];
        return $behaviors;
    }

    public function actionComments() {
        $blog_id = (int)$_GET['blog_id'];
        $blog = Blog::findOne($blog_id);
        if($blog) {
            $comments =  $blog->comments;

            foreach ($comments as $key => $value) {
                $comments[$key] = array_merge($value->attributes, ['likes' => $value->likes]);
            }
            return $comments;
        }
        return false;
    }

    public function actionInsertComment() {
        $model = new Comment();
        $post = Yii::$app->getRequest()->getBodyParams();
        if(isset($post['author']))
            $model->title = $post['author'];
        else
            $model->title = Yii::$app->user->displayName;
        if(isset($post['comment_id']))
            $model->parent_id = $post['comment_id'];
        $model->content = $post['text'];
        $model->slug = Functions::toSlug($post['text']) . uniqid('_cmt_');
        if ($model->save()) {
            $meta_comment = new BlogMeta();
            $meta_comment->meta_key = 'comment';
            $meta_comment->meta_value = $model->id;
            $meta_comment->post_id = $post['blog_id'];
            $meta_comment->save();
            $success = array('status' => "Success", "msg" => "Comment Created Successfully.", "data" => $model);
            return $success;
        }
        $model->validate();
        return $model;
    }

    public function actionDeleteComment() {
        $id = (int)$_GET['id'];
        $model = Comment::findOne($id);

        //if(!Yii::$app->user->can('editor') && Yii::$app->user->id != $model->user_id)
        //    throw new Exception("Không có quyền", 403);

        if ($model->delete()) {
            // Xóa tất cả meta 
            BlogMeta::deleteAll(['post_id' => $model->id]);
            $success = array('status' => "Success", "msg" => "Successfully deleted one record.");
            return $success;
        }

        return $model;
    }

}
