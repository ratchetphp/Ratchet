<?php
namespace Ratchet\Component\Session\Serialize;

class PhpHandler implements HandlerInterface {
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
            if (!strstr(substr($raw, $offset), "|")) {
                throw new Exception("invalid data, remaining: " . substr($raw, $offset));
            }

            $pos     = strpos($raw, "|", $offset);
            $num     = $pos - $offset;
            $varname = substr($raw, $offset, $num);
            $offset += $num + 1;
            $data    = unserialize(substr($raw, $offset));

            $returnData[$varname] = $data;
            $offset += strlen(serialize($data));
        }

        return $returnData;
    }
}