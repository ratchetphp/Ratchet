<?php

namespace Ratchet\Wamp2;

class WampProtocol {
    public function challengeMessage($challenge, $extra) {
        $result = array(WampMessageType::MSG_CHALLENGE, $challenge, $extra);
        return $result;
    }

    public function welcomeMessage($session, $details) {
        $result = array(WampMessageType::MSG_WELCOME, $session, $details);
        return $result;
    }

    public function abortMessage($details, $reason) {
        $result = array(WampMessageType::MSG_ABORT, $details, $reason);
        return $result;
    }

    public function goodbyeMessage($details, $reason) {
        $result = array(WampMessageType::MSG_GOODBYE, $details, $reason);
        return $result;
    }

    public function heartbeatMessage($incomingSeq, $outgoingSeq, $discard = null) {
        $result = array(WampMessageType::MSG_HEARTBEAT, $incomingSeq, $outgoingSeq);

        if (null !== $discard) {
            $result[] = $discard;
        }

        return $result;
    }

    public function errorMessage($requestType, $requestId, $details, $error, array $arguments = null, $argumentsKeywords = null) {
        $result = array(WampMessageType::MSG_ERROR, $requestType, $requestId, $details, $error);

        if (null !== $arguments) {
            $result[] = $arguments;
            if (null !== $argumentsKeywords) {
                $result[] = $argumentsKeywords;
            }
        }

        return $result;
    }

    public function registeredMessage($requestId, $registrationId) {
        $result = array(WampMessageType::MSG_REGISTERED, $requestId, $registrationId);
        return $result;
    }

    public function unregisteredMessage($requestId) {
        $result = array(WampMessageType::MSG_UNREGISTERED, $requestId);
        return $result;
    }

    public function invocationMessage($requestId, $registrationId, $details, array $arguments = null, $argumentsKeywords = null) {
        $result = array(WampMessageType::MSG_INVOCATION, $requestId, $registrationId, $details);

        if (null !== $arguments) {
            $result[] = $arguments;
            if (null !== $argumentsKeywords) {
                $result[] = $argumentsKeywords;
            }
        }

        return $result;
    }

    public function interruptMessage($requestId, $options) {
        $result = array(WampMessageType::MSG_INTERRUPT, $requestId, $options);
        return $result;
    }

    public function resultMessage($requestId, $details, array $arguments = null, $argumentsKeywords = null) {
        $result = array(WampMessageType::MSG_RESULT, $requestId, $details);

        if (null !== $arguments) {
            $result[] = $arguments;
            if (null !== $argumentsKeywords) {
                $result[] = $argumentsKeywords;
            }
        }

        return $result;
    }

    public function publishedMessage($requestId, $publicationId) {
        $result = array(WampMessageType::MSG_PUBLISHED, $requestId, $publicationId);
        return $result;
    }

    public function subscribedMessage($requestId, $subscriptionId) {
        $result = array(WampMessageType::MSG_SUBSCRIBED, $requestId, $subscriptionId);
        return $result;
    }

    public function unsubscribedMessage($requestId, $subscriptionId) {
        $result = array(WampMessageType::MSG_UNSUBSCRIBED, $requestId, $subscriptionId);
        return $result;
    }

    public function eventMessage($subscriptionId, $publicationId, $details, array $arguments = null, $argumentsKeywords = null) {
        $result = array(WampMessageType::MSG_EVENT, $subscriptionId, $publicationId, $details);

        if (null !== $arguments) {
            $result[] = $arguments;
            if (null !== $argumentsKeywords) {
                $result[] = $argumentsKeywords;
            }
        }

        return $result;
    }

    public function helloMessage($realm, $details) {
        $result = array(WampMessageType::MSG_HELLO, $realm, $details);
        return $result;
    }

    public function authenticateMessage($signature, $extra) {
        $result = array(WampMessageType::MSG_AUTHENTICATE, $signature, $extra);
        return $result;
    }

    public function registerMessage($requestId, $options, $procedure) {
        $result = array(WampMessageType::MSG_REGISTER, $requestId, $options, $procedure);
        return $result;
    }

    public function unregisterMessage($requestId, $registrationId) {
        $result = array(WampMessageType::MSG_UNREGISTER, $requestId, $registrationId);
        return $result;
    }

    public function callMessage($requestId, $options, $procedure, array $arguments = null, $argumentsKeywords = null) {
        $result = array(WampMessageType::MSG_CALL, $requestId, $options, $procedure);

        if (null !== $arguments) {
            $result[] = $arguments;
            if (null !== $argumentsKeywords) {
                $result[] = $argumentsKeywords;
            }
        }

        return $result;
    }

    public function cancelMessage($requestId, $options) {
        $result = array(WampMessageType::MSG_CANCEL, $requestId, $options);
        return $result;
    }

    public function yieldMessage($requestId, $options, array $arguments = null, $argumentsKeywords = null) {
        $result = array(WampMessageType::MSG_YIELD, $requestId, $options);

        if (null !== $arguments) {
            $result[] = $arguments;
            if (null !== $argumentsKeywords) {
                $result[] = $argumentsKeywords;
            }
        }

        return $result;
    }

    public function publishMessage($requestId, $options, $topicUri, array $arguments = null, $argumentKeywords = null) {
        $result = array(WampMessageType::MSG_PUBLISH, $requestId, $options, $topicUri);

        if (null !== $arguments) {
            $result[] = $arguments;
            if (null !== $argumentKeywords) {
                $result[] = $argumentKeywords;
            }
        }

        return $result;
    }

    public function subscribeMessage($requestId, $options, $topicUri) {
        $result = array(WampMessageType::MSG_SUBSCRIBE, $requestId, $options, $topicUri);
        return $result;
    }

    public function unsubscribeMessage($requestId, $subscriptionId) {
        $result = array(WampMessageType::MSG_UNSUBSCRIBE, $requestId, $subscriptionId);
        return $result;
    }
}