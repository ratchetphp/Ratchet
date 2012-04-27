<?php
namespace Ratchet\Resource\Command;
use Ratchet\Resource\ConnectionInterface;

/**
 * A factory pattern class to easily create all the things in the Ratchet\Resource\Command interface
 */
class Factory {
    protected $_paths = array();

    protected $_mapped_commands = array();

    protected static $globalPaths = array();

    protected $_ignoreGlobals = false;

    /**
     * @param bool If set to TRUE this will ignore all the statically registered namespaces
     */
    public function __construct($ignoreGlobals = false) {
        $this->addActionPath(__NAMESPACE__ . '\\Action');
        $this->_ignoreGlobals = (boolean)$ignoreGlobals;
    }

    /**
     * Add a new namespace of which CommandInterfaces reside under to autoload with $this->newCommand()
     * @param string
     */
    public function addActionPath($namespace) {
        $this->_paths[] = $this->slashIt($namespace);
    }

    public static function registerActionPath($namespace) {
        static::$globalPaths[$namespace] = 1;
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
    public function newCommand($name, ConnectionInterface $conn) {
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

        if (false === $this->_ignoreGlobals) {
            foreach (static::$globalPaths as $path => $one) {
                $path = $this->slashIt($path);
                if (class_exists($path . $name)) {
                    $this->_mapped_commands[$name] = $path . $name;
                    return $this->newCommand($name, $conn);
                }
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