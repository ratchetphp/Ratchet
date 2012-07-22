<?php
namespace Ratchet\WebSocket;
use Ratchet\MessageInterface;
use Ratchet\ConnectionInterface;
use Ratchet\WebSocket\Guzzle\Http\Message\RequestFactory;
use Ratchet\WebSocket\Version\VersionInterface;
use Guzzle\Http\Message\RequestInterface;

/**
 * This class receives streaming data from a client request
 * and parses HTTP headers, returning a Guzzle Request object
 * once it's been buffered
 */
class HttpRequestParser implements MessageInterface {
    const EOM = "\r\n\r\n";

    /**
     * The maximum number of bytes the request can be
     * This is a security measure to prevent attacks
     * @var int
     */
    public $maxSize = 4096;

    /**
     * @param Ratchet\ConnectionInterface
     * @param string Data stream to buffer
     * @return Guzzle\Http\Message\RequestInterface|null
     * @throws OverflowException If the message buffer has become too large
     */
    public function onMessage(ConnectionInterface $context, $data) {
        if (!isset($context->httpBuffer)) {
            $context->httpBuffer = '';
        }

        $context->httpBuffer .= $data;

        if (strlen($context->httpBuffer) > (int)$this->maxSize) {
            throw new \OverflowException("Maximum buffer size of {$this->maxSize} exceeded parsing HTTP header");
        }

        if ($this->isEom($context->httpBuffer)) {
            $request = RequestFactory::getInstance()->fromMessage($context->httpBuffer);

            unset($context->httpBuffer);

            return $request;
        }
    }

    /**
     * Determine if the message has been buffered as per the HTTP specification
     * @param string
     * @return boolean
     */
    public function isEom($message) {
        //return (static::EOM === substr($message, 0 - strlen(static::EOM)));
        return (boolean)strpos($message, static::EOM);
    }
}