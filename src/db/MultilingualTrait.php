<?php

namespace h0rseduck\multilingual\db;

use Yii;

/**
 * Multilingual trait.
 *
 * Modify ActiveRecord query for multilingual support.
 */
trait MultilingualTrait
{
    /**
     * Scope for querying by all languages
     * @return $this
     */
    public function multilingual()
    {
        if (isset($this->with['translation'])) {
            unset($this->with['translation']);
        }
        $this->with('translations');
        return $this;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    abstract public function with();
}
