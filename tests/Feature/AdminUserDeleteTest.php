<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUserDeleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_administrator_can_delete_another_user(): void
    {
        $administrator = User::factory()->create([
            'role' => 'administrator',
        ]);

        $user = User::factory()->create([
            'role' => 'user',
        ]);

        $response = $this
            ->actingAs($administrator)
            ->delete(route('admin.users.destroy', $user));

        $response
            ->assertRedirect(route('admin.users'))
            ->assertSessionHas('success', 'User deleted successfully.');

        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
        ]);
    }

    public function test_administrator_cannot_delete_their_own_account(): void
    {
        $administrator = User::factory()->create([
            'role' => 'administrator',
        ]);

        $response = $this
            ->actingAs($administrator)
            ->delete(route('admin.users.destroy', $administrator));

        $response
            ->assertRedirect(route('admin.users'))
            ->assertSessionHas('error', 'You cannot delete your own account.');

        $this->assertDatabaseHas('users', [
            'id' => $administrator->id,
        ]);
    }

    public function test_administrator_cannot_delete_the_last_administrator_account(): void
    {
        $administrator = User::factory()->create([
            'role' => 'administrator',
        ]);

        $otherUser = User::factory()->create([
            'role' => 'user',
        ]);

        $response = $this
            ->actingAs($otherUser)
            ->delete(route('admin.users.destroy', $administrator));

        $response->assertForbidden();

        $response = $this
            ->actingAs($administrator)
            ->delete(route('admin.users.destroy', $administrator));

        $response
            ->assertRedirect(route('admin.users'))
            ->assertSessionHas('error', 'You cannot delete your own account.');

        $this->assertDatabaseHas('users', [
            'id' => $administrator->id,
            'role' => 'administrator',
        ]);
    }

    public function test_non_administrator_cannot_delete_user(): void
    {
        $editor = User::factory()->create([
            'role' => 'editor',
        ]);

        $user = User::factory()->create([
            'role' => 'user',
        ]);

        $response = $this
            ->actingAs($editor)
            ->delete(route('admin.users.destroy', $user));

        $response->assertForbidden();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
        ]);
    }
}
