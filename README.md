#Ratchet

A PHP 5.3 (PSR-0 compliant) application for serving and consuming sockets.
Build up your application (like Lego!) through simple interfaces using the decorator pattern.
Re-use your application without changing any of its code just by wrapping it in a different protocol.

---

##WebSockets

* Supports the HyBi-10 and Hixie76 protocol versions
* Tested on Chrome 14, Firefox 7, Safari 5, iOS 4.2

---

###A Quick server example

```php
<?php
    namespace Me;
    use Ratchet\SocketObserver, Ratchet\SocketInterface;
    use Ratchet\Socket, Ratchet\Server, Ratchet\Protocol\WebSocket;
    use Ratchet\SocketCollection, Ratchet\Command\SendMessage;

    /**
     * Send any incoming messages to all connected clients (except sender)
     */
    class Chat implements SocketObserver {
        protected $_clients;

        public function __construct() {
            $this->_clients = new \SplObjectStorage;
        }

        public function onOpen(SocketInterface $conn) {
            $this->_clients->attach($conn);
        }

        public function onRecv(SocketInterface $from, $msg) {
            $stack = new SocketCollection;
            foreach ($this->_clients as $client) {
                if ($from != $client) {
                    $stack->enqueue($client);
                }
            }

            $command = new SendMessage($stack);
            $command->setMessage($msg);
            return $command;
        }

        public function onClose(SocketInterface $conn) {
            $this->_clients->detach($conn);
        }
    }

    // Run the server application through the WebSocket protocol
    $server = new Server(new Socket, new WebSocket(new Chat));
    $server->run('0.0.0.0', 80);
```