<?php

namespace h0rseduck\multilingual\components;

use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\base\InvalidValueException;
use yii\db\ActiveRecord;

/**
 * Class LanguageManager
 * @package h0rseduck\multilingual\components
 */
class LanguageManager extends Component
{
    /**
     * @var string
     */
    public $modelClassName;

    /**
     * @var string
     */
    public $modelFieldCode = 'code';

    /**
     * @var string
     */
    public $modelFieldTitle = 'title';

    /**
     * @var ActiveRecord
     */
    protected $model;

    /**
     * @var array
     */
    protected $languages;

    /**
     * @var ActiveRecord
     */
    protected $currentLanguage;

    /**
     * @return array
     */
    public function getLanguages()
    {
        if(!$this->languages) {
            $model = $this->getModel();
            $this->languages = $model::find()->asArray()->all();
        }
        return $this->languages;
    }

    /**
     * @return ActiveRecord
     * @throws InvalidConfigException
     */
    public function getModel()
    {
        if(!$this->model) {
            if (!$this->modelClassName) {
                throw new InvalidConfigException('ModelClassName can not be empty!');
            }
            $model = new $this->modelClassName;
            if (!($model instanceof ActiveRecord)) {
                throw new InvalidConfigException('ModelClassName not instance of ActiveRecord!');
            }
            $this->model = $model;
        }
        return $this->model;
    }

    /**
     * @return ActiveRecord|array
     * @throws InvalidValueException
     */
    public function getCurrentLanguage()
    {
        if(!$this->currentLanguage) {
            $model = $this->getModel();
            $model = $model::find()->where([$this->modelFieldCode => Yii::$app->language])->asArray()->one();
            if (!$model) {
                throw new InvalidValueException('Current language not found!');
            }
            $this->currentLanguage = $model;
        }
        return $this->currentLanguage;
    }
}