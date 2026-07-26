@extends('layouts.hospital')

@section('title', 'Consultation')
@section('eyebrow', 'Clinical')
@section('heading', 'Consultation — '.$visit->visit_no)

@section('actions')
    <a href="{{ route('visits.show', $visit) }}" class="mp-btn-secondary">Back to visit</a>
@endsection

@section('content')
@php $consultation = $visit->consultation; @endphp

<div class="mp-card mb-4 rounded-xl bg-brand-50/60 p-4 text-sm">
    <strong>{{ $visit->patient->full_name }}</strong> · {{ $visit->patient->mrn }}
    · Doctor: {{ $consultation->doctor->name ?? '—' }}
    · {{ $consultation->created_at->format('d M Y H:i') }}
</div>

<div class="grid gap-6 lg:grid-cols-2">
    <div class="mp-card">
        <h2 class="font-display text-lg text-ink-900">Clinical record</h2>
        <dl class="mt-4 space-y-3 text-sm">
            @foreach([
                'History of present illness' => $consultation->history_of_present_illness,
                'Past medical history' => $consultation->past_medical_history,
                'Examination findings' => $consultation->examination_findings,
                'Diagnosis' => $consultation->diagnosis_summary,
                'ICD-10' => $consultation->icd10_codes,
                'Treatment plan' => $consultation->treatment_plan,
                'Advice' => $consultation->advice,
            ] as $label => $value)
                @if($value)
                    <div>
                        <dt class="font-semibold text-brand-800">{{ $label }}</dt>
                        <dd class="mt-1 whitespace-pre-wrap text-ink-800">{{ $value }}</dd>
                    </div>
                @endif
            @endforeach
            <div>
                <dt class="font-semibold text-brand-800">Outcome</dt>
                <dd class="mt-1 capitalize">{{ str_replace('_', ' ', $consultation->outcome) }}</dd>
            </div>
            @if($consultation->follow_up_date)
                <div>
                    <dt class="font-semibold text-brand-800">Follow-up</dt>
                    <dd class="mt-1">{{ $consultation->follow_up_date->format('d M Y') }}</dd>
                </div>
            @endif
        </dl>
    </div>

    <div class="space-y-6">
        @if($visit->labOrders->isNotEmpty())
            <div class="mp-card">
                <h2 class="font-display text-lg text-ink-900">Lab orders</h2>
                <ul class="mt-3 space-y-2 text-sm">
                    @foreach($visit->labOrders as $order)
                        <li class="flex justify-between rounded-lg bg-brand-50/50 px-3 py-2">
                            <span>{{ $order->order_no }}</span>
                            <a href="{{ route('lab.orders.show', $order) }}" class="font-semibold text-brand-700">View</a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if($visit->prescriptions->isNotEmpty())
            <div class="mp-card">
                <h2 class="font-display text-lg text-ink-900">Prescriptions</h2>
                @foreach($visit->prescriptions as $rx)
                    <div class="mt-3">
                        <p class="text-sm font-semibold">{{ $rx->prescription_no }}</p>
                        <table class="mp-table mt-2">
                            <thead><tr><th>Medicine</th><th>Dosage</th><th>Qty</th></tr></thead>
                            <tbody class="divide-y divide-brand-50">
                                @foreach($rx->items as $item)
                                    <tr>
                                        <td>{{ $item->medicine->name }}</td>
                                        <td>{{ collect([$item->dosage, $item->frequency, $item->duration])->filter()->implode(' · ') ?: '—' }}</td>
                                        <td>{{ $item->quantity }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <a href="{{ route('pharmacy.show', $rx) }}" class="mt-2 inline-block text-sm font-semibold text-brand-700">Open in pharmacy →</a>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
