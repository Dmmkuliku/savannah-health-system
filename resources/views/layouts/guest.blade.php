<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Savannah Health System') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=fraunces:500,600,700|plus-jakarta-sans:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans text-ink-800 antialiased">
<div class="relative min-h-screen overflow-hidden">
    <div class="absolute inset-0 bg-brand-950"></div>
    <div class="absolute inset-0 opacity-50"
         style="background-image:
            radial-gradient(circle at 18% 20%, rgba(208,240,192,0.35), transparent 38%),
            radial-gradient(circle at 82% 12%, rgba(90,168,61,0.28), transparent 42%),
            linear-gradient(135deg, rgba(19,38,14,0.96), rgba(47,84,35,0.92));"></div>
    <div class="absolute inset-0 bg-clinic-grid bg-[length:24px_24px] opacity-30"></div>

    <div class="relative z-10 grid min-h-screen lg:grid-cols-2">
        <section class="flex flex-col justify-between px-8 py-10 text-white sm:px-14 lg:py-16">
            <div class="animate-slide-in">
                <p class="text-xs font-semibold uppercase tracking-[0.28em] text-brand-200">United Republic of Tanzania</p>
                <h1 class="mt-4 font-display text-5xl leading-none tracking-tight sm:text-6xl">Savannah Health System</h1>
                <p class="mt-4 max-w-md text-base text-brand-100/90">
                    Hospital management for Tanzanian facilities — registration, clinical care, laboratory, pharmacy, wards, NHIF & cash billing.
                </p>
            </div>
            <div class="mt-10 hidden animate-rise space-y-3 text-sm text-brand-100/80 lg:block" style="animation-delay:0.2s">
                <p>Electronic Medical Records · OPD / IPD · RCH · Maternity · Theatre</p>
                <p>Billing in TZS · Exemptions · NHIF · MTUHA-ready reports</p>
            </div>
        </section>

        <section class="flex items-center justify-center px-6 py-10 sm:px-10">
            <div class="animate-rise w-full max-w-md rounded-3xl border border-white/20 bg-white/95 p-8 shadow-soft backdrop-blur">
                {{ $slot }}
            </div>
        </section>
    </div>
</div>
</body>
</html>
