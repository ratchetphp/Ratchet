<?php
namespace Ratchet\Server;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\WebSocket\WsServerInterface;

class Limiter implements MessageComponentInterface, WsServerInterface {
    /**
     * @var Ratchet\MessageComponentInterface
     */
    protected $app;

    protected $settings = array(
        'maxConnections'           => 10000
      , 'maxConnectionsPerAddress' => 20
      , 'maxDataPerInterval'       => array(1048576, 60)
      , 'maxDuration'              => 28800
    );

    protected $connections = 0;

    protected $connectionsAddress = array();

    /**
     * @param MessageComponentInterface
     */
    public function __construct(MessageComponentInterface $app) {
        $this->app = $app;
        $this->connectionsAddress['addresses'] = new \SplFixedArray($this->settings['maxConnections']);
        $this->connectionsAddress['counters']  = new \SplFixedArray($this->settings['maxConnections']);
    }

    /**
     * @param int
     */
    public function maxConnections($num) {
        $this->settings['maxConnections'] = (int)$num;
    }

    /**
     * @param  int     $num
     * @return Limiter Provides fluent interface
     * @throws \InvalidArgumentException
     */
    public function maxConnectionsPerAddress($num) {
        $num = (int)$num;

        if ($num > $this->settings['maxConnections']) {
            throw new \InvalidArgumentException(
                sprintf(
                    "maxConnectionsPerAddress can't be greater than %d (maxConnections setting), %d given.",
                    $this->settings['maxConnections'],
                    $num
                )
            );
        }

        $this->settings['maxConnectionsPerAddress'] = $num;

        return $this;
    }
/*
    public function maxDataPerInterval($size, $seconds) {
    }

    public function maxDuration($seconds) {
    }
*/

    /**
     * {@inheritdoc}
     */
    public function onOpen(ConnectionInterface $conn) {
        ++$this->connections;

        if ($this->connections > $this->settings['maxConnections']) {
            return $conn->close();
        }
        $this->updateAddressConnectionsCount($conn->remoteAddress, 1);
        if ($this->getAddressConnectionsCount($conn->remoteAddress) > $this->settings['maxConnectionsPerAddress']) {
            return $conn->close();
        }

        $this->app->onOpen($conn);
    }

    /**
     * {@inheritdoc}
     */
    public function onMessage(ConnectionInterface $from, $msg) {
        $this->app->onMessage($from, $msg);
    }

    /**
     * {@inheritdoc}
     */
    public function onClose(ConnectionInterface $conn) {
        --$this->connections;

        $this->updateAddressConnectionsCount($conn->remoteAddress, -1);

        $this->app->onClose($conn);
    }

    /**
     * {@inheritdoc}
     */
    public function onError(ConnectionInterface $conn, \Exception $e) {
        $this->app->onError($conn, $e);
    }

    /**
     * {@inheritdoc}
     */
    public function getSubProtocols() {
        return ($this->app instanceof WsServerInterface ? $this->app->getSubProtocols() : array());
    }

    protected function getAddressConnectionsCount($remoteAddress)
    {
        $index = $this->lookupAddressConnectionsIndex($remoteAddress);

        return (isset($this->connectionsAddress['counters'][$index]))
            ? $this->connectionsAddress['counters'][$index]
            : 0;
    }

    protected function updateAddressConnectionsCount($remoteAddress, $flag) {

        $flag = ((int) $flag > 0) ? +1 : -1;
        $index = $this->lookupAddressConnectionsIndex($remoteAddress);
        if (!isset($this->connectionsAddress['addresses'][$index])) {
            $index = key($this->connectionsAddress['addresses']);
            $this->connectionsAddress['addresses'][$index] = $remoteAddress;
            $this->connectionsAddress['counters'][$index] = 0;
        }

        $newCount = abs($flag + $this->connectionsAddress['counters'][$index]);

        if (0 >= $newCount) {
            unset($this->connectionsAddress['counters'][$index]);
            unset($this->connectionsAddress['addresses'][$index]);
        }

        $this->connectionsAddress['counters'][$index] = $newCount;

        return $newCount;
    }

    protected function lookupAddressConnectionsIndex($remoteAddress)
    {
        foreach ($this->connectionsAddress['addresses'] as $key => $address) {
            if ($remoteAddress === $address) {

                return $key;
            }
        }

        return null;
    }
}
