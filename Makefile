.PHONY: init up down migrate shell

init:
	docker compose build
	docker compose run --rm php composer install
	$(MAKE) migrate

up:
	docker compose up -d

down:
	docker compose down

migrate:
	docker compose run --rm php php -r "require 'vendor/autoload.php'; $$runMigrations = new \Application\UseCase\Migration\RunMigrationsUseCase(new \Infrastructure\Database\MigrationRunner(getcwd() . '/Infrastructure/Database/migrations'), new \Infrastructure\Database\PdoConnection()); $$appliedMigrations = $$runMigrations->execute(); echo $$appliedMigrations === [] ? \"No new migrations.\n\" : sprintf(\"Applied %d migration(s): %s\n\", count($$appliedMigrations), implode(', ', $$appliedMigrations));"

shell:
	docker compose exec php sh
