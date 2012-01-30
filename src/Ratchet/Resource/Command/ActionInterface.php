<?php
namespace Ratchet\Resource\Command;
use Ratchet\Resource\Connection;

/**
 * A single command tied to 1 socket connection
 */
interface ActionInterface extends CommandInterface {
    /**
     * Pass the Sockets to execute the command on
     * @param Ratchet\Resource\Connection
     */
    function __construct(Connection $conn);

    /**
     * @return Ratchet\Command\Connection
     */
    function getConnection();
}