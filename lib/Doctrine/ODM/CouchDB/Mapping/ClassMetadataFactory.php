<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license. For more information, see
 * <http://www.doctrine-project.org>.
 */

namespace Doctrine\ODM\CouchDB\Mapping;

use Doctrine\Common\EventManager;
use Doctrine\ODM\CouchDB\DocumentManager;
use Doctrine\Common\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\Common\Persistence\Mapping\ClassMetadata as ClassMetadataInterface;
use Doctrine\Common\Persistence\Mapping\ReflectionService;
use Doctrine\Common\Persistence\Mapping\AbstractClassMetadataFactory;
use Doctrine\ODM\CouchDB\Event;

/**
 * The ClassMetadataFactory is used to create ClassMetadata objects that contain all the
 * metadata mapping information of a class which describes how a class should be mapped
 * to a document database.

 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link        www.doctrine-project.com
 * @since       1.0
 * @author      Benjamin Eberlei <kontakt@beberlei.de>
 * @author      Lukas Kahwe Smith <smith@pooteeweet.org>
 */
class ClassMetadataFactory extends AbstractClassMetadataFactory
{
    /**
     * @var DocumentManager
     */
    private $dm;

    /**
     *  The used metadata driver.
     *
     * @var MappingDriver
     */
    private $driver;

    /**
     * @var EventManager
     */
    private $evm;


    /**
     * {@inheritdoc}
     */
    protected function doLoadMetadata($class, $parent, $rootEntityFound, array $nonSuperclassParents)
    {
        /** @var $parent ClassMetaData */
        if ($parent) {
            $this->addAssociationsMapping($class, $parent);
            $this->addFieldMapping($class, $parent);
            $this->addIndexes($class, $parent);
            $parent->deriveChildMetadata($class);
            $class->setParentClasses($nonSuperclassParents);
        }

        if ($this->getDriver()) {
            $this->getDriver()->loadMetadataForClass($class->getName(), $class);
        }

        $this->validateMapping($class);

        if ($this->evm->hasListeners(Event::loadClassMetadata)) {
            $eventArgs = new \Doctrine\ODM\CouchDB\Event\LoadClassMetadataEventArgs($class, $this->dm);
            $this->evm->dispatchEvent(Event::loadClassMetadata, $eventArgs);
        }
    }

    /**
     * Check for any possible shortcomings in the class:
     *
     * The class must have an identifier field unless it's an embedded document or mapped superclass.
     */
    private function validateMapping(ClassMetadataInterface $class)
    {
        if (!$class->identifier && !$class->isEmbeddedDocument && !$class->isMappedSuperclass) {
            throw new MappingException("An identifier (@Id) field is required in {$class->getName()}.");
        }
    }

    private function addFieldMapping(ClassMetadataInterface $class, ClassMetadataInterface $parent)
    {
        foreach ($parent->reflFields as $name => $field) {
            $class->reflFields[$name] = $field;
        }

        foreach ($parent->fieldMappings as $name => $field) {
            $class->fieldMappings[$name] = $field;
        }

        foreach ($parent->jsonNames as $name => $field) {
            $class->jsonNames[$name] = $field;
        }

        if ($parent->identifier) {
            $class->setIdentifier($parent->identifier);
        }
    }

    private function addIndexes(ClassMetadata $class, ClassMetadata $parent)
    {
        $class->indexes = $parent->indexes;
    }

    /**
     *
     * @param ClassMetadataInterface $class
     * @param ClassMetadataInterface $parent
     */
    private function addAssociationsMapping(ClassMetadataInterface $class, ClassMetadataInterface $parent)
    {
        foreach ($parent->associationsMappings as $name => $field) {
            $class->associationsMappings[$name] = $field;
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function getFqcnFromAlias($namespaceAlias, $simpleClassName)
    {
        return $this->dm->getConfiguration()->getDocumentNamespace($namespaceAlias) . '\\' . $simpleClassName;
    }

    /**
     * Forces the factory to load the metadata of all classes known to the underlying
     * mapping driver.
     *
     * @return array The ClassMetadata instances of all mapped classes.
     */
    public function getAllMetadata()
    {
        $metadata = array();
        foreach ($this->driver->getAllClassNames() as $className) {
            $metadata[] = $this->getMetadataFor($className);
        }

        return $metadata;
    }

    /**
     * Gets the class metadata descriptor for a class.
     *
     * @param string $className The name of the class.
     *
     * @throws MappingException
     * @return ClassMetadata
     */
    public function getMetadataFor($className)
    {
        $metadata = parent::getMetadataFor($className);

        if ($metadata) {
            return $metadata;
        }

        throw MappingException::classNotMapped($className);
    }

    /**
     * Loads the metadata of the class in question and all it's ancestors whose metadata
     * is still not loaded.
     *
     * @param string $className The name of the class for which the metadata should get loaded.
     *
     * @throws MappingException
     * @return array
     */
    protected function loadMetadata($className)
    {
        if (class_exists($className)) {
            return parent::loadMetadata($className);
        }
        throw MappingException::classNotFound($className);
    }

    /**
     * Creates a new ClassMetadata instance for the given class name.
     *
     * @param string $className
     * @return ClassMetadata
     */
    protected function newClassMetadataInstance($className)
    {
        return new ClassMetadata($className);
    }

    /**
     * {@inheritdoc}
     */
    protected function getDriver()
    {
        return $this->driver;
    }

    /**
     * {@inheritdoc}
     */
    protected function initialize()
    {
        $config = $this->dm->getConfiguration();
        $this->setCacheDriver($config->getMetadataCacheImpl());
        $this->driver = $config->getMetadataDriverImpl();
        $this->evm = $this->dm->getEventManager();
        if (!$this->driver) {
            throw new \RuntimeException('No metadata driver was configured.');
        }
        $this->initialized = true;
    }

    /**
     * {@inheritdoc}
     */
    protected function initializeReflection(ClassMetadataInterface $class, ReflectionService $reflService)
    {
        $class->initializeReflection($reflService);
    }

    /**
     * {@inheritdoc}
     */
    protected function wakeupReflection(ClassMetadataInterface $class, ReflectionService $reflService)
    {
        $class->wakeupReflection($reflService);
    }

    /**
     * {@inheritDoc}
     */
    protected function isEntity(ClassMetadataInterface $class)
    {
        return isset($class->isMappedSuperclass) && $class->isMappedSuperclass === false;
    }

    /**
     * @param \Doctrine\ODM\CouchDB\DocumentManager $dm
     */
    public function setDocumentManager($dm)
    {
        $this->dm = $dm;
    }

}
