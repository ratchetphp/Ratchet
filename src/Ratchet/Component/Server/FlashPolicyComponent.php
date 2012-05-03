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
    protected $_headers     = array();
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
            $this->_cache = $this->renderPolicy()->asXML();
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
     * @return void
     */
    public function setSiteControl($permittedCrossDomainPolicies = 'all') {
        if (!$this->validateSiteControl($permittedCrossDomainPolicies)) {
            throw new \UnexpectedValueException('Invalid site control set');
        }
        $this->_siteControl = $permittedCrossDomainPolicies;
    }

    /**
     * renderPolicy function.
     * 
     * @access public
     * @return void
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

        foreach ($this->_headers as $header) {

            $tmp = $policy->addChild('allow-http-request-headers-from');
            $tmp->addAttribute('domain', $access[0]);
            $tmp->addAttribute('headers', $access[1]);
            $tmp->addAttribute('secure', ($access[2] == true) ? 'true' : 'false');
        }

        return $policy;

    }

    /**
     * addAllowedAccess function.
     * 
     * @access public
     * @param mixed $domain
     * @param string $ports (default: '*')
     * @param bool $secure (default: false)
     * @return void
     */
    public function addAllowedAccess($domain, $ports = '*', $secure = false) {

        if (!$this->validateDomain($domain)) {
           throw new \UnexpectedValueException('Invalid domain');
        }
        if (!$this->validatePorts($ports)) {
           throw new \UnexpectedValueException('Invalid Port');
        }


        $this->_access[]   = array($domain, $ports, $secure);
        $this->_cacheValid = false;
    }

    /**
     * addAllowedHTTPRequestHeaders function.
     * 
     * @access public
     * @param mixed $domain
     * @param mixed $headers
     * @param bool $secure (default: true)
     * @return void
     */
    public function addAllowedHTTPRequestHeaders($domain, $headers, $secure = true) {

        if (!$this->validateDomain($domain)) {
           throw new \UnexpectedValueException('Invalid domain');
        }
        if (!$this->validateHeaders($headers)) {
           throw new \UnexpectedValueException('Invalid Header');
        }
        $this->_headers[]   = array($domain, $headers, (string)$secure);
        $this->_cacheValid = false;
    }

    /**
     * validateSiteControl function.
     * 
     * @access public
     * @param mixed $permittedCrossDomainPolicies
     * @return void
     */
    public function validateSiteControl($permittedCrossDomainPolicies) {

        return (bool)in_array($permittedCrossDomainPolicies, array('none', 'master-only', 'by-content-type', 'all'));
    }

    /**
     * validateDomain function.
     * 
     * @access public
     * @param mixed $domain
     * @return void
     */
    public function validateDomain($domain) {

        if ($domain == '*') {
            return true;
        }

        if (filter_var($domain, FILTER_VALIDATE_IP)) {
            return true;
        }

        $d = parse_url($domain);
        if (!isset($d['scheme']) || empty($d['scheme'])) {
            $domain = 'http://' . $domain;
        }

        if (substr($domain, -1) == '*') {
            return false;
        }

        $d = parse_url($domain);

        $parts = explode('.', $d['host']);
        $tld   = array_pop($parts);

        if (($pos = strpos($tld, '*')) !== false) {
            return false;
        }

        return (bool)filter_var(str_replace(array('*.', '.*'), '123', $domain), FILTER_VALIDATE_URL);
    }
    
    /**
     * validatePorts function.
     * 
     * @access public
     * @param mixed $port
     * @return void
     */
    public function validatePorts($port) {

        if ($port == '*') {
            return true;
        }

        $ports = explode(',', $port);

        foreach ($ports as $port) {
            $range = substr_count($port, '-');

            if ($range > 1) {
                return false;
            } else if ($range == 1) {
                $ranges = explode('-', $port);

                if (!is_numeric($ranges[0]) || !is_numeric($ranges[1]) || $ranges[0] > $ranges[1]) {
                    return false;
                } else {
                    return true;
                }
            }

            if (!is_numeric($port) || $port == '') {
                return false;
            }
        }

        return true;
    }

    /**
     * validateHeaders function.
     * 
     * @access public
     * @param mixed $headers
     * @return void
     */
    public function validateHeaders($headers) {

        if ($headers == '*') {
            return true;
        }
        $headers = explode(',', $headers);

        foreach ($headers as $header) {

            if ((bool)preg_match('/.*\*+.+/is', $header)) {
                return false;
            }

            if(!ctype_alnum(str_replace(array('-', '_', '*' ), '', $header))) {
                return false;
            }
        }

        return true;
    }

    /**
     * validateSecure function.
     * 
     * @access public
     * @param mixed $secure
     * @return void
     */
    public function validateSecure($secure) {

        return is_bool($secure);
    }
}