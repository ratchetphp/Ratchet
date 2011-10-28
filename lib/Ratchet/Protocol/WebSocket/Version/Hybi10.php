<?php
namespace Ratchet\Protocol\WebSocket\Version;

/**
 * The HyBi-10 version, identified in the headers as version 8, is currently implemented by the latest Chrome and Firefix version
 * @link http://tools.ietf.org/html/draft-ietf-hybi-thewebsocketprotocol-10
 */
class Hybi10 implements VersionInterface {
    const GUID = '258EAFA5-E914-47DA-95CA-C5AB0DC85B11';

    public function handshake(array $headers) {
        $key = $this->sign($headers['Sec-Websocket-Key']);

        return array(
            ''                     => 'HTTP/1.1 101 Switching Protocols'
          , 'Upgrade'              => 'websocket'
          , 'Connection'           => 'Upgrade'
          , 'Sec-WebSocket-Accept' => $this->sign($headers['Sec-Websocket-Key'])
//          , 'Sec-WebSocket-Protocol' => ''
        );
   }

    /**
     * Unframe a message received from the client
     * Thanks to @lemmingzshadow for the code on decoding a HyBi-10 frame
     * @link https://github.com/lemmingzshadow/php-websocket
     * @param string
     * @return string
     * @throws UnexpectedValueException
     */
    public function unframe($message) {
        $data            = $message;
		$payloadLength   = '';
		$mask            = '';
		$unmaskedPayload = '';
		$decodedData     = array();

		// estimate frame type:
		$firstByteBinary  = sprintf('%08b', ord($data[0]));		
		$secondByteBinary = sprintf('%08b', ord($data[1]));
		$opcode           = bindec(substr($firstByteBinary, 4, 4));
		$isMasked         = ($secondByteBinary[0] == '1') ? true : false;
		$payloadLength    = ord($data[1]) & 127;

		// close connection if unmasked frame is received:
		if($isMasked === false) {
            throw new \UnexpectedValueException('Masked byte is false');
		}

		switch($opcode) {
			// text frame:
			case 1:
				$decodedData['type'] = 'text';				
			break;
			
			// connection close frame:
			case 8:
				$decodedData['type'] = 'close';
			break;
			
			// ping frame:
			case 9:
				$decodedData['type'] = 'ping';				
			break;
			
			// pong frame:
			case 10:
				$decodedData['type'] = 'pong';
			break;
			
			default:
				// Close connection on unknown opcode:
                throw new UnexpectedValueException('Unknown opcode');
			break;
		}

		if($payloadLength === 126) {
		   $mask = substr($data, 4, 4);
		   $payloadOffset = 8;
		} elseif($payloadLength === 127) {
			$mask = substr($data, 10, 4);
			$payloadOffset = 14;
		} else {
			$mask = substr($data, 2, 4);	
			$payloadOffset = 6;
		}

		$dataLength = strlen($data);

		if($isMasked === true) {
			for($i = $payloadOffset; $i < $dataLength; $i++) {
				$j = $i - $payloadOffset;
				$unmaskedPayload .= $data[$i] ^ $mask[$j % 4];
			}
			$decodedData['payload'] = $unmaskedPayload;
		} else {
			$payloadOffset = $payloadOffset - 4;
			$decodedData['payload'] = substr($data, $payloadOffset);
		}

		return $decodedData;
    }

    /**
     * @todo Complete this method
     */
    public function frame($message) {
        return $message;
    }

    public function sign($key) {
        return base64_encode(sha1($key . static::GUID, 1));
    }
}