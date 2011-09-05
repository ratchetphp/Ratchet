<?php
namespace Ratchet;

interface ApplicationInterface {
    /**
     * @return string
     */
    function getName();

    function onConnect();

    function onMessage();

    function onClose();
}