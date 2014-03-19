<?php

namespace Doctrine\ODM\CouchDB\Types;

class IntegerType extends Type
{
    public function convertToDatabaseValue($value)
    {
        return (int)$value;
    }

    public function convertToPHPValue($value)
    {
        return (int)$value;
    }
}