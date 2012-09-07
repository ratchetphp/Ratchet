CHANGELOG
=========

###Legend

* "BC": Backwards compatibility break (from public component APIs)
* "BF": Bug fix

---

* 0.2 (2012-09-07)

 * Ratchet passes every non-binary-frame test from the Autobahn Testsuite
 * Major performance improvements
 * BC: Renamed "WampServer" to "ServerProtocol"
 * BC: New "WampServer" component passes Topic container objects of subscribed Connections
 * Option to turn off UTF-8 checks in order to increase performance
 * Switched dependancy guzzle/guzzle to guzzle/http (no API changes)
 * mbstring no longer required

* 0.1.5 (2012-07-12)

 * BF: Error where service wouldn't run on PHP <= 5.3.8
 * Dependancy library updates

* 0.1.4 (2012-06-17)

 * Fixed dozens of failing AB tests
 * BF: Proper socket buffer handling

* 0.1.3 (2012-06-15)

 * Major refactor inside WebSocket protocol handling, more loosley coupled
 * BF: Proper error handling on failed WebSocket connections
 * BF: Handle TCP message concatination
 * Inclusing of the AutobahnTestSuite checking WebSocket protocol compliance
 * mb_string now a requirement

* 0.1.2 (2012-05-19)

 * BC/BF: Updated WAMP API to coincide with the official spec
 * Tweaks to improve running as a long lived process

* 0.1.1 (2012-05-14)

 * Separated interfaces allowing WebSockets to support multiple sub protocols
 * BF: remoteAddress variable on connections returns proper value

* 0.1 (2012-05-11)

 * First release with components: IoServer, WsServer, SessionProvider, WampServer, FlashPolicy, IpBlackList
 * I/O now handled by React, making Ratchet fully asyncronous 