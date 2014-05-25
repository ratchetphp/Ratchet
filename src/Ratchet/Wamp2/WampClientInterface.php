<?php

namespace Ratchet\Wamp2;

interface WampClientInterface
{
    function onChallenge($challenge, $extra);

    function onWelcome($session, $details);

    function onAbort($details, $reason);

    function onGoodbye($details, $reason);

    function onHeartbeat($incomingSeq, $outgoingSeq, $discard = null);

    function onError($requestType, $requestId, $details, $error, array $arguments = null, $argumentsKeywords = null);

    function onRegistered($requestId, $registrationId);

    function onUnregistered($requestId);

    function onInvocation($requestId, $registrationId, $details, array $arguments = null, $argumentsKeywords = null);

    function onInterrupt($requestId, $options);

    function onResult($requestId, $details, array $arguments = null, $argumentsKeywords = null);

    function onPublished($requestId, $publicationId);

    function onSubscribed($requestId, $subscriptionId);

    function onUnsubscribed($requestId, $subscriptionId);

    function onEvent($subscriptionId, $publicationId, $details, array $arguments = null, $argumentsKeywords = null);
}