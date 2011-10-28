<?php
namespace Ratchet\Logging;

/**
 * Sends all logs into the void
 * No one can hear you scream in /dev/null
 */
class NullLogger implements LoggerInterface {
    function note($msg) {
    }

    function warning($msg) {
    }

    function error($msg) {
    }
}