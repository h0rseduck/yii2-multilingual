<?php

namespace h0rseduck\multilingual\widgets;

use Yii;
use h0rseduck\multilingual\helpers\MultilingualHelper;
use h0rseduck\multilingual\assets\FormLanguageSwitcherAsset;

/**
 * @inheritdoc
 */
class ActiveField extends \yii\bootstrap\ActiveField
{

    /**
     * @var string Language of the field.
     */
    public $language;

    /**
     * @var string List of languages of the field. For static multilingual fields.
     */
    public $languages;

    /**
     * @var bool Whether is field multilingual. Use this option to mark an attribute as multilingual
     * in dynamic models.
     */
    public $multilingual = false;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if ($this->language && ($this->model->getBehavior('multilingual') || $this->multilingual)) {

            $this->attribute = MultilingualHelper::getAttributeName($this->attribute, $this->language);
            $activeLanguage = (Yii::$app->language === $this->language);
            $switcherUsed = isset(Yii::$app->assetManager->bundles[FormLanguageSwitcherAsset::className()]);

            $this->options = array_merge($this->options, [
                'data-lang' => $this->language,
                'data-toggle' => 'multilingual-field',
                'class' => ($activeLanguage ? 'in' : ''),
                'style' => ((!$activeLanguage && $switcherUsed) ? 'display:none' : ''),
            ]);
        }
    }
}
