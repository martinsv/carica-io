<?php

namespace Carica\Io {

  class Callbacks implements \IteratorAggregate, \Countable {

    private $_callbacks = array();
    private $_disabled = FALSE;
    private $_locked = FALSE;

    public function add(Callable $callback) {
      $hash = $this->getCallableHash($callback);
      if (!($this->_locked || isset($this->_callbacks[$hash]))) {
        $this->_callbacks[$hash] = $callback;
      }
    }

    public function remove(Callable $callback) {
      $hash = $this->getCallableHash($callback);
      if (!$this->_locked && isset($this->_callbacks[$hash])) {
        unset($this->_callbacks[$hash]);
      }
    }

    public function clear() {
      if (!$this->_locked) {
        $this->_callbacks = array();
      }
    }

    public function has(Callable $callback) {
      $hash = $this->getCallableHash($callback);
      return isset($this->_callbacks[$hash]);
    }

    public function lock() {
      $this->_locked = TRUE;
    }

    public function locked() {
      return $this->_locked;
    }

    public function disable() {
      $this->_disabled = TRUE;
    }

    public function disabled() {
      return $this->_diabled;
    }

    public function fire() {
      if (!$this->_disabled) {
        $arguments = func_get_args();
        foreach ($this->_callbacks as $callback) {
          call_user_func_array($callback, $arguments);
        }
      }
    }

    public function __invoke() {
      return call_user_func_array(array($this, 'fire'), func_get_args());
    }

    public function __get($functionName) {
      if (method_exists($this, $functionName)) {
        $callback = array($this, $functionName);
        return function() use ($callback) {
          call_user_func_array($callback, func_get_args());
        };
      }
    }

    public function getIterator() {
      return new \ArrayIterator(array_values($this->_callbacks));
    }

    public function count() {
      return count($this->_callbacks);
    }

    private function getCallableHash($callable) {
      if (is_object($callable)) {
        return spl_object_hash($callable);
      } elseif (is_array($callable)) {
        return md5($this->getCallableHash($callable[0]), $callable[1]);
      } else {
        return md5((string)$callable);
      }
    }
  }
}