<?php
namespace Ratchet\Resource\Socket;

/**
 * A wrapper for the PHP socket_ functions
 * @author Chris Boden <shout at chrisboden dot ca>
 * @link http://ca2.php.net/manual/en/book.sockets.php
 */
class BSDSocket implements SocketInterface {
    /**
     * @type resource
     */
    protected $_resource;

    public static $_defaults = array(
        'domain'   => AF_INET
      , 'type'     => SOCK_STREAM
      , 'protocol' => SOL_TCP
    );

    /**
     * @param int Specifies the protocol family to be used by the socket.
     * @param int The type of communication to be used by the socket
     * @param int Sets the specific protocol within the specified domain to be used when communicating on the returned socket
     * @throws BSDSocketException
     */
    public function __construct($domain = null, $type = null, $protocol = null) {
        list($domain, $type, $protocol) = static::getConfig($domain, $type, $protocol);

        $this->_resource = @socket_create($domain, $type, $protocol);

        if (!is_resource($this->_resource)) {
            throw new BSDSocketException($this);
        }
    }

    public function __destruct() {
        @socket_close($this->_resource);
    }

    public function __toString() {
        $id = (string)$this->getResource();
        return (string)substr($id, strrpos($id, '#') + 1);
    }

    /**
     * @return resource (Socket)
     */
    public function getResource() {
        return $this->_resource;
    }

    public function __clone() {
        $this->_resource = @socket_accept($this->_resource);

        if (false === $this->_resource) {
            throw new BSDSocketException($this);
        }
    }

    public function deliver($message) {
        $len = strlen($message);

        do {
            $sent    = $this->write($message, $len);
            $len    -= $sent;
            $message = substr($message, $sent);
        } while ($len > 0);
    }

    public function bind($address, $port = 0) {
        if (false === @socket_bind($this->getResource(), $address, $port)) {
            throw new BSDSocketException($this);
        }

        return $this;
    }

    public function close() {
        @socket_close($this->getResource());
        unset($this->_resource);
    }

    public function connect($address, $port = 0) {
        if (false === @socket_connect($this->getResource(), $address, $port)) {
            throw new BSDSocketException($this);
        }

        return $this;
    }

    public function getRemoteAddress() {
        $address = $port = '';
        if (false === @socket_getpeername($this->getResource(), $address, $port)) {
            throw new BSDSocketException($this);
        }

        return $address;
    }

    public function get_option($level, $optname) {
        if (false === ($res = @socket_get_option($this->getResource(), $level, $optname))) {
            throw new BSDSocketException($this);
        }

        return $res;
    }

    public function listen($backlog = 0) {
        if (false === @socket_listen($this->getResource(), $backlog)) {
            throw new BSDSocketException($this);
        }

        return $this;
    }

    public function read($length, $type = PHP_BINARY_READ) {
        if (false === ($res = @socket_read($this->getResource(), $length, $type))) {
            throw new BSDSocketException($this);
        }

        return $res;
    }

    /**
     * @see http://ca3.php.net/manual/en/function.socket-recv.php
     * @param string Variable to write data to
     * @param int Number of bytes to read
     * @param int
     * @return int Number of bytes received
     * @throws BSDSocketException
     */
    public function recv(&$buf, $len, $flags) {
        if (false === ($bytes = @socket_recv($this->_resource, $buf, $len, $flags))) {
            throw new BSDSocketException($this);
        }

        return $bytes;
    }

    /**
     * Since PHP is retarded and their golden hammer, the array, doesn't implement any interfaces I have to hackishly overload socket_select
     * @see http://ca3.php.net/manual/en/function.socket-select.php
     * @param Iterator|array|NULL The sockets listed in the read array will be watched to see if characters become available for reading (more precisely, to see if a read will not block - in particular, a socket resource is also ready on end-of-file, in which case a socket_read() will return a zero length string).
     * @param Iterator|array|NULL The sockets listed in the write array will be watched to see if a write will not block.
     * @param Iterator|array|NULL The sockets listed in the except array will be watched for exceptions.
     * @param int The tv_sec and tv_usec together form the timeout parameter. The timeout is an upper bound on the amount of time elapsed before socket_select() return. tv_sec may be zero , causing socket_select() to return immediately. This is useful for polling. If tv_sec is NULL (no timeout), socket_select() can block indefinitely.
     * @param int
     * @throws \InvalidArgumentException
     * @throws BSDSocketException
     */
    public function select(&$read, &$write, &$except, $tv_sec, $tv_usec = 0) {
        $read   = static::mungForSelect($read);
        $write  = static::mungForSelect($write);
        $except = static::mungForSelect($except);

        $num = socket_select($read, $write, $except, $tv_sec, $tv_usec);

        if (false === $num) {
            throw new BSDSocketException($this);
        }

        return $num;
    }

    public function set_block() {
        if (false === @socket_set_block($this->getResource())) {
            throw new BSDSocketException($this);
        }

        return $this;
    }

    public function set_nonblock() {
        if (false === @socket_set_nonblock($this->getResource())) {
            throw new BSDSocketException($this);
        }

        return $this;
    }

    public function set_option($level, $optname, $optval) {
        if (false === @socket_set_option($this->getResource(), $level, $optname, $optval)) {
            throw new BSDSocketException($this);
        }

        return $this;
    }

    public function shutdown($how = 2) {
        if (false === @socket_shutdown($this->getResource(), $how)) {
            throw new BSDSocketException($this);
        }

        return $this;
    }

    public function write($buffer, $length = 0) {
        if (false === ($res = @socket_write($this->getResource(), $buffer, $length))) {
            throw new BSDSocketException($this);
        }

        return $res;
    }

    /**
     * @internal
     * @param int Specifies the protocol family to be used by the socket.
     * @param int The type of communication to be used by the socket
     * @param int Sets the specific protocol within the specified domain to be used when communicating on the returned socket
     * @return array
     */
    protected static function getConfig($domain = null, $type = null, $protocol = null) {
        foreach (static::$_defaults as $key => $val) {
            if (null === $$key) {
                $$key = $val;
            }
        }

        return array($domain, $type, $protocol);
    }

    /**
     * @internal
     * @param Iterator|array|NULL
     * @return array|NULL
     * @throws \InvalidArgumentException
     */
    protected static function mungForSelect($collection) {
        if (null === $collection || is_array($collection)) {
            return $collection;
        }

        if (!($collection instanceof \Traversable)) {
            throw new \InvalidArgumentException('Object pass is not traversable');
        }

        $return = array();
        foreach ($collection as $key => $socket) {
            $return[$key] = ($socket instanceof $this ? $socket->getResource() : $socket);
        }

        return $return;
    }
}