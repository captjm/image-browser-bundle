SHELL := /bin/bash

up:
	docker compose up --build -d

ps:
	docker compose ps

logs:
	docker compose logs -f

down:
	docker compose down -v

restart:
	docker compose down && docker compose up --build

sh:
	docker compose run --rm app bash

install:
	docker compose run --rm app composer install

require:
	docker compose run --rm app composer require $(pkg)

cc:
	docker compose run --rm app php bin/console cache:clear

migrate:
	docker compose run --rm app php bin/console doctrine:migrations:migrate --no-interaction
