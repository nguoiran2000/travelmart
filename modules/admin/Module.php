<?php

namespace app\modules\admin;

use Yii;
use yii\base\InvalidConfigException;
use yii\db\ActiveRecord;

/**
 * User module
 *
 * @author nguoiran2000 <nguoiran2000@gmail.com>
 */
class Module extends \yii\base\Module
{
    /**
     * @var string Module version
     */
    protected $version = "1.0.0";

    /**
     * @var string Alias for module
     */
    public $alias = "@admin";

    /**
     * Get module version
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        // set up i8n
        if (empty(Yii::$app->i18n->translations['admin'])) {
            Yii::$app->i18n->translations['admin'] = [
                'class' => 'yii\i18n\PhpMessageSource',
                'basePath' => __DIR__ . '/messages',
                //'forceTranslation' => true,
            ];
        }

        // set alias
        $this->setAliases([
            $this->alias => __DIR__,
        ]);
    }
}