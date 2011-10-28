<?php
namespace Ratchet\Logging;
use Monolog\Logger;

/**
 * Adapt the awesome Monolog Logger into the lowly Ratchet Logger
 */
class MonologAdapter extends Logger implements LoggerInterface {
    /**
     * Maps to Monolog\Logger::addInfo
     * @param string
     */
    function note($msg) {
        $this->addInfo($msg);
    }

    /**
     * Maps to Monolog\Logger::addWarning
     * @param string
     */
    function warning($msg) {
        $this->addWarning($msg);
    }

    /**
     * Maps to Monolog\Logger::addError
     * @param string
     */
    function error($msg) {
        $this->addError($msg);
    }
}