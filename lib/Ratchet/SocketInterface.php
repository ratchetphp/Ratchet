<?php
namespace Ratchet;

/**
 * An object-oriented container for a single socket connection
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
     * @param int
     * @return SocketInterface
     * @throws Exception
     */
    function listen($backlog = 0);

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

    // @todo Figure out how to break this out to not do pass by reference
//    function select(array &$read, array &$write, array &$except, $tv_sec, $tv_usec = 0);

    /**
     * @return SocketInterface
     * @throws Exception
     */
    function set_block();

    /**
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