<?php
namespace Ratchet\Logging;

interface LoggerInterface {
    /**
     * @param string
     */
    function note($msg);

    /**
     * @param string
     */
    function warning($msg);

    /**
     * @param string
     */
    function error($msg);
}