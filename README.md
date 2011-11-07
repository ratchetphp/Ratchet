#Ratchet

A PHP 5.3 (PSR-0 compliant) application for serving and consuming sockets.
Build up your application (like Lego!) through simple interfaces using the decorator pattern.
Re-use your application without changing any of its code just by wrapping it in a different protocol.

---

##WebSockets

* Supports the HyBi-10 and Hixie76 protocol versions (at the same time)
* Tested on Chrome 14, Firefox 7, Safari 5, iOS 4.2

---

###A quick server example

```php
<?php
namespace MyApps;
use Ratchet\SocketObserver, Ratchet\SocketInterface;
use Ratchet\Socket, Ratchet\Server, Ratchet\Protocol\WebSocket;
use Ratchet\Command\Factory;

/**
 * Send any incoming messages to all connected clients (except sender)
 */
class Chat implements SocketObserver {
    protected $_factory;
    protected $_clients;

    public function __construct() {
        $this->_factory = new Factory;
        $this->_clients = new \SplObjectStorage;
    }

    public function onOpen(SocketInterface $conn) {
        $this->_clients->attach($conn);
    }

    public function onRecv(SocketInterface $from, $msg) {
        $stack = $this->_factory->newComposite();

        foreach ($this->_clients as $client) {
            if ($from != $client) {
                $stack->enqueue($this->_factory->newCommand('SendMessage', $client)->setMessage($msg));
            }
        }

        return $stack;
    }

    public function onClose(SocketInterface $conn) {
        $this->_clients->detach($conn);
    }
}

    // Run the server application through the WebSocket protocol
    $server = new Server(new Socket, new WebSocket(new Chat));
    $server->run('0.0.0.0', 80);
```