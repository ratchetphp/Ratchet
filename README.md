#Ratchet

A PHP 5.3 (PSR-0 compliant) application for serving and consuming sockets.

---

##WebSocket

Ratchet (so far) includes an "application" (in development) to handle the WebSocket protocol.

---

###A Quick server example

    <?php
        namespace Me;

        class MyApp implements \Ratchet\ReceiverInterface {
            public function getName() {
                return 'my_app';
            }

            public function handleConnect() {
            }

            public function handleMessage() {
            }

            public function handleClose() {
            }
        }

            $protocol    = new \Ratchet\Protocol\WebSocket();
            $application = new MyApp();
            $server      = new \Ratchet\Server(\Ratchet\Socket::createFromConfig($protocol));

            $server->attatchReceiver($protocol);
            $server->attatchReceiver($application);

            $server->run();