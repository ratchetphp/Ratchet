<?php

namespace Ratchet\Wamp2\Client;

use Ratchet\ConnectionInterface;
use Ratchet\Wamp2\WampFormatterInterface;
use Ratchet\Wamp2\WampProtocol;

class WampServerProxy implements WampServerProxyInterface {

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

    public function onHello($realm, $details)
    {
        $message = $this->protocol->helloMessage($realm, $details);
        $this->sendMessage($message);
    }

    public function onAbort($details, $reason)
    {
        $message = $this->protocol->abortMessage($details, $reason);
        $this->sendMessage($message);
    }

    public function onAuthenticate($signature, $extra)
    {
        $message = $this->protocol->authenticateMessage($signature, $extra);
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

    public function onRegister($requestId, $options, $procedure)
    {
        $message = $this->protocol->registerMessage($requestId, $options, $procedure);
        $this->sendMessage($message);
    }

    public function onUnregister($requestId, $registrationId)
    {
        $message = $this->protocol->unregisterMessage($requestId, $registrationId);
        $this->sendMessage($message);
    }

    public function onCall($requestId, $options, $procedure, array $arguments = null, $argumentsKeywords = null)
    {
        $message = $this->protocol->callMessage($requestId, $options, $procedure, $arguments, $argumentsKeywords);
        $this->sendMessage($message);
    }

    public function onCancel($requestId, $options)
    {
        $message = $this->protocol->cancelMessage($requestId, $options);
        $this->sendMessage($message);
    }

    public function onYield($requestId, $options, array $arguments = null, $argumentsKeywords = null)
    {
        $message = $this->protocol->yieldMessage($requestId, $options, $arguments, $argumentsKeywords);
        $this->sendMessage($message);
    }

    public function onPublish($requestId, $options, $topicUri, array $arguments = null, $argumentKeywords = null)
    {
        $message = $this->protocol->publishMessage($requestId, $options, $topicUri, $arguments, $argumentKeywords);
        $this->sendMessage($message);
    }

    public function onSubscribe($requestId, $options, $topicUri)
    {
        $message = $this->protocol->subscribeMessage($requestId, $options, $topicUri);
        $this->sendMessage($message);
    }

    public function onUnsubscribe($requestId, $subscriptionId)
    {
        $message = $this->protocol->unsubscribeMessage($requestId, $subscriptionId);
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