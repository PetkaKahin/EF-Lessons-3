.PHONY: init up down

init:
	docker compose build
	docker compose run --rm php composer install

up:
	docker compose up -d

down:
	docker compose down
