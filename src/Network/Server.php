<?php

namespace Carica\Io\Network {

  use Carica\Io;

  class Server
    implements
      Io\Event\HasEmitter,
      Io\Event\HasLoop {

    use Io\Event\Emitter\Aggregation;
    use Io\Event\Loop\Aggregation;

    private $_listener = NULL;

    private $_stream = FALSE;

    private $_address = 'tcp://0.0.0.0';

    public function __construct($address = 'tcp://0.0.0.0') {
      $this->_address = $address;
    }

    public function __destruct() {
      $this->close();
    }

    public function resource($stream = NULL) {
      if (isset($stream)) {
        $this->_stream = $stream;
        if (isset($this->_listener)) {
          $this->loop()->remove($this->_listener);
        }
        if ($this->isActive()) {
          $that = $this;
          $this->loop()->setStreamReader(
            function() use ($that) {
              $that->accept();
            },
            $stream
          );
        }
      }
      return $this->_stream;
    }

    public function isActive() {
      return is_resource($this->_stream);
    }

    public function listen($port = 8080) {
      if (!$this->isActive()) {
        $stream = stream_socket_server($this->_address.':'.$port, $errorNumber, $errorString);
        if ($stream) {
          stream_set_blocking($stream, 0);
          $this->resource($stream);
          $this->events()->emit('listen', $this->_address.':'.$port);
          return TRUE;
        }
        return FALSE;
      }
      return TRUE;
    }

    public function close() {
      if ($this->isActive()) {
        stream_socket_shutdown($this->resource(), STREAM_SHUT_RDWR);
      }
      $this->resource(FALSE);
    }

    public function accept() {
      if ($this->isActive() && ($stream = @stream_socket_accept($this->resource(), 1, $peer))) {
        $this->events()->emit('connection', $stream, $peer);
      }
    }
  }
}
