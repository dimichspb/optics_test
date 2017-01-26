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
     */
    public function update(\SplSubject $subject)
    {
        $this->_changedEntities[] = clone $subject;
        $this->log($subject);
    }

    /**
     * @return Entity[]
     */
    public function getChangedUsers()
    {
        return $this->_changedEntities;
    }

    abstract protected function log(\SplSubject $subject);
}