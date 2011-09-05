<?php
namespace Ratchet\Protocol;
use Ratchet\ReceiverInterface;

interface ProtocolInterface extends ReceiverInterface {
    /**
     * @return Array
     */
    static function getDefaultConfig();
}