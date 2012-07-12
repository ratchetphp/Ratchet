unittests:
	phpunit --coverage-text --coverage-html=reports/coverage

abtests:
	ulimit -n 2048
	php tests/ab-wstest-libevent.php &
	php tests/ab-wstest-stream.php &
	cd tests && wstest -m fuzzingclient -s ab-wstest-fuzzyconf.json
	killall php
	echo

apidocs:
	apigen --title Ratchet -d reports/api -s src/ \
		-s vendor/react \
		-s vendor/guzzle/guzzle/src/Guzzle/Http \
		-s vendor/guzzle/guzzle/src/Guzzle/Common \
		-s vendor/symfony/http-foundation/Symfony/Component/HttpFoundation/Session \
		-s vendor/evenement/evenement/src/Evenement
