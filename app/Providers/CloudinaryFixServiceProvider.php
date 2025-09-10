<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Cloudinary\Cloudinary;

class CloudinaryFixServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(Cloudinary::class, function () {
            return new Cloudinary([
                'cloud' => [
                    'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
                    'api_key'    => env('CLOUDINARY_API_KEY'),
                    'api_secret' => env('CLOUDINARY_API_SECRET'),
                ],
                'url' => [
                    'secure' => filter_var(env('CLOUDINARY_SECURE', true), FILTER_VALIDATE_BOOL),
                ],
            ]);
        });
    }

    public function boot()
    {
        //
    }
}
