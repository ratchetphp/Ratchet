<?php
namespace Ratchet;

interface SocketInterface {
    function write($buffer, $length = 0);

    function recv(&$buf, $len, $flags);

    function close();
}