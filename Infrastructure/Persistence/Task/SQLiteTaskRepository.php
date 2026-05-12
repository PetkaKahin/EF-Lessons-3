<?php

declare(strict_types=1);

namespace Infrastructure\Persistence\Task;

use Domain\Task\Contracts\TaskRepositoryInterface;
use Domain\Task\DTO\TaskPage;
use Domain\Task\Task;
use Domain\Task\TaskId;
use Domain\Task\TaskStatus;
use Infrastructure\Database\PdoConnection;
use PDO;

final readonly class SQLiteTaskRepository implements TaskRepositoryInterface
{
    public function __construct(
        private PdoConnection $pdoConnection,
        private TaskMapper    $mapper,
    )
    {
    }

    public function create(string $title, ?string $description, TaskStatus $status): Task
    {
        $createTaskSql = <<<SQL
            INSERT INTO tasks (title, description, status, created_at)
            VALUES (:title, :description, :status, strftime('%Y-%m-%dT%H:%M:%SZ', 'now'))
            RETURNING id, title, description, status, created_at
            SQL;

        $createTaskParameters = [
            'title'       => $title,
            'description' => $description,
            'status'      => $status->value,
        ];

        $statement = $this->pdo()->prepare($createTaskSql);
        $statement->execute($createTaskParameters);

        return $this->mapper->fromArray($statement->fetch());
    }

    public function findById(TaskId $id): ?Task
    {
        $findTaskByIdSql = <<<SQL
            SELECT id, title, description, status, created_at
            FROM tasks
            WHERE id = :id
            SQL;
        $findTaskByIdParameters = [
            'id' => $id->value,
        ];

        $statement = $this->pdo()->prepare($findTaskByIdSql);
        $statement->execute($findTaskByIdParameters);

        $taskRow = $statement->fetch();

        return $taskRow === false ? null : $this->mapper->fromArray($taskRow);
    }

    /**
     * Возвращает страницу тасок <br>
     * Если передан статус - добавляем фильтр по статусу <br>
     * Если передан cursor - то берем следующую страницу <br>
     */
    public function findPage(?TaskStatus $status, int $limit, ?TaskId $cursor): TaskPage
    {
        $listTasksSql = 'SELECT id, title, description, status, created_at FROM tasks';
        $conditions = [];

        if ($status !== null) {
            $conditions[] = 'status = :status';
        }

        if ($cursor !== null) {
            $conditions[] = 'id < :cursor_id';
        }

        if ($conditions !== []) {
            $listTasksSql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $listTasksSql .= ' ORDER BY id DESC LIMIT :limit';
        $requestedRowsLimit = $limit + 1;

        $statement = $this->pdo()->prepare($listTasksSql);

        if ($status !== null) {
            $statement->bindValue(':status', $status->value);
        }

        if ($cursor !== null) {
            $statement->bindValue(':cursor_id', $cursor->value, PDO::PARAM_INT);
        }

        $statement->bindValue(':limit', $requestedRowsLimit, PDO::PARAM_INT);
        $statement->execute();

        $taskRows = $statement->fetchAll();
        $hasNextPage = count($taskRows) > $limit;
        $currentPageRows = array_slice($taskRows, 0, $limit);
        $tasks = array_map(
            fn(array $taskRow): Task => $this->mapper->fromArray($taskRow),
            $currentPageRows,
        );
        $nextCursor = $hasNextPage && $tasks !== [] ? $tasks[array_key_last($tasks)]->id : null;

        return new TaskPage($tasks, $nextCursor);
    }

    /**
     * Сохраняет изменения задачи в базе.
     */
    public function save(Task $task): void
    {
        $updateTaskSql = <<<SQL
            UPDATE tasks
            SET title = :title,
                description = :description,
                status = :status
            WHERE id = :id
            SQL;

        $updateTaskParameters = $this->mapper->toArray($task);

        $statement = $this->pdo()->prepare($updateTaskSql);
        $statement->execute($updateTaskParameters);
    }

    /**
     * Удаляет задачу по id и возвращает true, если задача действительно была в базе.
     */
    public function delete(TaskId $id): bool
    {
        $deleteTaskSql = 'DELETE FROM tasks WHERE id = :id';
        $deleteTaskParameters = [
            'id' => $id->value,
        ];

        $statement = $this->pdo()->prepare($deleteTaskSql);
        $statement->execute($deleteTaskParameters);

        return $statement->rowCount() > 0;
    }

    /**
     * Берет готовое PDO-подключение из PdoConnection.
     */
    private function pdo(): PDO
    {
        return $this->pdoConnection->get();
    }
}
