<?php
namespace Ratchet\WebSocket\Version;

interface FrameInterface {
    /**
     * Dunno if I'll use this
     * Thinking could be used if a control frame?
     */
//    function __invoke();

    /**
     * @return bool
     */
    function isCoalesced();

    /**
     * @param string
     * @todo Theoretically, there won't be a buffer overflow (end of frame + start of new frame) - but test later, return a string with overflow here
     */
    function addBuffer($buf);

    /**
     * @return bool
     */
//    function isFragment();

    /**
     * @return bool
     */
    function isFinal();

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
//    function getReceivedPayloadLength();

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