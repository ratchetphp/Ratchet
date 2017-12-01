<?php
namespace Ratchet\Session\Serialize;

class PhpSerializeHandler implements HandlerInterface {
    /**
     * {@inheritdoc}
     */
    public function serialize(array $data) {
        return serialize($data);
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($raw) {
        return unserialize($raw);
    }
}
