<?php
namespace Ratchet\WebSocket;
use Ratchet\WebSocket\Guzzle\Http\Message\RequestFactory;
use Ratchet\WebSocket\Version\VersionInterface;
use Ratchet\WebSocket\Version;
use Guzzle\Http\Message\RequestInterface;
use Guzzle\Http\Message\Response;

class HandshakeNegotiator {
    const EOM = "\r\n\r\n";

    /**
     * The maximum number of bytes the request can be
     * This is a security measure to prevent attacks
     * @var int
     */
    public $maxSize = 4096;

    private $versionString = '';

    protected $versions = array();

    public function __construct($autoloadVersions = true) {
        if ($autoloadVersions) {
            $this->enableVersion(new Version\RFC6455);
            $this->enableVersion(new Version\HyBi10);
            $this->enableVersion(new Version\Hixie76);
        }
    }

    /**
     * @param WsConnection
     */
    public function onOpen(WsConnection $conn) {
        $conn->WebSocket->handshakeBuffer = '';
    }

    /**
     * @param WsConnection
     * @param string Data stream to buffer
     * @return Guzzle\Http\Message\Response|null Response object if it's done parsing, null if there's more to be buffered
     * @throws HttpException
     */
    public function onData(WsConnection $conn, $data) {
        $conn->WebSocket->handshakeBuffer .= $data;

        if (strlen($conn->WebSocket->handshakeBuffer) >= (int)$this->maxSize) {
            return new Response(413, array('X-Powered-By' => \Ratchet\VERSION));
        }

        if ($this->isEom($conn->WebSocket->handshakeBuffer)) {
            $conn->WebSocket->request = RequestFactory::getInstance()->fromMessage($conn->WebSocket->handshakeBuffer);

            if (null === ($version = $this->getVersion($conn->WebSocket->request))) {
                return new Response(400, array(
                    'Sec-WebSocket-Version' => $this->getSupportedVersionString()
                  , 'X-Powered-By'          => \Ratchet\VERSION
                ));
            }

            // TODO: confirm message is buffered
            // Hixie requires the body to complete the handshake (6 characters long) - is that 6 ASCII or UTF-8 characters?
            // Update VersionInterface to check for this, ::canHandshake() maybe
            // return if can't, continue buffering

            $response = $version->handshake($conn->WebSocket->request);
            $response->setHeader('X-Powered-By', \Ratchet\VERSION);

            $conn->setVersion($version);
            unset($conn->WebSocket->handshakeBuffer);

            return $response;
        }
    }

    /**
     * Determine if the message has been buffered as per the HTTP specification
     * @param string
     * @return boolean
     * @todo Safari does not send 2xCRLF after the 6 byte body...this will always return false for Hixie
     */
    public function isEom($message) {
        return (static::EOM === substr($message, 0 - strlen(static::EOM)));
    }

    /**
     * Get the protocol negotiator for the request, if supported
     * @param Guzzle\Http\Message\RequestInterface
     * @return Ratchet\WebSocket\Version\VersionInterface
     */
    public function getVersion(RequestInterface $request) {
        foreach ($this->versions as $version) {
            if ($version->isProtocol($request)) {
                return $version;
            }
        }
    }

    /**
     * Enable support for a specific version of the WebSocket protocol
     * @param Ratchet\WebSocket\Vesion\VersionInterface
     * @return HandshakeNegotiator
     */
    public function enableVersion(VersionInterface $version) {
        $this->versions[$version->getVersionNumber()] = $version;

        if (empty($this->versionString)) {
            $this->versionString = (string)$version->getVersionNumber();
        } else {
            $this->versionString .= ", {$version->getVersionNumber()}";
        }

        return $this;
    }

    /**
     * Disable support for a specific WebSocket protocol version
     * @param int The version ID to un-support
     * @return HandshakeNegotiator
     */
    public function disableVersion($versionId) {
        unset($this->versions[$versionId]);

        $this->versionString = '';

        foreach ($this->versions as $id => $object) {
            $this->versionString .= "{$id}, ";
        }
        $this->versionString = substr($this->versionString, 0, -2);

        return $this;
    }

    /**
     * Get a string of version numbers supported (comma delimited)
     * @return string
     */
    public function getSupportedVersionString() {
        return $this->versionString;
    }
}