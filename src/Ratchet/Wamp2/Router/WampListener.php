<?php

namespace Ratchet\Wamp2\Router;


use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;
use Ratchet\Wamp2\Router\WampClientProxy;
use Ratchet\Wamp2\WampFormatterInterface;
use Ratchet\Wamp2\WampJsonFormatter;
use Ratchet\Wamp2\WampProtocol;
use Ratchet\Wamp2\Router\WampServerIncomingMessageHandler;
use Ratchet\Wamp2\Router\WampServerIncomingMessageHandlerInterface;
use Ratchet\Wamp2\Router\WampServerInterface;
use Ratchet\WebSocket\WsServerInterface;

class WampListener implements MessageComponentInterface, WsServerInterface {

    /**
     * @var WampServerInterface
     */
    protected $_decorating;

    /**
     * @var WampServerIncomingMessageHandlerInterface
     */
    protected $handler;

    /**
     * @var WampFormatterInterface
     */
    protected $formatter;

    /**
     * @var WampProtocol
     */
    protected $protocol;

    /**
     * @var \SplObjectStorage
     */
    protected $connections;

    function __construct($serverComponent)
    {
        $this->_decorating = $serverComponent;
        $this->handler = new WampServerIncomingMessageHandler($serverComponent);
        $this->formatter = new WampJsonFormatter();
        $this->connections = new \SplObjectStorage;
        $this->protocol = new WampProtocol();
    }

    /**
     * If any component in a stack supports a WebSocket sub-protocol return each supported in an array
     * @return array
     * @temporary This method may be removed in future version (note that will not break code, just make some code obsolete)
     */
    function getSubProtocols()
    {
        // TODO: move this to another place, so also 'wamp.2.msgpack' can be supported
        if ($this->_decorating instanceof WsServerInterface) {
            $subs   = $this->_decorating->getSubProtocols();
            $subs[] = 'wamp.2.json';

            return $subs;
        } else {
            return array('wamp.2.json');
        }
    }

    /**
     * When a new connection is opened it will be passed to this method
     * @param  ConnectionInterface $conn The socket/connection that just connected to your application
     * @throws \Exception
     */
    function onOpen(ConnectionInterface $conn)
    {
        $decor = new WampClientProxy($conn, $this->formatter, $this->protocol);
        $this->connections->attach($conn, $decor);

        $this->_decorating->onOpen($decor);
    }

    /**
     * This is called before or after a socket is closed (depends on how it's closed).  SendMessage to $conn will not result in an error if it has already been closed.
     * @param  ConnectionInterface $conn The socket/connection that is closing/closed
     * @throws \Exception
     */
    function onClose(ConnectionInterface $conn)
    {
        $decor = $this->connections[$conn];
        $this->connections->detach($conn);

        $this->_decorating->onClose($decor);
    }

    /**
     * If there is an error with one of the sockets, or somewhere in the application where an Exception is thrown,
     * the Exception is sent back down the stack, handled by the Server and bubbled back up the application through this method
     * @param  ConnectionInterface $conn
     * @param  \Exception $e
     * @throws \Exception
     */
    function onError(ConnectionInterface $conn, \Exception $e)
    {
        return $this->_decorating->onError($this->connections[$conn], $e);
    }

    /**
     * Triggered when a client sends data through the socket
     * @param  \Ratchet\ConnectionInterface $from The socket/connection that sent the message to your application
     * @param  string $msg The message received
     * @throws \Exception
     */
    function onMessage(ConnectionInterface $from, $msg)
    {
        $client = $this->connections[$from];

        $message = $this->formatter->deserialize($msg);

        $this->handler->handleMessage($client, $message);
    }
}