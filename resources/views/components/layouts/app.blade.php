<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>{{ $title ?? 'Bakpao Serdam'}}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

</head>

<body class="bg-slate-200 dark:bg-slate-700">

    <livewire:partials.navbar />

    <main>
        {{ $slot }}
    </main>

    <livewire:partials.footer />
    @livewireScripts

    {{-- <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <livewire-alert::scripts /> --}}


</body>

</html>