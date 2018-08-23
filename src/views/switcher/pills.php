<?php

use h0rseduck\multilingual\components\MultilingualUrlManager;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use h0rseduck\multilingual\assets\LanguageSwitcherAsset;

/* @var $this yii\web\View */
/* @var $languages array */
/* @var $params array */
/* @var $display string */
/* @var $url string */

LanguageSwitcherAsset::register($this);

?>

<div class="language-switcher language-switcher-pills">
    <ul class="nav nav-pills">
        <?php foreach ($languages as $key => $lang) : ?>
            <?php $title = ($display == 'code') ? $key : $lang; ?>
            <?php if ($language == $key) : ?>
                <li class="active">
                    <a><?= $title ?></a>
                </li>
            <?php else: ?>
                <li>
                    <?= Html::a($title, ArrayHelper::merge($params, [$url, MultilingualUrlManager::REQUEST_PARAM => $key, 'forceLanguageParam' => true])) ?>
                </li>
            <?php endif; ?>
        <?php endforeach; ?>
    </ul>
</div>


