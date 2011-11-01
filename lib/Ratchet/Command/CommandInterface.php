<?php
namespace Ratchet\Command;
use Ratchet\SocketCollection;

/**
 * Socket implementation of the Command Pattern
 * User created applications are to return a Command to the server for execution
 * @todo Bad format - very limited
 */
interface CommandInterface {
    /**
     * Pass the Sockets to execute the command on
     */
    function __construct(SocketCollection $sockets);

    /**
     * The Server class will call the execution
     */
    function execute();
}