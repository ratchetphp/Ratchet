<?php
namespace Ratchet\Component;
use Ratchet\Resource\ConnectionInterface;

/**
 * This is the interface to build a Ratchet application with
 * It impelemtns the decorator and command pattern to build an application stack
 */
interface ComponentInterface {
    /**
     * When a new connection is opened it will be passed to this method
     * @param Ratchet\Resource\Connection The socket/connection that just connected to your application
     * @return Ratchet\Resource\Command\CommandInterface|null
     * @throws Exception
     */
    function onOpen(ConnectionInterface $conn);

    /**
     * This is called before or after a socket is closed (depends on how it's closed).  SendMessage to $conn will not result in an error if it has already been closed.
     * @param Ratchet\Resource\Connection The socket/connection that is closing/closed
     * @return Ratchet\Resource\Command\CommandInterface|null
     * @throws Exception
     */
    function onClose(ConnectionInterface $conn);

    /**
     * If there is an error with one of the sockets, or somewhere in the application where an Exception is thrown,
     * the Exception is sent back down the stack, handled by the Server and bubbled back up the application through this method
     * @param Ratchet\Resource\Connection
     * @param \Exception
     * @return Ratchet\Resource\Command\CommandInterface|null
     * @throws Exception
     */
    function onError(ConnectionInterface $conn, \Exception $e);
}