<?php
namespace Ratchet\Wamp;

class JsonException extends Exception {
    public function __construct() {
        $code = json_last_error();

        switch ($code) {
            case JSON_ERROR_DEPTH:
                $msg = 'Maximum stack depth exceeded';
            break;
            case JSON_ERROR_STATE_MISMATCH:
                $msg = 'Underflow or the modes mismatch';
            break;
            case JSON_ERROR_CTRL_CHAR:
                $msg = 'Unexpected control character found';
            break;
            case JSON_ERROR_SYNTAX:
                $msg = 'Syntax error, malformed JSON';
            break;
            case JSON_ERROR_UTF8:
                $msg = 'Malformed UTF-8 characters, possibly incorrectly encoded';
            break;
            default:
                $msg = 'Unknown error';
            break;
        }

        parent::__construct($msg, $code);
    }
}