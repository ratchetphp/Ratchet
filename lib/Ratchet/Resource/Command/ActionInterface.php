<?php
namespace Ratchet\Command;
use Ratchet\SocketInterface;

/**
 * A single command tied to 1 socket connection
 */
interface ActionInterface extends CommandInterface {
    /**
     * Pass the Sockets to execute the command on
     * @param Ratchet\SocketInterface
     */
    function __construct(SocketInterface $socket);

    /**
     * @return Ratchet\SocketInterface
     */
    function getSocket();
}