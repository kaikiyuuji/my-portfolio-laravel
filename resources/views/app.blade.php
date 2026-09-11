<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title inertia>{{ config('app.name', 'Laravel') }}</title>

        <!-- Primary Meta Tags -->
        <meta name="description" content="Kaiki Hirata, desenvolvedor backend. Veja minhas experiências, projetos e stacks tecnológicas.">

        <!-- Open Graph / Facebook / WhatsApp -->
        <meta property="og:type" content="website">
        <meta property="og:url" content="https://kaikihirata.dev">
        <meta property="og:title" content="Kaiki Hirata - Backend Portfolio">
        <meta property="og:description" content="Conheça minhas experiências, projetos e stacks tecnológicas.">
        <meta property="og:image" content="https://kaikihirata.dev/link-card.png">

        <!-- Twitter -->
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:domain" content="kaikihirata.dev">
        <meta name="twitter:url" content="https://kaikihirata.dev">
        <meta name="twitter:title" content="Kaiki Hirata - Backend Portfolio">
        <meta name="twitter:description" content="Conheça minhas experiências, projetos e stacks tecnológicas.">
        <meta name="twitter:image" content="https://kaikihirata.dev/link-card.png">

        <link rel="icon" type="image/x-icon" href="/favicon.ico">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=ibm-plex-mono:400,500,600,700|space-grotesk:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @routes
        @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
