<?php
namespace Ratchet\Protocol;

class WebSocket implements ProtocolInterface {
    /**
     * @return Array
     */
    public static function getDefaultConfig() {
        return Array(
            'domain'   => AF_INET
          , 'type'     => SOCK_STREAM
          , 'protocol' => SOL_TCP
          , 'options'  => Array(
                SOL_SOCKET => Array(SO_REUSEADDR => 1)
            )
        );
    }

    /**
     * @return string
     */
    function getName() {
        return __CLASS__;
    }

    function handleConnect() {
    }

    function handleMessage() {
    }

    function handleClose() {
    }
}