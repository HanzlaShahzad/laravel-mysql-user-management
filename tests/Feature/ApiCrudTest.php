<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_be_created(): void
    {
        $response = $this->postJson('/api/user/post', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('user.name', 'Test User')
            ->assertJsonMissingPath('user.password');

        $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
    }

    public function test_post_can_be_created_for_a_user(): void
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/post/post', [
            'title' => 'Test Post',
            'description' => 'A description',
            'user_id' => $user->id,
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('post.title', 'Test Post')
            ->assertJsonPath('post.user_id', $user->id);

        $this->assertDatabaseHas('posts', [
            'title' => 'Test Post',
            'user_id' => $user->id,
        ]);
    }

    public function test_posts_can_be_retrieved_with_their_users(): void
    {
        config(['cache.default' => 'database']);

        $user = User::factory()->create();
        $user->posts()->create([
            'title' => 'Test Post',
            'description' => 'A description',
        ]);

        $response = $this->getJson('/api/post/get');

        $response
            ->assertOk()
            ->assertJsonPath('msg', 'Posts Get Successfully')
            ->assertJsonPath('posts.0.title', 'Test Post')
            ->assertJsonPath('posts.0.user.id', $user->id);

        $this->getJson('/api/post/get')
            ->assertOk()
            ->assertJsonPath('posts.0.title', 'Test Post')
            ->assertJsonPath('posts.0.user.id', $user->id);
    }
}
