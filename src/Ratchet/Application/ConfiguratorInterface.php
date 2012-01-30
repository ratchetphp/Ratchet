<?php
namespace Ratchet\Application;

/**
 * @todo Does this belong in root dir of application
 */
interface ConfiguratorInterface {
    /**
     * @return array
     */
    static function getDefaultConfig();
}