<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', __('hospital.nav.dashboard')) · {{ config('app.name', 'Savannah Health System') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=fraunces:500,600,700|plus-jakarta-sans:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans">
@php
    $user = auth()->user();
    $nav = [
        ['route' => 'dashboard', 'label' => __('hospital.nav.dashboard'), 'roles' => []],
        ['route' => 'patients.index', 'label' => __('hospital.nav.patients'), 'roles' => ['admin','receptionist','doctor','nurse','records']],
        ['route' => 'visits.index', 'label' => __('hospital.nav.visits'), 'roles' => ['admin','receptionist','doctor','nurse']],
        ['route' => 'admissions.index', 'label' => __('hospital.nav.admissions'), 'roles' => ['admin','doctor','nurse']],
        ['route' => 'rch.index', 'label' => __('hospital.nav.rch'), 'roles' => ['admin','doctor','nurse','receptionist']],
        ['route' => 'maternity.index', 'label' => __('hospital.nav.maternity'), 'roles' => ['admin','doctor','nurse','receptionist']],
        ['route' => 'theatre.index', 'label' => __('hospital.nav.theatre'), 'roles' => ['admin','doctor','nurse']],
        ['route' => 'blood.index', 'label' => __('hospital.nav.blood_bank'), 'roles' => ['admin','lab_technician','doctor','nurse']],
        ['route' => 'lab.orders.index', 'label' => __('hospital.nav.laboratory'), 'roles' => ['admin','lab_technician','doctor']],
        ['route' => 'radiology.orders.index', 'label' => __('hospital.nav.radiology'), 'roles' => ['admin','radiologist','doctor']],
        ['route' => 'pharmacy.index', 'label' => __('hospital.nav.pharmacy'), 'roles' => ['admin','pharmacist','doctor']],
        ['route' => 'medicines.index', 'label' => __('hospital.nav.inventory'), 'roles' => ['admin','pharmacist']],
        ['route' => 'billing.index', 'label' => __('hospital.nav.billing'), 'roles' => ['admin','cashier','receptionist']],
        ['route' => 'nhif.claims.index', 'label' => __('hospital.nav.nhif_claims'), 'roles' => ['admin','cashier','receptionist']],
        ['route' => 'appointments.index', 'label' => __('hospital.nav.appointments'), 'roles' => ['admin','receptionist','doctor']],
        ['route' => 'departments.index', 'label' => __('hospital.nav.departments'), 'roles' => ['admin','records']],
        ['route' => 'reports.index', 'label' => __('hospital.nav.reports'), 'roles' => ['admin','records','cashier']],
        ['route' => 'users.index', 'label' => __('hospital.nav.users'), 'roles' => ['admin']],
    ];
@endphp
<div class="mp-shell">
    <aside class="mp-sidebar animate-slide-in hidden lg:flex">
        <div class="border-b border-white/10 px-6 py-6">
            <p class="font-display text-2xl tracking-tight text-white">Savannah Health</p>
            <p class="mt-1 text-xs uppercase tracking-[0.2em] text-brand-200">{{ __('hospital.common.tanzania') }}</p>
        </div>
        <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4">
            @foreach($nav as $item)
                @if(empty($item['roles']) || $user->hasRole(...$item['roles']) || $user->isAdmin())
                    <a href="{{ route($item['route']) }}"
                       class="mp-nav-link {{ request()->routeIs(str_replace('.index','.*', $item['route'])) || request()->routeIs($item['route']) ? 'active' : '' }}">
                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-white/5 text-xs font-bold">{{ strtoupper(\Illuminate\Support\Str::substr($item['label'],0,2)) }}</span>
                        <span>{{ $item['label'] }}</span>
                    </a>
                @endif
            @endforeach
        </nav>
        <div class="border-t border-white/10 p-4 space-y-3">
            <div class="flex gap-2">
                <a href="{{ route('locale.switch', 'en') }}" class="flex-1 rounded-lg px-2 py-1.5 text-center text-xs font-semibold {{ app()->getLocale() === 'en' ? 'bg-sand-400/30 text-sand-100' : 'bg-white/5 text-brand-100 hover:bg-white/10' }}">EN</a>
                <a href="{{ route('locale.switch', 'sw') }}" class="flex-1 rounded-lg px-2 py-1.5 text-center text-xs font-semibold {{ app()->getLocale() === 'sw' ? 'bg-sand-400/30 text-sand-100' : 'bg-white/5 text-brand-100 hover:bg-white/10' }}">SW</a>
            </div>
            <div class="rounded-xl bg-white/5 p-3">
                <p class="text-sm font-semibold text-white">{{ $user->name }}</p>
                <p class="text-xs capitalize text-brand-200">{{ str_replace('_', ' ', $user->role) }}</p>
                <form method="POST" action="{{ route('logout') }}" class="mt-3">
                    @csrf
                    <button class="text-xs font-semibold text-sand-300 hover:text-white">{{ __('hospital.nav.sign_out') }}</button>
                </form>
            </div>
        </div>
    </aside>

    <div class="lg:pl-72">
        <header class="sticky top-0 z-30 border-b border-brand-900/5 bg-white/70 backdrop-blur">
            <div class="flex items-center justify-between gap-4 px-4 py-4 sm:px-8">
                <div class="flex items-center gap-3">
                    <details class="relative lg:hidden">
                        <summary class="cursor-pointer list-none rounded-xl bg-brand-700 px-3 py-2 text-sm font-semibold text-white">{{ __('hospital.nav.menu') }}</summary>
                        <div class="absolute left-0 z-50 mt-2 w-64 rounded-2xl border border-brand-100 bg-white p-2 shadow-soft">
                            @foreach($nav as $item)
                                @if(empty($item['roles']) || $user->hasRole(...$item['roles']) || $user->isAdmin())
                                    <a href="{{ route($item['route']) }}" class="block rounded-lg px-3 py-2 text-sm text-ink-800 hover:bg-brand-50">{{ $item['label'] }}</a>
                                @endif
                            @endforeach
                            <div class="mt-2 flex gap-2 border-t border-brand-50 px-2 pt-2">
                                <a href="{{ route('locale.switch', 'en') }}" class="text-xs font-semibold text-brand-700">EN</a>
                                <a href="{{ route('locale.switch', 'sw') }}" class="text-xs font-semibold text-brand-700">SW</a>
                            </div>
                        </div>
                    </details>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-brand-600">@yield('eyebrow', __('hospital.common.hospital_ops'))</p>
                        <h1 class="font-display text-2xl text-ink-900 sm:text-3xl">@yield('heading', __('hospital.nav.dashboard'))</h1>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <div class="hidden rounded-xl bg-brand-50 px-3 py-2 text-right text-xs text-brand-800 sm:block">
                        <div class="font-semibold">{{ now()->format('d M Y') }}</div>
                        <div>{{ now()->format('H:i') }} · TZS</div>
                    </div>
                    @yield('actions')
                </div>
            </div>
        </header>

        <main class="px-4 py-6 sm:px-8">
            @if(session('success'))
                <div class="mb-4 animate-rise rounded-xl border border-brand-200 bg-brand-50 px-4 py-3 text-sm text-brand-800">
                    {{ session('success') }}
                </div>
            @endif
            @if($errors->any())
                <div class="mb-4 animate-rise rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
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
</body>
</html>
