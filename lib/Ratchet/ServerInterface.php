<?php
namespace Ratchet;

interface ServerInterface {
    function attatchApplication(ApplicationInterface $app);

    function run();
}