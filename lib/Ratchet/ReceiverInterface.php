<?php
namespace Ratchet;
use Ratchet\Server;
use Ratchet\SocketObserver;

interface ReceiverInterface extends SocketObserver {
    /**
     * @return string
     */
    function getName();

    function setUp(Server $server);
}