@extends('layouts.hospital')

@section('title', $radiologyOrder->order_no)
@section('eyebrow', 'Radiology')
@section('heading', 'Radiology — '.$radiologyOrder->order_no)

@section('actions')
    @if($radiologyOrder->visit)
        <a href="{{ route('visits.show', $radiologyOrder->visit) }}" class="mp-btn-secondary">Visit</a>
    @endif
@endsection

@section('content')
<div class="mp-card mb-6">
    <dl class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4 text-sm">
        <div><dt class="text-ink-700/60">Patient</dt><dd class="font-semibold">{{ $radiologyOrder->patient->full_name }} ({{ $radiologyOrder->patient->mrn }})</dd></div>
        <div><dt class="text-ink-700/60">Service</dt><dd>{{ $radiologyOrder->radiologyService->name }}</dd></div>
        <div><dt class="text-ink-700/60">Price</dt><dd>{{ \App\Support\Hospital::money($radiologyOrder->price) }}</dd></div>
        <div><dt class="text-ink-700/60">Status</dt><dd><span class="mp-badge bg-brand-50 text-brand-800">{{ $radiologyOrder->status }}</span></dd></div>
        @if($radiologyOrder->clinical_info)
            <div class="sm:col-span-2 lg:col-span-4"><dt class="text-ink-700/60">Clinical info</dt><dd>{{ $radiologyOrder->clinical_info }}</dd></div>
        @endif
    </dl>
</div>

@if($radiologyOrder->status !== 'completed')
<form method="POST" action="{{ route('radiology.orders.update-report', $radiologyOrder) }}">
    @csrf
    <div class="mp-card">
        <h2 class="font-display text-lg text-ink-900">Radiology report</h2>
        <div class="mt-4 space-y-4">
            <div>
                <label class="mp-label" for="findings">Findings *</label>
                <textarea class="mp-input" name="findings" id="findings" rows="5" required>{{ old('findings', $radiologyOrder->findings) }}</textarea>
            </div>
            <div>
                <label class="mp-label" for="impression">Impression *</label>
                <textarea class="mp-input" name="impression" id="impression" rows="3" required>{{ old('impression', $radiologyOrder->impression) }}</textarea>
            </div>
        </div>
        <button type="submit" class="mp-btn mt-4">Save report</button>
    </div>
</form>
@else
<div class="mp-card">
    <h2 class="font-display text-lg text-ink-900">Report</h2>
    <div class="mt-4 space-y-4 text-sm">
        <div>
            <h3 class="font-semibold text-brand-800">Findings</h3>
            <p class="mt-1 whitespace-pre-wrap">{{ $radiologyOrder->findings }}</p>
        </div>
        <div>
            <h3 class="font-semibold text-brand-800">Impression</h3>
            <p class="mt-1 whitespace-pre-wrap">{{ $radiologyOrder->impression }}</p>
        </div>
        @if($radiologyOrder->reportedBy)
            <p class="text-ink-700/60">Reported by {{ $radiologyOrder->reportedBy->name }} · {{ $radiologyOrder->completed_at?->format('d M Y H:i') }}</p>
        @endif
    </div>
</div>
@endif
@endsection
