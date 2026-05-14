<?php

declare(strict_types=1);

namespace Infrastructure\Persistence\Task;

use Application\Contracts\TaskRepositoryInterface;
use Application\DTO\Task\TaskPage;
use Application\DTO\Task\UpdateTaskInput;
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
            INSERT INTO tasks (uuid, title, description, status, created_at)
            VALUES (:uuid, :title, :description, :status, strftime('%Y-%m-%dT%H:%M:%SZ', 'now'))
            RETURNING uuid AS id, title, description, status, created_at
            SQL;

        $createTaskParameters = [
            'uuid'        => $this->newUuid(),
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

        $taskRows = $statement->fetchAll();
        $hasNextPage = count($taskRows) > $limit;
        $currentPageRows = array_slice($taskRows, 0, $limit);
        $tasks = array_map(
            fn(array $taskRow): Task => $this->mapper->fromArray($taskRow),
            $currentPageRows,
        );
        $nextCursor = $hasNextPage && $currentPageRows !== []
            ? (string) $currentPageRows[array_key_last($currentPageRows)]['cursor_id']
            : null;

        return new TaskPage($tasks, $nextCursor);
    }

    public function update(Task $task, UpdateTaskInput $input): ?Task
    {
        $sets = [];
        $parameters = [
            'id' => $task->id->value,
        ];

        if ($input->hasTitle()) {
            $sets[] = 'title = :title';
            $parameters['title'] = $task->title;
        }

        if ($input->hasDescription()) {
            $sets[] = 'description = :description';
            $parameters['description'] = $task->description;
        }

        if ($input->hasStatus()) {
            $sets[] = 'status = :status';
            $parameters['status'] = $task->status->value;
        }

        $updateTaskSql = 'UPDATE tasks SET ' . implode(', ', $sets) . ' WHERE uuid = :id';

        $statement = $this->pdo()->prepare($updateTaskSql);
        $statement->execute($parameters);

        return $this->findById($task->id);
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

    private function newUuid(): string
    {
        $bytes = random_bytes(16);
        $bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40);
        $bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);
        $hex = bin2hex($bytes);

        return substr($hex, 0, 8) . '-'
            . substr($hex, 8, 4) . '-'
            . substr($hex, 12, 4) . '-'
            . substr($hex, 16, 4) . '-'
            . substr($hex, 20);
    }
}
