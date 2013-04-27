<?php

namespace Carica\Io\Firmata\Response\Sysex {

  use Carica\Io\Firmata;

  class PulseIn extends Firmata\Response\Sysex {

    private $_pin = 0;
    private $_duration = 0;

    public function __construct($command, array $bytes) {
      parent::__construct($command, $bytes);
      $this->_pin = $bytes[1];
      $data = self::decodeBytes(array_slice($bytes, 3));
      $duration = unpack('N', $data);
      $this->_duration = $duration[1];
    }

    public function __get($name) {
      switch ($name) {
      case 'pin' :
        return $this->_pin;
      case 'duration' :
        return $this->_duration;
      }
      parent::__get($name);
    }
  }
}