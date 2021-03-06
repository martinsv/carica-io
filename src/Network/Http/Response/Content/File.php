<?php

namespace Carica\Io\Network\Http\Response\Content {

  use Carica\Io;
  use Carica\Io\Network;
  use Carica\Io\Network\Http\Response;

  class File
    extends
      Response\Content
    implements
      Io\Event\HasLoop,
      Io\File\HasAccess {

    use Io\File\Access\Aggregation;
    use Io\Event\Loop\Aggregation;

    private $_filename = NULL;
    private $_bufferSize = 51200;

    public function __construct($filename, $type = 'application/octet-stream', $encoding = '') {
      parent::__construct($type, $encoding);
      $this->_filename = (string)$filename;
    }

    public function sendTo(Network\Connection $connection) {
      if ($file = $this->fileAccess()->getFileResource($this->_filename)) {
        $defer = new Io\Deferred();
        $bytes = $this->_bufferSize;
        $that = $this;
        $interval = $this->loop()->setInterval(
          function () use ($file, $bytes, $defer, $connection) {
            if ($connection->isActive() && is_resource($file)) {
              if (feof($file)) {
                $defer->resolve();
              } else {
                $connection->write(fread($file, $bytes));
                return;
              }
            } else {
              $defer->reject();
            }
          },
          100
        );
        $defer->always(
          function () use ($that, $interval) {
            $that->loop()->remove($interval);
          }
        );
        return $defer->promise();
      }
      return FALSE;
    }

    public function getLength() {
      return $this->fileAccess()->getInfo($this->_filename)->getSize();
    }
  }
}
