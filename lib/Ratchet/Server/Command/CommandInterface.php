<?php
namespace Ratchet\Server\Command;
use Ratchet\SocketCollection;

interface CommandInterface {
    function __construct(SocketCollection $sockets);

    function execute();
}