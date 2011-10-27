<?php
namespace Ratchet;

interface SocketInterface {
    /**
     * @param string
     * @param int
     */
    function write($buffer, $length = 0);

    /**
     * @param string
     * @param int
     * @param int
     */
    function recv(&$buf, $len, $flags);

    function close();
}