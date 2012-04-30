[![Build Status](https://secure.travis-ci.org/cboden/Ratchet.png?branch=master)](http://travis-ci.org/cboden/Ratchet)

#Ratchet

A PHP 5.3 (PSR-0 compliant) component library for serving sockets and building socket based applications.
Build up your application through simple interfaces using the decorator and command patterns.
Re-use your application without changing any of its code just by combining different components. 

##WebSockets

* Supports the RFC6455, HyBi-10, and Hixie76 protocol versions (at the same time)
* Tested on Chrome 18 - 16, Firefox 6 - 8, Safari 5, iOS 4.2, iOS 5

##Requirements

Shell access is required and a dedicated machine with root access is recommended.
To avoid proxy/firewall blockage it's recommended WebSockets are run on port 80, which requires root access.
Note that you can not run two applications (Apache and Ratchet) on the same port, thus the requirement for a separate machine (for now).

Cookies from your domain will be passed to the socket server, allowing you to identify users.
Accessing your website's session data in Ratchet requires you to use [Symfony2 HttpFoundation Sessions](http://symfony.com/doc/master/components/http_foundation/sessions.html) on your website. 

### Documentation

User and API documentation is available on Ratchet's website: http://socketo.me

See https://github.com/cboden/Ratchet-examples for some out-of-the-box working demos using Ratchet.

---

###A quick server example

```php
<?php
namespace MyApps;
use Ratchet\Component\MessageComponentInterface;
use Ratchet\Resource\ConnectionInterface;
use Ratchet\Component\Server\IOServerComponent;
use Ratchet\Component\WebSocket\WebSocketComponent;
use Ratchet\Resource\Command\Composite as Cmds;
use Ratchet\Resource\Command\Action\SendMessage;
use Ratchet\Resource\Command\Action\CloseConnection;

/**
 * chat.php
 * Send any incoming messages to all connected clients (except sender)
 */
class Chat implements MessageComponentInterface {
    protected $_clients;

    public function __construct(MessageComponentInterface $app = null) {
        $this->_clients = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->_clients->attach($conn);
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $commands = new Cmds;

        foreach ($this->_clients as $client) {
            if ($from != $client) {
                $msg_cmd = new SendMessage($client);
                $msg_cmd->setMessage($msg);

                $commands->enqueue($msg_cmd);
            }
        }

        return $commands;
    }

    public function onClose(ConnectionInterface $conn) {
        $this->_clients->detach($conn);
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        return new CloseConnection($conn);
    }
}

// Run the server application through the WebSocket protocol
$server = new IOServerComponent(new WebSocketComponent(new Chat));
$server->run(8000);
```

    # php chat.php