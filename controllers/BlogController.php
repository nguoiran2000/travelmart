<?php
namespace app\controllers;

use Yii;
use yii\filters\ContentNegotiator;
use yii\web\Response;
use yii\filters\AccessControl;
use yii\rest\Controller;
use yii\filters\auth\HttpBearerAuth;
use yii\web\UploadedFile;
use app\models\blog\Blog;
use app\models\blog\Categories;
use app\helper\Functions;
use app\models\blog\Tag;
use app\models\blog\BlogMeta;

/**
 * Site controller
 */
class BlogController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::className(),
            'only' => ['insert-blog', 'admin-blogs', 'admin-blog', 'update-blog', 'insert-blog', 'status-blog', 'delete-blog'],
        ];
        $behaviors['contentNegotiator'] = [
            'class' => ContentNegotiator::className(),
            'formats' => [
                'application/json' => Response::FORMAT_JSON,
            ],
        ];
        $behaviors['access'] = [
            'class' => AccessControl::className(),
            'only' => ['insert-blog', 'admin-blogs', 'admin-blog', 'update-blog', 'insert-blog', 'status-blog', 'delete-blog'],
            'rules' => [
                [
                    'actions' => ['insert-blog', 'admin-blogs', 'admin-blog', 'update-blog', 'insert-blog', 'delete-blog'],
                    'allow' => true,
                    'roles' => ['writer'],
                ],
                [
                    'actions' => ['status-blog'],
                    'allow' => true,
                    'roles' => ['editor'],
                ],
            ],
        ];
        return $behaviors;
    }

    // Blog controller Yii2

    public function actionAdminBlogs() {
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

        $query = Blog::find()->select('posts.*, t.name')->joinWith('category t');

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
                'auth' => ($value->auth)?$value->auth->displayName:'',
                'tags' => $value->tags,
                ]);
        }

        $categories = Categories::find()->select('id, name, parent_id')->all();

        return ['totalCount' => $totalCount, 'response' => $response, 'categories' => $categories];
    }

    public function actionAdminBlog() {
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
                    ['imageThumb' => $model->imageThumb, 'categories' => $categories, 'tags' => $tags, 'tags' => $model->tags]
                );
            }
        }
        $model = new Blog();
        return array_merge($model->attributes, ['imageThumb' => $model->imageThumb, 'categories' => $categories, 'tags' => $tags]) ;
    }

    public function actionBlog() {
        $slug = $_GET['slug'];
        $model = Blog::find()->where(['slug' => $slug])->one();
        if($model) {

            // Đếm lượt xem theo IP
            $ip = !empty($_SERVER['HTTP_CLIENT_IP']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
            if($model->last_ip != $ip) {
                $model->view_count += 1;
                $model->last_ip = $ip;
                $model->save();
            }

            $top_views = Blog::find()->limit(5)->orderBy('view_count DESC')->all();
            $newsRelated = Blog::find()->where(['category_id' => $model->category_id])->limit(10)->orderBy('updated_at DESC')->all();
            foreach ($newsRelated as $key => $value) {
                $newsRelated[$key] = array_merge($value->attributes,[
                    'imageThumb' => $value->imageThumb,
                    'auth' => $value->auth->profile
                    ]);
            }

            return array_merge(
                $model->attributes,
                [   
                    'imageThumb' => $model->imageThumb, 
                    'tags' => $model->tags, 
                    'auth' => $model->auth->profile, 
                    'topViews' => $top_views,
                    'tags' => $model->tags,
                    'newsRelated' => $newsRelated,
                    'likes' => $model->likes
                ]
            );
        }

        return false;
    }

    public function actionInsertBlog() {
        $model = new Blog();
        // $model->user_id = Yii::$app->user->id;
        if ($model->load(Yii::$app->getRequest()->getBodyParams(), '') && $model->save()) {
            if ($_FILES) {
                $model->image = UploadedFile::getInstanceByName('image');
                $model->upload();                
            }

            if(!Yii::$app->user->can('editor'))
                    $model->status = 0;

            $model->save(false);

            if ($model->tag) {
                $model->tag = explode(',', $model->tag);
                foreach ($model->tag as $value) {
                    $new_tag = new BlogMeta();
                    $new_tag->post_id = $model->id;
                    $new_tag->meta_key = 'tag';
                    $new_tag->meta_value = $value;
                    $new_tag->save();
                }
            }

            $success = array('status' => "Success", "msg" => "Blog Created Successfully.", "data" => array_merge($model->attributes, [
                'imageThumb' => $model->imageThumb, 
                'category' => $model->category,
                'auth' => $model->auth->displayName,
                'tags' => $model->tags,
                ]));
            return $success;
        }
        $model->validate();
        return $model;
    }

    public function actionUpdateBlog() {
        $post = Yii::$app->getRequest()->getBodyParams();
        $id = (int)$post['id'];
        $model = Blog::findOne($id);

        if(!$model)
            throw new Exception("Không có dữ liệu", 404);

        if(!Yii::$app->user->can('editor') && Yii::$app->user->id != $model->user_id)
            throw new Exception("Không có quyền", 403);

        $model->old_image = $model->image;
        if($model->load($post, '') && $model->validate()) {
            if ($_FILES) {
                $model->image = UploadedFile::getInstanceByName('image');
                $model->upload();
            } else
                $model->image = $model->old_image;

            if ($model->tag) {
                $model->tag = explode(',', $model->tag);
                // Xóa tag cũ 
                BlogMeta::deleteAll(['post_id' => $model->id, 'meta_key' => 'tag']);
                foreach ($model->tag as $value) {
                    $new_tag = new BlogMeta();
                    $new_tag->post_id = $model->id;
                    $new_tag->meta_key = 'tag';
                    $new_tag->meta_value = $value;
                    $new_tag->save();
                }
            }

            $model->save(false);
            $success = array('status' => "Success", "msg" => "Blog ".$id." Updated Successfully.", "data" => array_merge($model->attributes, [
                'imageThumb' => $model->imageThumb, 
                'category' => $model->category,
                'auth' => $model->auth->displayName,
                'tags' => $model->tags,
                ]));
            return $success;
            
        }

        $model->validate();
        return $model;
    }

    public function actionStatusBlog() {
        $post = Yii::$app->getRequest()->getBodyParams();
        $id = (int)$post['id'];
        $status = $post['status'];
        $model = Blog::findOne($id);
        if ($model) {
            $model->status = $status;
            if($model->save(false)) {
                $success = array('status' => "Success", "msg" => "Blog ".$id." Updated Successfully." . $model->status, "data" => $model);
                return $success;
            }
        }
        return [];
    }

    public function actionDeleteBlog() {
        $id = (int)$_GET['id'];
        $model = Blog::findOne($id);

        if(!Yii::$app->user->can('editor') && Yii::$app->user->id != $model->user_id)
            throw new Exception("Không có quyền", 403);

        if ($model->delete()) {
            $path  = 'uploads/' . \yii\helpers\StringHelper::basename(get_class($model)) . '/' . $model->id;
            \yii\helpers\FileHelper::removeDirectory($path);
            // Xóa tất cả meta 
            BlogMeta::deleteAll(['post_id' => $model->id]);
            $success = array('status' => "Success", "msg" => "Successfully deleted one record.");
            return $success;
        }

        return $model;
    }

    public function actionSearchTag() {
        $query = $_GET['q'];
        $tags = Tag::find()
            ->select('id, title, description')
            ->where(['status' => 1, 'post_type' => 'tag'])
            ->andFilterWhere([
                'or',
                ['like', 'title', $query],
                ['like', 'description', $query],
                ['like', 'content', $query],
            ])->limit(10)->all();
        return $tags;
    }

    public function actionBlogPage() {

        $request = Yii::$app->request;
        $pagenum = $request->get('page', '');
        $pagesize = $request->get('size', '');
        $offset = ($pagenum - 1) * $pagesize;
        $search = $request->get('search', '');
        $sortKey = $request->get('sortKey', '');
        $reverse = $request->get('reverse', '');

        $select = 'posts.id, posts.slug, posts.title, posts.description, posts.view_count, posts.image, t.id as category_id, t.name';

        if($pagesize == 0)
            $pagesize = 20;

        if($sortKey != "") {
            if($reverse == 'true')
                $order = $sortKey . ' desc';
            else
                $order = $sortKey . ' asc';
        } else {
            $order = 'posts.updated_at DESC';
        }

        $query = Blog::find()->select($select)->joinWith('category t')->where(['post_type' => 'blog']);

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

        $totalCount = $query->count();

        $not_in = [];

        $hot_blog = Blog::find()->select($select)
            ->joinWith('category t')
            ->where(['post_type' => 'blog', 'hot' => 1])->orderBy('posts.updated_at DESC')->limit(5)->all();

        foreach ($hot_blog as $key => $value) {
            $hot_blog[$key] = array_merge($value->attributes, [
                'imageThumb' => $value->imageThumb
                ]);
            $not_in[$value->id] = $value->id;
        }

        $top_blog = Blog::find()->select($select)
            ->joinWith('category t')
            ->where(['post_type' => 'blog'])->orderBy('posts.view_count DESC')->limit(10)->all();

        foreach ($top_blog as $key => $value) {
            $top_blog[$key] = array_merge($value->attributes, [
                'imageThumb' => $value->imageThumb
                ]);
            $not_in[$value->id] = $value->id;
        }

        $tags = Blog::find()->select('id, slug, title')
            ->where(['post_type' => 'tag'])->orderBy('updated_at DESC')->limit(10)->all();

        $query = $query->where(['not in', 'posts.id', $not_in]);

        $blogs = $query->limit($pagesize)->offset($offset)->orderBy($order);

        $blogs = $blogs->all();
        
        foreach ($blogs as $key => $value) {
            $blogs[$key] = array_merge($value->attributes, [
                'imageThumb' => $value->imageThumb
                ]);
        }

        $categories = Categories::find()->select('id, name, parent_id')->where(['status' => 1])->all();

        return [
                'blogs' => $blogs,
                'hot_blog' => $hot_blog,
                'categories' => $categories,
                'top_blog' => $top_blog,
                'tags' => $tags];
    }

}
