<?php
namespace app\modules\admin\controllers;

use Yii;
use amnah\yii2\user\models\forms\LoginForm;
use app\models\ContactForm;
use app\models\SignupForm;
use yii\filters\ContentNegotiator;
use yii\web\Response;
use yii\filters\AccessControl;
use yii\rest\Controller;
use yii\filters\auth\HttpBearerAuth;
use app\models\blog\Blog;
use app\models\blog\Categories;

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
        ];
        $behaviors['contentNegotiator'] = [
            'class' => ContentNegotiator::className(),
            'formats' => [
                'application/json' => Response::FORMAT_JSON,
            ],
        ];
        $behaviors['access'] = [
            'class' => AccessControl::className(),
            'rules' => [
                [
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

    public function actionCategory()
    {
        $request = Yii::$app->request;
        $pagenum = $request->get('page', '');
        $pagesize = $request->get('size', '');
        $offset = ($pagenum - 1) * $pagesize;
        $search = $request->get('search', '');
        $sortKey = $request->get('sortKey', '');
        $reverse = $request->get('reverse', '');

        if ($search != "") {
            $where = ['like', 'name', $search];
        } else {
            $where = [];
        }

        if($sortKey != "") {
            if($reverse == 'true')
                $order = $sortKey . ' asc';
            else
                $order = $sortKey . ' desc';
        } else {
            $order = 'id DESC';
        }

        $query = Categories::find()->where($where);

        $totalCount = $query->count();

        $response = Categories::find()->where($where)->limit($pagesize)->offset($offset)->orderBy($order)->all();

        return ['totalCount' => $totalCount, 'response' => $response];
    }
}
