.PHONY: init up down migrate retry-webhooks shell check-config

init: check-config
	docker compose build
	docker compose run --rm php composer install
	$(MAKE) migrate

up:
	docker compose up -d

down:
	docker compose down

migrate: check-config
	docker compose run --rm php php -r "require 'vendor/autoload.php'; $$container = \Infrastructure\DI\AppContainerFactory::create(); $$runMigrations = $$container->get(\Infrastructure\Console\RunMigrationsCommand::class); $$appliedMigrations = $$runMigrations->execute(); echo $$appliedMigrations === [] ? \"No new migrations.\n\" : \"Applied \" . count($$appliedMigrations) . \" migration(s): \" . implode(', ', $$appliedMigrations) . \"\n\";"

retry-webhooks: check-config
	docker compose exec php php -r "require 'vendor/autoload.php'; $$container = \Infrastructure\DI\AppContainerFactory::create(); $$retryWebhooks = $$container->get(\Infrastructure\Console\RunWebhookRetriesCommand::class); $$processed = $$retryWebhooks->execute(); echo \"Processed \" . $$processed . \" webhook delivery attempt(s).\n\";"

shell:
	docker compose exec php sh

check-config:
	@if not exist config.php ( echo config.php не найден. & echo Создай config.php из config.example.php и заполни значения. & exit /b 1 )
