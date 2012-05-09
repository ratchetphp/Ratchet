<?php
namespace Ratchet\WebSocket\Version;

/**
 * @todo Consider making parent interface/composite for Message/Frame with (isCoalesced, getOpcdoe, getPayloadLength, getPayload)
 */
interface MessageInterface {
    /**
     * @alias getPayload
     */
    function __toString();

    /**
     * @return bool
     */
    function isCoalesced();

    /**
     * @param FragmentInterface
     */
    function addFrame(FrameInterface $fragment);

    /**
     * @return int
     */
    function getOpcode();

    /**
     * @return int
     */
    function getPayloadLength();

    /**
     * @return string
     */
    function getPayload();
}