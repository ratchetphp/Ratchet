<?php
namespace Ratchet\WebSocket\Version;

interface MessageInterface extends DataInterface
{
    /**
     * @param  FrameInterface   $fragment
     * @return MessageInterface
     */
    public function addFrame(FrameInterface $fragment);

    /**
     * @return int
     */
    public function getOpcode();
}
