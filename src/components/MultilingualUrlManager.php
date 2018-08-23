<?php

namespace h0rseduck\multilingual\components;

use h0rseduck\multilingual\components\LanguageManager;
use Yii;
use yii\base\ActionEvent;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
use yii\web\Cookie;
use yii\web\MethodNotAllowedHttpException;
use yii\web\UrlManager;
use yii\web\Application;
use yii\web\NotFoundHttpException;
use h0rseduck\multilingual\helpers\MultilingualHelper;

/**
 * Class MultilingualUrlManager
 * @package h0rseduck\multilingual\web
 */
class MultilingualUrlManager extends UrlManager
{
    const SESSION_KEY = 'language';
    const COOKIE_KEY = 'language';
    const REQUEST_PARAM = 'language';

    /**
     * @var LanguageManager
     */
    public $languageComponent;

    /**
     * @var string the name of language component
     */
    public $languageComponentName = 'languageManager';

    /**
     * List of not multilingual actions. Should contain action id, including
     * controller id and module id (if module is used).
     *
     * For example,
     *
     * ```php
     * [
     *   'site/logout',
     *   'auth/default/oauth2',
     * ]
     *
     * @var array
     */
    public $excludedActions = [];

    /**
     * Name of param that is used to forced including of the language param to the
     * url. Is used for generating links for `excludedActions` in cases when we
     * need include language param to the link. For example in `LanguageSwitcher`.
     *
     * @var string
     */
    public $forceLanguageParam = 'forceLanguageParam';

    /**
     * @var array languages
     */
    private $_languages;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->languageComponent = Yii::$app->{$this->languageComponentName};
        $this->_languages = ArrayHelper::map(
            $this->languageComponent->getLanguages(),
            $this->languageComponent->modelFieldCode,
            $this->languageComponent->modelFieldCode
        );
        $this->_languages = array_values($this->_languages);
        Yii::$app->on(Application::EVENT_BEFORE_ACTION, [$this, 'beforeAction']);
    }

    /**
     * Tracks language parameter for multilingual controllers.
     *
     * @param ActionEvent $event
     * @return bool
     * @throws MethodNotAllowedHttpException|InvalidConfigException when the request method is not allowed.
     */
    public function beforeAction($event)
    {
        if (!Yii::$app->errorHandler->exception && count($this->_languages) > 1) {
            // Set language by GET request, session or cookie
            if ($language = Yii::$app->getRequest()->get(self::REQUEST_PARAM)) {
                Yii::$app->language = $this->getLanguageCode($language);
                Yii::$app->session->set(self::SESSION_KEY, Yii::$app->language);
                /** @var Cookie $cookie */
                $cookie = Yii::createObject([
                    'class' => 'yii\web\Cookie',
                    'name' => self::COOKIE_KEY,
                    'value' => Yii::$app->session->get(self::SESSION_KEY),
                    'expire' => time() + 31536000 // one year
                ]);
                Yii::$app->response->cookies->add($cookie);
            } elseif ($language = Yii::$app->session->get(self::SESSION_KEY)) {
                Yii::$app->language = $this->getLanguageCode($language);
            } elseif (isset(Yii::$app->request->cookies[self::COOKIE_KEY])) {
                $language = Yii::$app->request->cookies[self::COOKIE_KEY]->value;
                Yii::$app->language = $this->getLanguageCode($language);
            }
            Yii::$app->formatter->locale = Yii::$app->language;
        }
        return $event->isValid;
    }

    /**
     * @inheritdoc
     */
    public function createUrl($params)
    {
        $forceLanguage = (isset($params[$this->forceLanguageParam]) && $params[$this->forceLanguageParam]);
        if ($forceLanguage) {
            unset($params[$this->forceLanguageParam]);
        }
        if (!$forceLanguage && ((isset($params[self::REQUEST_PARAM]) && $params[self::REQUEST_PARAM] === false) || (isset($params[0])
                    && in_array($params[0], $this->excludedActions)))) {
            unset($params[self::REQUEST_PARAM]);
            return parent::createUrl($params);
        }
        if (count($this->_languages) > 1) {
            $languages = array_keys($this->_languages);
            //remove incorrect language param
            if (isset($params[self::REQUEST_PARAM]) && !in_array($params[self::REQUEST_PARAM], $languages)) {
                unset($params[self::REQUEST_PARAM]);
            }
            //trying to get language param
            if (!isset($params[self::REQUEST_PARAM])) {
                if (Yii::$app->session->has(self::SESSION_KEY)) {
                    $language = Yii::$app->session->get(self::SESSION_KEY);
                } elseif (isset(Yii::$app->request->cookies[self::COOKIE_KEY])) {
                    $language = Yii::$app->request->cookies[self::COOKIE_KEY]->value;
                } else {
                    $language = Yii::$app->language;
                }
                if (in_array($language, $languages)) {
                    Yii::$app->language = $language;
                }
                $params[self::REQUEST_PARAM] = Yii::$app->language;
            }
        }
        return parent::createUrl($params);
    }

    /**
     * @param string $languageCode
     * @return string
     * @throws NotFoundHttpException
     */
    protected function getLanguageCode($languageCode)
    {
        if (!in_array($languageCode, $this->_languages)) {
            throw new NotFoundHttpException(Yii::t('yii', 'Page not found.'));
        }
        return $languageCode;
    }
}
