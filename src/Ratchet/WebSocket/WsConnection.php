<?php
namespace Ratchet\WebSocket;
use Ratchet\ConnectionInterface;
use Ratchet\AbstractConnectionDecorator;
use Ratchet\WebSocket\Version\VersionInterface;

/**
 * {@inheritdoc}
 * @property stdClass $WebSocket
 */
class WsConnection extends AbstractConnectionDecorator {
    /**
     * @var Ratchet\WebSocket\Version\VersionInterface
     */
    protected $version = null;

    public function __construct(ConnectionInterface $conn) {
        parent::__construct($conn);

        $this->WebSocket = new \StdClass;
    }

    public function send($data) {
        if ($this->hasVersion()) {
            // need frame caching
            $data = $this->WebSocket->version->frame($data, false);
        }

        $this->getConnection()->send($data);
    }

    public function close() {
        // send close frame with code 1000

        // ???

        // profit

        $this->getConnection()->close(); // temporary
    }

    /**
     * @return boolean
     * @internal
     */
    public function hasVersion() {
        return (null === $this->version);
    }

    /**
     * Set the WebSocket protocol version to communicate with
     * @param Ratchet\WebSocket\Version\VersionInterface
     * @internal
     */
    public function setVersion(VersionInterface $version) {
        $this->WebSocket->version = $version;

        return $this;
    }
}