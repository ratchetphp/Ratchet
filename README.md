#Ratchet

A PHP 5.3 (PSR-0 compliant) application for serving and consuming sockets.

---

##WebSocket

Ratchet (so far) includes an "application" (in development) to handle the WebSocket protocol.

---

###A Quick server example

    <?php
        namespace Me;
        use Ratchet\Socket;
        use Ratchet\SocketInterface as Sock;
        use Ratchet\Server;
        use Ratchet\Protocol\WebSocket;

        class MyApp implements \Ratchet\ReceiverInterface {
            protected $_server;

            public function getName() {
                return 'my_app';
            }

            public function setUp(Server $server) {
                $this->_server = $server;
            }

            public function onOpen(Sock $conn) {
            }

            public function onRecv(Sock $from, $msg) {
            }

            public function onClose(Sock $conn) {
            }
        }

        $server = new Server(new Socket, new WebSocket(new MyApp));
        $server->run('0.0.0.0', 80);