<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('hospital.reports.mtuha_print_title') }} · {{ $facilityName }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=fraunces:500,600,700|plus-jakarta-sans:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css'])
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white; }
            .mp-card { box-shadow: none; border: 1px solid #e5e7eb; break-inside: avoid; }
        }
    </style>
</head>
<body class="font-sans bg-white text-ink-800">
<div class="mx-auto max-w-5xl p-6">
    <div class="no-print mb-4 flex justify-end gap-2">
        <button onclick="window.print()" class="mp-btn-primary">{{ __('hospital.reports.print') }}</button>
        <a href="{{ route('reports.index', ['from' => $from, 'to' => $to]) }}" class="mp-btn-secondary">{{ __('hospital.reports.back') }}</a>
    </div>

    <header class="border-b border-brand-200 pb-4 text-center">
        <h1 class="font-display text-2xl font-bold text-brand-900">{{ $facilityName }}</h1>
        <p class="mt-1 text-sm uppercase tracking-widest text-brand-600">{{ __('hospital.reports.mtuha_print_title') }}</p>
        <p class="mt-2 text-sm">{{ \Carbon\Carbon::parse($from)->format('d M Y') }} – {{ \Carbon\Carbon::parse($to)->format('d M Y') }}</p>
        <p class="text-xs text-ink-700/60">{{ __('hospital.reports.generated') }} {{ now()->format('d M Y H:i') }}</p>
    </header>

    @php
        $ageBandLabels = [
            'under_5' => __('hospital.reports.age_under_5'),
            '5_14' => __('hospital.reports.age_5_14'),
            '15_49' => __('hospital.reports.age_15_49'),
            '50_plus' => __('hospital.reports.age_50_plus'),
            'unknown' => __('hospital.reports.age_unknown'),
        ];
    @endphp

    <section class="mt-6 mp-card">
        <h2 class="font-display text-lg">{{ __('hospital.reports.opd_attendance') }}</h2>
        <table class="mp-table mt-3 text-sm">
            <thead>
                <tr>
                    <th>{{ __('hospital.reports.age_band') }}</th>
                    <th>M</th>
                    <th>F</th>
                    <th>?</th>
                    <th>{{ __('hospital.reports.total') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach(['under_5', '5_14', '15_49', '50_plus', 'unknown'] as $band)
                    <tr>
                        <td>{{ $ageBandLabels[$band] }}</td>
                        <td>{{ $opdAttendance[$band]['male'] }}</td>
                        <td>{{ $opdAttendance[$band]['female'] }}</td>
                        <td>{{ $opdAttendance[$band]['unknown'] }}</td>
                        <td><strong>{{ $opdAttendance[$band]['total'] }}</strong></td>
                    </tr>
                @endforeach
                <tr class="font-semibold">
                    <td>{{ __('hospital.reports.total') }}</td>
                    <td>{{ $opdAttendance['totals']['male'] }}</td>
                    <td>{{ $opdAttendance['totals']['female'] }}</td>
                    <td>{{ $opdAttendance['totals']['unknown'] }}</td>
                    <td>{{ $opdAttendance['totals']['total'] }}</td>
                </tr>
            </tbody>
        </table>
    </section>

    <div class="mt-6 grid gap-4 sm:grid-cols-2">
        <section class="mp-card">
            <h2 class="font-display text-base">{{ __('hospital.reports.top_diagnoses') }}</h2>
            <table class="mp-table mt-2 text-xs">
                <thead><tr><th>{{ __('hospital.reports.diagnosis') }}</th><th>#</th></tr></thead>
                <tbody>
                    @foreach($topDiagnoses as $dx)
                        <tr><td>{{ Str::limit($dx->diagnosis_summary, 40) }}</td><td>{{ $dx->cases }}</td></tr>
                    @endforeach
                </tbody>
            </table>
        </section>

        <section class="mp-card">
            <h2 class="font-display text-base">{{ __('hospital.reports.mch_summary') }}</h2>
            <table class="mp-table mt-2 text-xs">
                <tbody>
                    <tr><td>{{ __('hospital.reports.anc_visits') }}</td><td>{{ $ancVisitsCount }}</td></tr>
                    <tr><td>{{ __('hospital.reports.new_pregnancies') }}</td><td>{{ $newPregnanciesCount }}</td></tr>
                    <tr><td>{{ __('hospital.reports.deliveries_total') }}</td><td>{{ $deliveriesByType->sum() }}</td></tr>
                    <tr><td>{{ __('hospital.reports.exemption_visits') }}</td><td>{{ $exemptionVisitsCount }}</td></tr>
                    <tr><td>{{ __('hospital.reports.exemption_invoices') }}</td><td>{{ $exemptionInvoicesCount }}</td></tr>
                </tbody>
            </table>
        </section>
    </div>

    <div class="mt-4 grid gap-4 sm:grid-cols-2">
        <section class="mp-card">
            <h2 class="font-display text-base">{{ __('hospital.reports.deliveries_by_type') }}</h2>
            <table class="mp-table mt-2 text-xs">
                <tbody>
                    @forelse($deliveriesByType as $type => $count)
                        <tr><td>{{ str_replace('_', ' ', $type) }}</td><td>{{ $count }}</td></tr>
                    @empty
                        <tr><td colspan="2">—</td></tr>
                    @endforelse
                </tbody>
            </table>
        </section>
        <section class="mp-card">
            <h2 class="font-display text-base">{{ __('hospital.reports.immunizations') }}</h2>
            <table class="mp-table mt-2 text-xs">
                <tbody>
                    @forelse($immunizationsByVaccine as $row)
                        <tr><td>{{ $row->vaccine_code }}</td><td>{{ $row->total }}</td></tr>
                    @empty
                        <tr><td colspan="2">—</td></tr>
                    @endforelse
                </tbody>
            </table>
        </section>
    </div>

    <section class="mt-4 mp-card">
        <h2 class="font-display text-base">{{ __('hospital.reports.revenue_by_category') }}</h2>
        <table class="mp-table mt-2 text-xs">
            <tbody>
                @foreach($revenueByCategory as $category => $total)
                    <tr>
                        <td>{{ $paymentCategories[$category] ?? $category }}</td>
                        <td>{{ \App\Support\Hospital::money($total) }}</td>
                    </tr>
                @endforeach
                <tr class="font-semibold"><td>{{ __('hospital.reports.total') }}</td><td>{{ $totalRevenueFormatted }}</td></tr>
            </tbody>
        </table>
    </section>

    <section class="mt-4 mp-card">
        <h2 class="font-display text-base">{{ __('hospital.reports.tracer_medicines') }}</h2>
        <table class="mp-table mt-2 text-xs">
            <thead><tr><th>{{ __('hospital.reports.medicine') }}</th><th>{{ __('hospital.reports.stock') }}</th><th>{{ __('hospital.reports.reorder_level') }}</th></tr></thead>
            <tbody>
                @forelse($tracerMedicines as $med)
                    <tr>
                        <td>{{ $med->name }}</td>
                        <td>{{ $med->stock_qty }}</td>
                        <td>{{ $med->reorder_level }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3">{{ __('hospital.reports.stock_ok') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </section>
</div>
</body>
</html>
