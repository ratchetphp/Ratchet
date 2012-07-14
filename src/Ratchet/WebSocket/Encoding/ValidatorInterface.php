<?php
namespace Ratchet\WebSocket\Encoding;

interface ValidatorInterface {
    /**
     * Verify a string matches the encoding type
     * @param string The string to check
     * @param string The encoding type to check against
     * @return bool
     */
    function checkEncoding($str, $encoding);
}