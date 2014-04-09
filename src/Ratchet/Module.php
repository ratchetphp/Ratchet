<?php
namespace Ratchet;

use Zend\ModuleManager\Feature\AutoloaderProviderInterface,
	Zend\ModuleManager\Feature\ControllerProviderInterface,
	Zend\ModuleManager\Feature\ServiceProviderInterface,
	Zend\ModuleManager\Feature\ConfigProviderInterface;

class Module 
	implements 
		AutoloaderProviderInterface,
		ControllerProviderInterface,
		ServiceProviderInterface,
		ConfigProviderInterface
{
    public function getConfig()
    {
        return include __DIR__ . '/../../config/module.config.php';
    }
    
    public function getControllerConfig()
    {
        return include __DIR__ . '/../../config/controllers.config.php';
    }

    public function getServiceConfig()
    {
        return include __DIR__ . '/../../config/services.config.php';
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/../../src/' . __NAMESPACE__,
                ),
            ),
        );
    }
}