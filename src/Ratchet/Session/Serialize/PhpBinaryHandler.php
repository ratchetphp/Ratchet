<?php
namespace Ratchet\Session\Serialize;

class PhpBinaryHandler implements HandlerInterface {
    /**
     * {@inheritdoc}
     */
    function serialize(array $data) {
        throw new \RuntimeException("Serialize PhpHandler:serialize code not written yet, write me!");
    }

    /**
     * {@inheritdoc}
     * @link http://ca2.php.net/manual/en/function.session-decode.php#108037 Code from this comment on php.net
     */
    public function unserialize($raw) {
        $returnData = array();
        $offset     = 0;

        while ($offset < strlen($raw)) {
            $num     = ord($raw[$offset]);
            $offset += 1;
            $varname = substr($raw, $offset, $num);
            $offset += $num;

            // try to unserialize one piece of data from current offset, ignoring any warnings for trailing data on PHP 8.3+
            // @link https://wiki.php.net/rfc/unserialize_warn_on_trailing_data
            $data = @unserialize(substr($raw, $offset));

            $returnData[$varname] = $data;
            $offset += strlen(serialize($data));
        }

        return $returnData;
    }
}
