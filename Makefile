.PHONY: installNoDev installWithDev test

installNoDev:
	-composer install -o --no-dev

installWithDev:
	-composer install -o

test: installWithDev
	-./vendor/bin/phpunit --coverage-html build/coverage/
