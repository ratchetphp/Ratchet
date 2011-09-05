<?php
namespace Ratchet;

interface ReceiverInterface {
    /**
     * @return string
     */
    function getName();

    function handleConnect();

    function handleMessage();

    function handleClose();
}