<?php
namespace Ratchet\Component\Server;
use Ratchet\Component\MessageComponentInterface;
use Ratchet\Resource\ConnectionInterface;
use Ratchet\Resource\Connection;
use Ratchet\Resource\Command\CommandInterface;

/**
 * An app to go on a server stack to pass a policy file to a Flash socket
 * Useful if you're using Flash as a WebSocket polyfill on IE
 * Be sure to run your server instance on port 843
 * By default this lets accepts everything, make sure you tighten the rules up for production
 * @final
 * @todo This just gets dumped with a whole xml file - I will make a nice API to implement this (eventually)
 * @todo Move this into Ratchet when the above todo is complete
 */
class FlashPolicyComponent implements MessageComponentInterface {

    protected $_policy      = '<?xml version="1.0"?><!DOCTYPE cross-domain-policy SYSTEM "http://www.adobe.com/xml/dtds/cross-domain-policy.dtd"><cross-domain-policy></cross-domain-policy>';
    protected $_access      = array();
    protected $_siteControl = '';

    protected $_cache      = '';
    protected $_cacheValid = false;

    /**
     * @{inheritdoc}
     */
    public function onOpen(ConnectionInterface $conn) {
        $conn->PolicyRequest = '';
    }

    /**
     * @{inheritdoc}
     */
    public function onMessage(ConnectionInterface $from, $msg) {

        if (!$this->_cacheValid) {
            $this->_cache      = $this->renderPolicy()->asXML();
            $this->_cacheValid = true;
        }

        $from->PolicyRequest .= $msg;
        if (strlen($from->_cache) < 20) {
            return;
        }


        $cmd = new SendMessage($from);
        $cmd->setMessage($this->_cache . "\0");

        return $cmd;
    }

    /**
     * @{inheritdoc}
     */
    public function onClose(ConnectionInterface $conn) {
    }

    /**
     * @{inheritdoc}
     */
    public function onError(ConnectionInterface $conn, \Exception $e) {
        return new CloseConnection($conn);
    }

    /**
     * setSiteControl function.
     *
     * @access public
     * @param string $permittedCrossDomainPolicies (default: 'all')
     * @return bool
     */
    public function setSiteControl($permittedCrossDomainPolicies = 'all') {
        if (!$this->validateSiteControl($permittedCrossDomainPolicies)) {
            throw new \UnexpectedValueException('Invalid site control set');
            return false;
        }
        $this->_siteControl = $permittedCrossDomainPolicies;
        return true;
    }

    /**
     * renderPolicy function.
     *
     * @access public
     * @return SimpleXMLElement
     */
    public function renderPolicy() {

        $policy = new \SimpleXMLElement($this->_policy);


        $siteControl = $policy->addChild('site-control');

        if ($this->_siteControl == '') {
            throw new \UnexpectedValueException('Where\'s my site control?');
        }
        $siteControl->addAttribute('permitted-cross-domain-policies', $this->_siteControl);


        if (empty($this->_access)) {
            throw new \UnexpectedValueException('Missing site access');
        }
        foreach ($this->_access as $access) {

            $tmp = $policy->addChild('allow-access-from');
            $tmp->addAttribute('domain', $access[0]);
            $tmp->addAttribute('to-ports', $access[1]);
            $tmp->addAttribute('secure', ($access[2] == true) ? 'true' : 'false');
        }

        return $policy;

    }

    /**
     * addAllowedAccess function.
     *
     * @access public
     * @param string $domain
     * @param string $ports (default: '*')
     * @param bool $secure (default: false)
     * @return bool
     */
    public function addAllowedAccess($domain, $ports = '*', $secure = false) {

        if (!$this->validateDomain($domain)) {
           throw new \UnexpectedValueException('Invalid domain');
           return false;
        }
        if (!$this->validatePorts($ports)) {
           throw new \UnexpectedValueException('Invalid Port');
           return false;
        }


        $this->_access[]   = array($domain, $ports, $secure);
        $this->_cacheValid = false;

        return true;
    }

    /**
     * validateSiteControl function.
     *
     * @access public
     * @param mixed $permittedCrossDomainPolicies
     * @return void
     */
    public function validateSiteControl($permittedCrossDomainPolicies) {

        //'by-content-type' and 'by-ftp-filename' not available for sockets
        return (bool)in_array($permittedCrossDomainPolicies, array('none', 'master-only', 'all'));
    }

    /**
     * validateDomain function.
     *
     * @access public
     * @param string $domain
     * @return bool
     */
    public function validateDomain($domain) {

        return (bool)preg_match("/^((http(s)?:\/\/)?([a-z0-9-_]+\.|\*\.)*([a-z0-9-_\.]+)|\*)$/i", $domain);
    }

    /**
     * validatePorts function.
     *
     * @access public
     * @param string $port
     * @return bool
     */
    public function validatePorts($port) {

        return (bool)preg_match('/^(\*|(\d+[,-]?)*\d+)$/', $port);
    }

    /**
     * validateSecure function.
     *
     * @access public
     * @param bool $secure
     * @return bool
     */
    public function validateSecure($secure) {

        return is_bool($secure);
    }
}