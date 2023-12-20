# Ratchet

[![GitHub Actions][GA Image]][GA Link]
[![Autobahn Testsuite](https://img.shields.io/badge/Autobahn-passing-brightgreen.svg)](http://socketo.me/reports/ab/index.html)
[![Latest Stable Version](https://poser.pugx.org/cboden/ratchet/v/stable.png)](https://packagist.org/packages/cboden/ratchet)

A PHP library for asynchronously serving WebSockets.
Build up your application through simple interfaces and re-use your application without changing any of its code just by combining different components.

## Requirements

Shell access is required and root access is recommended.
To avoid proxy/firewall blockage it's recommended WebSockets are requested on port 80 or 443 (SSL), which requires root access.
In order to do this, along with your sync web stack, you can either use a reverse proxy or two separate machines.
You can find more details in the [server conf docs](http://socketo.me/docs/deploy#server_configuration).

### Documentation

User and API documentation is available on Ratchet's website: http://socketo.me

See https://github.com/cboden/Ratchet-examples for some out-of-the-box working demos using Ratchet.

Need help?  Have a question?  Want to provide feedback?  Write a message on the [Google Groups Mailing List](https://groups.google.com/forum/#!forum/ratchet-php).

---

### A quick example

```php
<?php
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

// Make sure composer dependencies have been installed
require __DIR__ . '/vendor/autoload.php';

/**
 * chat.php
 * Send any incoming messages to all connected clients (except sender)
 */
class MyChat implements MessageComponentInterface 
{
    protected \SplObjectStorage $clients;

    public function __construct() 
    {
        $this->clients = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $connection) 
    {
        $this->clients->attach($connection);
    }

    public function onMessage(ConnectionInterface $connection, string $message)
    {
        foreach ($this->clients as $client) {
            if ($connection != $client) {
                $client->send($message);
            }
        }
    }

    public function onClose(ConnectionInterface $connection) 
    {
        $this->clients->detach($connection);
    }

    public function onError(ConnectionInterface $connection, \Exception $exception) 
    {
        $connection->close();
    }
}

    // Run the server application through the WebSocket protocol on port 8080
    $app = new Ratchet\App('localhost', 8080);
    $app->route('/chat', new MyChat, array('*'));
    $app->route('/echo', new Ratchet\Server\EchoServer, array('*'));
    $app->run();
```

    $ php chat.php

```javascript
    // Then some JavaScript in the browser:
    var conn = new WebSocket('ws://localhost:8080/echo');
    conn.onmessage = function(e) { console.log(e.data); };
    conn.onopen = function(e) { conn.send('Hello Me!'); };
```

[GA Image]: https://github.com/ratchetphp/Ratchet/workflows/CI/badge.svg

[GA Link]: https://github.com/ratchetphp/Ratchet/actions?query=workflow%3A%22CI%22+branch%3Amaster
