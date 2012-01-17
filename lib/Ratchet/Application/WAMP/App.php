<?php
namespace Ratchet\Application\WAMP;
use Ratchet\Application\ApplicationInterface;
use Ratchet\Application\WebSocket\WebSocketAppInterface;
use Ratchet\Resource\Connection;

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
 */
class App implements WebSocketAppInterface {
    protected $_app;

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

    public function onOpen(Connection $conn) {
        $conn->WAMP                = new \StdClass;
        $conn->WAMP->prefixes      = array();
        $conn->WAMP->subscriptions = array();

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
            case 1:
                return $this->addPrefix($conn, $json[2], $json[1]);
            break;

            case 2:
                return $this->_app->onCall($from, $json[1], $json[2]);
            break;

            case 5:
                return $this->_app->onSubscribe($from, $json[1]);
            break;

            case 6:
                return $this->_app->onUnSubscribe($from, $json[1]);
            break;

            case 7:
                return $this->_app->onPublish($from, $json[1], $json[2]);
            break;

            default:
                throw new Exception('Invalid message type');
        }
    }

    public function __construct(ServerInterface $app) {
        $this->_app = $app;
    }

    public function onClose(Connection $conn) {
        return $this->_app->onClose($conn);
    }

    public function onError(Connection $conn, \Exception $e) {
        return $this->_app->onError($conn, $e);
    }
}