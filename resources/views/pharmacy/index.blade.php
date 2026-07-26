@extends('layouts.hospital')

@section('title', 'Pharmacy')
@section('eyebrow', 'Dispensing')
@section('heading', 'Pharmacy Queue')

@section('content')
<div class="mp-card">
    <div class="overflow-x-auto">
        <table class="mp-table">
            <thead>
                <tr>
                    <th>Rx No</th>
                    <th>Patient</th>
                    <th>Doctor</th>
                    <th>Items</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-brand-50">
                @forelse($prescriptions as $rx)
                    <tr>
                        <td class="font-semibold text-brand-800">{{ $rx->prescription_no }}</td>
                        <td>{{ $rx->patient->full_name }}<br><span class="text-xs text-ink-700/60">{{ $rx->patient->mrn }}</span></td>
                        <td>{{ $rx->doctor->name ?? '—' }}</td>
                        <td>{{ $rx->items->count() }}</td>
                        <td><span class="mp-badge bg-brand-50 text-brand-800">{{ $rx->status }}</span></td>
                        <td>{{ $rx->created_at->format('d M Y') }}</td>
                        <td class="text-right"><a href="{{ route('pharmacy.show', $rx) }}" class="font-semibold text-brand-700">Dispense</a></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="py-8 text-center text-ink-700/60">No pending prescriptions.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($prescriptions->hasPages())
        <div class="mt-4">{{ $prescriptions->links() }}</div>
    @endif
</div>
@endsection
