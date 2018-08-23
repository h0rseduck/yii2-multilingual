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
