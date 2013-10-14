<?php
namespace Ratchet\Wamp;
use Ratchet\MessageComponentInterface;
use Ratchet\WebSocket\WsServerInterface;
use Ratchet\ConnectionInterface;

/**
 * WebSocket Application Messaging Protocol
 *
 * @link http://wamp.ws/spec
 * @link https://github.com/oberstet/AutobahnJS
 *
 * +--------------+----+------------------+
 * | Message Type | ID | DIRECTION        |
 * |--------------+----+------------------+
 * | WELCOME      | 0  | Server-to-Client |
 * | PREFIX       | 1  | Bi-Directional   |
 * | CALL         | 2  | Client-to-Server |
 * | CALL RESULT  | 3  | Server-to-Client |
 * | CALL ERROR   | 4  | Server-to-Client |
 * | SUBSCRIBE    | 5  | Client-to-Server |
 * | UNSUBSCRIBE  | 6  | Client-to-Server |
 * | PUBLISH      | 7  | Client-to-Server |
 * | EVENT        | 8  | Server-to-Client |
 * +--------------+----+------------------+
 */
class ServerProtocol implements MessageComponentInterface, WsServerInterface {
    const MSG_WELCOME     = 0;
    const MSG_PREFIX      = 1;
    const MSG_CALL        = 2;
    const MSG_CALL_RESULT = 3;
    const MSG_CALL_ERROR  = 4;
    const MSG_SUBSCRIBE   = 5;
    const MSG_UNSUBSCRIBE = 6;
    const MSG_PUBLISH     = 7;
    const MSG_EVENT       = 8;

    /**
     * @var WampServerInterface
     */
    protected $_decorating;

    /**
     * @var \SplObjectStorage
     */
    protected $connections;

    /**
     * @param WampServerInterface $serverComponent An class to propagate calls through
     */
    public function __construct(WampServerInterface $serverComponent) {
        $this->_decorating = $serverComponent;
        $this->connections = new \SplObjectStorage;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubProtocols() {
        if ($this->_decorating instanceof WsServerInterface) {
            $subs   = $this->_decorating->getSubProtocols();
            $subs[] = 'wamp';

            return $subs;
        } else {
            return array('wamp');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function onOpen(ConnectionInterface $conn) {
        $decor = new WampConnection($conn);
        $this->connections->attach($conn, $decor);

        $this->_decorating->onOpen($decor);
    }

    /**
     * {@inheritdoc}
     * @throws \Exception
     * @throws JsonException
     */
    public function onMessage(ConnectionInterface $from, $msg) {
        $from = $this->connections[$from];

        if (null === ($json = @json_decode($msg, true))) {
            throw new JsonException;
        }

        if (!is_array($json) || $json !== array_values($json)) {
            throw new \UnexpectedValueException("Invalid WAMP message format");
        }

        switch ($json[0]) {
            case static::MSG_PREFIX:
                $from->WAMP->prefixes[$json[1]] = $json[2];
            break;

            case static::MSG_CALL:
                array_shift($json);
                $callID  = array_shift($json);
                $procURI = array_shift($json);

                if (count($json) == 1 && is_array($json[0])) {
                    $json = $json[0];
                }

                $this->_decorating->onCall($from, $callID, $procURI, $json);
            break;

            case static::MSG_SUBSCRIBE:
                $this->_decorating->onSubscribe($from, $from->getUri($json[1]));
            break;

            case static::MSG_UNSUBSCRIBE:
                $this->_decorating->onUnSubscribe($from, $from->getUri($json[1]));
            break;

            case static::MSG_PUBLISH:
                $exclude  = (array_key_exists(3, $json) ? $json[3] : null);
                if (!is_array($exclude)) {
                    if (true === (boolean)$exclude) {
                        $exclude = array($from->WAMP->sessionId);
                    } else {
                        $exclude = array();
                    }
                }

                $eligible = (array_key_exists(4, $json) ? $json[4] : array());

                $this->_decorating->onPublish($from, $from->getUri($json[1]), $json[2], $exclude, $eligible);
            break;

            default:
                throw new Exception('Invalid message type');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function onClose(ConnectionInterface $conn) {
        $decor = $this->connections[$conn];
        $this->connections->detach($conn);

        $this->_decorating->onClose($decor);
    }

    /**
     * {@inheritdoc}
     */
    public function onError(ConnectionInterface $conn, \Exception $e) {
        return $this->_decorating->onError($this->connections[$conn], $e);
    }
}