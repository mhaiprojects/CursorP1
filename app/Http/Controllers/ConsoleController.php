<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessTask;
use App\Models\ChatMessage;
use App\Models\Task;
use App\Services\Cursor\Contracts\CursorAgent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Backend for the Cursor Console front-end page: manage/schedule Cursor tasks
 * and chat with the Cursor CLI agent.
 */
class ConsoleController extends Controller
{
    public function index(): View
    {
        return view('console', [
            'driver' => config('cursor.driver'),
        ]);
    }

    /**
     * Current state (tasks + chat messages) used for polling the UI.
     */
    public function state(): JsonResponse
    {
        return response()->json([
            'tasks' => Task::query()->latest()->limit(50)->get(),
            'messages' => ChatMessage::query()
                ->where('conversation', 'default')
                ->orderBy('id')
                ->limit(200)
                ->get(),
            'driver' => config('cursor.driver'),
        ]);
    }

    public function storeTask(Request $request): JsonResponse
    {
        $data = $this->validateJson($request, [
            'name' => ['required', 'string', 'max:255'],
            'prompt' => ['required', 'string'],
            'description' => ['nullable', 'string'],
            'mode' => ['nullable', 'in:ask,plan,agent'],
            'force' => ['sometimes', 'boolean'],
            'scheduled_at' => ['nullable', 'date'],
            'run_now' => ['sometimes', 'boolean'],
        ]);

        $task = Task::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'prompt' => $data['prompt'],
            'mode' => ($data['mode'] ?? null) === 'agent' ? null : ($data['mode'] ?? null),
            'force' => (bool) ($data['force'] ?? false),
            'scheduled_at' => $data['scheduled_at'] ?? null,
            'status' => Task::STATUS_PENDING,
        ]);

        if ($request->boolean('run_now')) {
            $this->dispatchTask($task);
        }

        return response()->json(['task' => $task->fresh()], 201);
    }

    public function runTask(Task $task): JsonResponse
    {
        if ($task->status === Task::STATUS_RUNNING || $task->status === Task::STATUS_QUEUED) {
            return response()->json(['task' => $task, 'message' => 'Task is already running.'], 422);
        }

        $this->dispatchTask($task);

        return response()->json(['task' => $task->fresh()]);
    }

    public function destroyTask(Task $task): JsonResponse
    {
        $task->delete();

        return response()->json(['deleted' => true]);
    }

    /**
     * Send a chat message to the Cursor agent (synchronous, ask mode).
     */
    public function chat(Request $request, CursorAgent $agent): JsonResponse
    {
        $data = $this->validateJson($request, [
            'message' => ['required', 'string'],
        ]);

        $userMessage = ChatMessage::create([
            'conversation' => 'default',
            'role' => ChatMessage::ROLE_USER,
            'content' => $data['message'],
        ]);

        $result = $agent->run($data['message'], ['mode' => 'ask']);

        $assistantMessage = ChatMessage::create([
            'conversation' => 'default',
            'role' => ChatMessage::ROLE_ASSISTANT,
            'content' => $result->ok ? $result->output : ($result->error ?? 'The Cursor agent returned an error.'),
            'failed' => ! $result->ok,
            'meta' => ['exit_code' => $result->exitCode],
        ]);

        return response()->json([
            'user' => $userMessage,
            'assistant' => $assistantMessage,
        ], 201);
    }

    public function clearChat(): JsonResponse
    {
        ChatMessage::where('conversation', 'default')->delete();

        return response()->json(['cleared' => true]);
    }

    private function dispatchTask(Task $task): void
    {
        $task->update(['status' => Task::STATUS_QUEUED]);
        ProcessTask::dispatch($task->id);
    }

    /**
     * Validate and always surface failures as a 422 JSON response.
     *
     * The app's exception handler only renders validation errors as JSON for
     * `api/*` routes, so we validate explicitly here for these fetch endpoints.
     */
    private function validateJson(Request $request, array $rules): array
    {
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            throw new ValidationException($validator, response()->json([
                'message' => 'The given data was invalid.',
                'errors' => $validator->errors(),
            ], 422));
        }

        return $validator->validated();
    }
}
