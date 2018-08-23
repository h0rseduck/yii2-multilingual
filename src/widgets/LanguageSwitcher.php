<?php

namespace h0rseduck\multilingual\widgets;

use h0rseduck\multilingual\components\LanguageManager;
use Yii;
use yii\helpers\ArrayHelper;
use h0rseduck\multilingual\helpers\MultilingualHelper;

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
        $this->_currentLanguage = $this->languageComponent->getCurrentLanguage();
        $this->languages = $this->languageComponent->getLanguages();
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        if (count($this->languages) > 1) {
            $view = isset($this->_reservedViews[$this->view]) ? $this->_reservedViews[$this->view] : $this->view;
            list($route, $params) = Yii::$app->getUrlManager()->parseRequest(Yii::$app->getRequest());
            $params = ArrayHelper::merge(Yii::$app->getRequest()->get(), $params);
            $url = isset($params['route']) ? $params['route'] : $route;

            return $this->render($view, [
                'url' => $url,
                'params' => $params,
                'display' => $this->display,
                'language' => $this->_currentLanguage,
                'languages' => $this->languages,
            ]);
        }
        return null;
    }
}
