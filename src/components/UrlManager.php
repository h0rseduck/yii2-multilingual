<?php

namespace h0rseduck\multilingual\components;

use Yii;
use yii\di\Instance;
use yii\helpers\ArrayHelper;

/**
 * Class UrlManager
 * @package h0rseduck\multilingual\components
 */
class UrlManager extends \codemix\localeurls\UrlManager
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
    public $languageSessionKey = 'language';

    /**
     * inheritdoc
     */
    public $languageCookieName = 'language';

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->languageComponent = Instance::ensure($this->languageComponentName, LanguageManager::className());
        $this->languages = ArrayHelper::getColumn($this->languageComponent->getLanguages(),$this->languageComponent->modelFieldCode);
        parent::init();
    }
}