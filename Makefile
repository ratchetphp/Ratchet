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
	apigen -s src/ -s vendor/ --title Ratchet -d reports/api
