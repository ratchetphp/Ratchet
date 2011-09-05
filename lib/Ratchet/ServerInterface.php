<?php
namespace Ratchet;

interface ServerInterface {
    function attatchApplication(ApplicationInterface $app);

    function run($address = '127.0.0.1', $port = 1025);
}