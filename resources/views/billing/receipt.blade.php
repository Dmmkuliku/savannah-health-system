<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Receipt {{ $payment->receipt_no }} · {{ $facilityName }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css'])
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white; }
        }
    </style>
</head>
<body class="font-sans bg-sand-50 text-ink-800">
<div class="mx-auto max-w-md p-6">
    <div class="mp-card">
        <div class="text-center border-b border-brand-100 pb-4">
            <h1 class="font-display text-xl font-bold text-brand-900">{{ $facilityName }}</h1>
            <p class="text-xs uppercase tracking-widest text-brand-600 mt-1">Official Payment Receipt</p>
        </div>

        <dl class="mt-4 space-y-2 text-sm">
            <div class="flex justify-between"><dt class="text-ink-700/60">Receipt No</dt><dd class="font-semibold">{{ $payment->receipt_no }}</dd></div>
            <div class="flex justify-between"><dt class="text-ink-700/60">Date</dt><dd>{{ $payment->created_at->format('d M Y H:i') }}</dd></div>
            <div class="flex justify-between"><dt class="text-ink-700/60">Invoice</dt><dd>{{ $payment->invoice->invoice_no }}</dd></div>
            <div class="flex justify-between"><dt class="text-ink-700/60">Patient</dt><dd>{{ $payment->invoice->patient->full_name }}</dd></div>
            <div class="flex justify-between"><dt class="text-ink-700/60">MRN</dt><dd>{{ $payment->invoice->patient->mrn }}</dd></div>
            <div class="flex justify-between"><dt class="text-ink-700/60">Method</dt><dd class="capitalize">{{ str_replace('_', ' ', $payment->method) }}@if($payment->mobile_provider) ({{ $payment->mobile_provider }})@endif</dd></div>
            @if($payment->reference)
                <div class="flex justify-between"><dt class="text-ink-700/60">Reference</dt><dd>{{ $payment->reference }}</dd></div>
            @endif
        </dl>

        <div class="mt-4 rounded-xl bg-brand-50 p-4 text-center">
            <p class="text-xs uppercase tracking-wide text-brand-700">Amount paid</p>
            <p class="font-display text-3xl font-bold text-brand-900">{{ \App\Support\Hospital::money($payment->amount) }}</p>
        </div>

        <div class="mt-4 text-xs text-ink-700/60">
            <p>Received by: {{ $payment->receivedBy->name ?? '—' }}</p>
            <p class="mt-2">Invoice balance after payment: {{ \App\Support\Hospital::money($payment->invoice->balance) }}</p>
            @if($payment->notes)
                <p class="mt-2">Notes: {{ $payment->notes }}</p>
            @endif
        </div>

        <p class="mt-6 text-center text-xs text-brand-700">Asante — Thank you for choosing {{ $facilityName }}</p>

        <div class="no-print mt-6 flex gap-3 justify-center">
            <button onclick="window.print()" class="mp-btn">Print receipt</button>
            <button onclick="window.close()" class="mp-btn-secondary">Close</button>
        </div>
    </div>
</div>
</body>
</html>
