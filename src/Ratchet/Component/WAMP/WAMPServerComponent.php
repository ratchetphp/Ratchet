<?php
namespace Ratchet\Component\WAMP;
use Ratchet\Component\ComponentInterface;
use Ratchet\Component\WebSocket\WebSocketComponentInterface;
use Ratchet\Resource\ConnectionInterface;
use Ratchet\Resource\Command\Composite;
use Ratchet\Resource\Command\CommandInterface;
use Ratchet\Component\WAMP\Command\Action\Prefix;

/**
 * WebSocket Application Messaging Protocol
 * 
 * +--------------+----+------------------+
 * | Message Type | ID | DIRECTION        |
 * |--------------+----+------------------+
 * | PREFIX       | 1  | Bi-Directional   |
 * | CALL         | 2  | Client-to-Server |
 * | CALL RESULT  | 3  | Server-to-Client |
 * | CALL ERROR   | 4  | Server-to-Client |
 * | SUBSCRIBE    | 5  | Client-to-Server |
 * | UNSUBSCRIBE  | 6  | Client-to-Server |
 * | PUBLISH      | 7  | Client-to-Server |
 * | EVENT        | 8  | Server-to-Client |
 * +--------------+----+------------------+
 * @link http://www.tavendo.de/autobahn/protocol.html
 * @link https://raw.github.com/oberstet/Autobahn/master/lib/javascript/autobahn.js
 */
class WAMPServerComponent implements WebSocketComponentInterface {
    const MSG_WELCOME     = 0;
    const MSG_PREFIX      = 1;
    const MSG_CALL        = 2;
    const MSG_CALL_RESULT = 3;
    const MSG_CALL_ERROR  = 4;
    const MSG_SUBSCRIBE   = 5;
    const MSG_UNSUBSCRIBE = 6;
    const MSG_PUBLISH     = 7;
    const MSG_EVENT       = 8;

    protected $_decorating;

    /**
     * Any server to client prefixes are stored here
     * They're taxied along with the next outgoing message
     * @var Ratchet\Resource\Command\Composite
     */
    protected $_msg_buffer = null;

    public function getSubProtocol() {
        return 'wamp';
    }

    /**
     * @todo WAMP spec does not say what to do when there is an error with PREFIX...
     */
    public function addPrefix(ConnectionInterface $conn, $curie, $uri, $from_server = false) {
        // validate uri
        // validate curie

        // make sure the curie is shorter than the uri

        $conn->WAMP->prefixes[$curie] = $uri;

        if ($from_server) {
            $prefix = new Prefix($conn);
            $prefix->setPrefix($curie, $uri);

            $this->_msg_buffer->enqueue($prefix);
        }
    }

    public function onOpen(ConnectionInterface $conn) {
        $conn->WAMP                = new \StdClass;
        $conn->WAMP->prefixes      = array();
        $conn->WAMP->subscriptions = array();

        $wamp = $this;
        $conn->WAMP->addPrefix = function($curie, $uri) use ($wamp, $conn) {
            $wamp->addPrefix($conn, $curie, $uri, true);
        };

        return $this->_decorating->onOpen($conn);
    }

    /**
     * @{inherit}
     * @throws Exception
     * @throws JSONException
     */
    public function onMessage(ConnectionInterface $from, $msg) {
        if (null === ($json = @json_decode($msg, true))) {
            throw new JSONException;
        }

        switch ($json[0]) {
            case static::MSG_PREFIX:
                $ret = $this->addPrefix($from, $json[1], $json[2]);
            break;

            case static::MSG_CALL:
                array_shift($json);
                $callID  = array_shift($json);
                $procURI = array_shift($json);

                if (count($json) == 1 && is_array($json[0])) {
                    $json = $json[0];
                }

                $ret = $this->_decorating->onCall($from, $callID, $procURI, $json);
            break;

            case static::MSG_SUBSCRIBE:
                $ret = $this->_decorating->onSubscribe($from, $this->getUri($from, $json[1]));
            break;

            case static::MSG_UNSUBSCRIBE:
                $ret = $this->_decorating->onUnSubscribe($from, $this->getUri($from, $json[1]));
            break;

            case static::MSG_PUBLISH:
                $ret = $this->_decorating->onPublish($from, $this->getUri($from, $json[1]), $json[2]);
            break;

            default:
                throw new Exception('Invalid message type');
        }

        return $this->attachStack($ret);
    }

    /**
     * Get the full request URI from the connection object if a prefix has been established for it
     * @param Ratchet\Resource\Connection
     * @param ...
     * @return string
     */
    protected function getUri(ConnectionInterface $conn, $uri) {
        return (isset($conn->WAMP->prefixes[$uri]) ? $conn->WAMP->prefixes[$uri] : $uri);
    }

    /**
     * If the developer's application as set some server-to-client prefixes to be set,
     * this method ensures those are taxied to the next outgoing message
     * @param Ratchet\Resource\Command\CommandInterface|NULL
     * @return Ratchet\Resource\Command\Composite
     */
    protected function attachStack(CommandInterface $command = null) {
        $stack = $this->_msg_buffer;
        $stack->enqueue($command);

        $this->_msg_buffer = new Composite;

        return $stack;
    }

    public function __construct(WAMPServerComponentInterface $server_component) {
        $this->_decorating = $server_component;
        $this->_msg_buffer = new Composite;
    }

    public function onClose(ConnectionInterface $conn) {
        return $this->_decorating->onClose($conn);
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        return $this->_decorating->onError($conn, $e);
    }
}