<?php

declare(strict_types=1);

namespace Infrastructure\Database;

use Application\Contracts\ClockInterface;
use Application\Contracts\TimeFormatterInterface;
use PDO;
use RuntimeException;
use Throwable;

/**
 * Выполняет все миграции в папке <br>
 * Не поддерживает под-каталоги
 */
final readonly class MigrationRunner
{
    public function __construct(
        private string $migrationsPath,
        private ClockInterface $clock,
        private TimeFormatterInterface $timeFormatter,
    ) {
    }

    /**
     * Запускает все не выполненные миграции
     *
     * @return string[]
     *
     * @throws Throwable
     */
    public function run(PDO $pdo): array
    {
        $this->tryCreateMigrationsTable($pdo);

        $appliedMigrationNames = $this->appliedMigrationNames($pdo);
        $appliedMigrations = [];

        foreach ($this->migrationFilePaths() as $filePath) {
            $migrationName = basename($filePath);

            if (isset($appliedMigrationNames[$migrationName])) {
                continue;
            }

            $this->apply($pdo, $migrationName, $filePath);
            $appliedMigrations[] = $migrationName;
        }

        return $appliedMigrations;
    }

    private function tryCreateMigrationsTable(PDO $pdo): void
    {
        $pdo->exec(<<<SQL
            CREATE TABLE IF NOT EXISTS schema_migrations (
                name TEXT PRIMARY KEY,
                executed_at TEXT NOT NULL
            )
            SQL);
    }

    /**
     * Возвращает отсортированный список путей ко всем SQL-файлам миграций
     *
     * @return string[]
     */
    private function migrationFilePaths(): array
    {
        $filePaths = glob($this->migrationsPath . '/*.sql');

        if ($filePaths === false) {
            throw new RuntimeException('Cannot read migrations directory.');
        }

        sort($filePaths);

        return $filePaths;
    }

    /**
     * Загружает из базы имена миграций, которые уже были выполнены
     *
     * @return array<string, true>
     */
    private function appliedMigrationNames(PDO $pdo): array
    {
        $statement = $pdo->query('SELECT name FROM schema_migrations');
        $names = $statement->fetchAll(PDO::FETCH_COLUMN);

        return array_fill_keys($names, true);
    }

    /**
     * Применяет одну миграцию из файла и записывает ее имя в schema_migrations <br>
     * Работает через транзакцию на случай ошибки
     *
     * @throws Throwable
     */
    private function apply(PDO $pdo, string $migrationName, string $filePath): void
    {
        $sql = file_get_contents($filePath);

        if ($sql === false) {
            throw new RuntimeException('Cannot read migration: ' . $migrationName);
        }

        $pdo->beginTransaction();

        try {
            $pdo->exec($sql);

            $statement = $pdo->prepare(<<<SQL
                INSERT INTO schema_migrations (name, executed_at)
                VALUES (:name, :executed_at)
                SQL);
            $statement->execute([
                'name' => $migrationName,
                'executed_at' => $this->timeFormatter->formatForDatabase($this->clock->now()),
            ]);

            $pdo->commit();
        } catch (Throwable $exception) {
            $pdo->rollBack();

            throw $exception;
        }
    }
}
