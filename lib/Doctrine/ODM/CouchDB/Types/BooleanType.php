<?php

namespace Doctrine\ODM\CouchDB\Types;

class BooleanType extends Type
{
    public function convertToDatabaseValue($value)
    {
        return (bool) $value;
    }

    public function convertToPHPValue($value)
    {
        return (bool) $value;
    }
}