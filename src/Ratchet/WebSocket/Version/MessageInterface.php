<?php
namespace Ratchet\WebSocket\Version;

interface MessageInterface extends DataInterface {
    /**
     * @param FrameInterface $fragment
     * @return MessageInterface
     */
    function addFrame(FrameInterface $fragment);

    /**
     * @return int
     */
    function getOpcode();
}
