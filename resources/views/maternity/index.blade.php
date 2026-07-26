@extends('layouts.hospital')

@section('title', __('hospital.maternity.title'))
@section('eyebrow', __('hospital.maternity.eyebrow'))
@section('heading', __('hospital.maternity.title'))

@section('actions')
    <a href="{{ route('rch.index') }}" class="mp-btn-secondary">{{ __('hospital.rch.title') }}</a>
    <a href="{{ route('maternity.create') }}" class="mp-btn">{{ __('hospital.maternity.record_delivery') }}</a>
@endsection

@section('content')
<div class="mb-6 grid gap-4 sm:grid-cols-2">
    <div class="mp-card rounded-xl p-4">
        <p class="text-xs font-semibold uppercase tracking-wide text-ink-700/60">{{ __('hospital.maternity.deliveries_today') }}</p>
        <p class="mt-1 font-display text-2xl text-brand-800">{{ $todayDeliveries->count() }}</p>
    </div>
    <div class="mp-card rounded-xl p-4">
        <p class="text-xs font-semibold uppercase tracking-wide text-ink-700/60">{{ __('hospital.maternity.newborns_today') }}</p>
        <p class="mt-1 font-display text-2xl text-brand-800">{{ $newbornCountToday }}</p>
    </div>
</div>

<div class="mp-card mb-6">
    <h2 class="font-display text-lg text-ink-900">{{ __('hospital.maternity.today_deliveries') }}</h2>
    <div class="mt-4 overflow-x-auto">
        <table class="mp-table">
            <thead>
                <tr>
                    <th>{{ __('hospital.maternity.delivery_no') }}</th>
                    <th>{{ __('hospital.rch.mother') }}</th>
                    <th>{{ __('hospital.maternity.delivered_at') }}</th>
                    <th>{{ __('hospital.maternity.delivery_type') }}</th>
                    <th>{{ __('hospital.maternity.outcome') }}</th>
                    <th>{{ __('hospital.maternity.newborns') }}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-brand-50">
                @forelse($todayDeliveries as $delivery)
                    <tr>
                        <td class="font-semibold text-brand-800">{{ $delivery->delivery_no }}</td>
                        <td>{{ $delivery->patient->full_name }}</td>
                        <td>{{ $delivery->delivered_at->format('H:i') }}</td>
                        <td class="capitalize">{{ str_replace('_', ' ', $delivery->delivery_type) }}</td>
                        <td class="capitalize">{{ str_replace('_', ' ', $delivery->outcome) }}</td>
                        <td>{{ $delivery->newborns->count() }}</td>
                        <td class="text-right">
                            <a href="{{ route('maternity.show', $delivery) }}" class="font-semibold text-brand-700">{{ __('hospital.rch.view') }}</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="py-6 text-center text-ink-700/60">{{ __('hospital.maternity.no_deliveries_today') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mp-card">
    <h2 class="font-display text-lg text-ink-900">{{ __('hospital.maternity.recent_deliveries') }}</h2>
    <div class="mt-4 overflow-x-auto">
        <table class="mp-table">
            <thead>
                <tr>
                    <th>{{ __('hospital.maternity.delivery_no') }}</th>
                    <th>{{ __('hospital.rch.mother') }}</th>
                    <th>{{ __('hospital.maternity.delivered_at') }}</th>
                    <th>{{ __('hospital.maternity.outcome') }}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-brand-50">
                @forelse($recentDeliveries as $delivery)
                    <tr>
                        <td class="font-semibold text-brand-800">{{ $delivery->delivery_no }}</td>
                        <td>{{ $delivery->patient->full_name }}</td>
                        <td>{{ $delivery->delivered_at->format('d M Y H:i') }}</td>
                        <td class="capitalize">{{ str_replace('_', ' ', $delivery->outcome) }}</td>
                        <td class="text-right">
                            <a href="{{ route('maternity.show', $delivery) }}" class="font-semibold text-brand-700">{{ __('hospital.rch.view') }}</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="py-6 text-center text-ink-700/60">{{ __('hospital.maternity.no_recent_deliveries') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
