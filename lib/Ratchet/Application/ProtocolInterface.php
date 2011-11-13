<?php
namespace Ratchet\Application;
use Ratchet\ObserverInterface;

interface ProtocolInterface extends ObserverInterface {
    /**
     * @param Ratchet\ObserverInterface Application to wrap in protocol
     */
//    function __construct(ObserverInterface $application);

    /**
     * @return array
     */
    static function getDefaultConfig();
}