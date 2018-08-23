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

<div class="language-switcher language-switcher-links">
    <ul>
        <?php foreach ($languages as $key => $lang) : ?>
            <?php $title = ($display == 'code') ? $key : $lang; ?>
            <li>
                <?php if ($language == $key) : ?>
                    <span><?= $title ?></span>
                <?php else: ?>
                    <?= Html::a($title, ArrayHelper::merge($params, [$url, MultilingualUrlManager::REQUEST_PARAM => $key, 'forceLanguageParam' => true])) ?>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>
</div>