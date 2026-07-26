@extends('layouts.hospital')

@section('title', __('hospital.dashboard.title'))
@section('eyebrow', __('hospital.dashboard.eyebrow'))
@section('heading', __('hospital.dashboard.title'))

@section('content')
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

<div class="mt-6 grid gap-6 lg:grid-cols-3">
    <div class="mp-card lg:col-span-2">
        <h2 class="font-display text-xl text-ink-900">{{ __('hospital.dashboard.ops_snapshot') }}</h2>
        <p class="mt-1 text-sm text-ink-700/70">{{ $facilityName }}</p>
        <div class="mt-5 grid gap-4 sm:grid-cols-2">
            <div class="rounded-xl border border-brand-100 bg-brand-50/60 p-4">
                <p class="text-sm text-brand-700">{{ __('hospital.dashboard.waiting_queue') }}</p>
                <p class="mt-1 font-display text-3xl">{{ $waitingQueue }}</p>
            </div>
            <div class="rounded-xl border border-sand-200 bg-sand-50 p-4">
                <p class="text-sm text-sand-500">{{ __('hospital.dashboard.pending_labs') }}</p>
                <p class="mt-1 font-display text-3xl">{{ $pendingLabOrders }}</p>
            </div>
        </div>
        <div class="mt-5 flex flex-wrap gap-3">
            <a href="{{ route('patients.create') }}" class="mp-btn">{{ __('hospital.dashboard.register_patient') }}</a>
            <a href="{{ route('visits.create') }}" class="mp-btn-secondary">{{ __('hospital.dashboard.start_visit') }}</a>
            <a href="{{ route('billing.index') }}" class="mp-btn-secondary">{{ __('hospital.dashboard.open_billing') }}</a>
            <a href="{{ route('theatre.index') }}" class="mp-btn-secondary">{{ __('hospital.nav.theatre') }}</a>
            <a href="{{ route('blood.index') }}" class="mp-btn-secondary">{{ __('hospital.nav.blood_bank') }}</a>
        </div>
    </div>

    <div class="mp-card">
        <h2 class="font-display text-xl text-ink-900">{{ __('hospital.dashboard.low_stock') }}</h2>
        <ul class="mt-4 space-y-3">
            @forelse($lowStockMedicines as $med)
                <li class="flex items-center justify-between rounded-lg bg-sand-50 px-3 py-2 text-sm">
                    <span>{{ $med->name }}</span>
                    <span class="mp-badge bg-red-100 text-red-700">{{ $med->stock_qty }} {{ $med->unit }}</span>
                </li>
            @empty
                <li class="text-sm text-brand-700">All stock levels healthy.</li>
            @endforelse
        </ul>
    </div>
</div>
@endsection
