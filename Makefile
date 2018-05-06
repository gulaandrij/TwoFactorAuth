# Show all running containers
dps:
	@docker ps --format "table {{.ID}}\t{{.Ports}}\t{{.Names}}"

# Up docker environment
up:
	@docker-compose up -d --build
	@make dps

# Down docker environment
down:
	@docker stop $(shell docker ps -a -q)

# Update composer packages
composer-update:
	@docker exec -it tfa-app composer update
	@docker exec -it tfa-app bin/console doctrine:cache:clear-metadata
	@make chmod

# Update composer packages
composer-install:
	@docker exec -it tfa-app composer install
	@docker exec -it tfa-app bin/console doctrine:cache:clear-metadata
	@make chmod

# Dump autoload
composer-du:
	@docker exec -it tfa-app composer du

# Pre-commit hooks (code sniffer + code beautifier)
precommit:
	@docker exec -i tfa-app /bin/bash -c "vendor/bin/phpcbf . && vendor/bin/phpcs . && bin/console doctrine:schema:validate --env=dev"

# Pre-commit hooks (code sniffer + code beautifier)
cbf:
	@vendor/bin/phpcbf . && \
	vendor/bin/phpcs . && \
	docker exec -i tfa-app /bin/bash -c "bin/console doctrine:schema:validate"

# Run unit tests in symfony-app
unit:
	@docker exec -i tfa-app bin/phpunit --colors=always

# Rebuild whole db and make seed data
db-refresh:
	@docker exec -i tfa-app bash -c "\
	bin/console doctrine:database:drop --force --if-exists --env=dev && \
	bin/console doctrine:database:create  --env=dev && \
	bin/console doctrine:migrations:migrate --no-interaction -q --env=dev && \
	bin/console doctrine:fixtures:load -n --env=dev && \
	composer du && \
	bin/console doctrine:schema:validate --env=dev && \
	bin/console fos:elastica:populate && \
	chmod -R 0777 ."

chmod:
	@docker exec -it tfa-app chmod -R 0777 .

cache-clear: composer-du
	@docker exec -i tfa-app bash -c "\
	bin/console doctrine:cache:clear-metadata && \
	rm -rf var/cache/* && \
	bin/console cache:warmup && \
	chmod -R 0777 ."

bash:
	@docker exec -it tfa-app bash
