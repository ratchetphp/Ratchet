#Ratchet

A PHP 5.3 (PSR-0 compliant) application for serving and consuming sockets.

---

##WebSocket

Ratchet (so far) includes an "application" (in development) to handle the WebSocket protocol.

---

###A Quick server example

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