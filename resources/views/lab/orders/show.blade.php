@extends('layouts.hospital')

@section('title', $labOrder->order_no)
@section('eyebrow', 'Laboratory')
@section('heading', 'Lab Order '.$labOrder->order_no)

@section('actions')
    @if($labOrder->visit)
        <a href="{{ route('visits.show', $labOrder->visit) }}" class="mp-btn-secondary">Visit</a>
    @endif
@endsection

@section('content')
<div class="mp-card mb-6">
    <dl class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4 text-sm">
        <div><dt class="text-ink-700/60">Patient</dt><dd class="font-semibold">{{ $labOrder->patient->full_name }} ({{ $labOrder->patient->mrn }})</dd></div>
        <div><dt class="text-ink-700/60">Priority</dt><dd class="capitalize">{{ $labOrder->priority }}</dd></div>
        <div><dt class="text-ink-700/60">Status</dt><dd><span class="mp-badge bg-brand-50 text-brand-800">{{ $labOrder->status }}</span></dd></div>
        <div><dt class="text-ink-700/60">Ordered by</dt><dd>{{ $labOrder->orderedBy->name ?? '—' }}</dd></div>
        @if($labOrder->clinical_notes)
            <div class="sm:col-span-2 lg:col-span-4"><dt class="text-ink-700/60">Clinical notes</dt><dd>{{ $labOrder->clinical_notes }}</dd></div>
        @endif
    </dl>
</div>

@if($labOrder->status !== 'completed')
<form method="POST" action="{{ route('lab.orders.update-results', $labOrder) }}">
    @csrf
    <div class="mp-card">
        <h2 class="font-display text-lg text-ink-900">Enter results</h2>
        <div class="mt-4 space-y-4">
            @foreach($labOrder->items as $index => $item)
                <div class="rounded-xl border border-brand-100 bg-brand-50/30 p-4">
                    <input type="hidden" name="items[{{ $index }}][id]" value="{{ $item->id }}">
                    <h3 class="font-semibold text-brand-900">{{ $item->labTest->name }}</h3>
                    <p class="text-xs text-brand-700">Reference: {{ $item->labTest->normal_range ?? '—' }} · {{ \App\Support\Hospital::money($item->price) }}</p>
                    <div class="mt-3 grid gap-3 sm:grid-cols-3">
                        <div class="sm:col-span-2">
                            <label class="mp-label" for="result_{{ $item->id }}">Result *</label>
                            <input class="mp-input" type="text" name="items[{{ $index }}][result]" id="result_{{ $item->id }}" value="{{ old('items.'.$index.'.result', $item->result) }}" required>
                        </div>
                        <div>
                            <label class="mp-label" for="flag_{{ $item->id }}">Flag</label>
                            <select class="mp-input" name="items[{{ $index }}][result_flag]" id="flag_{{ $item->id }}">
                                <option value="">Normal</option>
                                @foreach(['H' => 'High', 'L' => 'Low', 'CRITICAL' => 'Critical'] as $val => $label)
                                    <option value="{{ $val }}" @selected(old('items.'.$index.'.result_flag', $item->result_flag) === $val)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="sm:col-span-3">
                            <label class="mp-label" for="remarks_{{ $item->id }}">Remarks</label>
                            <input class="mp-input" type="text" name="items[{{ $index }}][remarks]" id="remarks_{{ $item->id }}" value="{{ old('items.'.$index.'.remarks', $item->remarks) }}">
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <button type="submit" class="mp-btn mt-4">Save results</button>
    </div>
</form>
@else
<div class="mp-card">
    <h2 class="font-display text-lg text-ink-900">Results</h2>
    <table class="mp-table mt-4">
        <thead><tr><th>Test</th><th>Result</th><th>Flag</th><th>Remarks</th></tr></thead>
        <tbody class="divide-y divide-brand-50">
            @foreach($labOrder->items as $item)
                <tr>
                    <td>{{ $item->labTest->name }}</td>
                    <td class="font-semibold">{{ $item->result }}</td>
                    <td>@if($item->result_flag)<span class="mp-badge bg-red-100 text-red-700">{{ $item->result_flag }}</span>@else—@endif</td>
                    <td>{{ $item->remarks ?? '—' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    @if($labOrder->processedBy)
        <p class="mt-3 text-sm text-ink-700/60">Processed by {{ $labOrder->processedBy->name }} · {{ $labOrder->completed_at?->format('d M Y H:i') }}</p>
    @endif
</div>
@endif
@endsection
