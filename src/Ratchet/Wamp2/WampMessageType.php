<?php

namespace Ratchet\Wamp2;


class WampMessageType {

    const MSG_HELLO = 1;
    const MSG_WELCOME = 2;
    const MSG_ABORT = 3;
    const MSG_CHALLENGE = 4;
    const MSG_AUTHENTICATE = 5;
    const MSG_GOODBYE = 6;
    const MSG_HEARTBEAT = 7;
    const MSG_ERROR = 8;
    const MSG_PUBLISH = 16;
    const MSG_PUBLISHED = 17;
    const MSG_SUBSCRIBE = 32;
    const MSG_SUBSCRIBED = 33;
    const MSG_UNSUBSCRIBE = 34;
    const MSG_UNSUBSCRIBED = 35;
    const MSG_EVENT = 36;
    const MSG_CALL = 48;
    const MSG_CANCEL = 49;
    const MSG_RESULT = 50;
    const MSG_REGISTER = 64;
    const MSG_REGISTERED = 65;
    const MSG_UNREGISTER = 66;
    const MSG_UNREGISTERED = 67;
    const MSG_INVOCATION = 68;
    const MSG_INTERRUPT = 69;
    const MSG_YIELD = 70;
}