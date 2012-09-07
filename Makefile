# This file is intended to ease the author's development and testing process
# Users do not need to use `make`; Ratchet does not need to be compiled

test:
	phpunit

cover:
	phpunit --coverage-text --coverage-html=reports/coverage

abtests:
	ulimit -n 2048 && php tests/AutobahnTestSuite/bin/fuzzingserver-libevent.php &
	ulimit -n 2048 && php54 tests/AutobahnTestSuite/bin/fuzzingserver-stream.php &
	wstest -m testeeserver -w ws://localhost:8002 &
	wstest -m fuzzingclient -s tests/AutobahnTestSuite/fuzzingclient-all.json
	killall php php54 python

profile:
	php -d 'xdebug.profiler_enable=1' tests/AutobahnTestSuite/bin/fuzzingserver-libevent.php &
	wstest -m fuzzingclient -s tests/AutobahnTestSuite/fuzzingclient-profile.json
	killall php

apidocs:
	apigen --title Ratchet -d reports/api -s src/ \
		-s vendor/react \
		-s vendor/guzzle \
		-s vendor/symfony/http-foundation/Symfony/Component/HttpFoundation/Session \
		-s vendor/evenement/evenement/src/Evenement
