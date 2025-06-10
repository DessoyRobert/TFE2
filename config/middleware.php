<?php

use App\Http\Middleware\IsAdmin;
use App\Http\Middleware\Authenticate;

return [
    'aliases' => [
        'auth' => Authenticate::class,
        'is_admin' => IsAdmin::class,
    ],
];
