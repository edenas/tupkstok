<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class AdminLoginRateLimitTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_login_blocks_ip_after_three_failed_attempts(): void
    {
        for ($attempt = 0; $attempt < 3; $attempt++) {
            $this
                ->from('/admin')
                ->post(route('login'), [
                    'username' => 'admin',
                    'password' => 'wrong-password',
                ])
                ->assertRedirect('/admin')
                ->assertSessionHasErrors('username');
        }

        $response = $this
            ->from('/admin')
            ->post(route('login'), [
                'username' => 'admin',
                'password' => 'wrong-password',
            ]);

        $response
            ->assertRedirect('/admin')
            ->assertSessionHasErrors([
                'username' => 'Prisijungimas laikinai užblokuotas, nes 3 kartus iš eilės buvo neteisingai įvestas vartotojo vardas arba slaptažodis. Bandykite dar kartą po 10 minučių.',
            ]);
    }

    public function test_successful_admin_login_resets_failed_attempts(): void
    {
        $administrator = User::factory()->create([
            'name' => 'admin',
            'password' => Hash::make('correct-password'),
            'role' => 'administrator',
        ]);

        $this
            ->post(route('login'), [
                'username' => $administrator->name,
                'password' => 'wrong-password',
            ])
            ->assertSessionHasErrors('username');

        $this
            ->post(route('login'), [
                'username' => $administrator->name,
                'password' => 'correct-password',
            ])
            ->assertRedirect('/admin');

        $this->assertSame(0, RateLimiter::attempts('admin-login-attempts:127.0.0.1'));
    }

    public function test_admin_login_allows_attempts_after_lockout_expires(): void
    {
        $this->travelTo(now());

        for ($attempt = 0; $attempt < 3; $attempt++) {
            $this->post(route('login'), [
                'username' => 'admin',
                'password' => 'wrong-password',
            ]);
        }

        $this->travel(10)->minutes();

        $this
            ->post(route('login'), [
                'username' => 'admin',
                'password' => 'wrong-password',
            ])
            ->assertSessionHasErrors([
                'username' => 'The provided credentials do not match our records.',
            ]);
    }

    protected function tearDown(): void
    {
        RateLimiter::clear('admin-login-attempts:127.0.0.1');

        parent::tearDown();
    }
}


