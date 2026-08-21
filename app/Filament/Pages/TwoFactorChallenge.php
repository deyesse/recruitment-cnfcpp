<?php

namespace App\Filament\Pages;

use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorChallenge extends Page
{
    protected string $view = 'filament.pages.two-factor-challenge';

    protected static ?string $slug = 'two-factor-challenge';

    public ?string $code = '';
    public bool $useRecoveryCode = false;

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public function getTitle(): string
    {
        return 'Vérification 2FA';
    }

    public function mount(): void
    {
        $user = Filament::auth()->user();

        if (! $user) {
            redirect()->route('filament.admin.auth.login');
            return;
        }

        if (! $user->hasTwoFactorEnabled()) {
            redirect(TwoFactorSetup::getUrl());
            return;
        }

        if (session('two_factor_authenticated', false)) {
            redirect(Filament::getUrl());
            return;
        }
    }

    public function toggleRecovery(): void
    {
        $this->useRecoveryCode = ! $this->useRecoveryCode;
        $this->code = '';
        $this->resetErrorBag();
    }

    public function verify(): void
    {
        $user = Filament::auth()->user();

        if (! $user) {
            return;
        }

        if ($this->useRecoveryCode) {
            $this->validate([
                'code' => ['required', 'string'],
            ], [
                'code.required' => 'Le code de secours est obligatoire.',
            ]);

            if ($user->useRecoveryCode(trim($this->code))) {
                session(['two_factor_authenticated' => true]);

                Notification::make()
                    ->title('Connexion réussie via le code de secours')
                    ->success()
                    ->send();

                redirect(Filament::getUrl());
                return;
            }

            Notification::make()
                ->title('Code de secours invalide')
                ->danger()
                ->send();
        } else {
            $this->validate([
                'code' => ['required', 'string', 'digits:6'],
            ], [
                'code.required' => 'Le code 2FA est obligatoire.',
                'code.digits' => 'Le code doit contenir 6 chiffres.',
            ]);

            $google2fa = new Google2FA();
            $valid = $google2fa->verifyKey($user->two_factor_secret, $this->code);

            if ($valid) {
                session(['two_factor_authenticated' => true]);

                redirect(Filament::getUrl());
                return;
            }

            Notification::make()
                ->title('Code de vérification 2FA invalide')
                ->body('Veuillez vérifier le code à 6 chiffres sur votre application Google Authenticator.')
                ->danger()
                ->send();
        }
    }
}
