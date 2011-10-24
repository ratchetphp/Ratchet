<?php
namespace Ratchet\Server;
use Ratchet\Socket;
use Ratchet\Exception;

class Aggregator implements \IteratorAggregator {
    /**
     * @var Ratchet\Socket
     */
    protected $_master;

    /**
     * @var SplObjectStorage
     */
    protected $_sockets;

    protected $_resources = array();

    /**
     * @param Ratchet\Socket
     * @throws Ratchet\Exception
     */
    public function __construct(Socket $master) {
        $this->_sockets = new \SplObjectStorage;

        $this->_master = $master;
        $this->insert($this->_master);
    }

    /**
     * @return Socket
     */
    public function getMaster() {
        return $this->_master;
    }

    /**
     * @param resource
     * @return Socket
     */
    public function getClientByResource($resource) {
        if ($this->_sockets->contains($resource)) {
            return $this->_sockets[$resource];
        }

        throw new Exception("Resource not found");
    }

    protected function insert(Socket $socket) {
        $resource = $socket->getResource();

        $this->_sockets[$socket] = $resource;
        $this->_resources[]      = $resource;
    }

    /**
     * @return SplObjectStorage
     */
    public function getIterator() {
        return $this->_sockets;
    }

    /**
     * @return array
     */
    public function asArray() {
        return $this->_resources;
    }
}