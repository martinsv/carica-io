<?php

namespace Carica\Io {

  interface Stream extends Event\HasEmitter {

    function isOpen();

    function open();

    function close();

    function read($bytes = 1024);

    function write($data);

  }

  function encodeBinaryFromArray(array $data) {
    array_unshift($data, 'C*');
    return call_user_func_array('pack', $data);
  }

  function decodeBinaryToArray($data) {
    return array_slice(unpack("C*", "\0".$data), 1);
  }

}