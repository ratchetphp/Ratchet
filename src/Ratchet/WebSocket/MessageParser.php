<?php
namespace Ratchet\WebSocket;

class MessageParser {
    public function onData(WsConnection $from, $data) {
        if (!isset($from->WebSocket->message)) {
            $from->WebSocket->message = $from->WebSocket->version->newMessage();
        }

        // There is a frame fragment attatched to the connection, add to it
        if (!isset($from->WebSocket->frame)) {
            $from->WebSocket->frame = $from->WebSocket->version->newFrame();
        }

        $from->WebSocket->frame->addBuffer($data);
        if ($from->WebSocket->frame->isCoalesced()) {
            if ($from->WebSocket->frame->getOpcode() > 2) {
                $from->close();
                throw new \UnexpectedValueException('Control frame support coming soon!');
            }
            // Check frame
            // If is control frame, do your thing
            // Else, add to message
            // Control frames (ping, pong, close) can be sent in between a fragmented message

            $from->WebSocket->message->addFrame($from->WebSocket->frame);
            unset($from->WebSocket->frame);
        }

        if ($from->WebSocket->message->isCoalesced()) {
            $parsed = (string)$from->WebSocket->message;
            unset($from->WebSocket->message);

            return $parsed;
        }
    }
}