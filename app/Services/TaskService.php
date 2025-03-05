<?php

namespace App\Services;

use App\Http\Resources\TaskResource;
use App\Events\TaskListUpdated;
use App\Models\Task;
use App\Jobs\ProcessTask;

class TaskService
{
  
  public function getTaskById(int $id, bool $withTrashed = false): ?Task
  {
      return $withTrashed ? Task::withTrashed()->find(id: $id) : Task::find(id: $id);
  }

  public function createTask(array $data): Task
  {
    $task = Task::create(attributes: $data);
    ProcessTask::dispatch(new TaskResource($task));
    return $task;
  }

  public function updateTask(Task $task, array $data): Task
  {
    $task->update(attributes: $data);

    broadcast(new TaskListUpdated(new TaskResource($task)))->toOthers();
    
    return $task;
  }

  public function bulkDelete(array $task_ids): bool|null
  {
    return Task::whereIn(column: 'id', values: $task_ids)->delete();
  }

  public function bulkRestore(array $task_ids): bool|null
  {
    return Task::onlyTrashed()->whereIn(column: 'id', values: $task_ids)->restore();
  }
}
