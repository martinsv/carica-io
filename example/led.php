<?php

include('../src/Io/Loader.php');
Carica\Io\Loader::register();

use Carica\Io\Event;
use Carica\Io\Stream;
use Carica\Io\Firmata;

$board = new Firmata\Board(
  new Stream\SerialPort('COM3')
);

$loop = Io\Event\Loop\Factory::get();

$active = $board->activate(
  function ($error = NULL) use ($board, $loop) {
    if (isset($error)) {
      echo $error."\n";
      return;
    }
    echo "activated\n";

    $led = 13;
    $board->pinMode($led, Firmata\OUTPUT);

    $loop->add(
      new Event\Loop\Listener\Interval(
        1000,
        function () use ($led) {
          static $ledOn = FALSE;
          $board->digitalWrite($led, $ledOn ? Firmata\LOW : Firmata\HIGH);
          $ledOn = !$ledOn;
        }
      )
    );
  }
);

if ($active) {
  $loop->run();
}


