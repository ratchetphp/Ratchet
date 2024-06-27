<?php
	namespace Ratchet\WebSocket;


	use React\Socket\Connection as BaseConnection;

	class Connection extends BaseConnection {
		public $decor;

		// Override constructor if necessary
		public function __construct($stream, $loop, array $context = []) {
			parent::__construct($stream, $loop, $context);
			$this->decor = null; // Initialize as necessary
		}


	}