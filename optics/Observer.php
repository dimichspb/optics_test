<?php
namespace optics;

use SplSubject;

abstract class Observer implements \SplObserver
{
    /**
     * @var Entity[]
     */
    private $_changedEntities = [];

    /**
     * @param \SplSubject $subject
     * @param Entity $entity
     */
    public function update(\SplSubject $subject, Entity $entity = null)
    {
        /** @var EntityManager $subject */
        if ($entity) {
            $this->_changedEntities[] = clone $entity;
            $this->log($subject, $entity);
        }
    }

    /**
     * @return Entity[]
     */
    public function getChangedUsers()
    {
        return $this->_changedEntities;
    }

    abstract protected function log(\SplSubject $subject, Entity $entity = null);
}