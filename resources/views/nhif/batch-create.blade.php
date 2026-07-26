@extends('layouts.hospital')

@section('title', __('hospital.nhif_claims.create_batch'))
@section('eyebrow', __('hospital.nhif_claims.eyebrow'))
@section('heading', __('hospital.nhif_claims.create_batch'))

@section('actions')
    <a href="{{ route('nhif.batches.index') }}" class="mp-btn-secondary">{{ __('hospital.nhif_claims.back') }}</a>
@endsection

@section('content')
<div class="mp-card max-w-4xl">
    <form method="POST" action="{{ route('nhif.batches.store') }}">
        @csrf

        <div class="grid gap-4 sm:grid-cols-2 mb-6">
            <div>
                <label class="mp-label" for="period_from">{{ __('hospital.nhif_claims.period_from') }} *</label>
                <input class="mp-input" type="date" name="period_from" id="period_from" value="{{ old('period_from', now()->startOfMonth()->toDateString()) }}" required>
            </div>
            <div>
                <label class="mp-label" for="period_to">{{ __('hospital.nhif_claims.period_to') }} *</label>
                <input class="mp-input" type="date" name="period_to" id="period_to" value="{{ old('period_to', now()->endOfMonth()->toDateString()) }}" required>
            </div>
            <div class="sm:col-span-2">
                <label class="mp-label" for="notes">{{ __('hospital.common.notes') }}</label>
                <textarea class="mp-input" name="notes" id="notes" rows="2">{{ old('notes') }}</textarea>
            </div>
        </div>

        <h2 class="font-display text-lg text-ink-900 mb-3">{{ __('hospital.nhif_claims.select_ready_claims') }}</h2>

        @error('batch')
            <p class="mb-3 text-sm text-red-600">{{ $message }}</p>
        @enderror

        <div class="overflow-x-auto">
            <table class="mp-table">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="select-all" class="rounded border-brand-200"></th>
                        <th>{{ __('hospital.nhif_claims.claim_no') }}</th>
                        <th>{{ __('hospital.common.patient') }}</th>
                        <th>{{ __('hospital.nhif_claims.invoice') }}</th>
                        <th>{{ __('hospital.nhif_claims.amount') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-brand-50">
                    @forelse($readyClaims as $claim)
                        <tr>
                            <td><input type="checkbox" name="claim_ids[]" value="{{ $claim->id }}" class="claim-checkbox rounded border-brand-200" @checked(in_array($claim->id, old('claim_ids', [])))></td>
                            <td class="font-semibold text-brand-800">{{ $claim->claim_no }}</td>
                            <td>{{ $claim->patient->full_name }}</td>
                            <td>{{ $claim->invoice?->invoice_no ?? '—' }}</td>
                            <td>{{ \App\Support\Hospital::money($claim->amount) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-8 text-center text-ink-700/60">{{ __('hospital.nhif_claims.no_ready_claims') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6 flex gap-3">
            <button type="submit" class="mp-btn-primary" @disabled($readyClaims->isEmpty())>{{ __('hospital.nhif_claims.create_batch') }}</button>
            <a href="{{ route('nhif.batches.index') }}" class="mp-btn-secondary">{{ __('hospital.common.cancel') }}</a>
        </div>
    </form>
</div>

<script>
document.getElementById('select-all')?.addEventListener('change', function () {
    document.querySelectorAll('.claim-checkbox').forEach(cb => cb.checked = this.checked);
});
</script>
@endsection
