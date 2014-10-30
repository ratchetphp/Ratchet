<?php


namespace Ratchet\JavaScript;

class Connect{
	public function get(&$request){
		$ret = '';
		$ret.='
// MIT License:
//
// Copyright (c) 2010-2012, Joe Walnes
//
// Permission is hereby granted, free of charge, to any person obtaining a copy
// of this software and associated documentation files (the "Software"), to deal
// in the Software without restriction, including without limitation the rights
// to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
// copies of the Software, and to permit persons to whom the Software is
// furnished to do so, subject to the following conditions:
//
// The above copyright notice and this permission notice shall be included in
// all copies or substantial portions of the Software.
//
// THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
// IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
// FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
// AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
// LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
// OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
// THE SOFTWARE.

/**
 * This behaves like a WebSocket in every way, except if it fails to connect,
 * or it gets disconnected, it will repeatedly poll until it succesfully connects
 * again.
 *
 * It is API compatible, so when you have:
 *   ws = new WebSocket(\'ws://....\');
 * you can replace with:
 *   ws = new ReconnectingWebSocket(\'ws://....\');
 *
 * The event stream will typically look like:
 *  onconnecting
 *  onopen
 *  onmessage
 *  onmessage
 *  onclose // lost connection
 *  onconnecting
 *  onopen  // sometime later...
 *  onmessage
 *  onmessage
 *  etc... 
 *
 * It is API compatible with the standard WebSocket API.
 *
 * Latest version: https://github.com/joewalnes/reconnecting-websocket/
 * - Joe Walnes
 */
(function (global, factory) {
    if (typeof define === \'function\' && define.amd) {
        define([], factory);
    } else if (typeof module !== \'undefined\' && module.exports){
        module.exports = factory();
    } else {
        global.ReconnectingWebSocket = factory();
    }
})(this, function () {

    function ReconnectingWebSocket(url, protocols) {
        protocols = protocols || [];

        // These can be altered by calling code.
        this.debug = false;
        this.reconnectInterval = 1000;
        this.reconnectDecay = 1.5;
        this.reconnectAttempts = 0;
        this.timeoutInterval = 2000;

        var self = this;
        var ws;
        var forcedClose = false;
        var timedOut = false;
        
        this.url = url;
        this.protocols = protocols;
        this.readyState = WebSocket.CONNECTING;
        this.URL = url; // Public API

        this.onopen = function(event) {
        };

        this.onclose = function(event) {
        };

        this.onconnecting = function(event) {
        };

        this.onmessage = function(event) {
        };

        this.onerror = function(event) {
        };

        function connect(reconnectAttempt) {
            ws = new WebSocket(url, protocols);
            
            if(!reconnectAttempt)
                self.onconnecting();
                
            if (self.debug || ReconnectingWebSocket.debugAll) {
                console.debug(\'ReconnectingWebSocket\', \'attempt-connect\', url);
            }
            
            var localWs = ws;
            var timeout = setTimeout(function() {
                if (self.debug || ReconnectingWebSocket.debugAll) {
                    console.debug(\'ReconnectingWebSocket\', \'connection-timeout\', url);
                }
                timedOut = true;
                localWs.close();
                timedOut = false;
            }, self.timeoutInterval);
            
            ws.onopen = function(event) {
                clearTimeout(timeout);
                if (self.debug || ReconnectingWebSocket.debugAll) {
                    console.debug(\'ReconnectingWebSocket\', \'onopen\', url);
                }
                self.readyState = WebSocket.OPEN;
                reconnectAttempt = false;
                self.reconnectAttempts = 0;
                self.onopen(event);
            };
            
            ws.onclose = function(event) {
                clearTimeout(timeout);
                ws = null;
                if (forcedClose) {
                    self.readyState = WebSocket.CLOSED;
                    self.onclose(event);
                } else {
                    self.readyState = WebSocket.CONNECTING;
                    self.onconnecting();
                    if (!reconnectAttempt && !timedOut) {
                        if (self.debug || ReconnectingWebSocket.debugAll) {
                            console.debug(\'ReconnectingWebSocket\', \'onclose\', url);
                        }
                        self.onclose(event);
                    }
                    setTimeout(function() {
                        self.reconnectAttempts++;
                        connect(true);
                    }, self.reconnectInterval * Math.pow(self.reconnectDecay, self.reconnectAttempts));
                }
            };
            ws.onmessage = function(event) {
                if (self.debug || ReconnectingWebSocket.debugAll) {
                    console.debug(\'ReconnectingWebSocket\', \'onmessage\', url, event.data);
                }
                self.onmessage(event);
            };
            ws.onerror = function(event) {
                if (self.debug || ReconnectingWebSocket.debugAll) {
                    console.debug(\'ReconnectingWebSocket\', \'onerror\', url, event);
                }
                self.onerror(event);
            };
        }
        connect(false);

        this.send = function(data) {
            if (ws) {
                if (self.debug || ReconnectingWebSocket.debugAll) {
                    console.debug(\'ReconnectingWebSocket\', \'send\', url, data);
                }
                return ws.send(data);
            } else {
                throw \'INVALID_STATE_ERR : Pausing to reconnect websocket\';
            }
        };

        this.close = function() {
            forcedClose = true;
            if (ws) {
                ws.close();
            }
        };

        /**
         * Additional public API method to refresh the connection if still open (close, re-open).
         * For example, if the app suspects bad data / missed heart beats, it can try to refresh.
         */
        this.refresh = function() {
            if (ws) {
                ws.close();
            }
        };
    }

    /**
     * Setting this to true is the equivalent of setting all instances of ReconnectingWebSocket.debug to true.
     */
    ReconnectingWebSocket.debugAll = false;

    return ReconnectingWebSocket;
}); 

var conn = new ReconnectingWebSocket("ws://'.$request->getHost().':'.$request->getPort().'");


';	
		return $ret;
	}
}
