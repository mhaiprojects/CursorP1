<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessTask;
use App\Models\ChatMessage;
use App\Models\Task;
use App\Services\Cursor\Contracts\CursorAgent;
use Cron\CronExpression;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Backend for the Cursor Console front-end page: manage/schedule Cursor tasks
 * and chat with the Cursor CLI agent.
 *
 * Every action is scoped to the authenticated user, so users only ever see and
 * control their own tasks and chat history.
 */
class ConsoleController extends Controller
{
    /** Max characters allowed in a task prompt / chat message. */
    private const MAX_PROMPT = 8000;

    public function index(): View
    {
        return view('console', [
            'driver' => config('cursor.driver'),
        ]);
    }

    /**
     * Current state (tasks + chat messages) used for polling the UI.
     */
    public function state(Request $request): JsonResponse
    {
        return response()->json([
            'tasks' => Task::query()
                ->where('user_id', $request->user()->id)
                ->withCount('runs')
                ->latest()
                ->limit(50)
                ->get(),
            'messages' => ChatMessage::query()
                ->where('user_id', $request->user()->id)
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
            'prompt' => ['required', 'string', 'max:'.self::MAX_PROMPT],
            'description' => ['nullable', 'string', 'max:2000'],
            'mode' => ['nullable', 'in:ask,plan,agent'],
            'force' => ['sometimes', 'boolean'],
            'scheduled_at' => ['nullable', 'date'],
            'cron_expression' => ['nullable', 'string', 'max:255', function (string $attribute, mixed $value, \Closure $fail) {
                if (! CronExpression::isValidExpression($value)) {
                    $fail('The :attribute is not a valid cron expression.');
                }
            }],
            'run_now' => ['sometimes', 'boolean'],
        ]);

        $task = Task::create([
            'user_id' => $request->user()->id,
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'prompt' => $data['prompt'],
            'mode' => ($data['mode'] ?? null) === 'agent' ? null : ($data['mode'] ?? null),
            'force' => (bool) ($data['force'] ?? false),
            'scheduled_at' => $data['scheduled_at'] ?? null,
            'cron_expression' => $data['cron_expression'] ?? null,
            'status' => Task::STATUS_PENDING,
        ]);

        if ($request->boolean('run_now')) {
            $this->dispatchTask($task);
        }

        return response()->json(['task' => $task->fresh()], 201);
    }

    public function runTask(Request $request, Task $task): JsonResponse
    {
        $this->authorizeTask($request, $task);

        if (in_array($task->status, [Task::STATUS_RUNNING, Task::STATUS_QUEUED], true)) {
            return response()->json(['task' => $task, 'message' => 'Task is already running.'], 422);
        }

        $this->dispatchTask($task);

        return response()->json(['task' => $task->fresh()]);
    }

    public function cancelTask(Request $request, Task $task): JsonResponse
    {
        $this->authorizeTask($request, $task);

        if (! $task->canBeCancelled()) {
            return response()->json(['task' => $task, 'message' => 'Only pending or queued tasks can be cancelled.'], 422);
        }

        $task->update(['status' => Task::STATUS_CANCELLED, 'finished_at' => now()]);

        return response()->json(['task' => $task->fresh()]);
    }

    public function destroyTask(Request $request, Task $task): JsonResponse
    {
        $this->authorizeTask($request, $task);

        $task->delete();

        return response()->json(['deleted' => true]);
    }

    /**
     * Send a chat message to the Cursor agent (synchronous, ask mode).
     */
    public function chat(Request $request, CursorAgent $agent): JsonResponse
    {
        $data = $this->validateJson($request, [
            'message' => ['required', 'string', 'max:'.self::MAX_PROMPT],
        ]);

        $conversation = $this->conversationKey($request);

        $userMessage = ChatMessage::create([
            'user_id' => $request->user()->id,
            'conversation' => $conversation,
            'role' => ChatMessage::ROLE_USER,
            'content' => $data['message'],
        ]);

        $result = $agent->run($data['message'], ['mode' => 'ask']);

        $assistantMessage = ChatMessage::create([
            'user_id' => $request->user()->id,
            'conversation' => $conversation,
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

    public function clearChat(Request $request): JsonResponse
    {
        ChatMessage::query()->where('user_id', $request->user()->id)->delete();

        return response()->json(['cleared' => true]);
    }

    private function dispatchTask(Task $task): void
    {
        $task->update(['status' => Task::STATUS_QUEUED]);
        ProcessTask::dispatch($task->id);
    }

    private function authorizeTask(Request $request, Task $task): void
    {
        abort_unless($task->user_id === $request->user()->id, 403);
    }

    private function conversationKey(Request $request): string
    {
        return 'user:'.$request->user()->id;
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
