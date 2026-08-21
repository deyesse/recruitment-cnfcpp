<?php

namespace App\Filament\Pages;

use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorSetup extends Page
{
    protected string $view = 'filament.pages.two-factor-setup';

    protected static ?string $slug = 'two-factor-setup';

    public ?string $code = '';
    public ?string $secret = null;
    public ?string $qrCodeSvg = null;
    public ?string $qrCodeImgUrl = null;
    public array $recoveryCodes = [];
    public bool $isConfirmed = false;

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public function getTitle(): string
    {
        return 'Configuration 2FA - Google Authenticator';
    }

    public function mount(): void
    {
        $user = Filament::auth()->user();

        if (! $user) {
            redirect()->route('filament.admin.auth.login');
            return;
        }

        if ($user->hasTwoFactorEnabled()) {
            redirect(Filament::getUrl());
            return;
        }

        $google2fa = new Google2FA();

        if (empty($user->two_factor_secret)) {
            $this->secret = $google2fa->generateSecretKey();
            $user->two_factor_secret = $this->secret;
            $user->save();
        } else {
            $this->secret = $user->two_factor_secret;
        }

        $otpauthUrl = $google2fa->getQRCodeUrl(
            config('app.name', 'CNFCPP Admin'),
            $user->email,
            $this->secret
        );

        if (class_exists(\BaconQrCode\Renderer\ImageRenderer::class)) {
            try {
                $renderer = new \BaconQrCode\Renderer\ImageRenderer(
                    new \BaconQrCode\Renderer\RendererStyle\RendererStyle(200),
                    new \BaconQrCode\Renderer\Image\SvgImageBackEnd()
                );
                $writer = new \BaconQrCode\Writer($renderer);
                $this->qrCodeSvg = $writer->writeString($otpauthUrl);
            } catch (\Throwable $e) {
                $this->qrCodeSvg = null;
            }
        }

        if (empty($this->qrCodeSvg)) {
            $this->qrCodeImgUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . urlencode($otpauthUrl);
        }
    }

    public function confirm(): void
    {
        $user = Filament::auth()->user();

        if (! $user) {
            return;
        }

        $this->validate([
            'code' => ['required', 'string', 'digits:6'],
        ], [
            'code.required' => 'Le code de vérification est obligatoire.',
            'code.digits' => 'Le code doit contenir exactement 6 chiffres.',
        ]);

        $google2fa = new Google2FA();

        $valid = $google2fa->verifyKey($user->two_factor_secret, $this->code);

        if ($valid) {
            $user->two_factor_confirmed_at = now();
            $this->recoveryCodes = $user->generateTwoFactorRecoveryCodes();
            $user->save();

            session(['two_factor_authenticated' => true]);
            $this->isConfirmed = true;

            Notification::make()
                ->title('Authentification à deux facteurs activée')
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('Code de vérification invalide')
                ->body('Le code saisie ne correspond pas à votre application Authenticator. Veuillez réespayer.')
                ->danger()
                ->send();
        }
    }

    public function finish(): void
    {
        redirect(Filament::getUrl());
    }
}
