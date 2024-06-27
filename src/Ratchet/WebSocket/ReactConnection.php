<?php
	namespace Ratchet\WebSocket;
	use React\Socket\Connection;
	use React\EventLoop\LoopInterface;

	class ReactConnection extends Connection {
	    public $decor;
		public $stream;


		public function __construct($stream, $loop) {
			parent::__construct($stream, $loop);
		}
	}