<?php

namespace Ratchet\Wamp2\Router;


use Ratchet\Wamp2\Router\WampClientProxyInterface;
use Ratchet\Wamp2\WampMessageType;
use Ratchet\Wamp2\Router\WampServerIncomingMessageHandlerInterface;
use Ratchet\Wamp2\Router\WampServerInterface;

class WampServerIncomingMessageHandler implements WampServerIncomingMessageHandlerInterface {

    /**
     * @var WampServerInterface
     */
    protected $_decorating;

    function __construct($serverComponent)
    {
        $this->_decorating = $serverComponent;
    }

    public function handleMessage(WampClientProxyInterface $client, array $message) {
        switch ($message[0]) {
            case WampMessageType::MSG_HELLO:
                $this->handleHello($client, $message);
                break;
            case WampMessageType::MSG_ABORT:
                $this->handleAbort($client, $message);
                break;
            case WampMessageType::MSG_AUTHENTICATE:
                $this->handleAuthenticate($client, $message);
                break;
            case WampMessageType::MSG_GOODBYE:
                $this->handleGoodbye($client, $message);
                break;
            case WampMessageType::MSG_HEARTBEAT:
                $this->handleHeartbeat($client, $message);
                break;
            case WampMessageType::MSG_REGISTER:
                $this->handleRegister($client, $message);
                break;
            case WampMessageType::MSG_UNREGISTER:
                $this->handleUnregister($client, $message);
                break;
            case WampMessageType::MSG_CALL:
                $this->handleCall($client, $message);
                break;
            case WampMessageType::MSG_CANCEL:
                $this->handleCancel($client, $message);
                break;
            case WampMessageType::MSG_YIELD:
                $this->handleYield($client, $message);
                break;
            case WampMessageType::MSG_ERROR:
                $this->handleError($client, $message);
                break;
            case WampMessageType::MSG_PUBLISH:
                $this->handlePublish($client, $message);
                break;
            case WampMessageType::MSG_SUBSCRIBE:
                $this->handleSubscribe($client, $message);
                break;
            case WampMessageType::MSG_UNSUBSCRIBE:
                $this->handleUnsubscribe($client, $message);
                break;
            default:
                $this->handleMissingMessage($client, $message);
                break;
        }
    }

    function handleMissingMessage(WampClientProxyInterface $client, array $message)
    {
    }

    function handleHello(WampClientProxyInterface $client, array $message) {
        $realm = $message[1];
        $details = $message[2];
        $this->_decorating->onHello($client, $realm, $details);
    }

    function handleAbort(WampClientProxyInterface $client, array $message) {
        $details = $message[1];
        $reason = $message[2];
        $this->_decorating->onAbort($client, $details, $reason);
    }

    function handleAuthenticate(WampClientProxyInterface $client, array $message) {
        $signature = $message[1];
        $extra = $message[2];
        $this->_decorating->onAuthenticate($client, $signature, $extra);
    }

    function handleGoodbye(WampClientProxyInterface $client, array $message) {
        $details = $message[1];
        $reason = $message[2];
        $this->_decorating->onGoodbye($client, $details, $reason);
    }

    function handleHeartbeat(WampClientProxyInterface $client, array $message) {
        $incomingSeq = $message[1];
        $outgoingSeq = $message[2];
        $discard  = (array_key_exists(3, $message) ? $message[3] : null);
        $this->_decorating->onHeartbeat($client, $incomingSeq, $outgoingSeq, $discard);
    }

    function handleRegister(WampClientProxyInterface $client, array $message) {
        $requestId = $message[1];
        $options = $message[2];
        $procedure = $message[3];
        $this->_decorating->onRegister($client, $requestId, $options, $procedure);
    }

    function handleUnregister(WampClientProxyInterface $client, array $message) {
        $requestId = $message[1];
        $registrationId = $message[2];
        $this->_decorating->onUnregister($client, $requestId, $registrationId);
    }

    function handleCall(WampClientProxyInterface $client, array $message) {
        $requestId = $message[1];
        $options = $message[2];
        $procedure = $message[3];
        $arguments  = (array_key_exists(4, $message) ? $message[4] : array());
        $argumentsKeywords  = (array_key_exists(5, $message) ? $message[5] : null);
        $this->_decorating->onCall($client, $requestId, $options, $procedure, $arguments, $argumentsKeywords);
    }

    function handleCancel(WampClientProxyInterface $client, array $message) {
        $requestId = $message[1];
        $options = $message[2];
        $this->_decorating->onCancel($client, $requestId, $options);
    }

    function handleYield(WampClientProxyInterface $client, array $message) {
        $requestId = $message[1];
        $options = $message[2];
        $arguments  = (array_key_exists(3, $message) ? $message[3] : array());
        $argumentsKeywords  = (array_key_exists(4, $message) ? $message[4] : null);
        $this->_decorating->onYield($client, $requestId, $options, $arguments, $argumentsKeywords);
    }

    function handleError(WampClientProxyInterface $client, array $message) {
        $requestType = $message[1];
        $requestId = $message[2];
        $details = $message[3];
        $error = $message[4];
        $arguments  = (array_key_exists(5, $message) ? $message[5] : array());
        $argumentsKeywords  = (array_key_exists(6, $message) ? $message[6] : null);
        $this->_decorating->onErrorMessage($client, $requestType, $requestId, $details, $error, $arguments, $argumentsKeywords);
    }

    function handlePublish(WampClientProxyInterface $client, array $message) {
        $requestId = $message[1];
        $options = $message[2];
        $topicUri = $message[3];
        $arguments  = (array_key_exists(4, $message) ? $message[4] : array());
        $argumentKeywords  = (array_key_exists(5, $message) ? $message[5] : null);
        $this->_decorating->onPublish($client, $requestId, $options, $topicUri, $arguments, $argumentKeywords);
    }

    function handleSubscribe(WampClientProxyInterface $client, array $message) {
        $requestId = $message[1];
        $options = $message[2];
        $topicUri = $message[3];
        $this->_decorating->onSubscribe($client, $requestId, $options, $topicUri);
    }

    function handleUnsubscribe(WampClientProxyInterface $client, array $message) {
        $requestId = $message[1];
        $subscriptionId = $message[2];
        $this->_decorating->onUnsubscribe($client, $requestId, $subscriptionId);
    }

}
