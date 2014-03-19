<?php

namespace Doctrine\ODM\CouchDB\Types;

class StringType extends Type
{
    public function convertToDatabaseValue($value)
    {
        return (string)$value;
    }

    public function convertToPHPValue($value)
    {
        return (string)$value;
    }
}