<?php
namespace Ratchet\Logging;
use Monolog\Logger;

class MonologAdapter extends Logger implements LoggerInterface {
    function note($msg) {
        $this->addInfo($msg);
    }

    function warning($msg) {
        $this->addWarning($msg);
    }

    function error($msg) {
        $this->addError($msg);
    }
}