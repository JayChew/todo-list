<?php

namespace App\Jobs;

use App\Http\Resources\TaskResource;
use App\Mail\TaskNotification;
use App\Events\TaskListUpdated;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendTaskNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $task;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(TaskResource $task)
    {
        $this->task = $task;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        // 发送邮件
        Mail::to(users: $this->task->user->email)->send(mailable: new TaskNotification(task: $this->task));
        
        // 记录日志
        Log::info(message: "新任务创建通知：{$this->task->title}");

        broadcast(new TaskListUpdated($this->task))->toOthers();
    }
}
