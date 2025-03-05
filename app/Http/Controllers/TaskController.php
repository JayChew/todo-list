<?php

namespace App\Http\Controllers;

use App\Http\Resources\TaskResource;
use App\Models\Task;
use App\Services\TaskService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TaskController extends Controller
{
    protected $taskService;
    
    public function __construct(TaskService $taskService)
    {
        $this->middleware(middleware: 'rate.limit.task')->only(methods: 'store');
        $this->taskService = $taskService;
    }
    
    /**
     * Display a listing of the resource.
     */
    public function index(): AnonymousResourceCollection
    {
        return TaskResource::collection(resource: Task::all()); // 返回格式化列表
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): TaskResource
    {
        $validated = $request->validate(rules: [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string'
        ]);

        $validated['user_id'] = $request->user()->id;

        $task = $this->taskService->createTask(data: $validated);

        return new TaskResource(resource: $task); // 返回格式化 JSON
    }

    /**
     * Display the specified resource.
     */
    public function show($id): JsonResponse|TaskResource
    {
        $task = $this->taskService->getTaskById(id: $id);

        if (!$task) {
            return response()->json(data: ['message' => 'Task not found'], status: 404);
        }
        
        return new TaskResource(resource: $task);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id): JsonResponse|TaskResource
    {
        $task = $this->taskService->getTaskById(id: $id, withTrashed: true);

        if (!$task) {
            return response()->json(data: ['message' => 'Task not found'], status: 404);
        }
        
        $validated = $request->validate(rules: [
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'completed' => 'sometimes|in:0,1',
            'deleted_at' => 'nullable|in:0,null'
        ]);

        if ($task->trashed() && isset($validated['deleted_at'])) {
            $task->restore();
        }else if($task->trashed()) {
            return response()->json(data: ['message' => 'Task deleted'], status: 404);
        }

        $this->taskService->updateTask(task: $task, data: $validated);

        return new TaskResource(resource: $task);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id): JsonResponse
    {
        $task = $this->taskService->getTaskById(id: $id);

        if (!$task) {
            return response()->json(data: ['message' => 'Task not found'], status: 404);
        }

        $task->delete();

        return response()->json(data: ['message' => 'Task deleted'], status: 200);
    }

    /**
     * Bulk delete tasks.
     */
    public function destroyBulk(Request $request): JsonResponse
    {
        $validated = $request->validate(rules: [
            'task_ids' => 'required|array|min:1',
            'task_ids.*' => 'exists:tasks,id',
        ]);

        $deleted = $this->taskService->bulkDelete(task_ids: $validated['task_ids']);

        return $deleted
            ? response()->json(data: ['message' => 'Tasks deleted successfully'], status: 200)
            : response()->json(data: ['message' => 'No tasks were deleted'], status: 400);
    }

    /**
     * Bulk restore tasks.
     */
    public function restoreBulk(Request $request): JsonResponse
    {
        $validated = $request->validate(rules: [
            'task_ids' => 'required|array|min:1',
            'task_ids.*' => 'exists:tasks,id,deleted_at,NULL',
        ]);
        
        $restored = $this->taskService->bulkRestore(task_ids: $validated['task_ids']);

        return $restored
            ? response()->json(data: ['message' => 'Tasks restored successfully'], status: 200)
            : response()->json(data: ['message' => 'No tasks were restored'], status: 400);
    }
}
