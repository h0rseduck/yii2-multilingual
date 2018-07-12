<?php

namespace h0rseduck\multilingual\widgets;

use h0rseduck\multilingual\behaviors\MultilingualBehavior;
use Yii;
use h0rseduck\multilingual\containers\MultilingualFieldContainer;
use yii\helpers\ArrayHelper;

/**
 * Multilingual ActiveForm
 */
class ActiveForm extends \yii\bootstrap\ActiveForm
{
    /**
     * @var string the default field class name when calling [[field()]] to create a new field.
     */
    public $fieldClass = 'h0rseduck\multilingual\widgets\ActiveField';

    /**
     * 
     * @param \yii\base\Model $model
     * @param string $attribute
     * @param array $options
     * @return MultilingualFieldContainer
     */
    public function field($model, $attribute, $options = [])
    {
        $fields = [];

        $notMultilingual = (isset($options['multilingual']) && $options['multilingual'] === false);
        $multilingualField = (isset($options['multilingual']) && $options['multilingual']);
        $multilingualAttribute = ($model->getBehavior('multilingual') && $model->hasMultilingualAttribute($attribute));

        if (!$notMultilingual && ($multilingualField || $multilingualAttribute)) {
            if($multilingualAttribute){
                /** @var MultilingualBehavior $behavior */
                $behavior = $model->getBehavior('multilingual');
                $languages = ArrayHelper::getColumn($behavior->getLanguages(), $behavior->languageComponent->modelFieldCode);
            } else {
                $languages = (!empty($options['languages'])) ? array_keys($options['languages']) : [Yii::$app->language];
            }
            
            foreach ($languages as $language) {
                $fields[] = parent::field($model, $attribute, array_merge($options, ['language' => $language]));
            }

        } else {
            return parent::field($model, $attribute, $options);
        }

        return new MultilingualFieldContainer(['fields' => $fields]);
    }
    
    /**
     * Renders form language switcher.
     * 
     * @param \yii\base\Model $model
     * @param string $view
     * @return string
     */
    public function languageSwitcher($model, $view = null)
    {
        $languages = [];
        if($behavior = $model->getBehavior('multilingual')) {
            /** @var MultilingualBehavior $behavior */
            $languages = ArrayHelper::map(
                $behavior->getLanguages(),
                $behavior->languageComponent->modelFieldCode,
                $behavior->languageComponent->modelFieldTitle
            );
        }
        return FormLanguageSwitcher::widget(['languages' => $languages, 'view' => $view]);
    }
}