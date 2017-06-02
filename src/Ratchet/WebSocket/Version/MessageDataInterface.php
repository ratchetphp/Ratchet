<?php
namespace Ratchet\WebSocket\Version;

interface MessageDataInterface extends DataInterface {
    /**
     * @param FrameInterface $fragment
     * @return MessageDataInterface
     */
    function addFrame(FrameInterface $fragment);

    /**
     * @return int
     */
    function getOpcode();
}
