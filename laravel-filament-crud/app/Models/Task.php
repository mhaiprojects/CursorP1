<?php

namespace App\Models;

use Cron\CronExpression;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Task extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_QUEUED = 'queued';

    public const STATUS_RUNNING = 'running';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_FAILED = 'failed';

    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'prompt',
        'mode',
        'force',
        'status',
        'scheduled_at',
        'cron_expression',
        'last_run_at',
        'started_at',
        'finished_at',
        'exit_code',
        'output',
        'error',
    ];

    protected $casts = [
        'force' => 'boolean',
        'scheduled_at' => 'datetime',
        'last_run_at' => 'datetime',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    /**
     * One-off pending tasks whose scheduled time is now/past (or unscheduled).
     * Recurring (cron) tasks are handled separately.
     */
    public function scopeDue(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING)
            ->whereNull('cron_expression')
            ->where(function (Builder $q) {
                $q->whereNull('scheduled_at')
                    ->orWhere('scheduled_at', '<=', now());
            });
    }

    /**
     * Recurring tasks that are idle (pending) and ready to consider for dispatch.
     */
    public function scopeRecurring(Builder $query): Builder
    {
        return $query->whereNotNull('cron_expression')
            ->where('status', self::STATUS_PENDING);
    }

    public function isRecurring(): bool
    {
        return ! empty($this->cron_expression);
    }

    public function isTerminal(): bool
    {
        return in_array($this->status, [
            self::STATUS_COMPLETED,
            self::STATUS_FAILED,
            self::STATUS_CANCELLED,
        ], true);
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_QUEUED], true);
    }

    /**
     * Whether a recurring task's cron schedule is due at the given moment
     * (and hasn't already run within the current minute).
     */
    public function isCronDue(?\DateTimeInterface $now = null): bool
    {
        if (! $this->isRecurring()) {
            return false;
        }

        $now ??= now();

        if (! CronExpression::isValidExpression($this->cron_expression)) {
            return false;
        }

        if ($this->last_run_at && $this->last_run_at->gte(now()->startOfMinute())) {
            return false;
        }

        return (new CronExpression($this->cron_expression))->isDue($now);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function runs(): HasMany
    {
        return $this->hasMany(TaskRun::class)->orderByDesc('id');
    }
}
