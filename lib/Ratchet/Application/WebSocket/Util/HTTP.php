<?php
namespace Ratchet\Application\WebSocket\Util;

/**
 * A helper class for handling HTTP requests
 * @todo Needs re-write...http_parse_headers is a PECL extension that changes the case to unexpected values
 * @todo Again, RE-WRITE - I want all the expected headers to at least be set in the returned, even if not there, set as null - having to do too much work in HandshaekVerifier
 */
class HTTP {
    /**
     * @todo Probably should iterate through the array, strtolower all the things, then return it
     * @param string
     * @return array
     */
    public static function getHeaders($http_message) {
        $header_array = function_exists('http_parse_headers') ? http_parse_headers($http_message) : self::http_parse_headers($http_message);

        return $header_array + array(
            'Host'                   => null
          , 'Upgrade'                => null
          , 'Connection'             => null
          , 'Sec-Websocket-Key'      => null
          , 'Origin'                 => null
          , 'Sec-Websocket-Protocol' => null
          , 'Sec-Websocket-Version'  => null
          , 'Sec-Websocket-Origin'   => null
        );
    }

    /**
     * @param string
     * @return array
     * This is a fallback method for http_parse_headers as not all php installs have the HTTP module present
     * @internal
     */
    protected static function http_parse_headers($http_message) {
        $retVal = array();
        $fields = explode("br", preg_replace("%(<|/\>|>)%", "", nl2br($http_message)));

        foreach ($fields as $field) {
            if (preg_match('%^(GET|POST|PUT|DELETE|PATCH)(\s)(.*)%', $field, $matchReq)) {
                $retVal["Request Method"] = $matchReq[1];
                $retVal["Request Url"]    = $matchReq[3];
            } elseif (preg_match('/([^:]+): (.+)/m', $field, $match) ) {
                $match[1] = preg_replace('/(?<=^|[\x09\x20\x2D])./e', 'strtoupper("\0")', strtolower(trim($match[1])));
                if (isset($retVal[$match[1]])) {
                    $retVal[$match[1]] = array($retVal[$match[1]], $match[2]);
                } else {
                    $retVal[$match[1]] = trim($match[2]);
                }
            }
        }

        return $retVal;
    }
}