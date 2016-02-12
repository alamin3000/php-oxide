<?php

/**
 * Oxide Framework
 *
 * @link https://github.com/alamin3000/php-oxide Git source code
 * @copyright (c) 2014, Alamin Ahmed
 * @license http://URL name
 */
namespace Oxide\Common\Pattern;

use SplObjectStorage;

trait ObservableTrait
{

    protected
        /**
         * @var SplObjectStorage
         */
        $_t_observers = null;

    /**
     * Get the
     * @return SplObjectStorage
     */
    protected function getObserverStorage()
    {
        if ($this->_t_observers === null) {
            $this->_t_observers = new SplObjectStorage();
        }

        return $this->_t_observers;
    }

    /**
     *
     * @param type $object
     * @param type $method
     */
    public function attachObserver($object, $method)
    {
        $this->getObserverStorage()->attach($object, $method);
    }

    /**
     *
     * @param type $object
     */
    public function deatchObserver($object)
    {
        $this->getObserverStorage()->detach($object);
    }

    /**
     *
     * @param type $args
     */
    public function notifyObservers(...$args)
    {
        foreach ($this->getObserverStorage() as $object => $method) {
            $object->$method($this, ...$args);
        }
    }
}