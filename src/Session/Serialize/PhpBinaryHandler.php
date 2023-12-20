<?php

namespace Ratchet\Session\Serialize;

class PhpBinaryHandler implements HandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public function serialize(array $data): string
    {
        throw new \RuntimeException('Serialize PhpHandler:serialize code not written yet, write me!');
    }

    /**
     * {@inheritdoc}
     *
     * @link http://ca2.php.net/manual/en/function.session-decode.php#108037 Code from this comment on php.net
     */
    public function unserialize(string $raw): array
    {
        $returnData = [];
        $offset = 0;

        while ($offset < strlen($raw)) {
            $num = ord($raw[$offset]);
            $offset += 1;
            $varname = substr($raw, $offset, $num);
            $offset += $num;
            $data = unserialize(substr($raw, $offset));

            $returnData[$varname] = $data;
            $offset += strlen(serialize($data));
        }

        return $returnData;
    }
}
