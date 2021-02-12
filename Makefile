.PHONY: clean fix-code-style code-check code-style static-analysis infection-testing test test-unit test-integration coverage install-dependencies update-dependencies
.DEFAULT_GOAL := test

INFECTION = ./vendor/bin/infection
PHPUNIT = ./vendor/bin/phpunit -c ./phpunit.xml
PHPSTAN = ./vendor/bin/phpstan analyse
PHPCS = ./vendor/bin/phpcs --extensions=php -v
PHPCBF = ./vendor/bin/phpcbf ./app --standard=PSR12
COVCHECK = ./vendor/bin/coverage-check

clean:
	rm -rf ./build ./vendor

fix-code-style:
	${PHPCBF}

code-check:
	${PHPCS}
	${PHPSTAN}

code-style:
	mkdir -p build/logs/phpcs
	${PHPCS}

static-analysis:
	mkdir -p build/logs/phpstan
	${PHPSTAN} --no-progress

infection-testing:
	make coverage
	cp -f build/logs/phpunit/junit.xml build/logs/phpunit/coverage/junit.xml
	${INFECTION} --coverage=build/logs/phpunit --min-msi=92 --threads=`nproc`

test:
	${PHPUNIT} --no-coverage

test-unit:
	${PHPUNIT} --no-coverage --testsuite=Unit

test-integration:
	${PHPUNIT} --no-coverage --testsuite=Integration

coverage:
	${PHPUNIT} && ${COVCHECK} build/logs/phpunit/clover.xml 100

install-dependencies:
	composer install

update-dependencies:
	composer update

help:
	# Usage:
	#   make <target> [OPTION=value]
	#
	# Targets:
	#   clean                   Cleans the coverage and the vendor directory
	#   fix-code-style          PHP Code fix using phpcbf
	#   code-check              Check code style using phpcs & Code analysis
	#   code-style              Check code style using phpcs
	#   static-analysis         Run static analysis using phpstan
	#   infection-testing       Run infection/mutation testing
	#   test                    Run tests
	#   test-unit               Run all unit tests
	#   test-integration        Run all integration tests
	#   coverage                Code Coverage display
	#   install-dependencies    Install dependencies
	#   update-dependencies     Run composer update
	#   help                    You're looking at it!
