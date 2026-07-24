<?php

namespace Tests\Feature\Console;

use App\Models\ChatMessage;
use App\Models\User;
use App\Services\Cursor\Contracts\CursorAgent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConsoleChatTest extends TestCase
{
    use RefreshDatabase;

    private function user(): User
    {
        return User::factory()->create();
    }

    public function test_chat_requires_authentication(): void
    {
        // The app only renders JSON errors for api/* routes, so unauthenticated
        // console requests are redirected to the login page.
        $this->postJson('/console/chat', ['message' => 'hi'])->assertRedirect();
    }

    public function test_sending_a_message_stores_user_and_assistant_messages(): void
    {
        app(CursorAgent::class)->pushResponse('Hi there, how can I help?');

        $response = $this->actingAs($this->user())->postJson('/console/chat', [
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

        $this->actingAs($this->user())->postJson('/console/chat', ['message' => 'break please'])
            ->assertCreated()
            ->assertJsonPath('assistant.failed', true);
    }

    public function test_it_validates_the_chat_message(): void
    {
        $this->actingAs($this->user())->postJson('/console/chat', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['message']);
    }

    public function test_chat_is_scoped_per_user(): void
    {
        $user = $this->user();
        $other = $this->user();
        ChatMessage::create(['user_id' => $other->id, 'conversation' => 'user:'.$other->id, 'role' => 'user', 'content' => 'theirs']);

        app(CursorAgent::class)->pushResponse('reply');
        $this->actingAs($user)->postJson('/console/chat', ['message' => 'mine']);

        $this->actingAs($user)->getJson('/console/state')
            ->assertJsonCount(2, 'messages'); // only this user's user+assistant messages
    }

    public function test_chat_can_be_cleared_for_the_user_only(): void
    {
        $user = $this->user();
        $other = $this->user();
        ChatMessage::create(['user_id' => $user->id, 'conversation' => 'user:'.$user->id, 'role' => 'user', 'content' => 'hi']);
        ChatMessage::create(['user_id' => $other->id, 'conversation' => 'user:'.$other->id, 'role' => 'user', 'content' => 'keep']);

        $this->actingAs($user)->postJson('/console/chat/clear')->assertOk();

        $this->assertDatabaseMissing('chat_messages', ['user_id' => $user->id]);
        $this->assertDatabaseHas('chat_messages', ['user_id' => $other->id]);
    }
}
