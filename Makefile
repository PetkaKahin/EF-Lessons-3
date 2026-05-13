.PHONY: init up down migrate shell check-config

init: check-config
	docker compose build
	docker compose run --rm php composer install
	$(MAKE) migrate

up:
	docker compose up -d

down:
	docker compose down

migrate: check-config
	docker compose run --rm php php -r "require 'vendor/autoload.php'; $$container = \Infrastructure\DI\AppContainerFactory::create(); $$runMigrations = $$container->get(\Infrastructure\Console\RunMigrationsCommand::class); $$appliedMigrations = $$runMigrations->execute(); echo $$appliedMigrations === [] ? \"No new migrations.\n\" : sprintf(\"Applied %d migration(s): %s\n\", count($$appliedMigrations), implode(', ', $$appliedMigrations));"

shell:
	docker compose exec php sh

check-config:
	@if not exist config.php ( echo config.php не найден. & echo Создай config.php из config.example.php и заполни значения. & exit /b 1 )
