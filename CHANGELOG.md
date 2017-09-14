CHANGELOG
=========

### Legend

* "BC": Backwards compatibility break (from public component APIs)
* "BF": Bug fix

---

* 0.4 (2017-09-14)

 * BC: $conn->WebSocket->request replaced with $conn->httpRequest which is a PSR-7 object
 * Binary messages now supported via Ratchet\WebSocket\MessageComponentInterface
 * Added heartbeat support via ping/pong in WsServer
 * BC: No longer support old (and insecure) Hixie76 and Hybi protocols
 * BC: No longer support disabling UTF-8 checks
 * BC: The Session component implements HttpServerInterface instead of WsServerInterface
 * BC: PHP 5.3 no longer supported
 * BC: Update to newer version of react/socket dependency
 * BC: WAMP topics reduced to 0 subscriptions are deleted, new subs to same name will result in new Topic instance
 * Significant performance enhancements

* 0.3.6 (2017-01-06)
 * BF: Keep host and scheme in HTTP request object attatched to connection
 * BF: Return correct HTTP response (405) when non-GET request made

* 0.3.5 (2016-05-25)

 * BF: Unmask responding close frame
 * Added write handler for PHP session serializer

* 0.3.4 (2015-12-23)

 * BF: Edge case where version check wasn't run on message coalesce
 * BF: Session didn't start when using pdo_sqlite
 * BF: WAMP currie prefix check when using '#'
 * Compatibility with Symfony 3

* 0.3.3 (2015-05-26)

 * BF: Framing bug on large messages upon TCP fragmentation
 * BF: Symfony Router query parameter defaults applied to Request
 * BF: WAMP CURIE on all URIs
 * OriginCheck rules applied to FlashPolicy
 * Switched from PSR-0 to PSR-4

* 0.3.2 (2014-06-08)

 * BF: No messages after closing handshake (fixed rare race condition causing 100% CPU)
 * BF: Fixed accidental BC break from v0.3.1
 * Added autoDelete parameter to Topic to destroy when empty of connections
 * Exposed React Socket on IoServer (allowing FlashPolicy shutdown in App)
 * Normalized Exceptions in WAMP

* 0.3.1 (2014-05-26)

 * Added query parameter support to Router, set in HTTP request (ws://server?hello=world)
 * HHVM compatibility
 * BF: React/0.4 support; CPU starvation bug fixes
 * BF: Allow App::route to ignore Host header
 * Added expected filters to WAMP Topic broadcast method
 * Resource cleanup in WAMP TopicManager

* 0.3.0 (2013-10-14)

 * Added the `App` class to help making Ratchet so easy to use it's silly
 * BC: Require hostname to do HTTP Host header match and do Origin HTTP header check, verify same name by default, helping prevent CSRF attacks
 * Added Symfony/2.2 based HTTP Router component to allowing for a single Ratchet server to handle multiple apps -> Ratchet\Http\Router
 * BC: Decoupled HTTP from WebSocket component -> Ratchet\Http\HttpServer
 * BF: Single sub-protocol selection to conform with RFC6455
 * BF: Sanity checks on WAMP protocol to prevent errors

* 0.2.8 (2013-09-19)

 * React 0.3 support

* 0.2.7 (2013-06-09)

 * BF: Sub-protocol negotation with Guzzle 3.6

* 0.2.6 (2013-06-01)

 * Guzzle 3.6 support

* 0.2.5 (2013-04-01)

 * Fixed Hixie-76 handshake bug

* 0.2.4 (2013-03-09)

 * Support for Symfony 2.2 and Guzzle 2.3
 * Minor bug fixes when handling errors

* 0.2.3 (2012-11-21)

 * Bumped dep: Guzzle to v3, React to v0.2.4
 * More tests

* 0.2.2 (2012-10-20)

 * Bumped deps to use React v0.2

* 0.2.1 (2012-10-13)

 * BF: No more UTF-8 warnings in browsers (no longer sending empty sub-protocol string)
 * Documentation corrections
 * Using new composer structure

* 0.2 (2012-09-07)

 * Ratchet passes every non-binary-frame test from the Autobahn Testsuite
 * Major performance improvements
 * BC: Renamed "WampServer" to "ServerProtocol"
 * BC: New "WampServer" component passes Topic container objects of subscribed Connections
 * Option to turn off UTF-8 checks in order to increase performance
 * Switched dependency guzzle/guzzle to guzzle/http (no API changes)
 * mbstring no longer required

* 0.1.5 (2012-07-12)

 * BF: Error where service wouldn't run on PHP <= 5.3.8
 * Dependency library updates

* 0.1.4 (2012-06-17)

 * Fixed dozens of failing AB tests
 * BF: Proper socket buffer handling

* 0.1.3 (2012-06-15)

 * Major refactor inside WebSocket protocol handling, more loosley coupled
 * BF: Proper error handling on failed WebSocket connections
 * BF: Handle TCP message concatenation
 * Inclusion of the AutobahnTestSuite checking WebSocket protocol compliance
 * mb_string now a requirement

* 0.1.2 (2012-05-19)

 * BC/BF: Updated WAMP API to coincide with the official spec
 * Tweaks to improve running as a long lived process

* 0.1.1 (2012-05-14)

 * Separated interfaces allowing WebSockets to support multiple sub protocols
 * BF: remoteAddress variable on connections returns proper value

* 0.1 (2012-05-11)

 * First release with components: IoServer, WsServer, SessionProvider, WampServer, FlashPolicy, IpBlackList
 * I/O now handled by React, making Ratchet fully asynchronous
