<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    public const ROLE_USER = 'user';

    public const ROLE_ASSISTANT = 'assistant';

    public const ROLE_SYSTEM = 'system';

    protected $fillable = [
        'conversation',
        'role',
        'content',
        'failed',
        'meta',
    ];

    protected $casts = [
        'failed' => 'boolean',
        'meta' => 'array',
    ];
}
