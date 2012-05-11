<?php
namespace Ratchet\WebSocket\Version;
use Guzzle\Http\Message\RequestInterface;

/**
 * Despite the version iterations of WebInterface the actions they go through are similar
 * This standardizes how the server handles communication with each protocol version
 * @todo Need better naming conventions...newMessage and newFrame are for reading incoming framed messages (action is unframing)
 *       The current method names suggest you could create a new message/frame to send, which they can not do
 */
interface VersionInterface {
    /**
     * Given an HTTP header, determine if this version should handle the protocol
     * @param Guzzle\Http\Message\RequestInterface
     * @return bool
     * @throws UnderflowException If the protocol thinks the headers are still fragmented
     */
    static function isProtocol(RequestInterface $request);

    /**
     * Perform the handshake and return the response headers
     * @param Guzzle\Http\Message\RequestInterface
     * @return array|string
     * @throws InvalidArgumentException If the HTTP handshake is mal-formed
     * @throws UnderflowException If the message hasn't finished buffering (not yet implemented, theoretically will only happen with Hixie version)
     * @todo Change param to accept a Guzzle RequestInterface object
     */
    function handshake(RequestInterface $request);

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
     * @param bool
     * @return string
     * @todo Change to use other classes, this will be removed eventually
     */
    function frame($message, $mask = true);
}