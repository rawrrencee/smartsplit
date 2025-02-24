<?php

use App\Providers\AppServiceProvider;
use App\Providers\RouteServiceProvider;
use JoelButcher\Socialstream\Features;
use JoelButcher\Socialstream\Providers;

return [
    'middleware' => ['web'],
    'prompt' => 'Or Login Via',
    'providers' => [
        Providers::google(),
    ],
    'features' => [
        // Features::generateMissingEmails(),
        Features::createAccountOnFirstLogin(),
        Features::rememberSession(),
        Features::providerAvatars(),
        Features::refreshOAuthTokens(),
    ],
    'home' => AppServiceProvider::HOME,
    'redirects' => [
        'login' => AppServiceProvider::HOME,
        'register' => AppServiceProvider::HOME,
        'login-failed' => '/login',
        'registration-failed' => '/register',
        'provider-linked' => '/user/profile',
        'provider-link-failed' => '/user/profile',
    ],
];
