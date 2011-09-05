<?php
namespace Ratchet;

interface ServerInterface {
    function attatchReceiver(ReceiverInterface $receiver);

    function run($address = '127.0.0.1', $port = 1025);
}