<?php
namespace Ratchet\Protocol\WebSocket;

interface FrameInterface {
    /**
     * @return bool
     */
    function isCoalesced();

    /**
     * @param string
     */
    function addBuffer($buf);

    /**
     * @return bool
     */
    function isFragment();

    /**
     * @return bool
     */
    function isFinial();

    /**
     * @return bool
     */
    function isMasked();

    /**
     * @return int
     */
    function getOpcode();

    /**
     * @return int
     */
    function getPayloadLength();

    /**
     * @return int
     */
    function getReceivedPayloadLength();

    /**
     * 32-big string
     * @return string
     */
    function getMaskingKey();

    /**
     * @param string
     */
    function getPayload();
}