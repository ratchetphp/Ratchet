<?php
namespace Ratchet\Protocol\WebSocket\Version;

class Hixie76 implements VersionInterface {
    protected $_headers = array();

    public function __construct(array $headers) {
    }

    /**
     * @param Headers
     * @return string
     */
    public function concatinateKeyString($headers) {
        
    }

    /**
     * @param string
     * @return string
     */
    public function sign($key) {
        
    }
}