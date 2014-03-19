<?php

namespace Doctrine\ODM\CouchDB\Types;

class FloatType extends Type
{
    public function convertToDatabaseValue($value)
    {
        return (float)$value;
    }

    public function convertToPHPValue($value)
    {
        return (float)$value;
    }
}