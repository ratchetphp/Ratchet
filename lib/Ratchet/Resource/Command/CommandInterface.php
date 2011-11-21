<?php
namespace Ratchet\Resource\Command;
use Ratchet\Application\ApplicationInterface;

/**
 * Socket implementation of the Command Pattern
 * User created applications are to return a Command to the server for execution
 */
interface CommandInterface {
    /**
     * The Server class will call the execution
     * @param Ratchet\ApplicationInterface Scope to execute the command under
     * @return CommandInterface|NULL
     */
    function execute(ApplicationInterface $scope = null);
}