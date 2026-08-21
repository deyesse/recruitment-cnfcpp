<?php

namespace App\Http\Middleware;

use App\Filament\Pages\TwoFactorChallenge;
use App\Filament\Pages\TwoFactorSetup;
use Closure;
use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTwoFactorAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Filament::auth()->user();

        if (! $user) {
            return $next($request);
        }

        // Allow 2FA setup, challenge, and logout endpoints to be accessed without looping
        if ($request->is('admin/two-factor-setup', 'admin/two-factor-challenge', 'admin/logout') ||
            $request->routeIs('filament.admin.pages.two-factor-setup', 'filament.admin.pages.two-factor-challenge', 'filament.admin.auth.logout')) {
            return $next($request);
        }

        if ($user->hasTwoFactorEnabled()) {
            if (! session('two_factor_authenticated', false)) {
                return redirect(TwoFactorChallenge::getUrl());
            }
        } else {
            return redirect(TwoFactorSetup::getUrl());
        }

        return $next($request);
    }
}
