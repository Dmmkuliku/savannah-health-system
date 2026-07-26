@extends('layouts.hospital')

@section('title', 'Radiology')
@section('eyebrow', 'Diagnostics')
@section('heading', 'Radiology Orders')

@section('content')
<div class="mp-card">
    <div class="mb-5 flex flex-wrap gap-2">
        @foreach(['pending' => 'Pending', 'in_progress' => 'In progress', 'completed' => 'Completed'] as $key => $label)
            <a href="{{ route('radiology.orders.index', ['status' => $key]) }}"
               class="mp-badge {{ $status === $key ? 'bg-brand-700 text-white' : 'bg-brand-50 text-brand-800' }}">{{ $label }}</a>
        @endforeach
    </div>

    <div class="overflow-x-auto">
        <table class="mp-table">
            <thead>
                <tr>
                    <th>Order No</th>
                    <th>Patient</th>
                    <th>Service</th>
                    <th>Price</th>
                    <th>Status</th>
                    <th>Ordered</th>
                    <th></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-brand-50">
                @forelse($orders as $order)
                    <tr>
                        <td class="font-semibold text-brand-800">{{ $order->order_no }}</td>
                        <td>{{ $order->patient->full_name }}<br><span class="text-xs text-ink-700/60">{{ $order->patient->mrn }}</span></td>
                        <td>{{ $order->radiologyService->name }}</td>
                        <td>{{ \App\Support\Hospital::money($order->price) }}</td>
                        <td><span class="mp-badge bg-brand-50 text-brand-800">{{ str_replace('_', ' ', $order->status) }}</span></td>
                        <td>{{ $order->created_at->format('d M Y') }}</td>
                        <td class="text-right"><a href="{{ route('radiology.orders.show', $order) }}" class="font-semibold text-brand-700">Open</a></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="py-8 text-center text-ink-700/60">No radiology orders.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($orders->hasPages())
        <div class="mt-4">{{ $orders->links() }}</div>
    @endif
</div>
@endsection
