test:
	phpunit

cover:
	phpunit --coverage-text --coverage-html=reports/coverage

abtests:
	ulimit -n 2048 && php tests/AutobahnTestSuite/bin/fuzzingserver-libevent.php &
	ulimit -n 2048 && php tests/AutobahnTestSuite/bin/fuzzingserver-stream.php &
	wstest -m testeeserver -w ws://localhost:8002 &
	wstest -m fuzzingclient -s tests/AutobahnTestSuite/fuzzingclient-all.json
	killall php
	killall python

profile:
	php -d 'xdebug.profiler_enable=1' tests/AutobahnTestSuite/bin/fuzzingserver-libevent.php &
	wstest -m fuzzingclient -s tests/AutobahnTestSuite/fuzzingclient-profile.json
	killall php

apidocs:
	apigen --title Ratchet -d reports/api -s src/ \
		-s vendor/react \
		-s vendor/guzzle/guzzle/src/Guzzle/Http \
		-s vendor/guzzle/guzzle/src/Guzzle/Common \
		-s vendor/symfony/http-foundation/Symfony/Component/HttpFoundation/Session \
		-s vendor/evenement/evenement/src/Evenement
