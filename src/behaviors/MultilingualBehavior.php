<?php

namespace h0rseduck\multilingual\behaviors;

use h0rseduck\multilingual\components\LanguageManager;
use Yii;
use yii\base\Behavior;
use yii\base\InvalidConfigException;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\validators\Validator;

/**
 * Class MultilingualBehavior
 * @package h0rseduck\multilingual\behaviors
 */
class MultilingualBehavior extends Behavior
{
    use MultilingualBehaviorTrait;

    /**
     * List of multilingual attributes.
     *
     * @var array
     */
    public $attributes;

    /**
     * @var string the name of the translation table.
     */
    public $tableName;

    /**
     * @var string translation table name suffix. If `tableName` is not set
     * $this->owner->tableName() . $this->tableNameSuffix will be used as `tableName`.
     */
    public $tableNameSuffix = '_lang';

    /**
     * @var string the name of the lang field of the translation table. Default to 'language_id'.
     */
    public $languageField = 'language_id';

    /**
     * @var string the name of the foreign key field of the translation table related to base model table.
     */
    public $languageForeignKey = 'owner_id';

    /**
     * @var string the name of language component
     */
    public $languageComponentName = 'languageManager';

    /**
     * The name of translation model class. If this value is empty translation model
     * class will be dynamically created using of the `eval()` function. No additional
     * files are required.
     *
     * @var string
     */
    public $translationClassName;

    /**
     * @var string if $translationClassName is not set, it will be assumed that $translationClassName is
     * get_class($this->owner) . $this->translationClassNameSuffix
     */
    public $translationClassNameSuffix = 'Lang';

    /**
     * @var boolean if this property is set to true required validators will be applied to all translation models.
     * Default to false.
     */
    public $requireTranslations = true;

    /**
     * Multilingual attribute label pattern. Available variables: `{label}` - original
     * attribute label, `{language}` - language name, `{code}` - language code.
     *
     * @var string
     */
    public $attributeLabelPattern = '{label} [{language}]';

    /**
     * @var string the prefix of the localized attributes in the language table. Here to avoid collisions in queries.
     * In the translation table, the columns corresponding to the localized attributes have to be name like this: 'prefix_[name of the attribute]'
     * and the id column (primary key) like this : 'prefix_id'
     * Default to ''.
     */
    public $localizedPrefix = '';

    /**
     * @var boolean whether to use force deletion of the associated translations when a base model is deleted.
     * Not needed if using foreign key with 'on delete cascade'.
     * Default to true.
     */
    public $forceDelete = true;

    /**
     * @var LanguageManager
     */
    public $languageComponent;

    /**
     * @var ActiveRecord current language.
     */
    protected $_currentLanguage;

    /**
     * @var array language keys.
     */
    private $_languageKeys;

    /**
     * @var array languages
     */
    private $_languages;

    /**
     * @var string Owner model class name
     */
    private $_ownerClassName;

    /**
     * @var string owner model primary key
     */
    private $_ownerPrimaryKey;

    /**
     * @var array multilingual attributes
     */
    private $_multilingualAttributes = [];

    /**
     * @var array excluded validators
     */
    private $_excludedValidators = ['unique'];

    /**
     * @var array multilingual attribute labels
     */
    private $_attributeLabels;

    /**
     * @inheritdoc
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_FIND => 'afterFind',
            ActiveRecord::EVENT_AFTER_UPDATE => 'afterUpdate',
            ActiveRecord::EVENT_AFTER_INSERT => 'afterInsert',
            ActiveRecord::EVENT_AFTER_DELETE => 'afterDelete',
            ActiveRecord::EVENT_BEFORE_VALIDATE => 'beforeValidate',
        ];
    }

    /**
     * @inheritdoc
     */
    public function attach($owner)
    {
        /* @var $owner ActiveRecord */
        parent::attach($owner);

        if (empty($this->attributes) || !is_array($this->attributes)) {
            throw new InvalidConfigException('Please specify multilingual attributes for the ' . get_class($this) . ' in the ' . get_class($this->owner));
        }
        $this->languageComponent = Yii::$app->{$this->languageComponentName};
        $this->_languages = $this->languageComponent->getLanguages();
        $this->_currentLanguage = $this->languageComponent->getCurrentLanguage();
        $this->_languageKeys = ArrayHelper::map($this->_languages, $this->languageComponent->modelFieldCode, 'id');

        if (!$this->tableName) {
            $this->tableName = $this->generateTableName($owner->tableName(), $this->tableNameSuffix);
        }

        if (!$this->translationClassName) {
            $this->translationClassName = get_class($this->owner) . $this->translationClassNameSuffix;
        }

        $this->initTranslationClass();

        $this->_ownerClassName = get_class($this->owner);

        /* @var $className ActiveRecord */
        $className = $this->_ownerClassName;
        $this->_ownerPrimaryKey = $className::primaryKey()[0];

        if (!isset($this->languageForeignKey)) {
            throw new InvalidConfigException('Please specify languageForeignKey for the ' . get_class($this) . ' in the ' . get_class($this->owner));
        }

        $rules = $owner->rules();
        $validators = $owner->getValidators();

        foreach ($rules as $rule) {
            if (in_array($rule[1], $this->_excludedValidators)) {
                continue;
            }

            $ruleAttributes = is_array($rule[0]) ? $rule[0] : [$rule[0]];
            $attributes = array_intersect($this->attributes, $ruleAttributes);

            if (empty($attributes)) {
                continue;
            }

            $multilingualAttributes = [];
            foreach ($attributes as $key => $attribute) {
                foreach ($this->_languageKeys as $language => $language_id)
                    $multilingualAttributes[] = $this->getAttributeName($attribute, $language);
            }

            if (isset($rule['skipOnEmpty']) && !$rule['skipOnEmpty'])
                $rule['skipOnEmpty'] = !$this->requireTranslations;

            $params = array_slice($rule, 2);

            if ($rule[1] !== 'required' || $this->requireTranslations) {
                $validators[] = Validator::createValidator($rule[1], $owner, $multilingualAttributes, $params);
            } elseif ($rule[1] === 'required') {
                $validators[] = Validator::createValidator('safe', $owner, $multilingualAttributes, $params);
            }
        }

        $translation = new $this->translationClassName;
        foreach ($this->_languageKeys as $language => $language_id) {
            foreach ($this->attributes as $attribute) {
                $attributeName = $this->localizedPrefix . $attribute;
                $this->setMultilingualAttribute($this->getAttributeName($attribute, $language), $translation->{$attributeName});
            }
        }
    }

    /**
     * Generate translation table name.
     *
     * @param ActiveRecord $ownerTableName
     * @param string $tableNameSuffix
     * @return string
     */
    public function generateTableName($ownerTableName, $tableNameSuffix)
    {
        if (preg_match('/{{%(\w+)}}$/', $ownerTableName, $matches)) {
            $ownerTableName = $matches[1];
        }

        return '{{%' . $ownerTableName . $tableNameSuffix . '}}';
    }

    /**
     * Relation to model translations
     * @return ActiveQuery
     */
    public function getTranslations()
    {
        return $this->owner->hasMany($this->translationClassName, [$this->languageForeignKey => $this->_ownerPrimaryKey]);
    }

    /**
     * Relation to model translation
     * @param integer $language_id
     * @return ActiveQuery
     */
    public function getTranslation($language_id = null)
    {
        $language_id = $language_id ?: $this->_currentLanguage->getPrimaryKey();
        return $this->owner->hasOne($this->translationClassName, [$this->languageForeignKey => $this->_ownerPrimaryKey])
            ->where([$this->languageField => $language_id]);
    }

    /**
     * @return array
     */
    public function getLanguages()
    {
        return $this->_languages;
    }

    /**
     * Handle 'beforeValidate' event of the owner.
     */
    public function beforeValidate()
    {
        foreach ($this->attributes as $attribute) {
            $language = $this->_currentLanguage[$this->languageComponent->modelFieldCode];
            $this->setMultilingualAttribute($this->getAttributeName($attribute, $language), $this->getMultilingualAttribute($attribute));
        }
    }

    /**
     * Handle 'afterFind' event of the owner.
     */
    public function afterFind()
    {
        /** @var ActiveRecord $owner */
        $owner = $this->owner;

        if ($owner->isRelationPopulated('translations') && $related = $owner->getRelatedRecords()['translations']) {
            $translations = $this->indexByLanguage($related);
            foreach ($this->_languageKeys as $language => $language_id) {
                foreach ($this->attributes as $attribute) {
                    foreach ($translations as $translation) {
                        if ($translation->{$this->languageField} == $language_id) {
                            $attributeName = $this->localizedPrefix . $attribute;
                            $this->setMultilingualAttribute($this->getAttributeName($attribute, $language), $translation->{$attributeName});
                        }
                    }
                }
            }
        } else if ($owner->getAttribute($this->_ownerPrimaryKey) !== null) {
            if (!$owner->isRelationPopulated('translation')) {
                $owner->translation;
            }

            $translation = $owner->getRelatedRecords()['translation'];
            if ($translation) {
                foreach ($this->attributes as $attribute) {
                    $attribute_name = $this->localizedPrefix . $attribute;
                    $owner->setMultilingualAttribute($attribute, $translation->$attribute_name);
                }
            }
        }

        foreach ($this->attributes as $attribute) {
            if ($owner->hasAttribute($attribute) && $this->getMultilingualAttribute($attribute)) {
                $owner->setAttribute($attribute, $this->getMultilingualAttribute($attribute));
            }
        }
    }

    /**
     * Handle 'afterInsert' event of the owner.
     */
    public function afterInsert()
    {
        $this->saveTranslations();
    }

    /**
     * Handle 'afterUpdate' event of the owner.
     */
    public function afterUpdate()
    {
        /** @var ActiveRecord $owner */
        $owner = $this->owner;

        if ($owner->isRelationPopulated('translations')) {
            $translations = $this->indexByLanguage($owner->getRelatedRecords()['translations']);
            $this->saveTranslations($translations);
        }
    }

    /**
     * Handle 'afterDelete' event of the owner.
     */
    public function afterDelete()
    {
        if ($this->forceDelete) {
            /** @var ActiveRecord $owner */
            $owner = $this->owner;
            $owner->unlinkAll('translations', true);
        }
    }

    /**
     * Create dynamic translation model.
     */
    protected function initTranslationClass()
    {
        if (!class_exists($this->translationClassName)) {
            $namespace = substr($this->translationClassName, 0, strrpos($this->translationClassName, '\\'));
            $translationClassName = $this->getShortClassName($this->translationClassName);

            eval('
            namespace ' . $namespace . ';
            use yii\db\ActiveRecord;
            class ' . $translationClassName . ' extends ActiveRecord
            {
                public static function tableName()
                {
                    return \'' . $this->tableName . '\';
                }
            }');
        }
    }

    /**
     * @param array $translations
     */
    protected function saveTranslations($translations = [])
    {
        /** @var ActiveRecord $owner */
        $owner = $this->owner;

        foreach ($this->_languageKeys as $language => $language_id) {
            if (!isset($translations[$language_id])) {
                /** @var ActiveRecord $translation */
                $translation = new $this->translationClassName;
                $translation->{$this->languageField} = $language_id;
                $translation->{$this->languageForeignKey} = $owner->getPrimaryKey();
            } else {
                $translation = $translations[$language_id];
            }

            $save = false;
            foreach ($this->attributes as $attribute) {
                $value = $this->getMultilingualAttribute($this->getAttributeName($attribute, $language));
                if ($value !== null) {
                    $field = $this->localizedPrefix . $attribute;
                    $translation->$field = $value;
                    $save = true;
                }
            }

            if ($translation->isNewRecord && !$save) {
                continue;
            }

            $translation->save();
        }
    }

    /**
     * @param $records
     * @return array
     */
    protected function indexByLanguage($records)
    {
        $sorted = array();
        foreach ($records as $record) {
            $sorted[$record->{$this->languageField}] = $record;
        }
        unset($records);
        return $sorted;
    }

    /**
     * @param string $className
     * @return string
     */
    protected function getShortClassName($className)
    {
        return substr($className, strrpos($className, '\\') + 1);
    }
}
