<?php
namespace Ratchet\WebSocket\Encoding;

interface ValidatorInterface {
    /**
     * Verify a string matches the encoding type
     * @param  string $str      The string to check
     * @param  string $encoding The encoding type to check against
     * @return bool
     */
    function checkEncoding($str, $encoding);
}