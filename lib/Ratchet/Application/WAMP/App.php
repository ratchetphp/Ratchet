<?php
namespace Ratchet\Application\WAMP;
use Ratchet\Application\ApplicationInterface;
use Ratchet\Application\WebSocket\WebSocketAppInterface;
use Ratchet\Resource\Connection;
use Ratchet\Resource\Command\Composite;
use Ratchet\Resource\Command\CommandInterface;
use Ratchet\Application\WAMP\Command\Action\Prefix;

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
class App implements WebSocketAppInterface {
    const MSG_WELCOME     = 0;
    const MSG_PREFIX      = 1;
    const MSG_CALL        = 2;
    const MSG_CALL_RESULT = 3;
    const MSG_CALL_ERROR  = 4;
    const MSG_SUBSCRIBE   = 5;
    const MSG_UNSUBSCRIBE = 6;
    const MSG_PUBLISH     = 7;
    const MSG_EVENT       = 8;

    protected $_app;

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
    public function addPrefix(Connection $conn, $curie, $uri, $from_server = false) {
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

    public function onOpen(Connection $conn) {
        $conn->WAMP                = new \StdClass;
        $conn->WAMP->prefixes      = array();
        $conn->WAMP->subscriptions = array();

        $wamp = $this;
        $conn->WAMP->addPrefix = function($curie, $uri) use ($wamp, $conn) {
            $wamp->addPrefix($conn, $curie, $uri, true);
        };

        return $this->_app->onOpen($conn);
    }

    /**
     * @{inherit}
     * @throws Exception
     * @throws JSONException
     */
    public function onMessage(Connection $from, $msg) {
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

                $ret = $this->_app->onCall($from, $callID, $procURI, $json);
            break;

            case static::MSG_SUBSCRIBE:
                $ret = $this->_app->onSubscribe($from, $this->getUri($from, $json[1]));
            break;

            case static::MSG_UNSUBSCRIBE:
                $ret = $this->_app->onUnSubscribe($from, $this->getUri($from, $json[1]));
            break;

            case static::MSG_PUBLISH:
                $ret = $this->_app->onPublish($from, $this->getUri($from, $json[1]), $json[2]);
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
    protected function getUri(Connection $conn, $uri) {
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

    public function __construct(ServerInterface $app) {
        $this->_app        = $app;
        $this->_msg_buffer = new Composite;
    }

    public function onClose(Connection $conn) {
        return $this->_app->onClose($conn);
    }

    public function onError(Connection $conn, \Exception $e) {
        return $this->_app->onError($conn, $e);
    }
}