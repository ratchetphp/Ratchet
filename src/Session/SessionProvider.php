<?php

namespace Ratchet\Session;

use Psr\Http\Message\RequestInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Http\HttpServerInterface;
use Ratchet\Session\Serialize\HandlerInterface;
use Ratchet\Session\Storage\VirtualSessionStorage;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\NullSessionHandler;

/**
 * This component will allow access to session data from your website for each user connected
 * Symfony HttpFoundation is required for this component to work
 * Your website must also use Symfony HttpFoundation Sessions to read your sites session data
 * If your are not using at least PHP 5.4 you must include a SessionHandlerInterface stub (is included in Symfony HttpFoundation, loaded w/ composer)
 */
class SessionProvider implements HttpServerInterface
{
    /**
     * @var \Ratchet\MessageComponentInterface
     */
    protected $app;

    /**
     * Selected handler storage assigned by the developer
     *
     * @var \SessionHandlerInterface
     */
    protected $handler;

    /**
     * Null storage handler if no previous session was found
     *
     * @var \SessionHandlerInterface
     */
    protected $null;

    /**
     * @var \Ratchet\Session\Serialize\HandlerInterface
     */
    protected $serializer;

    /**
     * @throws \RuntimeException
     */
    public function __construct(HttpServerInterface $app, \SessionHandlerInterface $handler, array $options = [], ?HandlerInterface $serializer = null)
    {
        $this->app = $app;
        $this->handler = $handler;
        $this->null = new NullSessionHandler;

        ini_set('session.auto_start', 0);
        ini_set('session.cache_limiter', '');
        ini_set('session.use_cookies', 0);

        $this->setOptions($options);

        if ($serializer === null) {
            $serialClass = __NAMESPACE__."\\Serialize\\{$this->toClassCase(ini_get('session.serialize_handler'))}Handler"; // awesome/terrible hack, eh?
            if (! class_exists($serialClass)) {
                throw new \RuntimeException('Unable to parse session serialize handler');
            }

            $serializer = new $serialClass;
        }

        $this->serializer = $serializer;
    }

    /**
     * {@inheritdoc}
     */
    public function onOpen(ConnectionInterface $connection, ?RequestInterface $request = null)
    {
        $sessionName = ini_get('session.name');

        $id = array_reduce($request->getHeader('Cookie'), function ($accumulator, $cookie) use ($sessionName) {
            if ($accumulator) {
                return $accumulator;
            }

            $crumbs = $this->parseCookie($cookie);

            return isset($crumbs['cookies'][$sessionName]) ? $crumbs['cookies'][$sessionName] : false;
        }, false);

        if ($request === null || $id === false) {
            $saveHandler = $this->null;
            $id = '';
        } else {
            $saveHandler = $this->handler;
        }

        $connection->Session = new Session(new VirtualSessionStorage($saveHandler, $id, $this->serializer));

        if (ini_get('session.auto_start')) {
            $connection->Session->start();
        }

        return $this->app->onOpen($connection, $request);
    }

    /**
     * {@inheritdoc}
     */
    public function onMessage(ConnectionInterface $connection, string $message)
    {
        return $this->app->onMessage($connection, $message);
    }

    /**
     * {@inheritdoc}
     */
    public function onClose(ConnectionInterface $connection)
    {
        // "close" session for Connection

        return $this->app->onClose($connection);
    }

    /**
     * {@inheritdoc}
     */
    public function onError(ConnectionInterface $connection, \Exception $exception)
    {
        return $this->app->onError($connection, $exception);
    }

    /**
     * Set all the php session. ini options
     * © Symfony
     *
     * @return array
     */
    protected function setOptions(array $options)
    {
        $all = [
            'auto_start', 'cache_limiter', 'cookie_domain', 'cookie_httponly',
            'cookie_lifetime', 'cookie_path', 'cookie_secure',
            'entropy_file', 'entropy_length', 'gc_divisor',
            'gc_maxlifetime', 'gc_probability', 'hash_bits_per_character',
            'hash_function', 'name', 'referer_check',
            'serialize_handler', 'use_cookies',
            'use_only_cookies', 'use_trans_sid', 'upload_progress.enabled',
            'upload_progress.cleanup', 'upload_progress.prefix', 'upload_progress.name',
            'upload_progress.freq', 'upload_progress.min-freq', 'url_rewriter.tags',
        ];

        foreach ($all as $key) {
            if (! array_key_exists($key, $options)) {
                $options[$key] = ini_get("session.{$key}");
            } else {
                ini_set("session.{$key}", $options[$key]);
            }
        }

        return $options;
    }

    /**
     * @param  string  $langDef Input to convert
     * @return string
     */
    protected function toClassCase($langDef)
    {
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $langDef)));
    }

    /**
     * Taken from Guzzle3
     */
    private static $cookieParts = [
        'domain' => 'Domain',
        'path' => 'Path',
        'max_age' => 'Max-Age',
        'expires' => 'Expires',
        'version' => 'Version',
        'secure' => 'Secure',
        'port' => 'Port',
        'discard' => 'Discard',
        'comment' => 'Comment',
        'comment_url' => 'Comment-Url',
        'http_only' => 'HttpOnly',
    ];

    /**
     * Taken from Guzzle3
     */
    private function parseCookie($cookie, $host = null, $path = null, $decode = false)
    {
        // Explode the cookie string using a series of semicolons
        $pieces = array_filter(array_map('trim', explode(';', $cookie)));

        // The name of the cookie (first kvp) must include an equal sign.
        if (empty($pieces) || ! strpos($pieces[0], '=')) {
            return false;
        }

        // Create the default return array
        $data = array_merge(array_fill_keys(array_keys(self::$cookieParts), null), [
            'cookies' => [],
            'data' => [],
            'path' => $path ?: '/',
            'http_only' => false,
            'discard' => false,
            'domain' => $host,
        ]);
        $foundNonCookies = 0;

        // Add the cookie pieces into the parsed data array
        foreach ($pieces as $part) {

            $cookieParts = explode('=', $part, 2);
            $key = trim($cookieParts[0]);

            if (count($cookieParts) == 1) {
                // Can be a single value (e.g. secure, httpOnly)
                $value = true;
            } else {
                // Be sure to strip wrapping quotes
                $value = trim($cookieParts[1], " \n\r\t\0\x0B\"");
                if ($decode) {
                    $value = urldecode($value);
                }
            }

            // Only check for non-cookies when cookies have been found
            if (! empty($data['cookies'])) {
                foreach (self::$cookieParts as $mapValue => $search) {
                    if (! strcasecmp($search, $key)) {
                        $data[$mapValue] = $mapValue == 'port' ? array_map('trim', explode(',', $value)) : $value;
                        $foundNonCookies++;

                        continue 2;
                    }
                }
            }

            // If cookies have not yet been retrieved, or this value was not found in the pieces array, treat it as a
            // cookie. IF non-cookies have been parsed, then this isn't a cookie, it's cookie data. Cookies then data.
            $data[$foundNonCookies ? 'data' : 'cookies'][$key] = $value;
        }

        // Calculate the expires date
        if (! $data['expires'] && $data['max_age']) {
            $data['expires'] = time() + (int) $data['max_age'];
        }

        return $data;
    }
}
