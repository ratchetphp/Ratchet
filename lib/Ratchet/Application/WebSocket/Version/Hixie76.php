<?php
namespace Ratchet\Protocol\WebSocket\Version;

/**
 * The Hixie76 is currently implemented by Safari
 * Handshake from Andrea Giammarchi (http://webreflection.blogspot.com/2010/06/websocket-handshake-76-simplified.html)
 * @link http://tools.ietf.org/html/draft-hixie-thewebsocketprotocol-76
 */
class Hixie76 implements VersionInterface {
    /**
     * @param string
     * @return string
     */
    public function handshake($message) {
        $buffer   = $message;
        $resource = $host = $origin = $key1 = $key2 = $protocol = $code = $handshake = null;

        preg_match('#GET (.*?) HTTP#', $buffer, $match) && $resource = $match[1];
        preg_match("#Host: (.*?)\r\n#", $buffer, $match) && $host = $match[1];
        preg_match("#Sec-WebSocket-Key1: (.*?)\r\n#", $buffer, $match) && $key1 = $match[1];
        preg_match("#Sec-WebSocket-Key2: (.*?)\r\n#", $buffer, $match) && $key2 = $match[1];
        preg_match("#Sec-WebSocket-Protocol: (.*?)\r\n#", $buffer, $match) && $protocol = $match[1];
        preg_match("#Origin: (.*?)\r\n#", $buffer, $match) && $origin = $match[1];
        preg_match("#\r\n(.*?)\$#", $buffer, $match) && $code = $match[1];

        return "HTTP/1.1 101 WebSocket Protocol Handshake\r\n".
            "Upgrade: WebSocket\r\n"
          . "Connection: Upgrade\r\n"
          . "Sec-WebSocket-Origin: {$origin}\r\n"
          . "Sec-WebSocket-Location: ws://{$host}{$resource}\r\n"
          . ($protocol ? "Sec-WebSocket-Protocol: {$protocol}\r\n" : "")
          . "\r\n"
          . $this->_createHandshakeThingy($key1, $key2, $code)
        ;
    }

    public function unframe($message) {
        return substr($message, 1, strlen($message) - 2);
    }

    public function frame($message) {
        return chr(0) . $message . chr(255);
    }

    protected function _doStuffToObtainAnInt32($key) {
        return preg_match_all('#[0-9]#', $key, $number) && preg_match_all('# #', $key, $space) ?
            implode('', $number[0]) / count($space[0]) :
            ''
        ;
    }

    protected function _createHandshakeThingy($key1, $key2, $code) {
        return md5(
            pack('N', $this->_doStuffToObtainAnInt32($key1))
          . pack('N', $this->_doStuffToObtainAnInt32($key2))
          . $code
        , true);
    }
}