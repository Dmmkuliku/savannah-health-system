@extends('layouts.hospital')

@section('title', $invoice->invoice_no)
@section('eyebrow', 'Billing')
@section('heading', 'Invoice '.$invoice->invoice_no)

@section('actions')
    @if($invoice->visit)
        <a href="{{ route('visits.show', $invoice->visit) }}" class="mp-btn-secondary">Visit</a>
    @endif
@endsection

@section('content')
<div class="grid gap-6 lg:grid-cols-3">
    <div class="mp-card lg:col-span-2">
        <dl class="grid gap-3 sm:grid-cols-2 text-sm mb-4">
            <div><dt class="text-ink-700/60">Patient</dt><dd class="font-semibold">{{ $invoice->patient->full_name }} ({{ $invoice->patient->mrn }})</dd></div>
            <div><dt class="text-ink-700/60">Payment category</dt><dd class="capitalize">{{ str_replace('_', ' ', $invoice->payment_category) }}</dd></div>
            <div><dt class="text-ink-700/60">Status</dt><dd><span class="mp-badge bg-brand-50 text-brand-800">{{ $invoice->status }}</span></dd></div>
            <div><dt class="text-ink-700/60">Created</dt><dd>{{ $invoice->created_at->format('d M Y H:i') }}</dd></div>
        </dl>

        <table class="mp-table">
            <thead><tr><th>Description</th><th>Qty</th><th>Unit price</th><th>Total</th></tr></thead>
            <tbody class="divide-y divide-brand-50">
                @foreach($invoice->items as $item)
                    <tr>
                        <td>{{ $item->description }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ \App\Support\Hospital::money($item->unit_price) }}</td>
                        <td>{{ \App\Support\Hospital::money($item->total) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="font-semibold">
                    <td colspan="3" class="text-right">Subtotal</td>
                    <td>{{ \App\Support\Hospital::money($invoice->subtotal) }}</td>
                </tr>
                <tr class="font-semibold text-brand-800">
                    <td colspan="3" class="text-right">Paid</td>
                    <td>{{ \App\Support\Hospital::money($invoice->paid_amount) }}</td>
                </tr>
                <tr class="font-display text-lg">
                    <td colspan="3" class="text-right">Balance due</td>
                    <td>{{ \App\Support\Hospital::money($invoice->balance) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>

    <div class="space-y-6">
        @if($invoice->balance > 0 && !in_array($invoice->status, ['paid', 'waived', 'cancelled']))
        <div class="mp-card">
            <h2 class="font-display text-lg text-ink-900">{{ __('hospital.gepg.title') }}</h2>
            <p class="mt-1 text-xs text-ink-700/60">{{ __('hospital.gepg.stub_note') }}</p>
            <form method="POST" action="{{ route('gepg.generate', $invoice) }}" class="mt-4 space-y-3">
                @csrf
                <div>
                    <label class="mp-label" for="payer_phone">Payer phone (mobile money)</label>
                    <input class="mp-input" type="text" name="payer_phone" id="payer_phone" value="{{ old('payer_phone', $invoice->patient->phone) }}">
                </div>
                <button type="submit" class="mp-btn w-full">{{ __('hospital.gepg.generate') }}</button>
            </form>
            @if($invoice->gepgBills->isNotEmpty())
                <ul class="mt-4 space-y-2 text-sm">
                    @foreach($invoice->gepgBills->sortByDesc('id') as $bill)
                        <li class="rounded-lg border border-brand-100 bg-brand-50/50 p-3">
                            <div class="font-semibold text-brand-900">{{ __('hospital.gepg.control_no') }}: {{ $bill->control_number }}</div>
                            <p class="text-xs text-ink-700/70">{{ \App\Support\Hospital::money($bill->amount) }} · {{ $bill->status }}
                                @if($bill->expires_at) · {{ __('hospital.gepg.expires') }} {{ $bill->expires_at->format('d M Y') }} @endif
                            </p>
                            @if($bill->status === 'pending')
                                <form method="POST" action="{{ route('gepg.simulate-pay', $bill) }}" class="mt-2">
                                    @csrf
                                    <button class="text-xs font-semibold text-sand-500 hover:text-brand-800">{{ __('hospital.gepg.mark_paid') }}</button>
                                </form>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        <div class="mp-card">
            <h2 class="font-display text-lg text-ink-900">Record payment</h2>
            <form method="POST" action="{{ route('billing.store-payment', $invoice) }}" class="mt-4 space-y-3" id="payment-form">
                @csrf
                <div>
                    <label class="mp-label" for="amount">Amount (TZS) *</label>
                    <input class="mp-input" type="number" step="0.01" min="1" max="{{ $invoice->balance }}" name="amount" id="amount" value="{{ old('amount', $invoice->balance) }}" required>
                </div>
                <div>
                    <label class="mp-label" for="method">Payment method *</label>
                    <select class="mp-input" name="method" id="method" required>
                        @foreach(['cash' => 'Cash (Fedha taslimu)', 'mobile_money' => 'Mobile money', 'bank' => 'Bank transfer', 'nhif' => 'NHIF', 'exemption' => 'Exemption (Msamaha)', 'card' => 'Card'] as $val => $label)
                            <option value="{{ $val }}" @selected(old('method', 'cash') === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div id="mobile-provider-wrap" class="hidden">
                    <label class="mp-label" for="mobile_provider">Mobile provider</label>
                    <select class="mp-input" name="mobile_provider" id="mobile_provider">
                        <option value="">— Select —</option>
                        @foreach(['M-Pesa', 'TigoPesa', 'AirtelMoney', 'Halopesa'] as $provider)
                            <option value="{{ $provider }}" @selected(old('mobile_provider') === $provider)>{{ $provider }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mp-label" for="reference">Reference / transaction ID</label>
                    <input class="mp-input" type="text" name="reference" id="reference" value="{{ old('reference') }}">
                </div>
                <div>
                    <label class="mp-label" for="notes">Notes</label>
                    <textarea class="mp-input" name="notes" id="notes" rows="2">{{ old('notes') }}</textarea>
                </div>
                <button type="submit" class="mp-btn w-full">Record payment</button>
            </form>
        </div>
        @endif

        @if($invoice->payments->isNotEmpty())
        <div class="mp-card">
            <h2 class="font-display text-lg text-ink-900">Payments</h2>
            <ul class="mt-3 space-y-2 text-sm">
                @foreach($invoice->payments as $payment)
                    <li class="rounded-lg bg-brand-50/50 px-3 py-2">
                        <div class="flex justify-between">
                            <span>{{ \App\Support\Hospital::money($payment->amount) }}</span>
                            <a href="{{ route('billing.receipt', $payment) }}" target="_blank" class="font-semibold text-brand-700">Receipt</a>
                        </div>
                        <p class="text-xs text-ink-700/60 capitalize">{{ str_replace('_', ' ', $payment->method) }} · {{ $payment->created_at->format('d M Y H:i') }}</p>
                    </li>
                @endforeach
            </ul>
        </div>
        @endif

        @if(in_array($invoice->payment_category, ['nhif'], true) || $invoice->patient->nhif_card_no)
        <div class="mp-card">
            <h2 class="font-display text-lg text-ink-900">{{ __('hospital.nhif_claims.title') }}</h2>
            <p class="mt-1 text-xs text-ink-700/60">Create an NHIF claim from this invoice (stub ready for live claims API).</p>
            <form method="POST" action="{{ route('nhif.claims.from-invoice') }}" class="mt-3">
                @csrf
                <input type="hidden" name="invoice_id" value="{{ $invoice->id }}">
                <button type="submit" class="mp-btn w-full">Create NHIF claim</button>
            </form>
            <a href="{{ route('nhif.claims.index') }}" class="mt-2 inline-block text-xs font-semibold text-brand-700">Open NHIF claims desk</a>
        </div>
        @endif
    </div>
</div>

<script>
const methodEl = document.getElementById('method');
const mobileWrap = document.getElementById('mobile-provider-wrap');
function toggleMobile() {
    if (!methodEl || !mobileWrap) return;
    mobileWrap.classList.toggle('hidden', methodEl.value !== 'mobile_money');
}
methodEl?.addEventListener('change', toggleMobile);
toggleMobile();
</script>
@endsection
