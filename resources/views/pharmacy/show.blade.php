@extends('layouts.hospital')

@section('title', $prescription->prescription_no)
@section('eyebrow', 'Pharmacy')
@section('heading', 'Dispense — '.$prescription->prescription_no)

@section('actions')
    @if($prescription->visit)
        <a href="{{ route('visits.show', $prescription->visit) }}" class="mp-btn-secondary">Visit</a>
    @endif
@endsection

@section('content')
<div class="mp-card mb-4 rounded-xl bg-brand-50/60 p-4 text-sm">
    <strong>{{ $prescription->patient->full_name }}</strong> · {{ $prescription->patient->mrn }}
    · Doctor: {{ $prescription->doctor->name ?? '—' }}
    · <span class="mp-badge bg-brand-100 text-brand-900">{{ $prescription->status }}</span>
</div>

@if(in_array($prescription->status, ['pending', 'partial']))
<form method="POST" action="{{ route('pharmacy.dispense', $prescription) }}">
    @csrf
    <div class="mp-card">
        <h2 class="font-display text-lg text-ink-900">Dispense medicines</h2>
        <div class="mt-4 overflow-x-auto">
            <table class="mp-table">
                <thead>
                    <tr>
                        <th>Medicine</th>
                        <th>Dosage / Frequency</th>
                        <th>Prescribed</th>
                        <th>Dispensed</th>
                        <th>Stock</th>
                        <th>Qty to dispense</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-brand-50">
                    @foreach($prescription->items as $index => $item)
                        @php $remaining = $item->quantity - $item->quantity_dispensed; @endphp
                        <tr>
                            <td>
                                {{ $item->medicine->name }}
                                <br><span class="text-xs text-brand-700">{{ \App\Support\Hospital::money($item->unit_price) }}/{{ $item->medicine->unit }}</span>
                            </td>
                            <td>{{ collect([$item->dosage, $item->frequency, $item->duration])->filter()->implode(' · ') ?: '—' }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td>{{ $item->quantity_dispensed }}</td>
                            <td>
                                <span class="mp-badge {{ $item->medicine->stock_qty <= $item->medicine->reorder_level ? 'bg-red-100 text-red-700' : 'bg-brand-50 text-brand-800' }}">
                                    {{ $item->medicine->stock_qty }} {{ $item->medicine->unit }}
                                </span>
                            </td>
                            <td>
                                @if($remaining > 0)
                                    <input type="hidden" name="items[{{ $index }}][prescription_item_id]" value="{{ $item->id }}">
                                    <input class="mp-input max-w-[100px]" type="number" name="items[{{ $index }}][quantity]" min="1" max="{{ min($remaining, $item->medicine->stock_qty) }}" value="{{ old('items.'.$index.'.quantity', $remaining) }}">
                                @else
                                    <span class="text-sm text-brand-700">Complete</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <button type="submit" class="mp-btn mt-4">Dispense selected</button>
    </div>
</form>
@endif

@if($prescription->dispensings->isNotEmpty())
<div class="mp-card mt-6">
    <h2 class="font-display text-lg text-ink-900">Dispensing history</h2>
    <table class="mp-table mt-3">
        <thead><tr><th>Medicine</th><th>Qty</th><th>Total</th><th>Date</th></tr></thead>
        <tbody class="divide-y divide-brand-50">
            @foreach($prescription->dispensings as $d)
                <tr>
                    <td>{{ $d->medicine->name }}</td>
                    <td>{{ $d->quantity }}</td>
                    <td>{{ \App\Support\Hospital::money($d->total_price) }}</td>
                    <td>{{ $d->created_at->format('d M Y H:i') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif
@endsection
