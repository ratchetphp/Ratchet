<?php
namespace Ratchet;

interface SocketObserver {
    function onOpen(SocketInterface $conn);

    function onRecv(SocketInterface $from, $msg);

    function onClose(SocketInterface $conn);
}