<?php

declare(strict_types=1);

namespace Infrastructure\Persistence\Task;

use Application\Contracts\TaskRepositoryInterface;
use Application\Contracts\TimeFormatterInterface;
use Application\DTO\Task\TaskPage;
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
        private TimeFormatterInterface $timeFormatter,
    )
    {
    }

    public function add(Task $task): void
    {
        $createTaskSql = <<<SQL
            INSERT INTO tasks (uuid, title, description, status, created_at)
            VALUES (:uuid, :title, :description, :status, :created_at)
            SQL;

        $createTaskParameters = [
            'uuid'        => $task->id->value,
            'title'       => $task->title,
            'description' => $task->description,
            'status'      => $task->status->value,
            'created_at'  => $this->timeFormatter->formatForDatabase($task->createdAt),
        ];

        $statement = $this->pdo()->prepare($createTaskSql);
        $statement->execute($createTaskParameters);
    }

    public function findById(TaskId $id): ?Task
    {
        $findTaskByIdSql = <<<SQL
            SELECT uuid AS id, title, description, status, created_at
            FROM tasks
            WHERE uuid = :id
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
    public function findPage(?TaskStatus $status, int $limit, ?string $cursor): TaskPage
    {
        $listTasksSql = 'SELECT id AS cursor_id, uuid AS id, title, description, status, created_at FROM tasks';
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
            $statement->bindValue(':cursor_id', (int) $cursor, PDO::PARAM_INT);
        }

        $statement->bindValue(':limit', $requestedRowsLimit, PDO::PARAM_INT);
        $statement->execute();

        $tasks = [];
        $nextCursor = null;
        $lastCursor = null;
        $fetchedRows = 0;

        while (($taskRow = $statement->fetch()) !== false) {
            $fetchedRows++;

            if ($fetchedRows > $limit) {
                $nextCursor = $lastCursor;
                break;
            }

            $lastCursor = (string) $taskRow['cursor_id'];
            $tasks[] = $this->mapper->fromArray($taskRow);
        }

        return new TaskPage($tasks, $nextCursor);
    }

    public function update(Task $task): bool
    {
        $updateTaskSql = <<<SQL
            UPDATE tasks
            SET title = :title,
                description = :description,
                status = :status
            WHERE uuid = :id
            SQL;

        $statement = $this->pdo()->prepare($updateTaskSql);
        $statement->execute([
            'id' => $task->id->value,
            'title' => $task->title,
            'description' => $task->description,
            'status' => $task->status->value,
        ]);

        return $statement->rowCount() > 0;
    }

    /**
     * Удаляет задачу по id и возвращает true, если задача действительно была в базе.
     */
    public function delete(TaskId $id): bool
    {
        $deleteTaskSql = 'DELETE FROM tasks WHERE uuid = :id';
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
