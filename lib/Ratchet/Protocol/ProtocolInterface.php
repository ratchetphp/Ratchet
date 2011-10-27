<?php
namespace Ratchet\Protocol;
use Ratchet\ReceiverInterface;

interface ProtocolInterface extends ReceiverInterface {
    /**
     * @param Ratchet\ReceiverInterface
     */
    function __construct(ReceiverInterface $application);

    /**
     * @return Array
     */
    static function getDefaultConfig();
}