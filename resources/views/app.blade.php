<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title inertia>{{ config('app.name', 'Laravel') }}</title>

        <meta name="description" content="Learn PHP, object-oriented design, SOLID, and Laravel with hands-on lessons and quizzes.">
        <script>try { if (localStorage.getItem('academy-theme') === 'dark') document.documentElement.classList.add('dark'); } catch (e) {}</script>

        <!-- Scripts -->
        @routes
        @vite('resources/js/app.js')
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
