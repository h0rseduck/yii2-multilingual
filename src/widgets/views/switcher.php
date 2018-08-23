<?php

use yii\helpers\Html;
use h0rseduck\multilingual\assets\LanguageSwitcherAsset;

/* @var $this yii\web\View */
/* @var $languages array */

LanguageSwitcherAsset::register($this);

?>

<div class="language-switcher language-switcher-pills">
    <ul class="nav nav-pills">
        <?php foreach ($languages as $language) : ?>
            <li>
                <?= Html::a($language['label'], $language['url']) ?>
            </li>
        <?php endforeach; ?>
    </ul>
</div>


