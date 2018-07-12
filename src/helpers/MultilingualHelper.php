<?php

namespace h0rseduck\multilingual\helpers;

use Yii;
use yii\db\ActiveRecord;
use yii\helpers\Inflector;
use yii\base\InvalidConfigException;

/**
 * Class MultilingualHelper
 * @package h0rseduck\multilingual\helpers
 */
class MultilingualHelper
{
    /**
     * Validates and returns list of language redirects.
     *
     * @param array $languageRedirects
     * @return array
     */
    public static function getLanguageRedirects($languageRedirects)
    {
        if (!$languageRedirects && isset(Yii::$app->params['languageRedirects'])) {
            $languageRedirects = Yii::$app->params['languageRedirects'];
        }

        return $languageRedirects;
    }

    /**
     * Returns code of language by its redirect language code.
     *
     * @param string $redirectLanguageCode
     * @param array $redirects
     * @return string
     */
    public static function getRedirectedLanguageCode($redirectLanguageCode, $redirects = null)
    {
        if (!$redirects && isset(Yii::$app->params['languageRedirects'])) {
            $redirects = Yii::$app->params['languageRedirects'];
        }

        if (!is_array($redirects) || empty($redirects)) {
            return $redirectLanguageCode;
        }

        $codes = array_flip($redirects);
        return (isset($codes[$redirectLanguageCode])) ? $codes[$redirectLanguageCode] : $redirectLanguageCode;
    }

    /**
     * Returns list of languages with applied language redirects.
     *
     * @param array $languages
     * @param array $languageRedirects
     * @return array
     */
    public static function getDisplayLanguages($languages, $languageRedirects)
    {
        foreach ($languages as $key => $value) {
            $key = (isset($languageRedirects[$key])) ? $languageRedirects[$key] : $key;
            $redirects[$key] = $value;
        }
        return $redirects;
    }

    /**
     * Returns language code that will be displayed on front-end.
     *
     * @param string $language
     * @return string
     */
    public static function getDisplayLanguageCode($language, $languageRedirects)
    {
        return (isset($languageRedirects[$language])) ? $languageRedirects[$language] : $language;
    }

    /**
     * Updates attribute name to multilingual.
     *
     * @param string $attribute
     * @param string $language
     * @return string
     */
    public static function getAttributeName($attribute, $language)
    {
        return $attribute . "_" . Inflector::camel2id(Inflector::id2camel($language), "_");
    }
}
