<?php

namespace h0rseduck\multilingual\db;

use yii\db\ActiveQuery;

/**
 * Class MultilingualQuery
 * @package h0rseduck\multilingual\db
 */
class MultilingualQuery extends ActiveQuery
{
    use MultilingualTrait;
}