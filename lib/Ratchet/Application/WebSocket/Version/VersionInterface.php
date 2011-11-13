<?php
namespace Ratchet\Application\WebSocket\Version;

/**
 * Despite the version iterations of WebInterface the actions they go through are similar
 * This standardizes how the server handles communication with each protocol version
 */
interface VersionInterface {
    /**
     * Perform the handshake and return the response headers
     * @param string
     * @return array|string
     */
    function handshake($message);

    /**
     * Get a framed message as per the protocol and return the decoded message
     * @param string
     * @return string
     * @todo Return a frame object with message, type, masked?
     */
    function unframe($message);

    /**
     * @param string
     * @return string
     */
    function frame($message);
}