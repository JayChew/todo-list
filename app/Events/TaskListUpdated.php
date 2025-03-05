<?php

namespace App\Events;

use App\Http\Resources\TaskResource;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TaskListUpdated implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public $task;

    public function __construct(TaskResource $task)
    {
        $this->task = $task;
    }

    // 使用私有频道，每个用户只能订阅自己的任务
    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel(name: "tasks.{$this->task->user_id}");
    }

    public function broadcastWith()
    {
        return ['task' => $this->task];
    }
}

