<?php

namespace Doctrine\ODM\CouchDB\Event;


use Doctrine\Common\EventArgs;
use Doctrine\Common\Persistence\ObjectManager;

class ModifyDataEventArgs extends EventArgs
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var object
     */
    private $object;

    /**
     * @var array
     */
    private $data;

    /**
     * @param object        $object
     * @param array         $data
     * @param ObjectManager $om
     */
    public function __construct($object, array &$data, ObjectManager $om)
    {
        $this->object = $object;
        $this->objectManager = $om;
        $this->data = &$data;
    }

    /**
     * @return ObjectManager
     */
    public function getObjectManager()
    {
        return $this->objectManager;
    }

    /**
     * @return object
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * @return array
     */
    public function &getData()
    {
        return $this->data;
    }
}
