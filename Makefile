# This file is intended to ease the author's development and testing process
# Users do not need to use `make`; Ratchet does not need to be compiled

test:
	phpunit

cover:
	phpunit --coverage-text --coverage-html=reports/coverage

abtests:
	ulimit -n 2048 && php tests/autobahn/bin/fuzzingserver.php 8001 LibEvent &
	ulimit -n 2048 && php tests/autobahn/bin/fuzzingserver.php 8002 StreamSelect &
	ulimit -n 2048 && php tests/autobahn/bin/fuzzingserver-noutf8.php 8003 StreamSelect &
	ulimit -n 2048 && php tests/autobahn/bin/fuzzingserver.php 8004 LibEv &
	wstest -m testeeserver -w ws://localhost:8000 &
	wstest -m fuzzingclient -s tests/autobahn/fuzzingclient-all.json
	killall php wstest

abtest:
	ulimit -n 2048 && php tests/autobahn/bin/fuzzingserver.php 8000 StreamSelect &
	wstest -m fuzzingclient -s tests/autobahn/fuzzingclient-quick.json
	killall php

profile:
	php -d 'xdebug.profiler_enable=1' tests/autobahn/bin/fuzzingserver.php 8000 LibEvent &
	wstest -m fuzzingclient -s tests/autobahn/fuzzingclient-profile.json
	killall php

apidocs:
	apigen --title Ratchet -d reports/api -s src/ \
		-s vendor/react \
		-s vendor/guzzle \
		-s vendor/symfony/http-foundation/Symfony/Component/HttpFoundation/Session \
		-s vendor/symfony/routing/Symfony/Component/Routing \
		-s vendor/evenement/evenement/src/Evenement
