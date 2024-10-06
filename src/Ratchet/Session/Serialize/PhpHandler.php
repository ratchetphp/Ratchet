<?php

namespace Ratchet\Session\Serialize;

class PhpHandler implements HandlerInterface {
    /**
     * Simply reverse behaviour of unserialize method.
     * {@inheritdoc}
     */
    #[\Override]
    function serialize(array $data): string {
        $preSerialized = [];
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
     * @link http://ca2.php.net/manual/en/function.session-decode.php#108037 Code from this comment on php.net
     *
     * @throws \UnexpectedValueException If there is a problem parsing the data
     *
     * @psalm-return array<string, mixed>
     */
    #[\Override]
    public function unserialize($raw): array {
        $returnData = [];
        $offset = 0;

        while ($offset < strlen((string) $raw)) {
            if (! strstr(substr((string) $raw, $offset), "|")) {
                throw new \UnexpectedValueException("invalid data, remaining: " . substr((string) $raw, $offset));
            }

            $pos = strpos((string) $raw, "|", $offset);
            $num = $pos - $offset;
            $varname = substr((string) $raw, $offset, $num);
            $offset += $num + 1;
            $data = unserialize(substr((string) $raw, $offset));

            $returnData[$varname] = $data;
            $offset += strlen(serialize($data));
        }

        return $returnData;
    }
}
