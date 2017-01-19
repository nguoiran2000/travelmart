<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\assets;

use yii\web\AssetBundle;
use yii\web\View;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class AdminAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/site.css',
        'css/loading-bar.css',
    ];
    public $js = [
        'js/admin/admin.js',
        'js/admin/adminControllers.js',
        'scripts/angular-slugify/angular-slugify.js',
        'scripts/angular-animate.min.js',
        'scripts/angular-sanitize.min.js',
        'scripts/angular-carousel/dist/angular-carousel.min.js',
        'scripts/angular-ui-router.min.js',
        'scripts/angular-material-fileinput/dist/lf-ng-md-file-input.min.js'
    ];
    public $depends = [
        'app\assets\AngularAsset',
    ];
    public $jsOptions = [
        'position' => View::POS_HEAD,
    ];
}
