<?php

namespace Carica\Io\Event\Loop {

  use Carica\Io;
  use Carica\Io\Event;

  class Libevent implements Event\Loop {

    /**
     * @var resource
     */
    private $_base = NULL;
    /**
     * @var Libevent\Listener[]
     */
    private $_timers = array();
    /**
     * @var Libevent\Listener\Stream[]
     */
    private $_streams = array();

    /**
     * @param resource $base
     */
    public function __construct($base) {
      $this->_base = $base;
    }

    /**
     * @param callable $callback
     * @param integer $milliseconds
     *
     * @return Libevent\Listener\Timeout
     */
    public function setTimeout(Callable $callback, $milliseconds) {
      $event = new Libevent\Listener\Timeout($this, $callback, $milliseconds);
      $this->_timers[spl_object_hash($event)] = $event();
      return $event;
    }

    /**
     * @param callable $callback
     * @param integer $milliseconds
     *
     * @return Libevent\Listener\Interval
     */
    public function setInterval(Callable $callback, $milliseconds) {
      $event = new Libevent\Listener\Interval($this, $callback, $milliseconds);
      $this->_timers[spl_object_hash($event)] = $event();
      return $event;
    }

    /**
     * @param callable $callback
     * @param resource $stream
     *
     * @return mixed
     * @throws \LogicException
     */
    public function setStreamReader(Callable $callback, $stream) {
      if (!is_resource($stream)) {
        throw new \LogicException('%s needs a valid stream resource.', __METHOD__);
      }
      $index = (int)$stream;
      if (!isset($this->_streams[$index])) {
        $this->_streams[$index] = $listener = new Libevent\Listener\Stream($this, $stream);
      } else {
        $listener = $this->_streams[$index];
      }
      $result = $listener->onRead($callback);
      return $result;
    }

    public function remove($event) {
      /**
       * @var Libevent\Listener $listener
       */
      if (isset($event)) {
        $key = spl_object_hash($event);
        if (array_key_exists($key, $this->_timers)) {
          $listener = $this->_timers[$key];
          $listener->cancel();
          unset($this->_timers[$key]);
        }
        if ($event instanceOf Libevent\Listener\Stream\Callback) {
          $event->remove();
        } elseif ($event instanceOf Libevent\Listener\Stream &&
                  ($stream = $event->getStream())) {
          $index = (int)$stream;
          if (is_resource($stream) && isset($this->_streams[$index])) {
            $listener = $this->_streams[$index];
            $listener->cancel();
            unset($this->_streams[$index]);
          }
        }
      }
    }

    public function run(Io\Deferred\Promise $for = NULL) {
      if (isset($for) &&
          $for->state() === Io\Deferred::STATE_PENDING) {
        $loop = $this;
        $for->always(
          function () use ($loop) {
            $loop->stop();
          }
        );
      }
      if (isset($for) &&
          $for->state() !== Io\Deferred::STATE_PENDING) {
        event_base_loop($this->_base, EVLOOP_ONCE);
      } else {
        event_base_loop($this->_base);
      }
    }

    public function stop() {
      event_base_loopbreak($this->_base);
    }

    public function getBase() {
      return $this->_base;
    }

    public function count() {
      return count($this->_timers) + count($this->_streams);
    }
  }
}