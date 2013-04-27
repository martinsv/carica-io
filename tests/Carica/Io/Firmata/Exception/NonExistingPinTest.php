<?php

namespace Carica\Io\Firmata\Exception {

  include_once(__DIR__.'/../../Bootstrap.php');

  class NonExistingPinTest extends \PHPUnit_Framework_TestCase {

    public function testConstructor() {
      $exception = new NonExistingPin(42);
      $this->assertEquals(
        'Pin 42 does not exists.',
        $exception->getMessage()
      );
    }
  }
}
