<?php
namespace Oxide\Support;

use Oxide\Support\Pattern\SharedInstanceTrait;

/**
 * Event Notifier class
 *
 * Centralizes event notification system. Object can notify any event, while other objects can register to listen to perticular event
 *
 * @package oxide
 * @subpackage engine
 */
class EventNotifier
{
    use SharedInstanceTrait;

    protected
        /**
         *
         * @var array Holds all listeneres
         */
        $_listeners = [];

    /**
     * construction
     *
     * @return
     *
     */
    public function __construct()
    {
    }

    /**
     * Register a $callable for an $event broadcast
     *
     * @param string $event
     * @param callable $callback
     * @param mixed $scope
     */
    public function register($event, callable $callback, $scope = null)
    {
        $callable_name = null;
        if (is_callable($callback, true, $callable_name)) {
            $this->_listeners[$event][$callable_name] = [
                $callback,
                $scope
            ];
        } else {
            throw new \InvalidArgumentException('Event callback is not callable.');
        }
    }

    /**
     * Unregister given $callback from the given $event
     *
     * @param string $event
     * @param callable $callback
     */
    public function unregister($event, callable $callback)
    {
        $callable_name = null;
        if (is_callable($callback, true, $callable_name)) {
            if (isset($this->_listeners[$event][$callable_name])) {
                $this->_listeners[$event][$callable_name] = null;
                unset($this->_listeners[$event][$callable_name]);
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     * Request an $event to be broadcast in $scope with given $args
     *
     * @param string $event
     * @param mixed $scope
     * @param mixed $args
     *            arguments to be passed to the listener
     */
    public function notify($event, $scope, $args = null)
    {
        // if no listeners for the event, do nothing
        if (!isset($this->_listeners[$event])) {
            return;
        }

        $args = func_get_args();
        foreach ($this->_listeners[$event] as $listener_name => $listener) {
            list ($listener_callback, $listener_scope) = $listener;
            if ($listener_scope && $listener_scope != $scope) {
                continue;
            } // if scope provided, it must match

            if (call_user_func_array($listener_callback, $args) === false) {
                throw new \Exception('Unable to broadcast to [' . $listener_name . ']');
            }
        }
    }

    /**
     * check if event listeners are available
     *
     * @param string $event
     *            check for listeneres on perticular event
     * @return bool
     */
    public function hasListener($event = null)
    {
        if ($event) {
            return isset($this->_listeners[$event]);
        }
        return (count($this->_listeners) > 0);
    }
}
