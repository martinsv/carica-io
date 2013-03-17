<?php

namespace Carica\Io\Firmata {

  include_once(__DIR__.'/../Bootstrap.php');

  class PinTest extends \PHPUnit_Framework_TestCase {

    /*
     * @covers Carica\Io\Firmata\Pin::__construct
     */
    public function testConstructor() {
      $board = $this->getBoardFixture();
      $pin = new Pin($board, 12, array(PIN_STATE_OUTPUT));
      $this->assertSame($board, $pin->board);
      $this->assertEquals(12, $pin->pin);
      $this->assertEquals(array(PIN_STATE_OUTPUT), $pin->supports);
    }

    /**
     * @covers Carica\Io\Firmata\Pin::__isset
     * @dataProvider providePinProperties
     */
    public function testPropertyIsset($propertyName) {
      $pin = new Pin($this->getBoardFixture(), 12, array(PIN_STATE_OUTPUT));
      $this->assertTrue(isset($pin->{$propertyName}));
    }

    /**
     * @covers Carica\Io\Firmata\Pin::__isset
     */
    public function testPropertyIssetWithInvalidPropertyExpectingFalse() {
      $pin = new Pin($this->getBoardFixture(), 12, array(PIN_STATE_OUTPUT));
      $this->assertFalse(isset($pin->INVALID_PROPERTY));
    }

    /*
     * @covers Carica\Io\Firmata\Pin::__get
     */
    public function testGetBoard() {
      $board = $this->getBoardFixture();
      $pin = new Pin($board, 12, array(PIN_STATE_OUTPUT));
      $this->assertSame($board, $pin->board);
    }

    /*
     * @covers Carica\Io\Firmata\Pin::__set
     */
    public function testSetBoardExpectingException() {
      $board = $this->getBoardFixture();
      $pin = new Pin($board, 12, array(PIN_STATE_OUTPUT));
      $this->setExpectedException('LogicException');
      $pin->board = $board;
    }

    /*
     * @covers Carica\Io\Firmata\Pin::__get
     */
    public function testGetPin() {
      $pin = new Pin($this->getBoardFixture(), 12, array(PIN_STATE_OUTPUT));
      $this->assertEquals(12, $pin->pin);
    }

    /*
     * @covers Carica\Io\Firmata\Pin::__set
     */
    public function testSetPinExpectingException() {
      $pin = new Pin($this->getBoardFixture(), 12, array(PIN_STATE_OUTPUT));
      $this->setExpectedException('LogicException');
      $pin->pin = 13;
    }

    /*
     * @covers Carica\Io\Firmata\Pin::__get
     */
    public function testGetSupports() {
      $pin = new Pin($this->getBoardFixture(), 12, array(PIN_STATE_OUTPUT, PIN_STATE_ANALOG));
      $this->assertEquals(array(PIN_STATE_OUTPUT, PIN_STATE_ANALOG), $pin->supports);
    }

    /*
     * @covers Carica\Io\Firmata\Pin::__set
     */
    public function testSetSupportsExpectingException() {
      $pin = new Pin($this->getBoardFixture(), 12, array(PIN_STATE_OUTPUT));
      $this->setExpectedException('LogicException');
      $pin->supports = array();
    }

    /*
     * @covers Carica\Io\Firmata\Pin::__get
     */
    public function testGetMode() {
      $pin = new Pin($this->getBoardFixture(), 12, array(PIN_STATE_OUTPUT, PIN_STATE_ANALOG));
      $this->assertEquals(array(PIN_STATE_OUTPUT, PIN_STATE_ANALOG), $pin->supports);
    }

    /*
     * @covers Carica\Io\Firmata\Pin::__set
     * @covers Carica\Io\Firmata\Pin::__get
     * @covers Carica\Io\Firmata\Pin::setMode
     */
    public function testSetMode() {
      $board = $this->getBoardFixture();
      $board
        ->expects($this->once())
        ->method('pinMode')
        ->with(12, PIN_STATE_OUTPUT);

      $pin = new Pin($board, 12, array(PIN_STATE_OUTPUT, PIN_STATE_ANALOG));
      $pin->mode = PIN_STATE_OUTPUT;
      $this->assertEquals(PIN_STATE_OUTPUT, $pin->mode);
    }

    /*
     * @covers Carica\Io\Firmata\Pin::__set
     * @covers Carica\Io\Firmata\Pin::__get
     * @covers Carica\Io\Firmata\Pin::setMode
     */
    public function testSetModeTwoTimeOnlySentOneTime() {
      $board = $this->getBoardFixture();
      $board
        ->expects($this->once())
        ->method('pinMode')
        ->with(12, PIN_STATE_ANALOG);

      $pin = new Pin($board, 12, array(PIN_STATE_OUTPUT, PIN_STATE_ANALOG));
      $pin->mode = PIN_STATE_ANALOG;
      $pin->mode = PIN_STATE_ANALOG;
      $this->assertEquals(PIN_STATE_ANALOG, $pin->mode);
    }

    /*
     * @covers Carica\Io\Firmata\Pin::__set
     * @covers Carica\Io\Firmata\Pin::setMode
     */
    public function testSetModeWithUnsupportedModeExpectingException() {
      $board = $this->getBoardFixture();
      $board
        ->expects($this->never())
        ->method('pinMode');

      $pin = new Pin($board, 12, array(PIN_STATE_OUTPUT));
      $this->setExpectedException('OutOfBoundsException');
      $pin->mode = PIN_STATE_ANALOG;
    }

    /*****************
     * Fixtures
     *****************/

    private function getBoardFixture() {
      $board = $this
        ->getMockBuilder('Carica\Io\Firmata\Board')
        ->disableOriginalConstructor()
        ->getMock();
      $board
        ->expects($this->any())
        ->method('events')
        ->will($this->returnValue($this->getMock('Carica\Io\Event\Emitter')));
      return $board;
    }

    /*****************
     * Data Provider
     *****************/

    public static function providePinProperties() {
      return array(
        array('board'),
        array('pin'),
        array('supports'),
        array('mode'),
        array('value'),
        array('digital'),
        array('analog')
      );
    }
  }
}