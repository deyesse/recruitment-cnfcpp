<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

class TwoFactorAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_without_2fa_is_redirected_to_2fa_setup(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_admin' => true,
            'two_factor_secret' => null,
            'two_factor_confirmed_at' => null,
        ]);

        $response = $this->actingAs($admin)->get('/admin');

        $response->assertRedirect('/admin/two-factor-setup');
    }

    public function test_admin_with_2fa_unauthenticated_session_is_redirected_to_2fa_challenge(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_admin' => true,
            'two_factor_secret' => (new Google2FA())->generateSecretKey(),
            'two_factor_confirmed_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get('/admin');

        $response->assertRedirect('/admin/two-factor-challenge');
    }

    public function test_admin_with_2fa_passed_session_can_access_dashboard(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_admin' => true,
            'two_factor_secret' => (new Google2FA())->generateSecretKey(),
            'two_factor_confirmed_at' => now(),
        ]);

        $response = $this->actingAs($admin)
            ->withSession(['two_factor_authenticated' => true])
            ->get('/admin');

        $response->assertStatus(200);
    }
}
