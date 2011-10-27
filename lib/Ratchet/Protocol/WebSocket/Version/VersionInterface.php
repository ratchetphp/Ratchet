<?php
namespace Ratchet\Protocol\WebSocket\Version;

interface VersionInterface {
    /**
     * @param array
     */
    function __construct(array $headers);

    /**
     * @param string
     * @return string
     */
    function sign($header);
}