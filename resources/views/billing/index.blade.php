@extends('layouts.hospital')

@section('title', 'Billing')
@section('eyebrow', 'Finance')
@section('heading', 'Billing & Invoices')

@section('content')
<div class="mp-card">
    <form method="GET" class="mb-5 flex flex-wrap items-end gap-3">
        <div>
            <label class="mp-label" for="status">Status</label>
            <select class="mp-input" name="status" id="status">
                <option value="">All</option>
                @foreach(['unpaid','partial','paid','waived','cancelled'] as $s)
                    <option value="{{ $s }}" @selected($status === $s)>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="mp-btn-secondary">Filter</button>
    </form>

    <div class="overflow-x-auto">
        <table class="mp-table">
            <thead>
                <tr>
                    <th>Invoice No</th>
                    <th>Patient</th>
                    <th>Visit</th>
                    <th>Total</th>
                    <th>Balance</th>
                    <th>Payment</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-brand-50">
                @forelse($invoices as $invoice)
                    <tr>
                        <td class="font-semibold text-brand-800">{{ $invoice->invoice_no }}</td>
                        <td>{{ $invoice->patient->full_name }}<br><span class="text-xs text-ink-700/60">{{ $invoice->patient->mrn }}</span></td>
                        <td>{{ $invoice->visit->visit_no ?? '—' }}</td>
                        <td>{{ \App\Support\Hospital::money($invoice->total) }}</td>
                        <td>{{ \App\Support\Hospital::money($invoice->balance) }}</td>
                        <td>{{ $paymentCategories[$invoice->payment_category] ?? $invoice->payment_category }}</td>
                        <td><span class="mp-badge {{ $invoice->status === 'paid' ? 'bg-brand-100 text-brand-900' : 'bg-sand-100 text-sand-600' }}">{{ $invoice->status }}</span></td>
                        <td class="text-right"><a href="{{ route('billing.show', $invoice) }}" class="font-semibold text-brand-700">Open</a></td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="py-8 text-center text-ink-700/60">No invoices found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($invoices->hasPages())
        <div class="mt-4">{{ $invoices->links() }}</div>
    @endif
</div>
@endsection
