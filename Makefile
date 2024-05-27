# --------------------------------#
# "make" command
# --------------------------------#

-include ./make/text.mk
-include ./make/help.mk
-include ./make/url.mk

###########
# Install #
###########

## Install dependencies
install: install.composer install.jwt

install.composer:
	symfony composer install
	symfony composer --working-dir=tools/php-cs-fixer install

install.jwt:
	php bin/console lexik:jwt:generate-keypair --skip-if-exists

## Update dependencies
update: update.composer

## Update Symfony dependencies only
update.symfony:
	symfony composer update "symfony/*"

update.composer:
	symfony composer update

############
# Database #
############

## Database - Init (create+schema)
db.install: db.create db.update-force

## Database - Create (db only)
db.create:
	symfony console doctrine:database:create --if-not-exists

## Database - Drop
db.drop:
	symfony console doctrine:database:drop --force --if-exists

## Database - Reset
db.reset: db.drop db.install

## Database - Load data fixtures
db.fixtures:
	symfony console doctrine:fixtures:load --no-interaction

# Database - Force update the database schema
db.update-force: db.create
	symfony console doctrine:schema:update --force --complete

# Generate hash-password
db.hash-password:
	symfony php bin/console security:hash-password

###############
# Development #
###############

## Dev - Start the whole application for development purposes
serve:
	# https://www.npmjs.com/package/concurrently
	npx concurrently "make serve.docker" "make serve.php" --names="Docker,Symfony" --prefix=name --kill-others --kill-others-on-fail

## Dev - Start Symfony web server
serve.php:
	symfony server:start --port=63280

## Dev - Start Docker services
serve.docker:
	docker compose up

## Dev - Start Docker services in background
serve.docker+detached:
	docker compose up -d

## Test - Start the whole application for test purposes (debug)
serve@test:
	# https://www.npmjs.com/package/concurrently
	npx concurrently "make serve.docker" "make serve.php@test" --names="Docker,Symfony" --prefix=name --kill-others --kill-others-on-fail

serve.php@test: export APP_ENV = test
serve.php@test: export APP_DEBUG = 1
serve.php@test:
	symfony server:start --port=63290

## Stop Symfony web server
stop.php:
	symfony server:stop

## Stop Docker services
stop.docker:
	docker compose down

## Clear cache
cache-clear:
	symfony console cache:clear

########
# Lint #
########

## Lint - Lint
lint: lint.php-cs-fixer lint.phpstan lint.twig lint.yaml lint.container lint.doctrine lint.graphql lint.composer

lint.composer:
	symfony composer validate --no-check-publish

lint.container:
	symfony console lint:container

lint.doctrine:
	symfony console doctrine:schema:validate

lint.php-cs-fixer:
	symfony php tools/php-cs-fixer/vendor/bin/php-cs-fixer fix

lint.twig:
	symfony console lint:twig templates --show-deprecations

lint.graphql:
	symfony console graphql:validate

lint.yaml:
	symfony console lint:yaml config translations --parse-tags

lint.phpstan:
	symfony console cache:clear --ansi
	symfony console cache:warmup --ansi
	symfony php vendor/bin/phpstan analyse --memory-limit=-1

############
# Security #
############

security.symfony:
	symfony check:security

password:
	symfony php bin/console security:hash-password

########
# Test #
########

## Test - Test
test:
	symfony php bin/phpunit

## Test - Test & update deprecations baseline (see ./docs/res/tests.md)
test.update-baseline: export UPDATE_DEPRECATIONS_BASELINE = 1
test.update-baseline: test
