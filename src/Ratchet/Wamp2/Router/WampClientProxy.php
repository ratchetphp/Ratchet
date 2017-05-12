<?php

namespace Ratchet\Wamp2\Router;

use Ratchet\ConnectionInterface;
use Ratchet\Wamp2\Router\WampClientProxyInterface;
use Ratchet\Wamp2\WampFormatterInterface;
use Ratchet\Wamp2\WampProtocol;

class WampClientProxy implements WampClientProxyInterface {

    /**
     * @var ConnectionInterface
     */
    protected $connection;

    /**
     * @var WampProtocol
     */
    protected $protocol;

    /**
     * @var WampFormatterInterface
     */
    protected $formatter;

    function __construct($connection, $formatter, $protocol)
    {
        $this->connection = $connection;
        $this->formatter = $formatter;
        $this->protocol = $protocol;
    }

    function sendMessage(array $message)
    {
        $raw = $this->formatter->serialize($message);
        $this->connection->send($raw);
    }

    public function onChallenge($challenge, $extra)
    {
        $message = $this->protocol->challengeMessage($challenge, $extra);
        $this->sendMessage($message);
    }

    public function onWelcome($session, $details)
    {
        $message = $this->protocol->welcomeMessage($session, $details);
        $this->sendMessage($message);
    }

    public function onAbort($details, $reason)
    {
        $message = $this->protocol->abortMessage($details, $reason);
        $this->sendMessage($message);
    }

    public function onGoodbye($details, $reason)
    {
        $message = $this->protocol->goodbyeMessage($details, $reason);
        $this->sendMessage($message);
    }

    public function onHeartbeat($incomingSeq, $outgoingSeq, $discard = null)
    {
        $message = $this->protocol->heartbeatMessage($incomingSeq, $outgoingSeq, $discard);
        $this->sendMessage($message);
    }

    public function onError($requestType, $requestId, $details, $error, array $arguments = null, $argumentsKeywords = null)
    {
        $message = $this->protocol->errorMessage($requestType, $requestId, $details, $error, $arguments, $argumentsKeywords);
        $this->sendMessage($message);
    }

    public function onRegistered($requestId, $registrationId)
    {
        $message = $this->protocol->registeredMessage($requestId, $registrationId);
        $this->sendMessage($message);
    }

    public function onUnregistered($requestId)
    {
        $message = $this->protocol->unregisteredMessage($requestId);
        $this->sendMessage($message);
    }

    public function onInvocation($requestId, $registrationId, $details, array $arguments = null, $argumentsKeywords = null)
    {
        $message = $this->protocol->invocationMessage($requestId, $registrationId, $details, $arguments, $argumentsKeywords);
        $this->sendMessage($message);
    }

    public function onInterrupt($requestId, $options)
    {
        $message = $this->protocol->interruptMessage($requestId, $options);
        $this->sendMessage($message);
    }

    public function onResult($requestId, $details, array $arguments = null, $argumentsKeywords = null)
    {
        $message = $this->protocol->resultMessage($requestId, $details, $arguments, $argumentsKeywords);
        $this->sendMessage($message);
    }

    public function onPublished($requestId, $publicationId)
    {
        $message = $this->protocol->publishedMessage($requestId, $publicationId);
        $this->sendMessage($message);
    }

    public function onSubscribed($requestId, $subscriptionId)
    {
        $message = $this->protocol->subscribedMessage($requestId, $subscriptionId);
        $this->sendMessage($message);
    }

    public function onUnsubscribed($requestId, $subscriptionId)
    {
        $message = $this->protocol->unsubscribedMessage($requestId, $subscriptionId);
        $this->sendMessage($message);
    }

    public function onEvent($subscriptionId, $publicationId, $details, array $arguments = null, $argumentsKeywords = null)
    {
        $message = $this->protocol->eventMessage($subscriptionId, $publicationId, $details, $arguments, $argumentsKeywords);
        $this->sendMessage($message);
    }

    /**
     * Close the connection
     */
    function close()
    {
        $this->connection->close();
    }

    /**
     * Send data to the connection
     * @param  string $data
     * @return \Ratchet\ConnectionInterface
     */
    function send($data)
    {
        $this->connection->send($data);
    }
}