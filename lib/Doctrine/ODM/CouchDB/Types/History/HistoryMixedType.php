<?php

namespace Doctrine\ODM\CouchDB\Types\History;

use Doctrine\ODM\CouchDB\Types\MixedType;

class HistoryMixedType extends MixedType
{
    /**
     * @param mixed $value
     *
     * @return mixed
     */
    public function convertToDatabaseValue($value)
    {
        return $value;
    }

    /**
     * @param mixed $value
     *
     * @return mixed
     */
    public function realConvertToDatabaseValue($value)
    {
        return parent::convertToDatabaseValue($value);
    }
}
