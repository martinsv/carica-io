<?php
include('../../src/Io/Loader.php');
Carica\Io\Loader::register();

use Carica\Io;

$board = new Io\Firmata\Board(
  //new Io\Stream\Serial('COM3')
  new Io\Stream\Tcp('127.0.0.1', 5333)
);

$loop = Io\Event\Loop\Factory::get();

$board
  ->activate()
  ->done(
    function () use ($board, $loop) {
      echo "Firmata ".$board->version." active\n";

      $dimmerPin = 14;
      $ledPin = 9;
      $board->pins[$dimmerPin]->mode = Io\Firmata\PIN_STATE_ANALOG;
      $board->pins[$ledPin]->mode = Io\Firmata\PIN_STATE_PWM;

      $board->analogRead(
        $dimmerPin,
        function($value) use ($board, $ledPin) {
          $value = 1023 - $value;
          $barLength = round($value * 0.07);
          $dimmed = floor($value * 255 / 1023);
          echo str_pad($dimmed, 3, 0, STR_PAD_LEFT), ' ';
          echo str_repeat('=', $barLength), "\n";
          $board->pins[$ledPin]->analog = $dimmed;
        }
      );
    }
  )
  ->fail(
    function ($error) {
      echo $error."\n";
    }
  );

if ($board->isActive()) {
  $loop->run();
}