<x-filament-panels::page>
    <div class="max-w-2xl mx-auto p-6 bg-white dark:bg-gray-800 rounded-xl shadow-md border border-gray-200 dark:border-gray-700">
        @if(! $isConfirmed)
            <div class="space-y-6">
                <div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                        Activer l'Authentification à Deux Facteurs (2FA)
                    </h2>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                        Pour sécuriser l'accès au panneau d'administration, veuillez associer votre compte avec une application de double authentification (Google Authenticator, Authy, Microsoft Authenticator, etc.).
                    </p>
                </div>

                <div class="flex flex-col md:flex-row items-center gap-6 p-4 bg-gray-50 dark:bg-gray-900 rounded-lg border border-gray-100 dark:border-gray-800">
                    <div class="p-2 bg-white rounded-lg shadow-sm">
                        {!! $qrCodeSvg !!}
                    </div>
                    <div class="space-y-3">
                        <h3 class="font-semibold text-gray-800 dark:text-gray-200">
                            1. Scannez le QR Code
                        </h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            Ouvrez votre application d'authentification et scannez ce QR Code.
                        </p>
                        <div class="pt-2">
                            <p class="text-xs font-medium text-gray-700 dark:text-gray-300">
                                Clé de configuration manuelle :
                            </p>
                            <code class="mt-1 inline-block px-2 py-1 bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded text-xs font-mono select-all">
                                {{ $secret }}
                            </code>
                        </div>
                    </div>
                </div>

                <form wire:submit.prevent="confirm" class="space-y-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <div>
                        <label for="code" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            2. Entrez le code à 6 chiffres généré par l'application
                        </label>
                        <input
                            type="text"
                            id="code"
                            wire:model="code"
                            maxlength="6"
                            placeholder="000000"
                            autocomplete="off"
                            class="mt-2 block w-full text-center tracking-widest text-2xl font-mono px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-primary-500 focus:border-primary-500"
                        />
                        @error('code')
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-end">
                        <x-filament::button type="submit" size="lg">
                            Vérifier et Activer 2FA
                        </x-filament::button>
                    </div>
                </form>
            </div>
        @else
            <div class="space-y-6">
                <div class="p-4 bg-green-50 dark:bg-green-950/40 border border-green-200 dark:border-green-800 rounded-lg flex items-center gap-3">
                    <x-heroicon-o-check-circle class="w-8 h-8 text-green-600 dark:text-green-400" />
                    <div>
                        <h3 class="font-bold text-green-900 dark:text-green-200">
                            Authentification 2FA activée avec succès !
                        </h3>
                        <p class="text-sm text-green-700 dark:text-green-300">
                            Votre compte administrateur est désormais sécurisé par Google Authenticator.
                        </p>
                    </div>
                </div>

                <div class="p-4 bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 rounded-lg space-y-3">
                    <h4 class="font-semibold text-amber-900 dark:text-amber-200 text-sm">
                        ⚠️ Codes de secours à conserver précieusement :
                    </h4>
                    <p class="text-xs text-amber-800 dark:text-amber-300">
                        Si vous perdez l'accès à votre application Google Authenticator, ces codes vous permettront de vous connecter. Chaque code ne peut être utilisé qu'une seule fois.
                    </p>

                    <div class="grid grid-cols-2 gap-2 pt-2 font-mono text-sm">
                        @foreach($recoveryCodes as $codeItem)
                            <div class="px-3 py-1.5 bg-white dark:bg-gray-900 border border-amber-200 dark:border-amber-700/50 rounded text-center text-gray-800 dark:text-gray-200 select-all">
                                {{ $codeItem }}
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="flex justify-end pt-4">
                    <x-filament::button wire:click="finish" size="lg" color="success">
                        Accéder au tableau de bord
                    </x-filament::button>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
