<?php
namespace Ratchet\Server;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

/**
 * An app to go on a server stack to pass a policy file to a Flash socket
 * Useful if you're using Flash as a WebSocket polyfill on IE
 * Be sure to run your server instance on port 843
 * By default this lets accepts everything, make sure you tighten the rules up for production
 * @final
 * @link http://www.adobe.com/devnet/articles/crossdomain_policy_file_spec.html
 * @link http://learn.adobe.com/wiki/download/attachments/64389123/CrossDomain_PolicyFile_Specification.pdf?version=1
 * @link view-source:http://www.adobe.com/xml/schemas/PolicyFileSocket.xsd
 */
class FlashPolicy implements MessageComponentInterface {

    /**
     * Contains the root policy node
     * @var string
     */
    protected $_policy = '<?xml version="1.0"?><!DOCTYPE cross-domain-policy SYSTEM "http://www.adobe.com/xml/dtds/cross-domain-policy.dtd"><cross-domain-policy></cross-domain-policy>';

    /**
     * Stores an array of allowed domains and their ports
     * @var array
     */
    protected $_access = array();

    /**
     * @var string
     */
    protected $_siteControl = '';

    /**
     * @var string
     */
    protected $_cache = '';

    /**
     * @var string
     */
    protected $_cacheValid = false;

    /**
     * Add a domain to an allowed access list.
     *
     * @param string $domain Specifies a requesting domain to be granted access. Both named domains and IP
     * addresses are acceptable values. Subdomains are considered different domains. A wildcard (*) can
     * be used to match all domains when used alone, or multiple domains (subdomains) when used as a
     * prefix for an explicit, second-level domain name separated with a dot (.)
     * @param string $ports A comma-separated list of ports or range of ports that a socket connection
     * is allowed to connect to. A range of ports is specified through a dash (-) between two port numbers.
     * Ranges can be used with individual ports when separated with a comma. A single wildcard (*) can
     * be used to allow all ports.
     * @param bool $secure
     * @throws \UnexpectedValueException
     * @return FlashPolicy
     */
    public function addAllowedAccess($domain, $ports = '*', $secure = false) {
        if (!$this->validateDomain($domain)) {
           throw new \UnexpectedValueException('Invalid domain');
        }

        if (!$this->validatePorts($ports)) {
           throw new \UnexpectedValueException('Invalid Port');
        }

        $this->_access[]   = array($domain, $ports, (boolean)$secure);
        $this->_cacheValid = false;

        return $this;
    }

    /**
     * site-control defines the meta-policy for the current domain. A meta-policy specifies acceptable
     * domain policy files other than the master policy file located in the target domain's root and named
     * crossdomain.xml.
     *
     * @param string $permittedCrossDomainPolicies
     * @throws \UnexpectedValueException
     * @return FlashPolicy
     */
    public function setSiteControl($permittedCrossDomainPolicies = 'all') {
        if (!$this->validateSiteControl($permittedCrossDomainPolicies)) {
            throw new \UnexpectedValueException('Invalid site control set');
        }

        $this->_siteControl = $permittedCrossDomainPolicies;
        $this->_cacheValid  = false;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function onOpen(ConnectionInterface $conn) {
    }

    /**
     * {@inheritdoc}
     */
    public function onMessage(ConnectionInterface $from, $msg) {
        if (!$this->_cacheValid) {
            $this->_cache      = $this->renderPolicy()->asXML();
            $this->_cacheValid = true;
        }

        $from->send($this->_cache . "\0");
        $from->close();
    }

    /**
     * {@inheritdoc}
     */
    public function onClose(ConnectionInterface $conn) {
    }

    /**
     * {@inheritdoc}
     */
    public function onError(ConnectionInterface $conn, \Exception $e) {
        $conn->close();
    }

    /**
     * Builds the crossdomain file based on the template policy
     *
     * @throws \UnexpectedValueException
     * @return \SimpleXMLElement
     */
    public function renderPolicy() {
        $policy = new \SimpleXMLElement($this->_policy);

        $siteControl = $policy->addChild('site-control');

        if ($this->_siteControl == '') {
            $this->setSiteControl();
        }

        $siteControl->addAttribute('permitted-cross-domain-policies', $this->_siteControl);

        if (empty($this->_access)) {
            throw new \UnexpectedValueException('You must add a domain through addAllowedAccess()');
        }

        foreach ($this->_access as $access) {
            $tmp = $policy->addChild('allow-access-from');
            $tmp->addAttribute('domain', $access[0]);
            $tmp->addAttribute('to-ports', $access[1]);
            $tmp->addAttribute('secure', ($access[2] === true) ? 'true' : 'false');
        }

        return $policy;
    }

    /**
     * Make sure the proper site control was passed
     *
     * @param string $permittedCrossDomainPolicies
     * @return bool
     */
    public function validateSiteControl($permittedCrossDomainPolicies) {
        //'by-content-type' and 'by-ftp-filename' are not available for sockets
        return (bool)in_array($permittedCrossDomainPolicies, array('none', 'master-only', 'all'));
    }

    /**
     * Validate for proper domains (wildcards allowed)
     *
     * @param string $domain
     * @return bool
     */
    public function validateDomain($domain) {
        return (bool)preg_match("/^((http(s)?:\/\/)?([a-z0-9-_]+\.|\*\.)*([a-z0-9-_\.]+)|\*)$/i", $domain);
    }

    /**
     * Make sure valid ports were passed
     *
     * @param string $port
     * @return bool
     */
    public function validatePorts($port) {
        return (bool)preg_match('/^(\*|(\d+[,-]?)*\d+)$/', $port);
    }
}