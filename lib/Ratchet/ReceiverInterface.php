<?php
namespace Ratchet;
use Ratchet\Server;
use Ratchet\Server\Client;
use Ratchet\Server\Message;

interface ReceiverInterface {
    /**
     * @return string
     */
    function getName();

    function setUp(Server $server);

    function handleConnect(Socket $client);

    function handleMessage($message, Socket $from);

    function handleClose(Socket $client);
}