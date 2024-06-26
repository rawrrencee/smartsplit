<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" style="background-color: #374151;">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="description" content="Smartsplit - Expense sharing simplified" />
    <link rel="icon" href="/favicon.ico" />
    <link rel="apple-touch-icon" href="/img/icons/apple-touch-icon.png" sizes="180x180" />
    <link rel="mask-icon" href="/img/icons/safari-pinned-tab.svg" color="#fafafa" />
    <meta name="theme-color" content="#fafafa" />

    <title inertia>{{ config('app.name', 'Smartsplit') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @routes
    @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
    @inertiaHead
</head>

<body class="font-sans antialiased">
    @inertia
</body>

</html>