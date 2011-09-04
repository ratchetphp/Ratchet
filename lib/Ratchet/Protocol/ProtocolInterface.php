<?php
namespace Ratchet\Protocol;
use Ratchet\Server;

interface ProtocolInterface {
    function __construct(Server $server);
}