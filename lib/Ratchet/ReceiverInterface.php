<?php
namespace Ratchet;
use Ratchet\Server;
use Ratchet\SocketObserver;

/**
 * Decorator interface for internal protocols
 * @todo Should probably move this into \Ratchet\Server namespace
 */
interface ReceiverInterface extends SocketObserver {
    /**
     * @return string
     */
    function getName();

    /**
     * @param Ratchet\Server
     */
    function setUp(Server $server);
}