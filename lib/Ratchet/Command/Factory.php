<?php
namespace Ratchet\Command;
use Ratchet\SocketInterface;

/**
 * A factory pattern class to easily create all the things in the Ratchet\Command interface
 */
class Factory {
    protected $_paths = array();

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
    public function newCommand($name, SocketInterface $conn) {
        $cmd = null;
        foreach ($this->_paths as $path) {
            if (class_exists($path . $name)) {
                $cmd = $path . $name;
                break;
            }
        }

        if (null === $cmd) {
            throw new \UnexepctedValueException("Command {$name} not found");
        }

        return new $cmd($conn);
    }

    /**
     * @param string
     * @return string
     */
    protected function slashIt($ns) {
        return (substr($ns, -1) == '\\' ? $ns : $ns . '\\');
    }
}