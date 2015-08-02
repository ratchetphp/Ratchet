<?php
namespace Ratchet\WebSocket;

/**
 * WebSocket Server Interface
 */
interface WsServerInterface {
    /**
     * If any component in a stack supports a WebSocket sub-protocol return each supported in an array
     * @return array
     * @todo This method may be removed in future version (note that will not break code, just make some code obsolete)
     */
    function getSubProtocols();
}
