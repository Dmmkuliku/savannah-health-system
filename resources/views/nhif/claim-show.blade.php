@extends('layouts.hospital')

@section('title', __('hospital.nhif_claims.claim_no').' '.$claim->claim_no)
@section('eyebrow', __('hospital.nhif_claims.eyebrow'))
@section('heading', $claim->claim_no)

@section('actions')
    <a href="{{ route('nhif.claims.index') }}" class="mp-btn-secondary">{{ __('hospital.nhif_claims.back') }}</a>
@endsection

@section('content')
<div class="grid gap-6 lg:grid-cols-3 mb-6">
    <div class="mp-card lg:col-span-2">
        <h2 class="font-display text-lg text-ink-900">{{ __('hospital.nhif_claims.claim_details') }}</h2>
        <dl class="mt-4 grid gap-3 text-sm sm:grid-cols-2">
            <div><dt class="text-ink-700/60">{{ __('hospital.common.patient') }}</dt><dd class="font-semibold">{{ $claim->patient->full_name }} ({{ $claim->patient->mrn }})</dd></div>
            <div><dt class="text-ink-700/60">{{ __('hospital.nhif.card_no') }}</dt><dd>{{ $claim->card_no ?? '—' }}</dd></div>
            <div><dt class="text-ink-700/60">{{ __('hospital.nhif_claims.amount') }}</dt><dd class="font-semibold text-brand-800">{{ \App\Support\Hospital::money($claim->amount) }}</dd></div>
            <div><dt class="text-ink-700/60">{{ __('hospital.common.status') }}</dt><dd><span class="mp-badge bg-sand-100 text-sand-700">{{ $claim->status }}</span></dd></div>
            <div><dt class="text-ink-700/60">{{ __('hospital.nhif_claims.invoice') }}</dt><dd>{{ $claim->invoice?->invoice_no ?? '—' }}</dd></div>
            <div><dt class="text-ink-700/60">{{ __('hospital.nhif_claims.batch') }}</dt><dd>{{ $claim->batch?->batch_no ?? '—' }}</dd></div>
            <div class="sm:col-span-2"><dt class="text-ink-700/60">{{ __('hospital.nhif_claims.diagnosis') }}</dt><dd>{{ $claim->diagnosis ?? '—' }}</dd></div>
        </dl>

        @if($claim->authorization)
            <div class="mt-4 rounded-lg bg-brand-50/60 p-3 text-sm">
                <strong>{{ __('hospital.nhif_claims.auth_no') }}:</strong> {{ $claim->authorization->auth_no }}
                · <strong>{{ __('hospital.nhif_claims.auth_code') }}:</strong> {{ $claim->authorization->authorization_code }}
                · {{ \App\Support\Hospital::money($claim->authorization->approved_amount ?? 0) }}
            </div>
        @endif
    </div>

    <div class="mp-card">
        <h2 class="font-display text-lg text-ink-900">{{ __('hospital.nhif_claims.timeline') }}</h2>
        <ul class="mt-4 space-y-2 text-sm">
            <li class="flex justify-between border-b border-brand-50 py-2"><span>{{ __('hospital.nhif_claims.created') }}</span><span>{{ $claim->created_at->format('d M Y H:i') }}</span></li>
            @if($claim->submitted_at)
                <li class="flex justify-between py-2"><span>{{ __('hospital.nhif_claims.submitted') }}</span><span>{{ $claim->submitted_at->format('d M Y H:i') }}</span></li>
            @endif
        </ul>
    </div>
</div>

<div class="mp-card">
    <h2 class="font-display text-lg text-ink-900">{{ __('hospital.nhif_claims.items_snapshot') }}</h2>
    <div class="mt-4 overflow-x-auto">
        <table class="mp-table">
            <thead>
                <tr>
                    <th>{{ __('hospital.nhif_claims.description') }}</th>
                    <th>{{ __('hospital.nhif_claims.qty') }}</th>
                    <th>{{ __('hospital.nhif_claims.unit_price') }}</th>
                    <th>{{ __('hospital.nhif_claims.total') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-brand-50">
                @forelse($claim->items_snapshot ?? [] as $item)
                    <tr>
                        <td>{{ $item['description'] ?? '—' }}</td>
                        <td>{{ $item['quantity'] ?? 1 }}</td>
                        <td>{{ \App\Support\Hospital::money($item['unit_price'] ?? 0) }}</td>
                        <td>{{ \App\Support\Hospital::money($item['total'] ?? 0) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="py-6 text-center text-ink-700/60">{{ __('hospital.nhif_claims.no_items') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
