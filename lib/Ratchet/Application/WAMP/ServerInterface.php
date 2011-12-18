<?php
namespace Ratchet\Application\WAMP;
use Ratchet\Resource\Connection;

interface ServerInterface {
    function onCall(Connection $conn, $callID, $uri);

    function onSubscribe(Connection $conn, $uri);

    function onUnSubscribe(Connection $conn, $uri);

    function onPublish(Connection $conn, $uri, $event);
}