<?php
namespace Ratchet\Application;
use Ratchet\ObserverInterface;

interface ApplicationInterface extends ObserverInterface {
    /**
     * Decorator pattern
     * @param Ratchet\ObserverInterface Application to wrap in protocol
     * @throws UnexpectedValueException
     */
    public function __construct(ApplicationInterface $app = null);
}