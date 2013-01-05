<?php
namespace Ratchet\WebSocket;
use Ratchet\WebSocket\Version\VersionInterface;
use Guzzle\Http\Message\RequestInterface;

/**
 * Manage the various versions of the WebSocket protocol
 * This accepts interfaces of versions to enable/disable
 */
class VersionManager {
    /**
     * The header string to let clients know which versions are supported
     * @var string
     */
    private $versionString = '';

    /**
     * Storage of each version enabled
     * @var array
     */
    protected $versions = array();

    /**
     * Get the protocol negotiator for the request, if supported
     * @param  \Guzzle\Http\Message\RequestInterface $request
     * @throws \InvalidArgumentException
     * @return \Ratchet\WebSocket\Version\VersionInterface
     */
    public function getVersion(RequestInterface $request) {
        foreach ($this->versions as $version) {
            if ($version->isProtocol($request)) {
                return $version;
            }
        }

        throw new \InvalidArgumentException("Version not found");
    }

    /**
     * @param  \Guzzle\Http\Message\RequestInterface
     * @return bool
     */
    public function isVersionEnabled(RequestInterface $request) {
        foreach ($this->versions as $version) {
            if ($version->isProtocol($request)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Enable support for a specific version of the WebSocket protocol
     * @param  \Ratchet\WebSocket\Version\VersionInterface $version
     * @return VersionManager
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
     * @param  int $versionId The version ID to un-support
     * @return VersionManager
     */
    public function disableVersion($versionId) {
        unset($this->versions[$versionId]);

        $this->versionString = implode(',', array_keys($this->versions));

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