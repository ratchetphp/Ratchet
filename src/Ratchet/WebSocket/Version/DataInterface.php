<?php
namespace Ratchet\WebSocket\Version;

interface DataInterface
{
    /**
     * Determine if the message is complete or still fragmented
     * @return bool
     */
    public function isCoalesced();

    /**
     * Get the number of bytes the payload is set to be
     * @return int
     */
    public function getPayloadLength();

    /**
     * Get the payload (message) sent from peer
     * @return string
     */
    public function getPayload();

    /**
     * Get raw contents of the message
     * @return string
     */
    public function getContents();
}
