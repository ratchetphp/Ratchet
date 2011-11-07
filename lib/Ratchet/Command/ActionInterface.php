<?php
namespace Ratchet\Command;
use Ratchet\SocketInterface;

interface ActionInterface extends CommandInterface {
    /**
     * Pass the Sockets to execute the command on
     * @param Ratchet\SocketInterface
     */
    function __construct(SocketInterface $socket);
}