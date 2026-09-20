<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SICAM')</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/icons/logo-sin-fondo.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/2.3.4/css/dataTables.dataTables.min.css">

    <script src="https://cdn.datatables.net/2.3.4/js/dataTables.min.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-canvas font-sans text-ink antialiased">
    <div class="min-h-screen lg:flex">
        @include('includes.sidebar')
        <div class="min-w-0 flex-1">
            @include('includes.header')
            <main>@yield('content')</main>
            @include('includes.notifications')
        </div>
    </div>
</body>

</html>
