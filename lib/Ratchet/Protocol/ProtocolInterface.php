<?php
namespace Ratchet\Protocol;
use Ratchet\SocketObserver;

interface ProtocolInterface extends SocketObserver {
    /**
     * @param Ratchet\SocketObserver Application to wrap in protocol
     */
    function __construct(SocketObserver $application);

    /**
     * @return Array
     */
    static function getDefaultConfig();
}