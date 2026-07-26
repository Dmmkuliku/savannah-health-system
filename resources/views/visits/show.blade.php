@extends('layouts.hospital')

@section('title', $visit->visit_no)
@section('eyebrow', 'Visit details')
@section('heading', 'Visit '.$visit->visit_no)

@section('actions')
    <a href="{{ route('patients.show', $visit->patient) }}" class="mp-btn-secondary">Patient file</a>
    @if(!$visit->consultation)
        <a href="{{ route('consultations.create', $visit) }}" class="mp-btn">Start consultation</a>
    @else
        <a href="{{ route('consultations.show', $visit) }}" class="mp-btn-secondary">View consultation</a>
    @endif
@endsection

@section('content')
<div class="grid gap-6 lg:grid-cols-3">
    <div class="mp-card lg:col-span-2">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h2 class="font-display text-lg text-ink-900">{{ $visit->patient->full_name }}</h2>
                <p class="text-sm text-ink-700/70">{{ $visit->patient->mrn }} · {{ strtoupper($visit->visit_type) }} · {{ $visit->department->name ?? 'General' }}</p>
            </div>
            <span class="mp-badge bg-brand-100 text-brand-900">{{ str_replace('_', ' ', $visit->status) }}</span>
        </div>
        <dl class="mt-4 grid gap-3 sm:grid-cols-2 text-sm">
            <div><dt class="text-ink-700/60">Visited at</dt><dd>{{ $visit->visited_at?->format('d M Y H:i') }}</dd></div>
            <div><dt class="text-ink-700/60">Payment</dt><dd class="capitalize">{{ str_replace('_', ' ', $visit->payment_category) }}</dd></div>
            <div><dt class="text-ink-700/60">Doctor</dt><dd>{{ $visit->doctor->name ?? '—' }}</dd></div>
            <div><dt class="text-ink-700/60">Chief complaint</dt><dd>{{ $visit->chief_complaint ?? '—' }}</dd></div>
        </dl>

        <div class="mt-6 flex flex-wrap gap-2 border-t border-brand-50 pt-4">
            <form method="POST" action="{{ route('billing.create-from-visit', $visit) }}" class="inline">
                @csrf
                <button type="submit" class="mp-btn-secondary">Create invoice</button>
            </form>
            <a href="{{ route('admissions.create', $visit) }}" class="mp-btn-secondary">Admit patient</a>
            @if($visit->labOrders->isNotEmpty())
                @foreach($visit->labOrders as $labOrder)
                    <a href="{{ route('lab.orders.show', $labOrder) }}" class="mp-btn-secondary">Lab {{ $labOrder->order_no }}</a>
                @endforeach
            @endif
        </div>
    </div>

    <div class="space-y-6">
        <div class="mp-card">
            <h3 class="font-display text-base text-ink-900">Record vitals</h3>
            <form method="POST" action="{{ route('vital-signs.store', $visit) }}" class="mt-3 space-y-3">
                @csrf
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="mp-label text-xs" for="temperature_c">Temp (°C)</label>
                        <input class="mp-input" type="number" step="0.1" name="temperature_c" id="temperature_c" value="{{ old('temperature_c') }}">
                    </div>
                    <div>
                        <label class="mp-label text-xs" for="pulse">Pulse</label>
                        <input class="mp-input" type="number" name="pulse" id="pulse" value="{{ old('pulse') }}">
                    </div>
                    <div>
                        <label class="mp-label text-xs" for="bp_systolic">BP sys</label>
                        <input class="mp-input" type="number" name="bp_systolic" id="bp_systolic" value="{{ old('bp_systolic') }}">
                    </div>
                    <div>
                        <label class="mp-label text-xs" for="bp_diastolic">BP dia</label>
                        <input class="mp-input" type="number" name="bp_diastolic" id="bp_diastolic" value="{{ old('bp_diastolic') }}">
                    </div>
                    <div>
                        <label class="mp-label text-xs" for="respiratory_rate">RR</label>
                        <input class="mp-input" type="number" name="respiratory_rate" id="respiratory_rate" value="{{ old('respiratory_rate') }}">
                    </div>
                    <div>
                        <label class="mp-label text-xs" for="spo2">SpO₂ %</label>
                        <input class="mp-input" type="number" name="spo2" id="spo2" value="{{ old('spo2') }}">
                    </div>
                    <div>
                        <label class="mp-label text-xs" for="weight_kg">Weight (kg)</label>
                        <input class="mp-input" type="number" step="0.1" name="weight_kg" id="weight_kg" value="{{ old('weight_kg') }}">
                    </div>
                    <div>
                        <label class="mp-label text-xs" for="height_cm">Height (cm)</label>
                        <input class="mp-input" type="number" step="0.1" name="height_cm" id="height_cm" value="{{ old('height_cm') }}">
                    </div>
                </div>
                <div>
                    <label class="mp-label text-xs" for="notes">Notes</label>
                    <textarea class="mp-input" name="notes" id="notes" rows="2">{{ old('notes') }}</textarea>
                </div>
                <button type="submit" class="mp-btn w-full">Save vitals</button>
            </form>
        </div>
    </div>
</div>

@if($visit->vitalSigns->isNotEmpty())
<div class="mp-card mt-6">
    <h3 class="font-display text-lg text-ink-900">Vital signs history</h3>
    <div class="mt-3 overflow-x-auto">
        <table class="mp-table">
            <thead>
                <tr>
                    <th>Time</th>
                    <th>Temp</th>
                    <th>Pulse</th>
                    <th>BP</th>
                    <th>SpO₂</th>
                    <th>Weight</th>
                    <th>BMI</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-brand-50">
                @foreach($visit->vitalSigns as $vital)
                    <tr>
                        <td>{{ $vital->created_at->format('H:i') }}</td>
                        <td>{{ $vital->temperature_c ?? '—' }}</td>
                        <td>{{ $vital->pulse ?? '—' }}</td>
                        <td>{{ $vital->bp_systolic && $vital->bp_diastolic ? $vital->bp_systolic.'/'.$vital->bp_diastolic : '—' }}</td>
                        <td>{{ $vital->spo2 ? $vital->spo2.'%' : '—' }}</td>
                        <td>{{ $vital->weight_kg ?? '—' }}</td>
                        <td>{{ $vital->bmi ?? '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

@if($visit->invoices->isNotEmpty())
<div class="mp-card mt-6">
    <h3 class="font-display text-lg text-ink-900">Invoices</h3>
    <ul class="mt-3 space-y-2">
        @foreach($visit->invoices as $invoice)
            <li class="flex items-center justify-between rounded-lg bg-brand-50/50 px-3 py-2 text-sm">
                <span>{{ $invoice->invoice_no }} — {{ \App\Support\Hospital::money($invoice->total) }}</span>
                <a href="{{ route('billing.show', $invoice) }}" class="font-semibold text-brand-700">View</a>
            </li>
        @endforeach
    </ul>
</div>
@endif

@if($visit->prescriptions->isNotEmpty())
<div class="mp-card mt-6">
    <h3 class="font-display text-lg text-ink-900">Prescriptions</h3>
    <ul class="mt-3 space-y-2 text-sm">
        @foreach($visit->prescriptions as $rx)
            <li class="flex items-center justify-between rounded-lg bg-sand-50 px-3 py-2">
                <span>{{ $rx->prescription_no }} <span class="mp-badge bg-brand-50 text-brand-800">{{ $rx->status }}</span></span>
                <a href="{{ route('pharmacy.show', $rx) }}" class="font-semibold text-brand-700">Pharmacy</a>
            </li>
        @endforeach
    </ul>
</div>
@endif
@endsection
