<?php

namespace h0rseduck\multilingual\assets;

use yii\web\AssetBundle;

/**
 * Class LanguageSwitcherAsset
 * @package h0rseduck\multilingual\assets
 */
class LanguageSwitcherAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public $css = [
        'css/switcher.css',
    ];

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->sourcePath = __DIR__ . '/source/switcher';
    }
}
