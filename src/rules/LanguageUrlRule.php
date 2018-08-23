<?php

namespace h0rseduck\multilingual\rules;

use h0rseduck\multilingual\components\MultilingualUrlManager;
use Yii;
use yii\web\UrlRule;

/**
 * Class LanguageUrlRule
 * @package h0rseduck\multilingual\rules
 */
class LanguageUrlRule extends UrlRule
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        if ($this->pattern !== null) {
            $this->pattern = '<language>/' . $this->pattern;
        }
        $this->defaults['language'] = Yii::$app->language;
        parent::init();
    }
}