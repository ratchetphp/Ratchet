<?php
namespace Ratchet\WebSocket;
use Ratchet\AbstractConnectionDecorator;
use Ratchet\ConnectionDecorator;
use Ratchet\ConnectionInterface;
use Ratchet\RFC6455\Messaging\DataInterface;
use Ratchet\RFC6455\Messaging\Frame;

/**
 * {@inheritdoc}
 */
class WsConnection extends AbstractConnectionDecorator {
    use ConnectionDecorator {
        ConnectionDecorator::__construct as _decorator;
    }

    // TODO: Can we make/put a MessageBuffer in here instead of using ConnContext?
    //       I think the reason I made ConnContext was to keep it out of user hands
    public function __construct(ConnectionInterface $conn) {
        parent::__construct($conn); // deprecate
        $this->_decorator($conn, [
            'WebSocket.closing' => false
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function send($msg) {
        if (!$this->WebSocket->closing) {
            if (!($msg instanceof DataInterface)) {
                $msg = new Frame($msg);
            }

            $this->connection->send($msg->getContents());
        }

        return $this;
    }

    /**
     * @param int|\Ratchet\RFC6455\Messaging\DataInterface
     */
    public function close($code = 1000) {
        if ($this->properties['WebSocket.closing']) {
            return;
        }

        if ($code instanceof DataInterface) {
            $this->send($code);
        } else {
            $this->send(new Frame(pack('n', $code), true, Frame::OP_CLOSE));
        }

        $this->connection->close();

        $this->properties['WebSocket.closing'] = true;
        $this->WebSocket->closing = true; // @deprecated
    }
}
