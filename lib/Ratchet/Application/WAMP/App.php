<?php
namespace Ratchet\Application\WAMP;
use Ratchet\Application\ApplicationInterface;
use Ratchet\Application\WebSocket\WebSocketAppInterface;
use Ratchet\Resource\Connection;

/**
 * WebSocket Application Messaging Protocol
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
 * @todo I can't make up my mind what interface to present to the server application
 */
class App implements WebSocketAppInterface {
    protected $_app;

    protected static $_incoming = array(1, 2, 5, 6, 7);

    public function getSubProtocol() {
        return 'wamp';
    }

    /**
     * @todo WAMP spec does not say what to do when there is an error with PREFIX...
     */
    public function addPrefix(Connection $conn, $uri, $curie) {
        // validate uri
        // validate curie

        // make sure the curie is shorter than the uri

        $conn->prefixes[$uri] = $curie;
    }

    public function sendEvent($uri, $event) {
    }

    public function onCall(Connection $conn, $id, $uri) {
    }

    public function onOpen(Connection $conn) {
        $conn->WAMP                = new \StdClass;
        $conn->WAMP->prefixes      = array();
        $conn->WAMP->subscriptions = array();
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

        if (!in_array($json[0], static::$_incoming)) {
            throw new Exception('Invalid message type');
        }

        if ($json[0] == 1) {
            $this->addPrefix($conn, $json[2], $json[1]);
        }

        // Determine WAMP message type, call $_this->_app->on();
    }

    public function __construct(ServerInterface $app) {
        $this->_app = $app;
    }

    public function onClose(Connection $conn) {
        // remove all prefixes associated with connection? or will those just be destroyed w/ Connection
    }

    public function onError(Connection $conn, \Exception $e) {
    }
}