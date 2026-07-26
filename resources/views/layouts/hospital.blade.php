<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#1c7d5c">
    <title>@yield('title', __('hospital.nav.dashboard')) · {{ config('app.name', 'Savannah Health System') }}</title>
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
<body class="font-sans text-ink-900 dark:text-ink-50" x-data>
@php
    $user = auth()->user();
    $facilityName = \App\Support\Hospital::facilityName();
    $facilityCode = \App\Support\Hospital::facilityCode();
    $nav = [
        ['route' => 'dashboard', 'label' => __('hospital.nav.dashboard'), 'roles' => [], 'short' => 'DB'],
        ['route' => 'patients.index', 'label' => __('hospital.nav.patients'), 'roles' => ['admin','receptionist','doctor','nurse','records'], 'short' => 'PT'],
        ['route' => 'visits.index', 'label' => __('hospital.nav.visits'), 'roles' => ['admin','receptionist','doctor','nurse'], 'short' => 'OP'],
        ['route' => 'admissions.index', 'label' => __('hospital.nav.admissions'), 'roles' => ['admin','doctor','nurse'], 'short' => 'IP'],
        ['route' => 'rch.index', 'label' => __('hospital.nav.rch'), 'roles' => ['admin','doctor','nurse','receptionist'], 'short' => 'RC'],
        ['route' => 'maternity.index', 'label' => __('hospital.nav.maternity'), 'roles' => ['admin','doctor','nurse','receptionist'], 'short' => 'MT'],
        ['route' => 'theatre.index', 'label' => __('hospital.nav.theatre'), 'roles' => ['admin','doctor','nurse'], 'short' => 'OT'],
        ['route' => 'blood.index', 'label' => __('hospital.nav.blood_bank'), 'roles' => ['admin','lab_technician','doctor','nurse'], 'short' => 'BB'],
        ['route' => 'lab.orders.index', 'label' => __('hospital.nav.laboratory'), 'roles' => ['admin','lab_technician','doctor'], 'short' => 'LB'],
        ['route' => 'radiology.orders.index', 'label' => __('hospital.nav.radiology'), 'roles' => ['admin','radiologist','doctor'], 'short' => 'RD'],
        ['route' => 'pharmacy.index', 'label' => __('hospital.nav.pharmacy'), 'roles' => ['admin','pharmacist','doctor'], 'short' => 'PH'],
        ['route' => 'medicines.index', 'label' => __('hospital.nav.inventory'), 'roles' => ['admin','pharmacist'], 'short' => 'IN'],
        ['route' => 'billing.index', 'label' => __('hospital.nav.billing'), 'roles' => ['admin','cashier','receptionist'], 'short' => 'BL'],
        ['route' => 'nhif.claims.index', 'label' => __('hospital.nav.nhif_claims'), 'roles' => ['admin','cashier','receptionist'], 'short' => 'NH'],
        ['route' => 'appointments.index', 'label' => __('hospital.nav.appointments'), 'roles' => ['admin','receptionist','doctor'], 'short' => 'AP'],
        ['route' => 'departments.index', 'label' => __('hospital.nav.departments'), 'roles' => ['admin','records'], 'short' => 'DP'],
        ['route' => 'reports.index', 'label' => __('hospital.nav.reports'), 'roles' => ['admin','records','cashier'], 'short' => 'RP'],
        ['route' => 'users.index', 'label' => __('hospital.nav.users'), 'roles' => ['admin'], 'short' => 'ST'],
    ];
@endphp
<div class="mp-shell">
    <aside class="mp-sidebar animate-slide-in hidden lg:flex">
        <div class="border-b border-white/15 px-5 py-5">
            <div class="flex items-start gap-3">
                <x-savannah-mark class="h-11 w-11 shrink-0" />
                <div class="min-w-0">
                    <p class="font-display text-xl leading-tight tracking-tight text-white">Savannah Health</p>
                    <p class="mt-0.5 truncate text-xs font-medium text-brand-100">{{ $facilityName }}</p>
                    <p class="mt-1 text-[10px] font-bold uppercase tracking-[0.18em] text-brand-200">{{ $facilityCode }} · Tanzania</p>
                </div>
            </div>
        </div>
        <nav class="flex-1 space-y-0.5 overflow-y-auto px-2.5 py-3">
            @foreach($nav as $item)
                @if(empty($item['roles']) || $user->hasRole(...$item['roles']) || $user->isAdmin())
                    <a href="{{ route($item['route']) }}"
                       class="mp-nav-link {{ request()->routeIs(str_replace('.index','.*', $item['route'])) || request()->routeIs($item['route']) ? 'active' : '' }}">
                        <span class="inline-flex h-7 w-7 items-center justify-center rounded-md bg-white/10 text-[10px] font-bold tracking-wide text-white">{{ $item['short'] }}</span>
                        <span class="truncate">{{ $item['label'] }}</span>
                    </a>
                @endif
            @endforeach
        </nav>
        <div class="border-t border-white/15 p-3 space-y-2.5">
            <div class="flex gap-1.5">
                <a href="{{ route('locale.switch', 'en') }}" class="flex-1 rounded-md px-2 py-1.5 text-center text-xs font-bold {{ app()->getLocale() === 'en' ? 'bg-brand-300/30 text-white' : 'bg-white/10 text-brand-50 hover:bg-white/15' }}">EN</a>
                <a href="{{ route('locale.switch', 'sw') }}" class="flex-1 rounded-md px-2 py-1.5 text-center text-xs font-bold {{ app()->getLocale() === 'sw' ? 'bg-brand-300/30 text-white' : 'bg-white/10 text-brand-50 hover:bg-white/15' }}">SW</a>
            </div>
            <div class="rounded-lg bg-white/10 p-3">
                <p class="text-sm font-bold text-white">{{ $user->name }}</p>
                <p class="text-xs font-medium capitalize text-brand-100">{{ str_replace('_', ' ', $user->role) }}@if($user->employee_no) · {{ $user->employee_no }}@endif</p>
                <form method="POST" action="{{ route('logout') }}" class="mt-2.5">
                    @csrf
                    <button class="text-xs font-bold text-brand-200 hover:text-white">{{ __('hospital.nav.sign_out') }}</button>
                </form>
            </div>
        </div>
    </aside>

    <div class="lg:pl-72">
        <header class="mp-header sticky top-0 z-30">
            <div class="flex items-center justify-between gap-4 px-4 py-3 sm:px-6">
                <div class="flex items-center gap-3">
                    <details class="relative lg:hidden">
                        <summary class="cursor-pointer list-none rounded-lg bg-brand-700 px-3 py-2 text-sm font-bold text-white">{{ __('hospital.nav.menu') }}</summary>
                        <div class="absolute left-0 z-50 mt-2 max-h-[70vh] w-64 overflow-y-auto rounded-xl border-2 border-ink-200 bg-white p-2 shadow-soft dark:border-ink-600 dark:bg-ink-800">
                            @foreach($nav as $item)
                                @if(empty($item['roles']) || $user->hasRole(...$item['roles']) || $user->isAdmin())
                                    <a href="{{ route($item['route']) }}" class="block rounded-md px-3 py-2 text-sm font-semibold text-ink-900 hover:bg-brand-50 dark:text-ink-50 dark:hover:bg-ink-700">{{ $item['label'] }}</a>
                                @endif
                            @endforeach
                            <div class="mt-2 flex gap-2 border-t border-ink-200 px-2 pt-2 dark:border-ink-600">
                                <a href="{{ route('locale.switch', 'en') }}" class="text-xs font-bold text-brand-800 dark:text-brand-300">EN</a>
                                <a href="{{ route('locale.switch', 'sw') }}" class="text-xs font-bold text-brand-800 dark:text-brand-300">SW</a>
                            </div>
                        </div>
                    </details>
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-brand-700 dark:text-brand-300">@yield('eyebrow', __('hospital.common.hospital_ops'))</p>
                        <h1 class="font-display text-xl text-ink-900 dark:text-white sm:text-2xl">@yield('heading', __('hospital.nav.dashboard'))</h1>
                    </div>
                </div>
                <div class="flex items-center gap-2 sm:gap-3">
                    <x-theme-toggle />
                    <div class="hidden rounded-lg border-2 border-ink-200 bg-brand-50 px-3 py-1.5 text-right text-xs text-ink-900 dark:border-ink-600 dark:bg-ink-800 dark:text-ink-50 sm:block">
                        <div class="font-bold" id="shs-clock-date">{{ now()->timezone(config('app.timezone'))->format('D, d M Y') }}</div>
                        <div class="font-semibold"><span id="shs-clock-time">{{ now()->timezone(config('app.timezone'))->format('H:i:s') }}</span> · TZS · {{ $facilityCode }}</div>
                    </div>
                    @yield('actions')
                </div>
            </div>
        </header>

        <main class="px-4 py-5 sm:px-6">
            @if(session('success'))
                <div class="mb-4 animate-rise rounded-lg border-2 border-brand-600 bg-brand-50 px-4 py-3 text-sm font-semibold text-brand-900 dark:border-brand-400 dark:bg-brand-950 dark:text-brand-100">
                    {{ session('success') }}
                </div>
            @endif
            @if($errors->any())
                <div class="mb-4 animate-rise rounded-lg border-2 border-red-600 bg-red-50 px-4 py-3 text-sm font-semibold text-red-900 dark:bg-red-950 dark:text-red-100">
                    <ul class="list-disc pl-4">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="animate-fade-in">
                @yield('content')
            </div>
        </main>
    </div>
</div>
<script>
    (function () {
        const dateEl = document.getElementById('shs-clock-date');
        const timeEl = document.getElementById('shs-clock-time');
        if (!dateEl || !timeEl) return;
        const tick = () => {
            const now = new Date();
            dateEl.textContent = now.toLocaleDateString(undefined, { weekday: 'short', day: '2-digit', month: 'short', year: 'numeric' });
            timeEl.textContent = now.toLocaleTimeString(undefined, { hour12: false });
        };
        setInterval(tick, 1000);
    })();
</script>
</body>
</html>
