<?php
namespace Ratchet\Logging;

class NullLogger implements LoggerInterface {
    function note($msg) {
    }

    function warning($msg) {
    }

    function error($msg) {
    }
}