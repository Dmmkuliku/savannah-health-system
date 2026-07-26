@extends('layouts.hospital')

@section('title', __('hospital.dashboard.title'))
@section('eyebrow', __('hospital.dashboard.eyebrow'))
@section('heading', __('hospital.dashboard.title'))

@section('content')
<div class="mb-5 flex flex-wrap items-center justify-between gap-3 rounded-xl border border-brand-100 bg-white px-4 py-3 shadow-soft">
    <div>
        <p class="text-sm font-semibold text-ink-900">{{ $facilityName }}</p>
        <p class="mp-muted text-xs font-semibold">{{ __('hospital.dashboard.shift_ready') }} · {{ now()->format('l') }}</p>
    </div>
    <div class="flex flex-wrap gap-2">
        <span class="mp-badge bg-brand-100 text-brand-800">{{ __('hospital.dashboard.queue_badge', ['count' => $waitingQueue]) }}</span>
        <span class="mp-badge bg-amber-100 text-amber-800">{{ __('hospital.dashboard.labs_badge', ['count' => $pendingLabOrders]) }}</span>
        <span class="mp-badge bg-sky-100 text-sky-800">{{ __('hospital.dashboard.ipd_badge', ['count' => $occupiedBeds]) }}</span>
    </div>
</div>

<div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
    <div class="mp-stat animate-rise" style="animation-delay:.05s">
        <p class="text-xs font-semibold uppercase tracking-wide text-brand-600">{{ __('hospital.dashboard.new_patients') }}</p>
        <p class="mt-2 font-display text-4xl text-ink-900">{{ $todayPatients }}</p>
    </div>
    <div class="mp-stat animate-rise" style="animation-delay:.1s">
        <p class="text-xs font-semibold uppercase tracking-wide text-brand-600">{{ __('hospital.dashboard.opd_visits') }}</p>
        <p class="mt-2 font-display text-4xl text-ink-900">{{ $todayOpdVisits }}</p>
    </div>
    <div class="mp-stat animate-rise" style="animation-delay:.15s">
        <p class="text-xs font-semibold uppercase tracking-wide text-brand-600">{{ __('hospital.dashboard.ipd_admissions') }}</p>
        <p class="mt-2 font-display text-4xl text-ink-900">{{ $todayIpdAdmissions }}</p>
    </div>
    <div class="mp-stat animate-rise" style="animation-delay:.2s">
        <p class="text-xs font-semibold uppercase tracking-wide text-brand-600">{{ __('hospital.dashboard.revenue') }}</p>
        <p class="mt-2 font-display text-3xl text-ink-900">{{ $todayRevenueFormatted }}</p>
    </div>
</div>

<div class="mt-6 grid gap-5 lg:grid-cols-3">
    <div class="mp-card lg:col-span-2">
        <h2 class="font-display text-xl text-ink-900">{{ __('hospital.dashboard.ops_snapshot') }}</h2>
        <p class="mp-muted mt-1 text-sm font-medium">{{ __('hospital.dashboard.front_desk_hint') }}</p>

        <div class="mt-5 grid gap-3 sm:grid-cols-3">
            <div class="rounded-lg border border-brand-100 bg-brand-50/70 p-4">
                <p class="text-sm text-brand-700">{{ __('hospital.dashboard.waiting_queue') }}</p>
                <p class="mt-1 font-display text-3xl">{{ $waitingQueue }}</p>
            </div>
            <div class="rounded-lg border border-amber-100 bg-amber-50/70 p-4">
                <p class="text-sm text-amber-800">{{ __('hospital.dashboard.pending_labs') }}</p>
                <p class="mt-1 font-display text-3xl">{{ $pendingLabOrders }}</p>
            </div>
            <div class="rounded-lg border border-sky-100 bg-sky-50/70 p-4">
                <p class="text-sm text-sky-800">{{ __('hospital.dashboard.bed_occupancy') }}</p>
                <p class="mt-1 font-display text-3xl">{{ $occupiedBeds }}/{{ $totalBeds }}</p>
            </div>
        </div>

        <div class="mt-5">
            <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-brand-700">{{ __('hospital.dashboard.quick_actions') }}</p>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('patients.create') }}" class="mp-btn">{{ __('hospital.dashboard.register_patient') }}</a>
                <a href="{{ route('visits.create') }}" class="mp-btn-secondary">{{ __('hospital.dashboard.start_visit') }}</a>
                <a href="{{ route('billing.index') }}" class="mp-btn-secondary">{{ __('hospital.dashboard.open_billing') }}</a>
                <a href="{{ route('pharmacy.index') }}" class="mp-btn-secondary">{{ __('hospital.nav.pharmacy') }}</a>
                <a href="{{ route('admissions.index') }}" class="mp-btn-secondary">{{ __('hospital.nav.admissions') }}</a>
                @if(auth()->user()->isAdmin())
                    <a href="{{ route('users.create') }}" class="mp-btn-secondary">{{ __('hospital.dashboard.register_staff') }}</a>
                @endif
            </div>
        </div>
    </div>

    <div class="space-y-5">
        <div class="mp-card">
            <h2 class="font-display text-xl text-ink-900">{{ __('hospital.dashboard.low_stock') }}</h2>
            <ul class="mt-4 space-y-2">
                @forelse($lowStockMedicines as $med)
                    <li class="flex items-center justify-between rounded-md bg-sand-50 px-3 py-2 text-sm">
                        <span class="truncate pr-2 font-medium">{{ $med->name }} <span class="mp-muted">{{ $med->strength }}</span></span>
                        <span class="mp-badge bg-red-100 text-red-700 shrink-0">{{ $med->stock_qty }} {{ $med->unit }}</span>
                    </li>
                @empty
                    <li class="text-sm text-brand-700">{{ __('hospital.dashboard.stock_ok') }}</li>
                @endforelse
            </ul>
        </div>

        <div class="mp-card overflow-hidden !p-0">
            <div class="border-b border-brand-50 px-5 py-3">
                <h2 class="font-display text-lg text-ink-900">{{ __('hospital.dashboard.brand_strip') }}</h2>
            </div>
            <div class="relative bg-brand-950 px-4 pb-2 pt-3 text-brand-50">
                <x-savannah-scene class="mx-auto h-28 w-full opacity-90" />
                <p class="relative z-10 -mt-2 pb-3 text-center text-[11px] text-brand-200">Mint green · acacia · savannah</p>
            </div>
        </div>
    </div>
</div>
@endsection
