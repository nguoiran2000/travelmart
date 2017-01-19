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
class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'scripts/font-awesome.css',
        'scripts/angular-material/angular-material.min.css',
        'scripts/angular-carousel/dist/angular-carousel.min.css',
        'scripts/angular-jk-rating-stars/jk-rating-stars.min.css',
        'scripts/angular-material-fileinput/dist/lf-ng-md-file-input.min.css',
        'scripts/md-color-picker/dist/mdColorPicker.min.css',
        'scripts/textAngular/dist/textAngular.css',
        'css/site.css',
    ];
    public $js = [
        'js/app.js',
        'js/controllers.js',
        'js/ServiceControllers.js',
        'js/EmployerControllers.js',
        'js/BlogControllers.js',
        'js/TourControllers.js',
        'scripts/angular-slugify/angular-slugify.js',
        'scripts/angular-animate.min.js',
        'scripts/angular-sanitize.min.js',
        'scripts/angular-carousel/dist/angular-carousel.min.js',
        'scripts/angular-ui-router.min.js',
        'scripts/angular-aria/angular-aria.min.js',
        'scripts/angular-messages/angular-messages.min.js',
        'scripts/angular-material/angular-material.min.js',
        'scripts/angular-touch/angular-touch.min.js',
        'scripts/angular-jk-rating-stars/jk-rating-stars.min.js',
        'scripts/angular-material-fileinput/dist/lf-ng-md-file-input.min.js',
        'scripts/angular-simple-logger.min.js',
        'scripts/angular-google-maps.min.js',
        'scripts/pagination/paging.js',
        'scripts/pagination/tabindex.js',
        'scripts/pagination/pagination.js',
        'scripts/md-color-picker/dist/tinycolor-min.js',
        'scripts/md-color-picker/dist/mdColorPicker.min.js',
        'scripts/toArrayFilter.js',
        'scripts/textAngular/dist/textAngular-rangy.min.js',
        'scripts/textAngular/dist/textAngular-sanitize.min.js',
        'scripts/textAngular/dist/textAngular.min.js',
        'scripts/angular-filter.min.js',
        'scripts/angular-drag-and-drop-lists/angular-drag-and-drop-lists.min.js'
    ];
    public $depends = [
        'app\assets\AngularAsset',
    ];
    public $jsOptions = [
        'position' => View::POS_HEAD,
    ];
}
