<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUserRoleSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_administrator_cannot_remove_their_own_administrator_role(): void
    {
        $administrator = User::factory()->create([
            'role' => 'administrator',
        ]);

        $response = $this
            ->actingAs($administrator)
            ->put(route('admin.users.update', $administrator), [
                'name' => $administrator->name,
                'email' => $administrator->email,
                'role' => 'editor',
            ]);

        $response
            ->assertRedirect(route('admin.users'))
            ->assertSessionHas('error', 'Negalite pašalinti savo administratoriaus rolės.');

        $this->assertDatabaseHas('users', [
            'id' => $administrator->id,
            'role' => 'administrator',
        ]);
    }

    public function test_administrator_cannot_remove_the_last_administrator_role(): void
    {
        $administrator = User::factory()->create([
            'role' => 'administrator',
        ]);

        $editor = User::factory()->create([
            'role' => 'editor',
        ]);

        $response = $this
            ->actingAs($administrator)
            ->put(route('admin.users.update', $administrator), [
                'name' => $administrator->name,
                'email' => $administrator->email,
                'role' => 'user',
            ]);

        $response
            ->assertRedirect(route('admin.users'))
            ->assertSessionHas('error', 'Negalite pašalinti savo administratoriaus rolės.');

        $this->assertDatabaseHas('users', [
            'id' => $administrator->id,
            'role' => 'administrator',
        ]);

        $response = $this
            ->actingAs($administrator)
            ->put(route('admin.users.update', $editor), [
                'name' => $editor->name,
                'email' => $editor->email,
                'role' => 'user',
            ]);

        $response
            ->assertRedirect(route('admin.users'))
            ->assertSessionHas('success', 'Vartotojas atnaujintas sėkmingai.');
    }

    public function test_administrator_can_update_another_administrator_when_one_administrator_remains(): void
    {
        $administrator = User::factory()->create([
            'role' => 'administrator',
        ]);

        $secondAdministrator = User::factory()->create([
            'role' => 'administrator',
        ]);

        $response = $this
            ->actingAs($administrator)
            ->put(route('admin.users.update', $secondAdministrator), [
                'name' => $secondAdministrator->name,
                'email' => $secondAdministrator->email,
                'role' => 'editor',
            ]);

        $response
            ->assertRedirect(route('admin.users'))
            ->assertSessionHas('success', 'Vartotojas atnaujintas sėkmingai.');

        $this->assertDatabaseHas('users', [
            'id' => $secondAdministrator->id,
            'role' => 'editor',
        ]);

        $this->assertSame(1, User::query()->where('role', 'administrator')->count());
    }
}


