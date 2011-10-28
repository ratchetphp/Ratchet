<?php
namespace Ratchet\Protocol\WebSocket\Version;

/**
 * Despite the version iterations of WebInterface the actions they go through are similar
 * This standardizes how the server handles communication with each protocol version
 */
interface VersionInterface {
    /**
     * Perform the handshake and return the response headers
     * @param array
     * @return array
     */
    function handshake(array $headers);

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

    /**
     * Used when doing the handshake to encode the key, verifying client/server are speaking the same language
     * @param string
     * @return string
     * @internal
     */
    function sign($key);
}