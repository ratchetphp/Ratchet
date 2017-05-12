<?php

namespace Ratchet\Wamp2\Client;

use Ratchet\Wamp2\WampClientInterface;
use Ratchet\Wamp2\WampMessageType;

class WampClientIncomingMessageHandler implements WampClientIncomingMessageHandlerInterface {

    /**
     * @var WampClientInterface
     */
    protected $_decorating;

    function __construct($clientComponent)
    {
        $this->_decorating = $clientComponent;
    }

    public function handleMessage(array $message) {
        switch ($message[0]) {
            case WampMessageType::MSG_CHALLENGE:
                $this->handleChallenge($message);
                break;
            case WampMessageType::MSG_WELCOME:
                $this->handleWelcome($message);
                break;
            case WampMessageType::MSG_ABORT:
                $this->handleAbort($message);
                break;
            case WampMessageType::MSG_GOODBYE:
                $this->handleGoodbye($message);
                break;
            case WampMessageType::MSG_HEARTBEAT:
                $this->handleHeartbeat($message);
                break;
            case WampMessageType::MSG_REGISTERED:
                $this->handleRegistered($message);
                break;
            case WampMessageType::MSG_UNREGISTERED:
                $this->handleUnregistered($message);
                break;
            case WampMessageType::MSG_INVOCATION:
                $this->handleInvocation($message);
                break;
            case WampMessageType::MSG_INTERRUPT:
                $this->handleInterrupt($message);
                break;
            case WampMessageType::MSG_RESULT:
                $this->handleResult($message);
                break;
            case WampMessageType::MSG_PUBLISHED:
                $this->handlePublished($message);
                break;
            case WampMessageType::MSG_SUBSCRIBED:
                $this->handleSubscribed($message);
                break;
            case WampMessageType::MSG_UNSUBSCRIBED:
                $this->handleUnsubscribed($message);
                break;
            case WampMessageType::MSG_EVENT:
                $this->handleEvent($message);
                break;
            default:
                $this->handleMissingMessage($message);
                break;
        }
    }

    function handleMissingMessage(array $message)
    {
    }

    function handleChallenge(array $message) {
        $challenge = $message[1];
        $extra = $message[2];
        $this->_decorating->onChallenge($$challenge, $extra);
    }

    function handleWelcome(array $message) {
        $session = $message[1];
        $details = $message[2];
        $this->_decorating->onWelcome($$session, $details);
    }

    function handleAbort(array $message) {
        $details = $message[1];
        $reason = $message[2];
        $this->_decorating->onAbort($$details, $reason);
    }

    function handleGoodbye(array $message) {
        $details = $message[1];
        $reason = $message[2];
        $this->_decorating->onGoodbye($$details, $reason);
    }

    function handleHeartbeat(array $message) {
        $incomingSeq = $message[1];
        $outgoingSeq = $message[2];
        $discard  = (array_key_exists(3, $message) ? $message[3] : null);
        $this->_decorating->onHeartbeat($incomingSeq, $outgoingSeq, $discard);
    }

    function handleError(array $message) {
        $requestType = $message[1];
        $requestId = $message[2];
        $details = $message[3];
        $error = $message[4];
        $arguments  = (array_key_exists(5, $message) ? $message[5] : array());
        $argumentsKeywords  = (array_key_exists(6, $message) ? $message[6] : null);
        $this->_decorating->onError($requestType, $requestId, $details, $error, $arguments, $argumentsKeywords);
    }

    function handleRegistered(array $message) {
        $requestId = $message[1];
        $registrationId = $message[2];
        $this->_decorating->onRegistered($$requestId, $registrationId);
    }

    function handleUnregistered(array $message) {
        $requestId = $message[1];
        $this->_decorating->onUnregistered($$requestId);
    }

    function handleInvocation(array $message) {
        $requestId = $message[1];
        $registrationId = $message[2];
        $details = $message[3];
        $arguments  = (array_key_exists(4, $message) ? $message[4] : array());
        $argumentsKeywords  = (array_key_exists(5, $message) ? $message[5] : null);
        $this->_decorating->onInvocation($requestId, $registrationId, $details, $arguments, $argumentsKeywords);
    }

    function handleInterrupt(array $message) {
        $requestId = $message[1];
        $options = $message[2];
        $this->_decorating->onInterrupt($$requestId, $options);
    }

    function handleResult(array $message) {
        $requestId = $message[1];
        $details = $message[2];
        $arguments  = (array_key_exists(3, $message) ? $message[3] : array());
        $argumentsKeywords  = (array_key_exists(4, $message) ? $message[4] : null);
        $this->_decorating->onResult($requestId, $details, $arguments, $argumentsKeywords);
    }

    function handlePublished(array $message) {
        $requestId = $message[1];
        $publicationId = $message[2];
        $this->_decorating->onPublished($$requestId, $publicationId);
    }

    function handleSubscribed(array $message) {
        $requestId = $message[1];
        $subscriptionId = $message[2];
        $this->_decorating->onSubscribed($$requestId, $subscriptionId);
    }

    function handleUnsubscribed(array $message) {
        $requestId = $message[1];
        $subscriptionId = $message[2];
        $this->_decorating->onUnsubscribed($$requestId, $subscriptionId);
    }

    function handleEvent(array $message) {
        $subscriptionId = $message[1];
        $publicationId = $message[2];
        $details = $message[3];
        $arguments  = (array_key_exists(4, $message) ? $message[4] : array());
        $argumentsKeywords  = (array_key_exists(5, $message) ? $message[5] : null);
        $this->_decorating->onEvent($subscriptionId, $publicationId, $details, $arguments, $argumentsKeywords);
    }
}