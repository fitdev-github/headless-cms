<?php

namespace App\Providers;

use App\Services\ApiQueryBuilder;
use App\Services\ApiResponseFormatter;
use App\Services\EntryService;
use App\Services\FieldRenderer;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(EntryService::class);
        $this->app->singleton(FieldRenderer::class);
        $this->app->singleton(ApiQueryBuilder::class);
        $this->app->singleton(ApiResponseFormatter::class, function ($app) {
            return new ApiResponseFormatter($app->make(EntryService::class));
        });
    }

    public function boot()
    {
        //
    }
}
