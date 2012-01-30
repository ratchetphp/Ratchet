<?php
namespace Ratchet\Resource\Command;
use Ratchet\Resource\ConnectionInterface;

/**
 * A single command tied to 1 socket connection
 */
interface ActionInterface extends CommandInterface {
    /**
     * Pass the Sockets to execute the command on
     * @param Ratchet\Resource\Connection
     */
    function __construct(ConnectionInterface $conn);

    /**
     * @return Ratchet\Command\Connection
     */
    function getConnection();
}