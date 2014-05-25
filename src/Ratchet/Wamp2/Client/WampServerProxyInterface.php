<?php

namespace Ratchet\Wamp2\Client;

use Ratchet\ConnectionInterface;

interface WampServerProxyInterface extends ConnectionInterface {

    function onHello($realm, $details);

    function onAbort($details, $reason);

    function onAuthenticate($signature, $extra);

    function onGoodbye($details, $reason);

    function onHeartbeat($incomingSeq, $outgoingSeq, $discard);

    function onRegister($requestId, $options, $procedure);

    function onUnregister($requestId, $registrationId);

    function onCall($requestId, $options, $procedure, array $arguments, $argumentsKeywords);

    function onCancel($requestId, $options);

    function onYield($requestId, $options, array $arguments, $argumentsKeywords);

    function onError($requestType, $requestId, $details, $error, array $arguments, $argumentsKeywords);

    function onPublish($requestId, $options, $topicUri, array $arguments, $argumentKeywords);

    function onSubscribe($requestId, $options, $topicUri);

    function onUnsubscribe($requestId, $subscriptionId);
}