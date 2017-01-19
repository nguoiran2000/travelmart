<?php

namespace app\modules\admin\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;

/**
 * Site controller
 */
class DefaultController extends Controller
{
    /**
     * @inheritdoc
     */
     public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    public function actionIndex()
    {
        return $this->renderContent(null);
    }
}