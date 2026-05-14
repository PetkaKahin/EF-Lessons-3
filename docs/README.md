# Документация проекта

## Что читать

1. [Жизненный цикл запроса](01 Жизненный цикл запроса.md) - путь HTTP-запроса от `public/index.php` до JSON-ответа
2. [Слои и зависимости](02 Слои и зависимости.md) - как разделены `Domain`, `Application` и `Infrastructure`
3. [Решения и паттерны](03 Решения и паттерны.md) - use case, repository, DTO, mapper, presenter, DI, middleware, idempotency, transactions

## Короткая модель

```text
HTTP request
-> public/index.php
-> Infrastructure\Kernel\Application
-> Infrastructure\Kernel\Request
-> Infrastructure\Kernel\Router
-> Request middleware, если подходит route или path prefix
-> Controller
-> RequestMapper
-> Application DTO
-> UseCase
-> Repository interface
-> SQLite repository
-> Domain object
-> Presenter
-> Response
-> Response middleware, если подходит path prefix
-> JSON
```

Если упростить еще сильнее:
```text
Infrastructure принимает внешний запрос и отдает внешний ответ.
Application выполняет сценарий приложения.
Domain хранит модель и правила предметной области.
Infrastructure читает и пишет данные через SQLite.
```

## Основные папки

`Domain/` - предметная модель: `Task`, `TaskId`, `TaskStatus`, `TaskPage`

`Application/` - сценарии приложения, DTO, контракты и idempotency-модель. Здесь лежит `TaskRepositoryInterface`, потому что use case'ам нужен контракт хранения, а не конкретная SQLite-реализация

`Infrastructure/` - технический слой: config, HTTP kernel, router, middleware, controllers, request mappers, presenters, DI container, SQLite repositories, PDO, migrations

`public/` - входная точка веб-приложения

## Главное правило зависимостей

```text
Infrastructure -> Application -> Domain
```

Стрелка означает "может зависеть от"

`Domain` не знает про HTTP, JSON, SQLite, PDO, Docker и routes

`Application` не знает про `Infrastructure`. Use case'ы получают интерфейсы и доменные объекты

`Infrastructure` знает про все слои, потому что она соединяет внешний мир с внутренней моделью приложения
