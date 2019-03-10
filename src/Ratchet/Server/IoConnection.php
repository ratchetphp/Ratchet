<?php
namespace Ratchet\Server;
use Ratchet\ConnectionInterface;
use Ratchet\ConnectionPropertyNotFoundException;
use React\Socket\ConnectionInterface as ReactConn;

/**
 * {@inheritdoc}
 */
final class IoConnection implements ConnectionInterface {
    /**
     * @var \React\Socket\ConnectionInterface
     */
    private $conn;

    private $properties = [];

    public function __construct(ReactConn $conn) {
        $this->conn = $conn;

        $uri = $conn->getRemoteAddress();
        $this->properties['Socket.remoteAddress'] = trim(
            parse_url((strpos($uri, '://') === false ? 'tcp://' : '') . $uri, PHP_URL_HOST),
            '[]'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function send($data) {
        $this->conn->write($data);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function close() {
        $this->conn->end();
    }

    public function get($id) {
        if (!$this->has($id)) {
            throw new ConnectionPropertyNotFoundException("{$id} not found");
        }
    }

    public function has($id) {
        return array_key_exists($id, $this->properties);
    }
}
