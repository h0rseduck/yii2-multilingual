<?php

namespace h0rseduck\multilingual\assets;

use yii\web\AssetBundle;

/**
 * Class FormLanguageSwitcherAsset
 * @package h0rseduck\multilingual\assets
 */
class FormLanguageSwitcherAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public $js = [
        'js/form-switcher.js',
    ];

    /**
     * @inheritdoc
     */
    public $css = [
        'css/form-switcher.css',
    ];

    /**
     * @inheritdoc
     */
    public $depends = [
        'yii\web\JqueryAsset',
        'yii\web\YiiAsset',
    ];

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->sourcePath = __DIR__ . '/source/form-switcher';
    }
}
