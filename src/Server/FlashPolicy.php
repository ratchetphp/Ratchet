<?php

namespace Ratchet\Server;

use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;

/**
 * An app to go on a server stack to pass a policy file to a Flash socket
 * Useful if you're using Flash as a WebSocket polyfill on IE
 * Be sure to run your server instance on port 843
 * By default this lets accepts everything, make sure you tighten the rules up for production
 *
 * @link http://www.adobe.com/devnet/articles/crossdomain_policy_file_spec.html
 * @link http://learn.adobe.com/wiki/download/attachments/64389123/CrossDomain_PolicyFile_Specification.pdf?version=1
 * @link view-source:http://www.adobe.com/xml/schemas/PolicyFileSocket.xsd
 */
class FlashPolicy implements MessageComponentInterface
{
    /**
     * Contains the root policy node
     */
    protected string $policy = '<?xml version="1.0"?><!DOCTYPE cross-domain-policy SYSTEM "http://www.adobe.com/xml/dtds/cross-domain-policy.dtd"><cross-domain-policy></cross-domain-policy>';

    /**
     * Stores an array of allowed domains and their ports
     */
    protected array $access = [];

    protected string $siteControl = '';

    protected string $cache = '';

    protected bool $cacheValid = false;

    /**
     * Add a domain to an allowed access list.
     *
     * @param  string  $domain Specifies a requesting domain to be granted access. Both named domains and IP
     * addresses are acceptable values. Subdomains are considered different domains. A wildcard (*) can
     * be used to match all domains when used alone, or multiple domains (subdomains) when used as a
     * prefix for an explicit, second-level domain name separated with a dot (.)
     * @param  string  $ports A comma-separated list of ports or range of ports that a socket connection
     * is allowed to connect to. A range of ports is specified through a dash (-) between two port numbers.
     * Ranges can be used with individual ports when separated with a comma. A single wildcard (*) can
     * be used to allow all ports.
     *
     * @throws \UnexpectedValueException
     */
    public function addAllowedAccess(string $domain, string $ports = '*', bool $secure = false): self
    {
        if (! $this->validateDomain($domain)) {
            throw new \UnexpectedValueException('Invalid domain');
        }

        if (! $this->validatePorts($ports)) {
            throw new \UnexpectedValueException('Invalid Port');
        }

        $this->access[] = [$domain, $ports, (bool) $secure];
        $this->cacheValid = false;

        return $this;
    }

    /**
     * Removes all domains from the allowed access list.
     */
    public function clearAllowedAccess(): self
    {
        $this->access = [];
        $this->cacheValid = false;

        return $this;
    }

    /**
     * site-control defines the meta-policy for the current domain. A meta-policy specifies acceptable
     * domain policy files other than the master policy file located in the target domain's root and named
     * cross domain.xml.
     *
     *
     * @throws \UnexpectedValueException
     */
    public function setSiteControl(string $permittedCrossDomainPolicies = 'all'): self
    {
        if (! $this->validateSiteControl($permittedCrossDomainPolicies)) {
            throw new \UnexpectedValueException('Invalid site control set');
        }

        $this->siteControl = $permittedCrossDomainPolicies;
        $this->cacheValid = false;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function onOpen(ConnectionInterface $connection)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function onMessage(ConnectionInterface $connection, string $message)
    {
        if (! $this->cacheValid) {
            $this->cache = $this->renderPolicy()->asXML();
            $this->cacheValid = true;
        }

        $connection->send($this->cache."\0");
        $connection->close();
    }

    /**
     * {@inheritdoc}
     */
    public function onClose(ConnectionInterface $connection)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function onError(ConnectionInterface $connection, \Exception $exception)
    {
        $connection->close();
    }

    /**
     * Builds the cross domain file based on the template policy
     *
     *
     * @throws \UnexpectedValueException
     */
    public function renderPolicy(): \SimpleXMLElement
    {
        $policy = new \SimpleXMLElement($this->policy);

        $siteControl = $policy->addChild('site-control');

        if ($this->siteControl == '') {
            $this->setSiteControl();
        }

        $siteControl->addAttribute('permitted-cross-domain-policies', $this->siteControl);

        if (empty($this->access)) {
            throw new \UnexpectedValueException('You must add a domain through addAllowedAccess()');
        }

        foreach ($this->access as $access) {
            $tmp = $policy->addChild('allow-access-from');
            $tmp->addAttribute('domain', $access[0]);
            $tmp->addAttribute('to-ports', $access[1]);
            $tmp->addAttribute('secure', ($access[2] === true) ? 'true' : 'false');
        }

        return $policy;
    }

    /**
     * Make sure the proper site control was passed
     */
    public function validateSiteControl(string $permittedCrossDomainPolicies): bool
    {
        //'by-content-type' and 'by-ftp-filename' are not available for sockets
        return (bool) in_array($permittedCrossDomainPolicies, ['none', 'master-only', 'all']);
    }

    /**
     * Validate for proper domains (wildcards allowed)
     */
    public function validateDomain(string $domain): bool
    {
        return (bool) preg_match("/^((http(s)?:\/\/)?([a-z0-9-_]+\.|\*\.)*([a-z0-9-_\.]+)|\*)$/i", $domain);
    }

    /**
     * Make sure valid ports were passed
     */
    public function validatePorts(string $port): bool
    {
        return (bool) preg_match('/^(\*|(\d+[,-]?)*\d+)$/', $port);
    }
}
