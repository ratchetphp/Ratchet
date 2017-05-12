<?php

namespace Ratchet\Wamp2\Router;
use Ratchet\ComponentInterface;


interface WampServerInterface extends ComponentInterface {

    function onHello(WampClientProxyInterface $client, $realm, $details);

    function onAbort(WampClientProxyInterface $client, $details, $reason);

    function onAuthenticate(WampClientProxyInterface $client, $signature, $extra);

    function onGoodbye(WampClientProxyInterface $client, $details, $reason);

    function onHeartbeat(WampClientProxyInterface $client, $incomingSeq, $outgoingSeq, $discard);

    function onRegister(WampClientProxyInterface $client, $requestId, $options, $procedure);

    function onUnregister(WampClientProxyInterface $client, $requestId, $registrationId);

    function onCall(WampClientProxyInterface $client, $requestId, $options, $procedure, array $arguments, $argumentsKeywords);

    function onCancel(WampClientProxyInterface $client, $requestId, $options);

    function onYield(WampClientProxyInterface $client, $requestId, $options, array $arguments, $argumentsKeywords);

    function onErrorMessage(WampClientProxyInterface $client, $requestType, $requestId, $details, $error, array $arguments, $argumentsKeywords);

    function onPublish(WampClientProxyInterface $client, $requestId, $options, $topicUri, array $arguments, $argumentKeywords);

    function onSubscribe(WampClientProxyInterface $client, $requestId, $options, $topicUri);

    function onUnsubscribe(WampClientProxyInterface $client, $requestId, $subscriptionId);
}