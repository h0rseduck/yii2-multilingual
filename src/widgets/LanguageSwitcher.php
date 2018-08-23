<?php

namespace h0rseduck\multilingual\widgets;

use h0rseduck\multilingual\components\LanguageManager;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * Class LanguageSwitcher
 * @package h0rseduck\multilingual\widgets
 */
class LanguageSwitcher extends \yii\base\Widget
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
     * @var string View file
     */
    public $view;

    /**
     *
     * @var string default view file
     */
    private $_defaultView = '@vendor/h0rseduck/yii2-multilingual/src/widgets/views/switcher';

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
        $this->view = $this->view ?: $this->_defaultView;
        $this->languageComponent = Yii::$app->{$this->languageComponentName};
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
        $route = Yii::$app->controller->route;
        $appLanguage = Yii::$app->language;
        $params = Yii::$app->getRequest()->getQueryParams();
        if($route === Yii::$app->errorHandler->errorAction) {
            return null;
        }
        array_unshift($params, '/' . $route);
        $languages = [];
        foreach ($this->_languages as $language => $languageTitle) {
            $isWildcard = substr($language, -2) === '-*';
            if (
                $language === $appLanguage ||
                // Also check for wildcard language
                $isWildcard && substr($appLanguage, 0, 2) === substr($language, 0, 2)
            ) {
                continue;   // Exclude the current language
            }
            if ($isWildcard) {
                $language = substr($language, 0, 2);
            }
            $params['language'] = $language;
            $languages[] = [
                'label' => $languageTitle,
                'url' => $params,
            ];
        }

        return $this->render($this->view, [
            'languages' => $languages
        ]);
    }
}
