<?php
namespace Ratchet\WebSocket\Version;

interface MessageInterface extends DataInterface {
    /**
     * @param FragmentInterface
     * @return MessageInterface
     */
    function addFrame(FrameInterface $fragment);

    /**
     * @return int
     */
    function getOpcode();
}