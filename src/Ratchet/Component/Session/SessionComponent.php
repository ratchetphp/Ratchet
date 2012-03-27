<?php
namespace Ratchet\Component\Session;
use Ratchet\Component\MessageComponentInterface;
use Ratchet\Resource\ConnectionInterface;
use Ratchet\Component\Session\Storage\VirtualSessionStorage;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\NullSessionHandler;

class SessionComponent implements MessageComponentInterface {
    protected $_app;

    protected $_options;
    protected $_handler;
    protected $_null;

    public function __construct(MessageComponentInterface $app, \SessionHandlerInterface $handler, array $options = array()) {
        $this->_app     = $app;
        $this->_handler = $handler;
        $this->_options = array();
        $this->_null    = new NullSessionHandler;

        ini_set('session.auto_start', 0);
        ini_set('session.cache_limiter', '');
        ini_set('session.use_cookies', 0);

        $options = $this->setOptions($options);
    }

    /**
     * {@inheritdoc}
     */
    function onOpen(ConnectionInterface $conn) {
        if (null === ($id = $conn->WebSocket->headers->getCookie($this->_options['name']))) {
            $saveHandler = new NullSessionHandler;
            $id = '';
        } else {
            $saveHandler = $this->_handler;
        }

        $conn->Session = new Session(new VirtualSessionStorage($saveHandler, $id));

        return $this->_app->onOpen($conn);
    }

    /**
     * {@inheritdoc}
     */
    function onMessage(ConnectionInterface $from, $msg) {
        return $this->_app->onMessage($from, $msg);
    }

    /**
     * {@inheritdoc}
     */
    function onClose(ConnectionInterface $conn) {
        // "close" session for Connection

        return $this->_app->onClose($conn);
    }

    /**
     * {@inheritdoc}
     */
    function onError(ConnectionInterface $conn, \Exception $e) {
        return $this->_app->onError($conn, $e);
    }

    protected function setOptions(array $options) {
        $all = array(
            'auto_start', 'cache_limiter', 'cookie_domain', 'cookie_httponly',
            'cookie_lifetime', 'cookie_path', 'cookie_secure',
            'entropy_file', 'entropy_length', 'gc_divisor',
            'gc_maxlifetime', 'gc_probability', 'hash_bits_per_character',
            'hash_function', 'name', 'referer_check',
            'serialize_handler', 'use_cookies',
            'use_only_cookies', 'use_trans_sid', 'upload_progress.enabled',
            'upload_progress.cleanup', 'upload_progress.prefix', 'upload_progress.name',
            'upload_progress.freq', 'upload_progress.min-freq', 'url_rewriter.tags'
        );

        foreach ($all as $key) {
            if (!array_key_exists($key, $options)) {
                $options[$key] = ini_get("session.{$key}");
            } else {
                ini_set("session.{$key}", $options[$key]);
            }
        }

        return $options;
    }
}