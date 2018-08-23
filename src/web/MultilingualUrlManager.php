<?php

namespace h0rseduck\multilingual\web;

use h0rseduck\multilingual\components\LanguageManager;
use Yii;
use yii\base\ActionEvent;
use yii\base\InvalidConfigException;
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
    /**
     * @var LanguageManager
     */
    public $languageComponent;

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
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->languages = $this->languageComponent->getLanguages();
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
        if (!Yii::$app->errorHandler->exception && count($this->languages) > 1) {

            // Set language by GET request, session or cookie
            if ($language = Yii::$app->getRequest()->get('language')) {

                Yii::$app->language = $this->getLanguageCode($language);
                Yii::$app->session->set('language', Yii::$app->language);
                Yii::$app->response->cookies->add(new \yii\web\Cookie([
                    'name' => 'language',
                    'value' => Yii::$app->session->get('language'),
                    'expire' => time() + 31536000 // one year
                ]));
            } elseif ($language = Yii::$app->session->get('language')) {

                Yii::$app->language = $this->getLanguageCode($language);
            } elseif (isset(Yii::$app->request->cookies['language'])) {

                $language = Yii::$app->request->cookies['language']->value;
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

        if (!$forceLanguage && ((isset($params['language']) && $params['language'] === false) || (isset($params[0]) && in_array($params[0], $this->excludedActions)))) {
            unset($params['language']);
            return parent::createUrl($params);
        }

        if (count($this->languages) > 1) {
            $languages = array_keys($this->languages);
            //remove incorrect language param
            if (isset($params['language']) && !in_array($params['language'], $languages)) {
                unset($params['language']);
            }
            //trying to get language param
            if (!isset($params['language'])) {
                if (Yii::$app->session->has('language')) {
                    $language = Yii::$app->session->get('language');
                } elseif (isset(Yii::$app->request->cookies['language'])) {
                    $language = Yii::$app->request->cookies['language']->value;
                } else {
                    $language = Yii::$app->language;
                }
                if (in_array($language, $languages)) {
                    Yii::$app->language = $language;
                }
                $params['language'] = Yii::$app->language;
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
        if (!isset($this->languages[$languageCode])) {
            throw new NotFoundHttpException(Yii::t('yii', 'Page not found.'));
        }
        return $languageCode;
    }
}
