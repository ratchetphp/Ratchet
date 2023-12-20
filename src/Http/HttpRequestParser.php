<?php

namespace Ratchet\Http;

use GuzzleHttp\Psr7\Message;
use Ratchet\ConnectionInterface;
use Ratchet\MessageInterface;

/**
 * This class receives streaming data from a client request
 * and parses HTTP headers, returning a PSR-7 Request object
 * once it's been buffered
 */
class HttpRequestParser implements MessageInterface
{
    const EOM = "\r\n\r\n";

    /**
     * The maximum number of bytes the request can be
     * This is a security measure to prevent attacks
     */
    public int $maxSize = 4096;

    /**
     * @param  string  $data Data stream to buffer
     *
     * @throws \OverflowException If the message buffer has become too large
     */
    public function onMessage(ConnectionInterface $context, string $data): \Psr\Http\Message\RequestInterface
    {
        if (! isset($context->httpBuffer)) {
            $context->httpBuffer = '';
        }

        $context->httpBuffer .= $data;

        if (strlen($context->httpBuffer) > (int) $this->maxSize) {
            throw new \OverflowException("Maximum buffer size of {$this->maxSize} exceeded parsing HTTP header");
        }

        if ($this->isEom($context->httpBuffer)) {
            $request = $this->parse($context->httpBuffer);

            unset($context->httpBuffer);

            return $request;
        }
    }

    /**
     * Determine if the message has been buffered as per the HTTP specification
     */
    public function isEom(string $message): bool
    {
        return (bool) strpos($message, static::EOM);
    }

    public function parse(string $headers): \Psr\Http\Message\RequestInterface
    {
        return Message::parseRequest($headers);
    }
}
