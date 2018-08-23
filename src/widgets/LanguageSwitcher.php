<?php

namespace h0rseduck\multilingual\widgets;

use h0rseduck\multilingual\components\LanguageManager;
use h0rseduck\multilingual\components\MultilingualUrlManager;
use Yii;
use yii\helpers\ArrayHelper;
use h0rseduck\multilingual\helpers\MultilingualHelper;
use yii\helpers\Html;

/**
 * Class LanguageSwitcher
 * @package h0rseduck\multilingual\widgets
 */
class LanguageSwitcher extends \yii\base\Widget
{
    const VIEW_LINKS = 'links';
    const VIEW_PILLS = 'pills';
    const DISPLAY_CODE = 'code';
    const DISPLAY_TITLE = 'title';

    /**
     * @var LanguageManager
     */
    public $languageComponent;

    /**
     * @var string the name of language component
     */
    public $languageComponentName = 'languageManager';

    /**
     * @var string View file of switcher. Could be `links`, `pills` or custom view.
     */
    public $view = self::VIEW_PILLS;

    /**
     * @var string  code | title
     */
    public $display = self::DISPLAY_TITLE;

    /**
     * @var string current language.
     */
    protected $_currentLanguage;

    /**
     * @var array languages
     */
    private $_languages;

    /**
     * @var array default views of switcher.
     */
    protected $_reservedViews = [
        'links' => '@vendor/h0rseduck/yii2-multilingual/src/views/switcher/links',
        'pills' => '@vendor/h0rseduck/yii2-multilingual/src/views/switcher/pills',
    ];

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->languageComponent = Yii::$app->{$this->languageComponentName};
        $this->_currentLanguage = $this->languageComponent->getCurrentLanguage();
        $this->_languages = ArrayHelper::map(
            $this->languageComponent->getLanguages(),
            $this->languageComponent->modelFieldCode,
            $this->languageComponent->modelFieldTitle
        );
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        if (count($this->_languages) > 1) {
            $view = isset($this->_reservedViews[$this->view]) ? $this->_reservedViews[$this->view] : $this->view;
            list($route, $params) = Yii::$app->getUrlManager()->parseRequest(Yii::$app->getRequest());
            $params = ArrayHelper::merge(Yii::$app->getRequest()->get(), $params);
            $url = isset($params['route']) ? $params['route'] : $route;
            return $this->render($view, [
                'url' => $url,
                'params' => $params,
                'display' => $this->display,
                'language' => $this->_currentLanguage,
                'languages' => $this->_languages,
            ]);
        }
        return null;
    }
}
