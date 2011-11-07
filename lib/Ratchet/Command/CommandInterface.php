<?php
namespace Ratchet\Command;
use Ratchet\SocketInterface;

/**
 * Socket implementation of the Command Pattern
 * User created applications are to return a Command to the server for execution
 */
interface CommandInterface {
    /**
     * Pass the Sockets to execute the command on
     * @param Ratchet\SocketInterface
     */
    function __construct(SocketInterface $socket);

    /**
     * The Server class will call the execution
     */
    function execute();
}