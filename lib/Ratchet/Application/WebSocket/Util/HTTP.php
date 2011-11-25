<?php
namespace Ratchet\Application\WebSocket\Util;

/**
 * A helper class for handling HTTP requests
 * @todo Needs re-write...http_parse_headers is a PECL extension that changes the case to unexpected values
 */
class HTTP {
    /**
     * @todo Probably should iterate through the array, strtolower all the things, then return it
     * @param string
     * @return array
     */
    public static function getHeaders($http_message) {
        return function_exists('http_parse_headers') ? http_parse_headers($http_message) : self::http_parse_headers($http_message);
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