<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Cursor Console</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-gray-950 text-gray-100 font-sans antialiased">
<div x-data="cursorConsole()" x-init="init()" class="min-h-full">
    <header class="border-b border-gray-800 bg-gray-900/60">
        <div class="mx-auto max-w-7xl px-6 py-4 flex items-center justify-between">
            <div>
                <h1 class="text-xl font-semibold">Cursor Console</h1>
                <p class="text-sm text-gray-400">Manage &amp; schedule Cursor CLI tasks and chat with the agent.</p>
            </div>
            <span class="text-xs px-3 py-1 rounded-full border"
                  :class="driver === 'cli' ? 'border-emerald-500 text-emerald-400' : 'border-amber-500 text-amber-400'">
                driver: <span x-text="driver"></span>
            </span>
        </div>
    </header>

    <main class="mx-auto max-w-7xl px-6 py-6 grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Task management --}}
        <section class="bg-gray-900 rounded-xl border border-gray-800 flex flex-col">
            <div class="px-5 py-4 border-b border-gray-800">
                <h2 class="font-semibold">Tasks</h2>
                <p class="text-xs text-gray-500">Scheduled tasks trigger the Cursor CLI via the queue.</p>
            </div>

            <form @submit.prevent="createTask()" class="px-5 py-4 space-y-3 border-b border-gray-800">
                <input x-model="form.name" required placeholder="Task name"
                       class="w-full rounded-md bg-gray-800 border border-gray-700 px-3 py-2 text-sm" />
                <textarea x-model="form.prompt" required rows="2" placeholder="Prompt for the Cursor agent…"
                          class="w-full rounded-md bg-gray-800 border border-gray-700 px-3 py-2 text-sm"></textarea>
                <div class="flex flex-wrap gap-3 items-center">
                    <select x-model="form.mode" class="rounded-md bg-gray-800 border border-gray-700 px-2 py-2 text-sm">
                        <option value="ask">ask</option>
                        <option value="plan">plan</option>
                        <option value="agent">agent</option>
                    </select>
                    <label class="text-xs text-gray-400 flex items-center gap-1">
                        schedule
                        <input type="datetime-local" x-model="form.scheduled_at"
                               class="rounded-md bg-gray-800 border border-gray-700 px-2 py-1 text-sm" />
                    </label>
                    <label class="text-xs text-gray-400 flex items-center gap-1">
                        cron
                        <input type="text" x-model="form.cron_expression" placeholder="*/5 * * * *"
                               class="w-28 rounded-md bg-gray-800 border border-gray-700 px-2 py-1 text-sm" />
                    </label>
                    <div class="ml-auto flex gap-2">
                        <button type="submit" name="run_now"
                                class="rounded-md bg-indigo-600 hover:bg-indigo-500 px-3 py-2 text-sm font-medium">
                            Add task
                        </button>
                        <button type="button" @click="createTask(true)"
                                class="rounded-md bg-emerald-600 hover:bg-emerald-500 px-3 py-2 text-sm font-medium">
                            Add &amp; run now
                        </button>
                    </div>
                </div>
            </form>

            <ul class="divide-y divide-gray-800 overflow-y-auto" style="max-height: 26rem">
                <template x-for="task in tasks" :key="task.id">
                    <li class="px-5 py-3">
                        <div class="flex items-center justify-between gap-2">
                            <div class="min-w-0">
                                <p class="font-medium truncate" x-text="task.name"></p>
                                <p class="text-xs text-gray-500 truncate" x-text="task.prompt"></p>
                            </div>
                            <div class="shrink-0 flex items-center gap-1">
                                <span class="text-xs px-2 py-1 rounded-full bg-purple-900 text-purple-200"
                                      x-show="task.cron_expression" x-text="'⟳ ' + task.cron_expression"></span>
                                <span class="text-xs px-2 py-1 rounded-full"
                                      :class="statusClass(task.status)" x-text="task.status"></span>
                            </div>
                        </div>
                        <div class="mt-2 flex items-center gap-3 text-xs">
                            <button @click="runTask(task.id)" class="text-indigo-400 hover:underline"
                                    x-show="!['queued','running'].includes(task.status)">Run now</button>
                            <button @click="cancelTask(task.id)" class="text-amber-400 hover:underline"
                                    x-show="['pending','queued'].includes(task.status)">Cancel</button>
                            <button @click="deleteTask(task.id)" class="text-red-400 hover:underline">Delete</button>
                            <span class="text-gray-600" x-show="task.runs_count > 0"
                                  x-text="task.runs_count + ' run(s)'"></span>
                            <span class="text-gray-600" x-show="task.scheduled_at"
                                  x-text="'scheduled: ' + task.scheduled_at"></span>
                        </div>
                        <pre x-show="task.output"
                             class="mt-2 whitespace-pre-wrap text-xs bg-gray-950 border border-gray-800 rounded-md p-2 text-gray-300"
                             x-text="task.output"></pre>
                        <p x-show="task.error" class="mt-2 text-xs text-red-400" x-text="task.error"></p>
                    </li>
                </template>
                <li x-show="tasks.length === 0" class="px-5 py-6 text-sm text-gray-500 text-center">No tasks yet.</li>
            </ul>
        </section>

        {{-- Chat --}}
        <section class="bg-gray-900 rounded-xl border border-gray-800 flex flex-col">
            <div class="px-5 py-4 border-b border-gray-800 flex items-center justify-between">
                <div>
                    <h2 class="font-semibold">Chat with Cursor</h2>
                    <p class="text-xs text-gray-500">Ask the Cursor CLI agent a question.</p>
                </div>
                <button @click="clearChat()" class="text-xs text-gray-400 hover:text-gray-200">Clear</button>
            </div>

            <div class="flex-1 overflow-y-auto px-5 py-4 space-y-3" style="max-height: 30rem" x-ref="messages">
                <template x-for="message in messages" :key="message.id">
                    <div :class="message.role === 'user' ? 'text-right' : 'text-left'">
                        <div class="inline-block max-w-[85%] rounded-lg px-3 py-2 text-sm whitespace-pre-wrap text-left"
                             :class="message.role === 'user' ? 'bg-indigo-600'
                                 : (message.failed ? 'bg-red-900/60 border border-red-700' : 'bg-gray-800')">
                            <span class="block text-[10px] uppercase tracking-wide opacity-60"
                                  x-text="message.role"></span>
                            <span x-text="message.content"></span>
                        </div>
                    </div>
                </template>
                <p x-show="messages.length === 0" class="text-sm text-gray-500 text-center py-6">
                    No messages yet. Say hello 👋
                </p>
                <p x-show="sending" class="text-xs text-gray-500">Cursor is thinking…</p>
            </div>

            <form @submit.prevent="sendChat()" class="px-5 py-4 border-t border-gray-800 flex gap-2">
                <input x-model="chatInput" required placeholder="Message the Cursor agent…"
                       class="flex-1 rounded-md bg-gray-800 border border-gray-700 px-3 py-2 text-sm" />
                <button type="submit" :disabled="sending"
                        class="rounded-md bg-indigo-600 hover:bg-indigo-500 disabled:opacity-50 px-4 py-2 text-sm font-medium">
                    Send
                </button>
            </form>
        </section>
    </main>
</div>

<script>
    function cursorConsole() {
        return {
            tasks: [],
            messages: [],
            driver: @json($driver),
            sending: false,
            chatInput: '',
            form: { name: '', prompt: '', mode: 'ask', scheduled_at: '', cron_expression: '' },

            init() {
                this.refresh();
                setInterval(() => this.refresh(), 3000);
            },

            csrf() {
                return document.querySelector('meta[name="csrf-token"]').content;
            },

            async api(url, options = {}) {
                const res = await fetch(url, {
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.csrf(),
                    },
                    ...options,
                });
                return res.json();
            },

            async refresh() {
                const data = await this.api('/console/state');
                this.tasks = data.tasks;
                this.messages = data.messages;
                this.driver = data.driver;
            },

            async createTask(runNow = false) {
                if (!this.form.name || !this.form.prompt) return;
                await this.api('/console/tasks', {
                    method: 'POST',
                    body: JSON.stringify({ ...this.form, run_now: runNow }),
                });
                this.form = { name: '', prompt: '', mode: 'ask', scheduled_at: '', cron_expression: '' };
                this.refresh();
            },

            async runTask(id) {
                await this.api(`/console/tasks/${id}/run`, { method: 'POST' });
                this.refresh();
            },

            async cancelTask(id) {
                await this.api(`/console/tasks/${id}/cancel`, { method: 'POST' });
                this.refresh();
            },

            async deleteTask(id) {
                await this.api(`/console/tasks/${id}`, { method: 'DELETE' });
                this.refresh();
            },

            async sendChat() {
                if (!this.chatInput) return;
                this.sending = true;
                const message = this.chatInput;
                this.chatInput = '';
                try {
                    await this.api('/console/chat', {
                        method: 'POST',
                        body: JSON.stringify({ message }),
                    });
                    await this.refresh();
                    this.$nextTick(() => {
                        this.$refs.messages.scrollTop = this.$refs.messages.scrollHeight;
                    });
                } finally {
                    this.sending = false;
                }
            },

            async clearChat() {
                await this.api('/console/chat/clear', { method: 'POST' });
                this.refresh();
            },

            statusClass(status) {
                return {
                    pending: 'bg-gray-700 text-gray-300',
                    queued: 'bg-amber-800 text-amber-200',
                    running: 'bg-blue-800 text-blue-200',
                    completed: 'bg-emerald-800 text-emerald-200',
                    failed: 'bg-red-800 text-red-200',
                    cancelled: 'bg-gray-600 text-gray-200',
                }[status] || 'bg-gray-700 text-gray-300';
            },
        };
    }
</script>
</body>
</html>
