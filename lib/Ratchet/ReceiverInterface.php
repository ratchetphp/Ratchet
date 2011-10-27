<?php
namespace Ratchet;
use Ratchet\Server;
use Ratchet\SocketObserver;

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