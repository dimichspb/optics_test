<?php
namespace optics;

//This class managed in-memory entities and commmunicates with the storage class (DataStore in our case).
use SplObserver;

class EntityManager implements \SplSubject
{

    protected $_entities = array();

    protected $_entityIdToPrimary = array();

    protected $_entityPrimaryToId = array();

    protected $_entitySaveList = array();

    protected $_nextId = null;

    protected $_dataStore = null;

    /**
     * @var \SplObjectStorage
     */
    private $_observers;

    public function __construct($storePath)
    {
        $this->_observers = new \SplObjectStorage();

        $this->_dataStore = new DataStore($storePath);

        $this->_nextId = 1;

        $itemTypes = $this->_dataStore->getItemTypes();
        foreach ($itemTypes as $itemType)
        {
            $itemKeys = $this->_dataStore->getItemKeys();
            foreach ($itemKeys as $itemKey) {
                $entity = $this->create($itemType, $this->_dataStore->get($itemType, $itemKey), true);
            }
        }
    }

    /**
     * create an entity
     *
     * @param $entityName
     * @param $data
     * @param bool $fromStore
     * @return Entity
     */
    public function create($entityName, $data, $fromStore = false)
    {
        $entityName = __NAMESPACE__ . '\\' . $entityName;
        /** @var Entity $entity */
        $entity = new $entityName;
        $entity->_entityName = $entityName;
        $entity->_data = $data;
        $entity->_em = Entity::getDefaultEntityManager();
        $id = $entity->_id = $this->_nextId++;
        $this->_entities[$id] = $entity;
        $primary = $data[$entity->getPrimary()];
        $this->_entityIdToPrimary[$id] = $primary;
        $this->_entityPrimaryToId[$primary] = $id;
        if ($fromStore !== true) {
            $this->_entitySaveList[] = $id;
        }

        return $entity;
    }

    //update
    public function update(Entity $entity, $newData)
    {
        if ($newData === $entity->_data) {
            //Nothing to do
            return $entity;
        }

        $this->_entitySaveList[] = $entity->_id;
        $oldPrimary = $entity->{$entity->getPrimary()};
        $newPrimary = $newData[$entity->getPrimary()];
        if ($oldPrimary != $newPrimary)
        {
            $this->_dataStore->delete(get_class($entity),$oldPrimary);
            unset($this->_entityPrimaryToId[$oldPrimary]);
            $this->_entityIdToPrimary[$entity->_id] = $newPrimary;
            $this->_entityPrimaryToId[$newPrimary] = $entity->_id;
        }
        $entity->_data = $newData;
        $this->notify();

        return $entity;
    }

    //Delete
    public function delete(Entity $entity)
    {
        $id = $entity->_id;
        $entity->_id = null;
        $entity->_data = null;
        $entity->_em = null;
        $this->_entities[$id] = null;
        $primary = $entity->{$entity->getPrimary()};
        $this->_dataStore->delete(get_class($entity),$primary);
        unset($this->_entityIdToPrimary[$id]);
        unset($this->_entityPrimaryToId[$primary]);
        return null;
    }

    public function findByPrimary($entity, $primary)
    {
        if (isset($this->_entityPrimaryToId[$primary])) {
            $id = $this->_entityPrimaryToId[$primary];
            return $this->_entities[$id];
        } else {
            return null;
        }
    }

    //Update the datastore to update itself and save.
    public function updateStore() {
        foreach($this->_entitySaveList as $id) {
            $entity = $this->_entities[$id];
            $this->_dataStore->set(get_class($entity),$entity->{$entity->getPrimary()},$entity->_data);
        }
        $this->_dataStore->save();
    }

    public function attach(SplObserver $observer)
    {
        $this->_observers->attach($observer);
    }

    public function detach(SplObserver $observer)
    {
        $this->_observers->detach($observer);
    }

    public function notify()
    {
        /** @var \SplObserver $observer */
        foreach ($this->_observers as $observer) {
            $observer->update($this);
        }
    }


}