<?php
namespace Ratchet\Application\WebSocket\Version;

/**
 * Despite the version iterations of WebInterface the actions they go through are similar
 * This standardizes how the server handles communication with each protocol version
 * @todo Need better naming conventions...newMessage and newFrame are for reading incoming framed messages (action is unframing)
 *       The current method names suggest you could create a new message/frame to send, which they can not do
 */
interface VersionInterface {
    /**
     * Given an HTTP header, determine if this version should handle the protocol
     * @param array
     * @return bool
     * @throws UnderflowException If the protocol thinks the headers are still fragmented
     */
    static function isProtocol(array $headers);

    /**
     * Perform the handshake and return the response headers
     * @param string
     * @return array|string
     */
    function handshake($message);

    /**
     * @return MessageInterface
     */
    function newMessage();

    /**
     * @return FrameInterface
     */
    function newFrame();

    /**
     * @param string
     * @return string
     * @todo Change to use other classes, this will be removed eventually
     */
    function frame($message);
}