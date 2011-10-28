<?php
namespace Ratchet\Logging;

/**
 * A logger used by the server and extending applications for debugging info
 */
interface LoggerInterface {
    /**
     * Just an informational log
     * @param string
     */
    function note($msg);

    /**
     * A problem, but nothing too serious
     * @param string
     */
    function warning($msg);

    /**
     * Bad things have happened...
     * @param string
     */
    function error($msg);
}