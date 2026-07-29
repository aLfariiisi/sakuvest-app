<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>SakuVest</title>

        <!-- Fonts: Plus Jakarta Sans -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

        <!-- Lucide Icons -->
        <script src="https://unpkg.com/lucide@latest"></script>

        <!-- ApexCharts -->
        <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

        <!-- Scripts & Styles (Tailwind / Vite) -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            html, body {
                font-family: 'Plus Jakarta Sans', sans-serif;
                width: 100%;
                max-width: 100%;
                overflow-x: hidden !important;
                margin: 0;
                padding: 0;
            }
            /* Paksa semua kotak, tabel, dan gambar agar mengecil menyesuaikan layar HP */
            * {
                box-sizing: border-box;
            }
            img, table, div, canvas {
                max-width: 100% !important;
            }
        </style>
    </head>
    <body class="bg-[#F8FAFC] text-[#0F172A] antialiased">
        <div class="min-h-screen flex flex-col w-full overflow-hidden">
            @isset($header)
                <header class="bg-white shadow w-full">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main class="flex-1 w-full overflow-x-hidden">
                {{ $slot }}
            </main>
        </div>

        <!-- Initialize Lucide Icons -->
        <script>
            lucide.createIcons();
        </script>
    </body>
</html>