<?php

namespace h0rseduck\multilingual\components;

use codemix\localeurls\LanguageChangedEvent;
use Yii;
use yii\di\Instance;
use yii\helpers\ArrayHelper;
use yii\web\Cookie;

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
     * @inheritdoc
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

    /**
     * @inheritdoc
     */
    protected function persistLanguage($language)
    {
        if ($this->hasEventHandlers(self::EVENT_LANGUAGE_CHANGED)) {
            $oldLanguage = $this->loadPersistedLanguage();
            if ($oldLanguage !== $language) {
                Yii::trace("Triggering languageChanged event: $oldLanguage -> $language", __METHOD__);
                $this->trigger(self::EVENT_LANGUAGE_CHANGED, new LanguageChangedEvent([
                    'oldLanguage' => $oldLanguage,
                    'language' => $language,
                ]));
            }
        }
        if ($this->languageSessionKey !== false) {
            Yii::$app->session[$this->languageSessionKey] = $language;
            Yii::trace("Persisting language '$language' in session.", __METHOD__);
        }
        if ($this->languageCookieDuration) {
            /** @var Cookie $cookie */
            $cookie = Yii::createObject(array_merge(
                ['class' => Cookie::className(), 'httpOnly' => true],
                $this->languageCookieOptions,
                [
                    'name' => $this->languageCookieName,
                    'value' => $language,
                    'expire' => time() + (int) $this->languageCookieDuration,
                ]
            ));
            Yii::$app->getResponse()->getCookies()->add($cookie);
            Yii::trace("Persisting language '$language' in cookie.", __METHOD__);
        }
    }
}