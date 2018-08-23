<?php

namespace h0rseduck\multilingual\widgets;

use Yii;

/**
 * Widget to display buttons to switch languages in forms
 */
class FormLanguageSwitcher extends \yii\base\Widget
{
    /**
     * @var string view file
     */
    public $view;

    /**
     * List of languages.
     *
     * @var array
     */
    public $languages;

    /**
     *
     * @var string default view file
     */
    private $_defaultView = '@vendor/h0rseduck/yii2-multilingual/src/widgets/views/form-switcher';

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->view = $this->view ?: $this->_defaultView;

        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        if ($this->languages) {
            return $this->render($this->view, [
                'language' => Yii::$app->language,
                'languages' => $this->languages,
            ]);
        }
        return null;
    }
}
