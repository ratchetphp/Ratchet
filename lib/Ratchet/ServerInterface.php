<?php
namespace Ratchet;

/**
 * @deprecated
 */
interface ServerInterface extends \IteratorAggregate {
    function attatchReceiver(ReceiverInterface $receiver);

    function run($address = '127.0.0.1', $port = 1025);
}