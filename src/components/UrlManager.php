<?php

namespace h0rseduck\multilingual\components;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * Class UrlManager
 * @package h0rseduck\multilingual\components
 */
class UrlManager extends codemix\localeurls\UrlManager
{
    /**
     * @var LanguageManager
     */
    public $languageComponent;

    /**
     * @var string the name of language component
     */
    public $languageComponentName = 'languageManager';

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->languageComponent = Yii::$app->{$this->languageComponentName};
        $this->languages = ArrayHelper::getColumn($this->languageComponent->getLanguages(),$this->languageComponent->modelFieldCode);
        parent::init();
    }
}