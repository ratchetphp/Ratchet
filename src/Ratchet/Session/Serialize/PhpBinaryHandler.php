<?php

namespace Ratchet\Session\Serialize;

class PhpBinaryHandler implements HandlerInterface {
    #[\Override]
    function serialize(array $data): never {
        throw new \RuntimeException("Serialize PhpHandler:serialize code not written yet, write me!");
    }

    /**
     * @link http://ca2.php.net/manual/en/function.session-decode.php#108037 Code from this comment on php.net
     *
     * @psalm-return array<string, mixed>
     */
    #[\Override]
    public function unserialize($raw): array {
        $returnData = [];
        $offset = 0;

        while ($offset < strlen((string) $raw)) {
            $num = ord($raw[$offset]);
            $offset += 1;
            $varname = substr((string) $raw, $offset, $num);
            $offset += $num;
            $data = unserialize(substr((string) $raw, $offset));

            $returnData[$varname] = $data;
            $offset += strlen(serialize($data));
        }

        return $returnData;
    }
}
