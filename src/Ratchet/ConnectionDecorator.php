<?php
namespace Ratchet;

trait ConnectionDecorator {
    private $connection;
    private $properties;

    public function __construct(ConnectionInterface $conn, array $properties = []) {
        $this->connection = $conn;
        $this->properties = $properties;
    }

    /**
     * @param $id
     * @return mixed
     * @throws ConnectionPropertyNotFoundException
     */
    public function get($id) {
        if (array_key_exists($id, $this->properties)) {
            return $this->properties[$id];
        }

        if ($this->connection->has($id)) {
            return $this->connection->get($id);
        }

        throw new ConnectionPropertyNotFoundException("{$id} not found");
    }

    public function has($id) {
        return array_key_exists($id, $this->properties) || $this->connection->has($id);
    }

    public function close() {
        $this->connection->close();
    }
}
