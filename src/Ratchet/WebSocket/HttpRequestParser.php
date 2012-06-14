<?php
namespace Ratchet\WebSocket;
use Ratchet\MessageInterface;
use Ratchet\ConnectionInterface;
use Ratchet\WebSocket\Guzzle\Http\Message\RequestFactory;
use Ratchet\WebSocket\Version\VersionInterface;
use Guzzle\Http\Message\RequestInterface;
use Guzzle\Http\Message\Response;

class HttpRequestParser implements MessageInterface {
    const EOM = "\r\n\r\n";

    /**
     * The maximum number of bytes the request can be
     * This is a security measure to prevent attacks
     * @var int
     */
    public $maxSize = 4096;

    /**
     * @param StdClass
     * @param string Data stream to buffer
     * @return Guzzle\Http\Message\Response|null Response object if it's done parsing, null if there's more to be buffered
     * @throws OverflowException
     */
    public function onMessage(ConnectionInterface $context, $data) {
        if (!isset($context->httpBuffer)) {
            $context->httpBuffer = '';
        }

        $context->httpBuffer .= $data;

        if (strlen($context->httpBuffer) > (int)$this->maxSize) {
            throw new \OverflowException("Maximum buffer size of {$this->maxSize} exceeded parsing HTTP header");

            //return new Response(413, array('X-Powered-By' => \Ratchet\VERSION));
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