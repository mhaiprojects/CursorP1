<?php

namespace Tests\Feature\Console;

use App\Models\ChatMessage;
use App\Services\Cursor\Contracts\CursorAgent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConsoleChatTest extends TestCase
{
    use RefreshDatabase;

    public function test_sending_a_message_stores_user_and_assistant_messages(): void
    {
        app(CursorAgent::class)->pushResponse('Hi there, how can I help?');

        $response = $this->postJson('/console/chat', [
            'message' => 'Hello Cursor',
        ]);

        $response->assertCreated()
            ->assertJsonPath('user.role', ChatMessage::ROLE_USER)
            ->assertJsonPath('user.content', 'Hello Cursor')
            ->assertJsonPath('assistant.role', ChatMessage::ROLE_ASSISTANT)
            ->assertJsonPath('assistant.content', 'Hi there, how can I help?');

        $this->assertDatabaseCount('chat_messages', 2);
    }

    public function test_a_failed_agent_response_is_flagged(): void
    {
        app(CursorAgent::class)->failNext();

        $this->postJson('/console/chat', ['message' => 'break please'])
            ->assertCreated()
            ->assertJsonPath('assistant.failed', true);
    }

    public function test_it_validates_the_chat_message(): void
    {
        $this->postJson('/console/chat', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['message']);
    }

    public function test_chat_can_be_cleared(): void
    {
        ChatMessage::create(['conversation' => 'default', 'role' => 'user', 'content' => 'hi']);

        $this->postJson('/console/chat/clear')->assertOk();

        $this->assertDatabaseCount('chat_messages', 0);
    }
}
