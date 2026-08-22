<?php

namespace Tests\Feature;

use App\Models\User;
use Filament\Auth\MultiFactor\App\Contracts\HasAppAuthentication;
use Filament\Auth\MultiFactor\App\Contracts\HasAppAuthenticationRecovery;
use Filament\Auth\MultiFactor\Email\Contracts\HasEmailAuthentication;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TwoFactorAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_implements_mfa_contracts(): void
    {
        $user = User::factory()->create();

        $this->assertInstanceOf(HasAppAuthentication::class, $user);
        $this->assertInstanceOf(HasAppAuthenticationRecovery::class, $user);
        $this->assertInstanceOf(HasEmailAuthentication::class, $user);
    }

    public function test_user_can_save_and_retrieve_app_authentication_secret(): void
    {
        $user = User::factory()->create();

        $this->assertNull($user->getAppAuthenticationSecret());

        $user->saveAppAuthenticationSecret('TESTSECRET123');
        $user->refresh();

        $this->assertEquals('TESTSECRET123', $user->getAppAuthenticationSecret());
        $this->assertEquals($user->email, $user->getAppAuthenticationHolderName());
    }

    public function test_user_can_save_and_retrieve_recovery_codes(): void
    {
        $user = User::factory()->create();

        $this->assertNull($user->getAppAuthenticationRecoveryCodes());

        $codes = ['code1-12345', 'code2-67890'];
        $user->saveAppAuthenticationRecoveryCodes($codes);
        $user->refresh();

        $this->assertEquals($codes, $user->getAppAuthenticationRecoveryCodes());
    }

    public function test_user_can_toggle_email_authentication(): void
    {
        $user = User::factory()->create();

        $this->assertFalse($user->hasEmailAuthentication());

        $user->toggleEmailAuthentication(true);
        $user->refresh();

        $this->assertTrue($user->hasEmailAuthentication());
    }

    public function test_admin_can_access_admin_panel(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_admin' => true,
        ]);

        $response = $this->actingAs($admin)->get('/admin');

        $response->assertStatus(200);
    }
}

