@extends('layouts.hospital')

@section('title', __('hospital.nhif_claims.title'))
@section('eyebrow', __('hospital.nhif_claims.eyebrow'))
@section('heading', __('hospital.nhif_claims.title'))

@section('actions')
    <a href="{{ route('nhif.authorize.create') }}" class="mp-btn-primary">{{ __('hospital.nhif_claims.request_auth') }}</a>
    <a href="{{ route('nhif.batches.create') }}" class="mp-btn-secondary">{{ __('hospital.nhif_claims.create_batch') }}</a>
    <a href="{{ route('nhif.batches.index') }}" class="mp-btn-secondary">{{ __('hospital.nhif_claims.batches') }}</a>
@endsection

@section('content')
<div class="grid gap-6 lg:grid-cols-2 mb-6">
    <div class="mp-card">
        <h2 class="font-display text-lg text-ink-900">{{ __('hospital.nhif_claims.recent_auth') }}</h2>
        <div class="mt-4 overflow-x-auto">
            <table class="mp-table">
                <thead>
                    <tr>
                        <th>{{ __('hospital.nhif_claims.auth_no') }}</th>
                        <th>{{ __('hospital.common.patient') }}</th>
                        <th>{{ __('hospital.common.status') }}</th>
                        <th>{{ __('hospital.common.date') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-brand-50">
                    @forelse($authorizations as $auth)
                        <tr>
                            <td class="font-semibold text-brand-800">{{ $auth->auth_no }}</td>
                            <td>{{ $auth->patient->full_name }}<br><span class="text-xs text-ink-700/60">{{ $auth->card_no }}</span></td>
                            <td><span class="mp-badge {{ $auth->status === 'approved' ? 'bg-brand-100 text-brand-900' : 'bg-red-100 text-red-700' }}">{{ $auth->status }}</span></td>
                            <td class="text-sm">{{ $auth->created_at->format('d M Y') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="py-6 text-center text-ink-700/60">{{ __('hospital.nhif_claims.no_auth') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mp-card">
        <h2 class="font-display text-lg text-ink-900">{{ __('hospital.nhif_claims.open_batches') }}</h2>
        <ul class="mt-4 space-y-2 text-sm">
            @forelse($openBatches as $batch)
                <li class="flex items-center justify-between rounded-lg bg-brand-50/50 px-3 py-2">
                    <span>
                        <strong>{{ $batch->batch_no }}</strong>
                        · {{ $batch->period_from->format('d M') }} – {{ $batch->period_to->format('d M Y') }}
                        · {{ $batch->claims_count }} {{ __('hospital.nhif_claims.claims') }}
                    </span>
                    <form method="POST" action="{{ route('nhif.batches.submit', $batch) }}" class="inline">
                        @csrf
                        <button type="submit" class="font-semibold text-brand-700">{{ __('hospital.nhif_claims.submit') }}</button>
                    </form>
                </li>
            @empty
                <li class="text-ink-700/60">{{ __('hospital.nhif_claims.no_open_batches') }}</li>
            @endforelse
        </ul>
    </div>
</div>

<div class="mp-card">
    <h2 class="font-display text-lg text-ink-900">{{ __('hospital.nhif_claims.claims_list') }}</h2>
    <div class="mt-4 overflow-x-auto">
        <table class="mp-table">
            <thead>
                <tr>
                    <th>{{ __('hospital.nhif_claims.claim_no') }}</th>
                    <th>{{ __('hospital.common.patient') }}</th>
                    <th>{{ __('hospital.nhif_claims.amount') }}</th>
                    <th>{{ __('hospital.common.status') }}</th>
                    <th>{{ __('hospital.nhif_claims.batch') }}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-brand-50">
                @forelse($claims as $claim)
                    <tr>
                        <td class="font-semibold text-brand-800">{{ $claim->claim_no }}</td>
                        <td>{{ $claim->patient->full_name }}<br><span class="text-xs text-ink-700/60">{{ $claim->card_no }}</span></td>
                        <td>{{ \App\Support\Hospital::money($claim->amount) }}</td>
                        <td><span class="mp-badge bg-sand-100 text-sand-700">{{ $claim->status }}</span></td>
                        <td>{{ $claim->batch?->batch_no ?? '—' }}</td>
                        <td class="text-right"><a href="{{ route('nhif.claims.show', $claim) }}" class="font-semibold text-brand-700">{{ __('hospital.nhif_claims.view') }}</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="py-8 text-center text-ink-700/60">{{ __('hospital.nhif_claims.no_claims') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<p class="mt-4 text-xs text-ink-700/60">{{ __('hospital.nhif.stub_note') }}</p>
@endsection
