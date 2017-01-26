<?php
namespace optics;

abstract class Entity
{
    static protected $_defaultEntityManager = null;

    protected $_data = null;

    /**
     * @var EntityManager
     */
    protected $_em = null;
    protected $_entityName = null;
    protected $_id = null;

    public function init() {}

    abstract public function getMembers();

    abstract public function getPrimary();

    //setter for properies and items in the underlying data array
    public function __set($variableName, $value)
    {
        if (array_key_exists($variableName, array_change_key_case($this->getMembers()))) {
            $newData = $this->_data;
            $newData[$variableName] = $value;
            $this->update($newData);
            $this->_data = $newData;
        } else {
            if (property_exists($this, $variableName)) {
                $this->$variableName = $value;
            } else {
                throw new Exception("Set failed. Class " . get_class($this) .
                    " does not have a member named " . $variableName . ".");
            }
        }
    }

    //getter for properies and items in the underlying data array
    public function __get($variableName)
    {
        if (array_key_exists($variableName, array_change_key_case($this->getMembers()))) {
            $data = $this->read();
            return $data[$variableName];
        } else {
            if (property_exists($this, $variableName)) {
                return $this->$variableName;
            } else {
                throw new Exception("Get failed. Class " . get_class($this) .
                    " does not have a member named " . $variableName . ".");
            }
        }
    }

    static public function setDefaultEntityManager($em)
    {
        self::$_defaultEntityManager = $em;
    }

    /**
     * Factory function for making entities.
     *
     * @param $entityName
     * @param $data
     * @param null $entityManager
     * @return Entity
     */
    static public function getEntity($entityName, $data, $entityManager = null)
    {
        /** @var EntityManager $em */

        $em = $entityManager === null ? self::$_defaultEntityManager : $entityManager;
        $entity = $em->create($entityName, $data);
        $entity->init();
        return $entity;
    }

    static public function getDefaultEntityManager()
    {
        return self::$_defaultEntityManager;
    }

    public function create($entityName, $data)
    {
        $entity = self::getEntity($entityName, $data);
        return $entity;
    }

    public function read()
    {
        return $this->_data;
    }

    public function update($newData)
    {
        $this->_em->update($this, $newData);
        $this->_data = $newData;
    }

    public function delete()
    {
        $this->_em->delete($this);
    }
}