<?php

namespace bestyii\ueditor\plus;

use Yii;
use yii\web\AssetBundle;

/**
 * Asset bundle for the UEditor
 *
 */
class UEditorPlusAsset extends AssetBundle
{

    public function init()
    {
        $this->sourcePath = dirname(__FILE__) . '/assets';
        parent::init();
    }

    public function getPublishedUrl()
    {
        return Yii::$app->assetManager->getPublishedUrl(dirname(__FILE__) . '/assets') . '/';
    }

    public $css = [];

    public $js = [
        'ueditor.config.js',
        'ueditor.all.js'
    ];

    public $depends = [
        'yii\web\JqueryAsset'
    ];

}
