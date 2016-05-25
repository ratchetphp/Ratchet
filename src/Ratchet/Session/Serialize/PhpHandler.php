<?php
namespace Ratchet\Session\Serialize;

class PhpHandler implements HandlerInterface {
    /**
     * Simply reverse behaviour of unserialize method.
     * {@inheritdoc}
     */
    function serialize(array $data) {
        $preSerialized = array();
        $serialized = '';

        if (count($data)) {
            foreach ($data as $bucket => $bucketData) {
                $preSerialized[] = $bucket . '|' . serialize($bucketData);
            }
            $serialized = implode('', $preSerialized);
        }

        return $serialized;
    }

    /**
     * {@inheritdoc}
     * @link http://ca2.php.net/manual/en/function.session-decode.php#108037 Code from this comment on php.net
     * @throws \UnexpectedValueException If there is a problem parsing the data
     */
    public function unserialize($raw) {
        $returnData = array();
        $offset     = 0;

        while ($offset < strlen($raw)) {
            if (!strstr(substr($raw, $offset), "|")) {
                throw new \UnexpectedValueException("invalid data, remaining: " . substr($raw, $offset));
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
