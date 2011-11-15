<?php
namespace Ratchet\Protocol\WebSocket;

/**
 * @todo Consider making parent interface/composite for Message/Frame with (isCoalesced, getOpcdoe, getPayloadLength, getPayload)
 */
interface MessageInterface {
    /**
     * @return bool
     */
    function isCoalesced();

    /**
     * @param FragmentInterface
     */
    function addFragment(FragmentInterface $fragment);

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