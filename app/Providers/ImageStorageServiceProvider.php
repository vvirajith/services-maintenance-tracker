<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Contracts\ImageStorageInterface;
use App\Services\ImageStorage\LocalImageStorage;

class ImageStorageServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ImageStorageInterface::class, LocalImageStorage::class);
    }

    public function boot(): void
    {
        //
    }
}
