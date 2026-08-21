<x-filament-panels::page>
    <div class="max-w-md mx-auto p-6 bg-white dark:bg-gray-800 rounded-xl shadow-md border border-gray-200 dark:border-gray-700">
        <div class="space-y-6">
            <div class="text-center">
                <div class="mx-auto w-12 h-12 bg-primary-100 dark:bg-primary-950 flex items-center justify-center rounded-full text-primary-600 dark:text-primary-400 mb-3">
                    <x-heroicon-o-shield-check class="w-7 h-7" />
                </div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                    Double Authentification (2FA)
                </h2>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                    @if($useRecoveryCode)
                        Saisissez un de vos codes de secours à usage unique.
                    @else
                        Veuillez entrer le code à 6 chiffres fourni par votre application Authenticator.
                    @endif
                </p>
            </div>

            <form wire:submit.prevent="verify" class="space-y-4">
                <div>
                    @if($useRecoveryCode)
                        <label for="code" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Code de secours
                        </label>
                        <input
                            type="text"
                            id="code"
                            wire:model="code"
                            placeholder="xxxxxx-xxxxxx"
                            autocomplete="off"
                            class="mt-2 block w-full text-center font-mono px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-primary-500 focus:border-primary-500"
                        />
                    @else
                        <label for="code" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Code d'authentification (6 chiffres)
                        </label>
                        <input
                            type="text"
                            id="code"
                            wire:model="code"
                            maxlength="6"
                            placeholder="000000"
                            autocomplete="off"
                            autofocus
                            class="mt-2 block w-full text-center tracking-widest text-2xl font-mono px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-primary-500 focus:border-primary-500"
                        />
                    @endif

                    @error('code')
                        <p class="mt-1 text-xs text-red-600 dark:text-red-400 text-center">{{ $message }}</p>
                    @enderror
                </div>

                <x-filament::button type="submit" size="lg" class="w-full">
                    Valider et Se Connecter
                </x-filament::button>

                <div class="text-center pt-2">
                    <button
                        type="button"
                        wire:click="toggleRecovery"
                        class="text-xs font-medium text-primary-600 dark:text-primary-400 hover:underline"
                    >
                        @if($useRecoveryCode)
                            Utiliser le code Google Authenticator à 6 chiffres
                        @else
                            Utiliser un code de secours
                        @endif
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-filament-panels::page>
