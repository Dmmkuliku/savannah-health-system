@extends('layouts.hospital')

@section('title', __('hospital.nhif_claims.batches'))
@section('eyebrow', __('hospital.nhif_claims.eyebrow'))
@section('heading', __('hospital.nhif_claims.batches'))

@section('actions')
    <a href="{{ route('nhif.batches.create') }}" class="mp-btn-primary">{{ __('hospital.nhif_claims.create_batch') }}</a>
    <a href="{{ route('nhif.claims.index') }}" class="mp-btn-secondary">{{ __('hospital.nhif_claims.back') }}</a>
@endsection

@section('content')
<div class="mp-card">
    <div class="overflow-x-auto">
        <table class="mp-table">
            <thead>
                <tr>
                    <th>{{ __('hospital.nhif_claims.batch_no') }}</th>
                    <th>{{ __('hospital.nhif_claims.period') }}</th>
                    <th>{{ __('hospital.nhif_claims.claims') }}</th>
                    <th>{{ __('hospital.nhif_claims.amount') }}</th>
                    <th>{{ __('hospital.common.status') }}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-brand-50">
                @forelse($batches as $batch)
                    <tr>
                        <td class="font-semibold text-brand-800">{{ $batch->batch_no }}</td>
                        <td>{{ $batch->period_from->format('d M Y') }} – {{ $batch->period_to->format('d M Y') }}</td>
                        <td>{{ $batch->claims_count }}</td>
                        <td>{{ \App\Support\Hospital::money($batch->total_amount) }}</td>
                        <td><span class="mp-badge {{ $batch->status === 'submitted' ? 'bg-brand-100 text-brand-900' : 'bg-sand-100 text-sand-700' }}">{{ $batch->status }}</span></td>
                        <td class="text-right">
                            @if($batch->status === 'open')
                                <form method="POST" action="{{ route('nhif.batches.submit', $batch) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="font-semibold text-brand-700">{{ __('hospital.nhif_claims.submit') }}</button>
                                </form>
                            @else
                                <span class="text-xs text-ink-700/60">{{ $batch->submitted_at?->format('d M Y') }}</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="py-8 text-center text-ink-700/60">{{ __('hospital.nhif_claims.no_batches') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($batches->hasPages())
        <div class="mt-4">{{ $batches->links() }}</div>
    @endif
</div>
@endsection
