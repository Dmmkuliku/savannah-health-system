@extends('layouts.hospital')

@section('title', __('hospital.blood.title'))
@section('eyebrow', __('hospital.blood.eyebrow'))
@section('heading', __('hospital.blood.title'))

@section('actions')
    <a href="{{ route('blood.units.create') }}" class="mp-btn-secondary">{{ __('hospital.blood.add_unit') }}</a>
    <a href="{{ route('blood.requests.create') }}" class="mp-btn">{{ __('hospital.blood.request') }}</a>
@endsection

@section('content')
<div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
    @forelse($stockByGroup as $group => $total)
        <div class="mp-card rounded-xl p-4">
            <p class="text-xs font-semibold uppercase tracking-wide text-ink-700/60">{{ __('hospital.blood.blood_group') }}</p>
            <p class="mt-1 font-display text-2xl text-brand-800">{{ $group }}</p>
            <p class="text-sm text-ink-700">{{ $total }} {{ __('hospital.blood.units') }}</p>
        </div>
    @empty
        <div class="mp-card rounded-xl p-4 sm:col-span-2 lg:col-span-4">
            <p class="text-sm text-ink-700/60">No available blood units in stock.</p>
        </div>
    @endforelse
</div>

<div class="grid gap-6 lg:grid-cols-2">
    <div class="mp-card">
        <h2 class="font-display text-lg text-ink-900">{{ __('hospital.blood.requests') }}</h2>
        <div class="mt-4 overflow-x-auto">
            <table class="mp-table">
                <thead>
                    <tr>
                        <th>Request No</th>
                        <th>Patient</th>
                        <th>{{ __('hospital.blood.blood_group') }}</th>
                        <th>Units</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-brand-50">
                    @forelse($pendingRequests as $request)
                        <tr>
                            <td class="font-semibold text-brand-800">{{ $request->request_no }}</td>
                            <td>{{ $request->patient->full_name }}</td>
                            <td>{{ $request->blood_group }}</td>
                            <td>{{ $request->units_issued }}/{{ $request->units_requested }}</td>
                            <td><span class="mp-badge bg-brand-50 text-brand-800">{{ $request->status }}</span></td>
                            <td class="text-right"><a href="{{ route('blood.requests.show', $request) }}" class="font-semibold text-brand-700">{{ __('hospital.blood.issue') }}</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-6 text-center text-ink-700/60">No pending requests.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mp-card">
        <h2 class="font-display text-lg text-ink-900">Expiring soon</h2>
        <div class="mt-4 overflow-x-auto">
            <table class="mp-table">
                <thead>
                    <tr>
                        <th>Unit No</th>
                        <th>{{ __('hospital.blood.blood_group') }}</th>
                        <th>{{ __('hospital.blood.component') }}</th>
                        <th>{{ __('hospital.blood.expiry') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-brand-50">
                    @forelse($expiringSoon as $unit)
                        <tr>
                            <td class="font-semibold text-brand-800">{{ $unit->unit_no }}</td>
                            <td>{{ $unit->blood_group }}</td>
                            <td>{{ str_replace('_', ' ', $unit->component) }}</td>
                            <td>{{ $unit->expiry_date->format('d M Y') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="py-6 text-center text-ink-700/60">No units expiring within 14 days.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
