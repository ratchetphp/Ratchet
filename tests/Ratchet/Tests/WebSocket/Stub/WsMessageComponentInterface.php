<?php
namespace Ratchet\Tests\WebSocket\Stub;
use Ratchet\MessageComponentInterface;
use Ratchet\WebSocket\WsServerInterface;

interface WsMessageComponentInterface extends MessageComponentInterface, WsServerInterface {
}