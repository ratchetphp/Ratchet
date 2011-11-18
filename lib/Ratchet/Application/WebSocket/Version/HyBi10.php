<?php
namespace Ratchet\Application\WebSocket\Version;
use Ratchet\Application\WebSocket\Util\HTTP;

/**
 * The HyBi-10 version, identified in the headers as version 8, is currently implemented by the latest Chrome and Firefix version
 * @link http://tools.ietf.org/html/draft-ietf-hybi-thewebsocketprotocol-10
 * @todo Naming...I'm not fond of this naming convention...
 */
class HyBi10 implements VersionInterface {
    const GUID = '258EAFA5-E914-47DA-95CA-C5AB0DC85B11';

    /**
     * @return array
     * I kept this as an array and combined in App for future considerations...easier to add a subprotol as a key value than edit a string
     */
    public function handshake($message) {
        $headers = HTTP::getHeaders($message);
        $key     = $this->sign($headers['Sec-Websocket-Key']);

        return array(
            ''                     => 'HTTP/1.1 101 Switching Protocols'
          , 'Upgrade'              => 'websocket'
          , 'Connection'           => 'Upgrade'
          , 'Sec-WebSocket-Accept' => $this->sign($headers['Sec-Websocket-Key'])
//          , 'Sec-WebSocket-Protocol' => ''
        );
   }

    /**
     * @return HyBi10\Message
     */
    public function newMessage() {
        return new HyBi10\Message;
    }

    /**
     * @return HyBi10\Frame
     */
    public function newFrame() {
        return new HyBi10\Frame;
    }

    /**
     * Unframe a message received from the client
     * Thanks to @lemmingzshadow for the code on decoding a HyBi-10 frame
     * @link https://github.com/lemmingzshadow/php-websocket
     * @param string
     * @return array
     * @throws UnexpectedValueException
     * @todo return a common interface instead of array
     */
    public function unframe($message) {
        $data        = $message;
        $mask        = $payloadLength = $unmaskedPayload = '';
        $decodedData = array();

        // estimate frame type:
        $firstByteBinary  = sprintf('%08b', ord($data[0]));
        $finIndicator     = bindec(substr($firstByteBinary, 0, 1));
        $opcode           = bindec(substr($firstByteBinary, 4, 4));

        $secondByteBinary = sprintf('%08b', ord($data[1]));
        $isMasked         = ($secondByteBinary[0] == '1') ? true : false;
        $payloadLength    = ord($data[1]) & 127;

        // close connection if unmasked frame is received:
        if($isMasked === false) {
            throw new \UnexpectedValueException('Masked byte is false');
        }

        switch($opcode) {
            // continuation frame
            case 0:
                throw new \UnexpectedValueException("Opcode CONTINUATION not accepted yet");
                $decodedData['type'] = 'text'; // incomplete
            break;

            // text frame:
            case 1:
                $decodedData['type'] = 'text';  
            break;

            // binary data frame
            case 2:
                throw new \UnexpectedValueException("Opcode BINARY not accepted yet");
                $decodedData['type'] = 'binary';
            break;

            // connection close frame:
            case 8:
                throw new \UnexpectedValueException("Opcode CLOSE not accepted yet");
                $decodedData['type'] = 'close';
            break;

            // ping frame:
            case 9:
                throw new \UnexpectedValueException("Opcode PING not accepted yet");
                $decodedData['type'] = 'ping';                
            break;

            // pong frame:
            case 10:
                throw new \UnexpectedValueException("Opcode PONG not accepted yet");
                $decodedData['type'] = 'pong';
            break;

            default:
                // Close connection on unknown opcode:
                throw new \UnexpectedValueException("Unknown opcode ({$opcode})");
            break;
        }

        if($payloadLength === 126) {
           $msg_len = bindec(sprintf('%08b', ord($data[2])) . sprintf('%08b', ord($data[3])));
           $mask = substr($data, 4, 4);
           $payloadOffset = 8;
        } elseif($payloadLength === 127) {
            $msg_len = bindec(
                sprintf('%08b', ord($data[2])) 
              . sprintf('%08b', ord($data[3]))
              . sprintf('%08b', ord($data[4]))
              . sprintf('%08b', ord($data[5]))
              . sprintf('%08b', ord($data[6]))
              . sprintf('%08b', ord($data[7]))
              . sprintf('%08b', ord($data[8]))
              . sprintf('%08b', ord($data[9]))
            );

            $mask = substr($data, 10, 4);
            $payloadOffset = 14;
        } else {
            $msg_len = $payloadLength;
            $mask = substr($data, 2, 4);    
            $payloadOffset = 6;
        }

        $dataLength = strlen($data);

        if($isMasked === true) { // This will always pass...
            for($i = $payloadOffset; $i < $dataLength; $i++) {
                $j = $i - $payloadOffset;
                $unmaskedPayload .= $data[$i] ^ $mask[$j % 4];
            }
            $decodedData['payload'] = $unmaskedPayload;
        }

        return $decodedData;
    }

    /**
     * Thanks to @lemmingzshadow for the code on decoding a HyBi-10 frame
     * @link https://github.com/lemmingzshadow/php-websocket
     * @todo look into what happens when false is returned here
     * @param string
     * @return string
     */
    public function frame($message) {
        $payload = $message;
        $type    = 'text';
        $masked  = true;

        $frameHead = array();
        $frame = '';
        $payloadLength = strlen($payload);

        switch($type) {
            case 'text':
                // first byte indicates FIN, Text-Frame (10000001):
                $frameHead[0] = 129;                
            break;            

            case 'close':
                // first byte indicates FIN, Close Frame(10001000):
                $frameHead[0] = 136;
            break;

            case 'ping':
                // first byte indicates FIN, Ping frame (10001001):
                $frameHead[0] = 137;
            break;

            case 'pong':
                // first byte indicates FIN, Pong frame (10001010):
                $frameHead[0] = 138;
            break;
        }

        // set mask and payload length (using 1, 3 or 9 bytes) 
        if($payloadLength > 65535) {
            $payloadLengthBin = str_split(sprintf('%064b', $payloadLength), 8);
            $frameHead[1] = ($masked === true) ? 255 : 127;
            for($i = 0; $i < 8; $i++) {
                $frameHead[$i+2] = bindec($payloadLengthBin[$i]);
            }
            // most significant bit MUST be 0 (return false if to much data)
            if($frameHead[2] > 127) {
                return false;
            }
        } elseif($payloadLength > 125) {
            $payloadLengthBin = str_split(sprintf('%016b', $payloadLength), 8);
            $frameHead[1] = ($masked === true) ? 254 : 126;
            $frameHead[2] = bindec($payloadLengthBin[0]);
            $frameHead[3] = bindec($payloadLengthBin[1]);
        } else {
            $frameHead[1] = ($masked === true) ? $payloadLength + 128 : $payloadLength;
        }

        // convert frame-head to string:
        foreach(array_keys($frameHead) as $i) {
            $frameHead[$i] = chr($frameHead[$i]);
        } if($masked === true) {
            // generate a random mask:
            $mask = array();
            for($i = 0; $i < 4; $i++)
            {
                $mask[$i] = chr(rand(0, 255));
            }

            $frameHead = array_merge($frameHead, $mask);            
        }                        
        $frame = implode('', $frameHead);

        // append payload to frame:
        $framePayload = array();
        for($i = 0; $i < $payloadLength; $i++) {        
            $frame .= ($masked === true) ? $payload[$i] ^ $mask[$i % 4] : $payload[$i];
        }

        return $frame;
    }

    /**
     * Used when doing the handshake to encode the key, verifying client/server are speaking the same language
     * @param string
     * @return string
     * @internal
     */
    public function sign($key) {
        return base64_encode(sha1($key . static::GUID, 1));
    }
}