<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#1c7d5c">
    <title>{{ config('app.name', 'Savannah Health System') }} · Staff Login</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <script>
        (function () {
            try {
                var mode = localStorage.getItem('shs-theme') || 'light';
                if (mode === 'dark') document.documentElement.classList.add('dark');
            } catch (e) {}
        })();
    </script>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=fraunces:500,600,700|plus-jakarta-sans:400,500,600,700&display=swap" rel="stylesheet" />
    <style>[x-cloak]{display:none!important}</style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans text-ink-900 antialiased dark:text-ink-50" x-data>
<div class="relative min-h-screen overflow-hidden savannah-horizon">
    <div class="pointer-events-none absolute inset-x-0 bottom-0 z-0 opacity-90">
        <x-savannah-scene class="h-auto w-full max-h-[52vh] object-cover object-bottom" />
    </div>
    <div class="absolute inset-0 bg-clinic-grid bg-[length:22px_22px] opacity-20"></div>

    <div class="absolute right-4 top-4 z-20 sm:right-8 sm:top-6">
        <x-theme-toggle />
    </div>

    <div class="relative z-10 grid min-h-screen lg:grid-cols-2">
        <section class="flex flex-col justify-between px-8 py-10 text-white sm:px-14 lg:py-14">
            <div class="animate-slide-in">
                <div class="flex items-center gap-3">
                    <x-savannah-mark class="h-14 w-14 drop-shadow-md" />
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.26em] text-brand-100">United Republic of Tanzania</p>
                        <p class="mt-1 text-sm font-semibold text-white">Facility workstation login</p>
                    </div>
                </div>
                <h1 class="mt-8 font-display text-5xl leading-none tracking-tight text-white sm:text-6xl">Savannah Health System</h1>
                <p class="mt-4 max-w-md text-base font-medium leading-relaxed text-brand-50">
                    Hospital information system for registration, clinical care, pharmacy, wards, NHIF and cash billing — ready for office laptops on the hospital LAN.
                </p>
            </div>
            <div class="mt-10 hidden animate-rise space-y-2 text-sm font-semibold text-brand-50 lg:block" style="animation-delay:.15s">
                <p class="flex items-center gap-2"><span class="h-2 w-2 rounded-full bg-brand-300"></span> EMR · OPD queue · IPD wards · RCH · Maternity · Theatre</p>
                <p class="flex items-center gap-2"><span class="h-2 w-2 rounded-full bg-brand-300"></span> Lab · Radiology · Pharmacy · Billing (TZS) · MTUHA reports</p>
                <p class="flex items-center gap-2"><span class="h-2 w-2 rounded-full bg-brand-300"></span> Admin registers staff after first install</p>
            </div>
        </section>

        <section class="flex items-center justify-center px-6 py-10 sm:px-10">
            <div class="animate-rise w-full max-w-md rounded-2xl border-2 border-white/40 bg-white p-8 text-ink-900 shadow-soft dark:border-ink-600 dark:bg-ink-800 dark:text-ink-50">
                {{ $slot }}
            </div>
        </section>
    </div>
</div>
</body>
</html>
