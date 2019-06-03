<?php

namespace h0rseduck\multilingual\containers;

use h0rseduck\multilingual\helpers\MultilingualHelper;
use yii\base\BaseObject;
use yii\bootstrap\ActiveField;

/**
 * Class MultilingualFieldContainer
 * @package h0rseduck\multilingual\containers
 */
class MultilingualFieldContainer extends BaseObject
{
    /**
     * Fields.
     * @var ActiveField[]
     */
    public $fields;

    /**
     * @var array List of languages.
     */
    public $languages = [];

    /**
     * @param $method
     * @param $arguments
     * @return string
     */
    public function __call($method, $arguments)
    {
        foreach ($this->fields as &$field) {
            $field = call_user_func_array(array($field, $method), $this->updateArguments($method, $arguments, $field));
        }
        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $html = '';
        foreach ($this->fields as $field) {
            $html .= (string)$field;
        }
        return $html;
    }

    /**
     * @param null|string $label
     * @return $this
     */
    public function label($label = null)
    {
        foreach ($this->fields as $field) {
            $language = ucfirst($field->language);
            if(count($this->languages) > 1) {
                $field->label("{$label} {$language}");
            } else {
                $field->label($label);
            }
        }
        return $this;
    }

    /**
     * Updates id for multilingual inputs with custom id.
     *
     * @param string $method
     * @param array $arguments
     * @param mixed $field
     * @return array
     */
    protected function updateArguments($method, $arguments, $field)
    {
        if ($method == 'widget' && isset($arguments[1]['options']['id'])) {
            $arguments[1]['options']['id'] = MultilingualHelper::getAttributeName($arguments[1]['options']['id'], $field->language);
        }

        if (in_array($method, ['textInput', 'textarea', 'radio', 'checkbox', 'fileInput', 'hiddenInput', 'passwordInput']) && isset($arguments[0]['id'])) {
            $arguments[0]['id'] = MultilingualHelper::getAttributeName($arguments[0]['id'], $field->language);
        }

        if (in_array($method, ['input', 'dropDownList', 'listBox', 'radioList', 'checkboxList']) && isset($arguments[1]['id'])) {
            $arguments[1]['id'] = MultilingualHelper::getAttributeName($arguments[1]['id'], $field->language);
        }

        return $arguments;
    }
}
