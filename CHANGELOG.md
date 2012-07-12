CHANGELOG
=========

###Legend

* "BC": Backwards compatibility break (from public component APIs)
* "BF": Bug fix

---

* 0.2 (2012-TBD)

 * Ratchet passes every non-binary message AB test
 * Ratchet now relies on all stable dependancies

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