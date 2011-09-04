<?php
namespace Ratchet\Protocol\WebSocket\Version;

interface VersionInterface {
    /**
     * @param string
     * @return string
     */
    function sign($header);
}