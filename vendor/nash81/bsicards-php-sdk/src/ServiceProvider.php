<?php

namespace BSICards;

/**
 * Laravel Service Provider for BSICARDS SDK
 *
 * This provider is only loaded if Laravel/Illuminate is installed.
 * It's optional and the SDK works without it.
 */
if (class_exists('Illuminate\Support\ServiceProvider')) {
    /**
     * @psalm-suppress InvalidStringClass
     */
    class ServiceProvider extends \Illuminate\Support\ServiceProvider
    {
        /**
         * Register services
         *
         * @return void
         */
        public function register(): void
        {
            $this->app->singleton(BSICardsClient::class, function ($app) {
                $publicKey = config('bsicards.public_key') ?? env('BSICARDS_PUBLIC_KEY');
                $secretKey = config('bsicards.secret_key') ?? env('BSICARDS_SECRET_KEY');

                return new BSICardsClient($publicKey, $secretKey);
            });
        }

        /**
         * Boot services
         *
         * @return void
         */
        public function boot(): void
        {
            $this->publishes([
                __DIR__ . '/../config/bsicards.php' => config_path('bsicards.php'),
            ], 'bsicards-config');
        }
    }
} else {
    /**
     * Stub class if Laravel is not installed
     * Allows the SDK to work without Laravel
     */
    class ServiceProvider
    {
        // Laravel not available - stub implementation
    }
}

