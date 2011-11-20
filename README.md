#Ratchet

A PHP 5.3 (PSR-0 compliant) application for serving and consuming sockets.
Build up your application (like Lego!) through simple interfaces using the decorator and command patterns.
Re-use your application without changing any of its code just by wrapping it in a different protocol.

##WebSockets

* Supports the HyBi-10 and Hixie76 protocol versions (at the same time)
* Tested on Chrome 14, Firefox 7, Safari 5, iOS 4.2

##Requirements

Shell access is required and a dedicated (virtual) machine with root access is recommended.
To avoid proxy/firewall blockage it's recommended WebSockets are run on port 80, which requires root access.
Note that you can not run two applications (Apache and Ratchet) on the same port, thus the requirement for a separate machine.

Cookies from your Apache/Nginx/IIS server will be passed to the socket server, allowing you to identify users.
It's recommended using a database/cache solution to store session data, so it's accessible on both servers.  
A demonstration of this will be posted (eventually).

See https://github.com/cboden/socket-demos for some out-of-the-box working demos using Ratchet.

###Future considerations

Ideally, soon, web servers will start supporting WebSockets to some capacity and PHP will no longer need to run its self from the command line.
In theory, the server (Apache/Nginx/ISS) would recognize the HTTP handshake request to upgrade the protocol to WebSockets and run/pass data through to a user 
configured PHP file. When this happens, in theory, you can keep your script the same, just remove the Server Application wrapper and maybe eventually the 
WebSocket Application wrapper if the servers recognize the protocol message framing. 

---

###A quick server example

```php
<?php
namespace MyApps;
use Ratchet\Application\ApplicationInterface;
use Ratchet\Resource\Connection;
use Ratchet\Socket;
use Ratchet\Application\Server\App as Server;
use Ratchet\Application\WebSocket\App as WebSocket;
use Ratchet\Resource\Command\Composite as Cmds;
use Ratchet\Resource\Command\Action\SendMessage;
use Ratchet\Resource\Command\Action\CloseConnection;

/**
 * Send any incoming messages to all connected clients (except sender)
 */
class Chat implements ApplicationInterface {
    protected $_clients;

    public function __construct(ApplicationInterface $app = null) {
        $this->_clients = new \SplObjectStorage;
    }

    public function onOpen(Connection $conn) {
        $this->_clients->attach($conn);
    }

    public function onMessage(Connection $from, $msg) {
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

    public function onClose(Connection $conn) {
        $this->_clients->detach($conn);
    }

    public function onError(Connection $conn, \Exception $e) {
        return new CloseConnection($conn);
    }
}
    // Run the server application through the WebSocket protocol
    $server = new Server(new WebSocket(new Chat));
    $server->run(new Socket, '0.0.0.0', 80);
```