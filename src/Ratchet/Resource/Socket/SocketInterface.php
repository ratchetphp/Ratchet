<?php
namespace Ratchet\Resource\Socket;

/**
 * An object-oriented container for a single socket connection
 * @todo Major refactor when socket streams are implemented against this interface
 */
interface SocketInterface {
    /**
     * @return resource
     */
    function getResource();

    /**
     * Return the unique ID of this socket instance
     */
    function __toString();

    /**
     * Calls socket_accept, duplicating its self
     * @throws Exception
     */
    function __clone();

    /**
     * Send a message through the socket.  This writes to the buffer until the entire message is delivered
     * @param string Your message to send to the socket
     * @return null
     * @throws Exception
     * @see write
     */
    function deliver($message);

    // Not sure if I'll implement this or leave it only in clone
//    function accept();

    /**
     * Bind the socket instance to an address/port
     * @param string
     * @param int
     * @return SocketInterface
     * @throws Exception
     */
    function bind($address, $port = 0);

    /**
     * Close the open connection to the client/socket
     */
    function close();

    /**
     * Initiates a connection to a socket
     * @param string
     * @param int
     * @return SocketInterface
     * @throws Exception
     */
    function connect($address, $port = 0);

    /**
     * Get the address the socket connected from
     * @return string
     * @throws Exception
     */
    function getRemoteAddress();

    /**
     * @param int
     * @param int
     * @return mixed
     * @throws Exception
     */
    function get_option($level, $optname);

    /**
     * Listen for incoming connections on this socket
     * @param int
     * @return SocketInterface
     * @throws Exception
     */
    function listen($backlog = 0);

    /**
     * Read a maximum of length bytes from a socket
     * @param int Number of bytes to read
     * @param int Flags
     * @return string Data read from the socket
     * @throws Exception
     */
    function read($length, $type = PHP_BINARY_READ);

    /**
     * Called when the client sends data to the server through the socket
     * @param string Variable to write data to
     * @param int Number of bytes to read
     * @param int
     * @return int Number of bytes received
     * @throws Exception
     * @todo Change the pass by reference
     */
    function recv(&$buf, $len, $flags);

    /**
     * @param array|Iterator
     * @param array|Iterator
     * @param array|Iterator
     * @param int
     * @param int
     * @return int
     * @throws Exception
     * @todo Figure out how to break this out to not do pass by reference
     */
    function select(&$read, &$write, &$except, $tv_sec, $tv_usec = 0);

    /**
     * Sets the blocking mode on the socket resource
     * Wen an operation (receive, send, connect, accept, etc) is performed after set_block() the script will pause execution until the operation is completed
     * @return SocketInterface
     * @throws Exception
     */
    function set_block();

    /**
     * Sets nonblocking mode for socket resource
     * @return SocketInterface
     * @throws Exception
     */
    function set_nonblock();

    /**
     * @param int
     * @param int
     * @param mixed
     * @return SocketInterface
     */
    function set_option($level, $optname, $optval);

    /**
     * @param int
     * @return SocketInterface
     * @throws Exception
     */
    function shutdown($how = 2);

    /**
     * Send text to the client on the other end of the socket
     * @param string
     * @param int
     */
    function write($buffer, $length = 0);
}