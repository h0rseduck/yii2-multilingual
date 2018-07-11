<?php

namespace h0rseduck\multilingual\helpers;

/**
 * Interface LanguageModelTrait
 * @package h0rseduck\multilingual\contracts
 */
trait LanguageModelTrait
{
    /**
     * @var string the name of language model class.
     */
    public $languageClassName;

    /**
     * @var string
     */
    public $languageModelFieldCode = 'code';

    /**
     * @var string
     */
    public $languageModelFieldTitle = 'title';
}