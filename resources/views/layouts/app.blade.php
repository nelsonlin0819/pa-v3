<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'nelsonlys.com')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/react/app.jsx'])
</head>
<body class="min-h-screen bg-slate-950 font-sans text-slate-100 antialiased">
    @yield('content')
    <footer class="pb-8 text-center text-xs text-slate-500">
        {{ config('app.name') }} · Laravel {{ app()->version() }} · {{ now()->format('Y') }}
    </footer>
</body>
</html>
