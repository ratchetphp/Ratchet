# This file is intended to ease the author's development and testing process
# Users do not need to use `make`; Ratchet does not need to be compiled

test:
	phpunit

cover:
	phpunit --coverage-text --coverage-html=reports/coverage

abtests:
	ulimit -n 2048 && php tests/autobahn/bin/fuzzingserver.php 8001 LibEvent &
	ulimit -n 2048 && php tests/autobahn/bin/fuzzingserver.php 8002 StreamSelect &
	ulimit -n 2048 && php tests/autobahn/bin/fuzzingserver.php 8004 LibEv &
	wstest -m testeeserver -w ws://localhost:8000 &
	sleep 1
	wstest -m fuzzingclient -s tests/autobahn/fuzzingclient-all.json
	killall php wstest

abtest:
	ulimit -n 2048 && php tests/autobahn/bin/fuzzingserver.php 8000 StreamSelect &
	sleep 1
	wstest -m fuzzingclient -s tests/autobahn/fuzzingclient-quick.json
	killall php

profile:
	php -d 'xdebug.profiler_enable=1' tests/autobahn/bin/fuzzingserver.php 8000 LibEvent &
	sleep 1
	wstest -m fuzzingclient -s tests/autobahn/fuzzingclient-profile.json
	killall php

apidocs:
	apigen --title Ratchet -d reports/api \
		-s src/ \
		-s vendor/ratchet/rfc6455/src \
		-s vendor/react/event-loop/src \
		-s vendor/react/socket/src \
		-s vendor/react/stream/src \
		-s vendor/psr/http-message/src \
		-s vendor/symfony/http-foundation/Session \
		-s vendor/symfony/routing \
		-s vendor/evenement/evenement/src/Evenement \
		--exclude=vendor/symfony/routing/Tests \
