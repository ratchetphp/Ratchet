<?php
namespace Ratchet\Protocol;
use Ratchet\ServerInterface;

interface ProtocolInterface extends ServerInterface {
    /**
     * @return Array
     */
    static function getDefaultConfig();
}