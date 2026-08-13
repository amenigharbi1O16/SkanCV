<?php

namespace App\Providers;

use App\Services\FastApi\FakeFastApiClient;
use App\Services\FastApi\FastApiClientInterface;
use App\Services\FastApi\RealFastApiClient;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(FastApiClientInterface::class, function () {
            return match (config('services.fastapi.mode', 'fake')) {
                'real' => new RealFastApiClient(config('services.fastapi.url')),
                default => new FakeFastApiClient(),
            };
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
