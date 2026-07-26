@extends('layouts.hospital')

@section('title', __('hospital.reports.title'))
@section('eyebrow', __('hospital.reports.eyebrow'))
@section('heading', __('hospital.reports.heading'))

@section('actions')
    <a href="{{ route('reports.mtuha-print', ['from' => $from, 'to' => $to]) }}" target="_blank" class="mp-btn-secondary print:hidden">{{ __('hospital.reports.print') }}</a>
@endsection

@section('content')
<form method="GET" class="mp-card mb-6 flex flex-wrap items-end gap-3 print:hidden">
    <div>
        <label class="mp-label" for="from">{{ __('hospital.reports.from') }}</label>
        <input class="mp-input" type="date" name="from" id="from" value="{{ $from }}">
    </div>
    <div>
        <label class="mp-label" for="to">{{ __('hospital.reports.to') }}</label>
        <input class="mp-input" type="date" name="to" id="to" value="{{ $to }}">
    </div>
    <button type="submit" class="mp-btn-secondary">{{ __('hospital.reports.generate') }}</button>
</form>

<p class="mb-6 text-sm text-ink-700/70">{{ $facilityName }} · {{ \Carbon\Carbon::parse($from)->format('d M Y') }} – {{ \Carbon\Carbon::parse($to)->format('d M Y') }}</p>

@php
    $ageBandLabels = [
        'under_5' => __('hospital.reports.age_under_5'),
        '5_14' => __('hospital.reports.age_5_14'),
        '15_49' => __('hospital.reports.age_15_49'),
        '50_plus' => __('hospital.reports.age_50_plus'),
        'unknown' => __('hospital.reports.age_unknown'),
    ];
@endphp

<div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4 mb-6">
    <div class="mp-stat">
        <p class="text-xs font-semibold uppercase tracking-wide text-brand-600">{{ __('hospital.reports.anc_visits') }}</p>
        <p class="mt-2 font-display text-3xl text-ink-900">{{ $ancVisitsCount }}</p>
    </div>
    <div class="mp-stat">
        <p class="text-xs font-semibold uppercase tracking-wide text-brand-600">{{ __('hospital.reports.new_pregnancies') }}</p>
        <p class="mt-2 font-display text-3xl text-ink-900">{{ $newPregnanciesCount }}</p>
    </div>
    <div class="mp-stat">
        <p class="text-xs font-semibold uppercase tracking-wide text-brand-600">{{ __('hospital.reports.exemption_visits') }}</p>
        <p class="mt-2 font-display text-3xl text-ink-900">{{ $exemptionVisitsCount }}</p>
    </div>
    <div class="mp-stat">
        <p class="text-xs font-semibold uppercase tracking-wide text-brand-600">{{ __('hospital.reports.total_revenue') }}</p>
        <p class="mt-2 font-display text-2xl text-brand-800">{{ $totalRevenueFormatted }}</p>
    </div>
</div>

<div class="grid gap-6 lg:grid-cols-2">
    <div class="mp-card">
        <h2 class="font-display text-lg text-ink-900">{{ __('hospital.reports.opd_attendance') }}</h2>
        <p class="text-sm text-ink-700/60">{{ __('hospital.reports.opd_attendance_hint') }}</p>
        <div class="mt-4 overflow-x-auto">
            <table class="mp-table">
                <thead>
                    <tr>
                        <th>{{ __('hospital.reports.age_band') }}</th>
                        <th>{{ __('hospital.reports.male') }}</th>
                        <th>{{ __('hospital.reports.female') }}</th>
                        <th>{{ __('hospital.reports.unknown') }}</th>
                        <th>{{ __('hospital.reports.total') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-brand-50">
                    @foreach(['under_5', '5_14', '15_49', '50_plus', 'unknown'] as $band)
                        <tr>
                            <td>{{ $ageBandLabels[$band] }}</td>
                            <td>{{ $opdAttendance[$band]['male'] }}</td>
                            <td>{{ $opdAttendance[$band]['female'] }}</td>
                            <td>{{ $opdAttendance[$band]['unknown'] }}</td>
                            <td class="font-semibold">{{ $opdAttendance[$band]['total'] }}</td>
                        </tr>
                    @endforeach
                    <tr class="bg-brand-50/50 font-semibold">
                        <td>{{ __('hospital.reports.total') }}</td>
                        <td>{{ $opdAttendance['totals']['male'] }}</td>
                        <td>{{ $opdAttendance['totals']['female'] }}</td>
                        <td>{{ $opdAttendance['totals']['unknown'] }}</td>
                        <td>{{ $opdAttendance['totals']['total'] }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="mp-card">
        <h2 class="font-display text-lg text-ink-900">{{ __('hospital.reports.top_diagnoses') }}</h2>
        <ul class="mt-4 space-y-2 text-sm">
            @forelse($topDiagnoses as $dx)
                <li class="flex justify-between rounded-lg bg-sand-50 px-3 py-2">
                    <span>{{ Str::limit($dx->diagnosis_summary, 50) }}</span>
                    <span class="mp-badge bg-brand-50 text-brand-800">{{ $dx->cases }}</span>
                </li>
            @empty
                <li class="text-ink-700/60">{{ __('hospital.reports.no_diagnoses') }}</li>
            @endforelse
        </ul>
    </div>

    <div class="mp-card">
        <h2 class="font-display text-lg text-ink-900">{{ __('hospital.reports.mch_summary') }}</h2>
        <dl class="mt-4 space-y-0 text-sm">
            <div class="flex justify-between border-b border-brand-50 py-2"><dt>{{ __('hospital.reports.anc_visits') }}</dt><dd class="font-semibold">{{ $ancVisitsCount }}</dd></div>
            <div class="flex justify-between border-b border-brand-50 py-2"><dt>{{ __('hospital.reports.new_pregnancies') }}</dt><dd class="font-semibold">{{ $newPregnanciesCount }}</dd></div>
            <div class="flex justify-between py-2"><dt>{{ __('hospital.reports.deliveries_total') }}</dt><dd class="font-semibold">{{ $deliveriesByType->sum() }}</dd></div>
        </dl>
        <h3 class="mt-4 text-sm font-semibold text-ink-800">{{ __('hospital.reports.deliveries_by_type') }}</h3>
        <ul class="mt-2 space-y-1 text-sm">
            @forelse($deliveriesByType as $type => $count)
                <li class="flex justify-between rounded-lg bg-brand-50/50 px-3 py-1.5">
                    <span>{{ str_replace('_', ' ', ucfirst($type)) }}</span>
                    <span class="font-semibold">{{ $count }}</span>
                </li>
            @empty
                <li class="text-ink-700/60">{{ __('hospital.reports.no_deliveries') }}</li>
            @endforelse
        </ul>
        <h3 class="mt-4 text-sm font-semibold text-ink-800">{{ __('hospital.reports.deliveries_by_outcome') }}</h3>
        <ul class="mt-2 space-y-1 text-sm">
            @forelse($deliveriesByOutcome as $outcome => $count)
                <li class="flex justify-between rounded-lg bg-sand-50 px-3 py-1.5">
                    <span>{{ str_replace('_', ' ', ucfirst($outcome)) }}</span>
                    <span class="font-semibold">{{ $count }}</span>
                </li>
            @empty
                <li class="text-ink-700/60">{{ __('hospital.reports.no_deliveries') }}</li>
            @endforelse
        </ul>
    </div>

    <div class="mp-card">
        <h2 class="font-display text-lg text-ink-900">{{ __('hospital.reports.immunizations') }}</h2>
        <ul class="mt-4 space-y-2 text-sm">
            @forelse($immunizationsByVaccine as $row)
                <li class="flex justify-between rounded-lg bg-brand-50/50 px-3 py-2">
                    <span><strong>{{ $row->vaccine_code }}</strong> · {{ $row->vaccine_name }}</span>
                    <span class="font-semibold">{{ $row->total }}</span>
                </li>
            @empty
                <li class="text-ink-700/60">{{ __('hospital.reports.no_immunizations') }}</li>
            @endforelse
        </ul>
    </div>

    <div class="mp-card">
        <h2 class="font-display text-lg text-ink-900">{{ __('hospital.reports.revenue_by_category') }}</h2>
        <ul class="mt-4 space-y-2 text-sm">
            @forelse($revenueByCategory as $category => $total)
                <li class="flex justify-between rounded-lg bg-brand-50/50 px-3 py-2">
                    <span>{{ $paymentCategories[$category] ?? ucfirst($category) }}</span>
                    <span class="font-semibold">{{ \App\Support\Hospital::money($total) }}</span>
                </li>
            @empty
                <li class="text-ink-700/60">{{ __('hospital.reports.no_revenue') }}</li>
            @endforelse
            <li class="flex justify-between border-t border-brand-100 pt-2 font-semibold text-brand-900">
                <span>{{ __('hospital.reports.total') }}</span>
                <span>{{ $totalRevenueFormatted }}</span>
            </li>
        </ul>
        @if($waivedExemptionAmount > 0)
            <p class="mt-3 text-xs text-ink-700/60">{{ __('hospital.reports.waived_included', ['amount' => \App\Support\Hospital::money($waivedExemptionAmount)]) }}</p>
        @endif
    </div>

    <div class="mp-card">
        <h2 class="font-display text-lg text-ink-900">{{ __('hospital.reports.exemptions') }}</h2>
        <dl class="mt-4 space-y-0 text-sm">
            <div class="flex justify-between border-b border-brand-50 py-2"><dt>{{ __('hospital.reports.exemption_visits') }}</dt><dd class="font-semibold">{{ $exemptionVisitsCount }}</dd></div>
            <div class="flex justify-between py-2"><dt>{{ __('hospital.reports.exemption_invoices') }}</dt><dd class="font-semibold">{{ $exemptionInvoicesCount }}</dd></div>
        </dl>
    </div>

    <div class="mp-card lg:col-span-2">
        <h2 class="font-display text-lg text-ink-900">{{ __('hospital.reports.tracer_medicines') }}</h2>
        <div class="mt-4 overflow-x-auto">
            <table class="mp-table">
                <thead>
                    <tr>
                        <th>{{ __('hospital.reports.medicine') }}</th>
                        <th>{{ __('hospital.reports.stock') }}</th>
                        <th>{{ __('hospital.reports.reorder_level') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-brand-50">
                    @forelse($tracerMedicines as $med)
                        <tr>
                            <td>{{ $med->name }}</td>
                            <td><span class="mp-badge bg-red-100 text-red-700">{{ $med->stock_qty }} {{ $med->unit }}</span></td>
                            <td>{{ $med->reorder_level }} {{ $med->unit }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="py-6 text-center text-brand-700">{{ __('hospital.reports.stock_ok') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
