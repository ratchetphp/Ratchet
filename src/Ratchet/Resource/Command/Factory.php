<?php
namespace Ratchet\Resource\Command;
use Ratchet\Resource\Connection;

/**
 * A factory pattern class to easily create all the things in the Ratchet\Resource\Command interface
 */
class Factory {
    protected $_paths = array();

    protected $_mapped_commands = array();

    public function __construct() {
        $this->addActionPath(__NAMESPACE__ . '\\Action');
    }

    /**
     * Add a new namespace of which CommandInterfaces reside under to autoload with $this->newCommand()
     * @param string
     */
    public function addActionPath($namespace) {
        $this->_paths[] = $this->slashIt($namespace);
    }

    /**
     * @return Composite
     */
    public function newComposite() {
        return new Composite;
    }

    /**
     * @param string
     * @return CommandInterface
     * @throws UnexpectedValueException
     */
    public function newCommand($name, Connection $conn) {
        if (isset($this->_mapped_commands[$name])) {
            $cmd = $this->_mapped_commands[$name];
            return new $cmd($conn);
        }

        foreach ($this->_paths as $path) {
            if (class_exists($path . $name)) {
                $this->_mapped_commands[$name] = $path . $name;
                return $this->newCommand($name, $conn);
            }
        }

        throw new \UnexepctedValueException("Command {$name} not found");
    }

    /**
     * @param string
     * @return string
     */
    protected function slashIt($ns) {
        return (substr($ns, -1) == '\\' ? $ns : $ns . '\\');
    }
}