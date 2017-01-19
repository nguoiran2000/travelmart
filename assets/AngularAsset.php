<?php
namespace app\assets;

use yii\web\AssetBundle;
use yii\web\View;

class AngularAsset extends AssetBundle
{
    public $sourcePath = '@bower';
    public $js = [
    	'angular/lodash.min.js',
        'angular/angular.min.js',
    ];
    public $jsOptions = [
        'position' => View::POS_HEAD,
    ];
}
