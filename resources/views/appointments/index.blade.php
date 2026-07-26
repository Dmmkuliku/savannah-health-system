@extends('layouts.hospital')

@section('title', 'Appointments')
@section('eyebrow', 'Scheduling')
@section('heading', 'Appointments')

@section('actions')
    <a href="{{ route('appointments.create') }}" class="mp-btn">Schedule appointment</a>
@endsection

@section('content')
<div class="mp-card">
    <form method="GET" class="mb-5 flex flex-wrap items-end gap-3">
        <div>
            <label class="mp-label" for="date">Date</label>
            <input class="mp-input" type="date" name="date" id="date" value="{{ $date }}">
        </div>
        <div>
            <label class="mp-label" for="status">Status</label>
            <select class="mp-input" name="status" id="status">
                <option value="">All</option>
                @foreach(['scheduled','checked_in','completed','cancelled','no_show'] as $s)
                    <option value="{{ $s }}" @selected($status === $s)>{{ str_replace('_', ' ', ucfirst($s)) }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="mp-btn-secondary">Filter</button>
    </form>

    <div class="overflow-x-auto">
        <table class="mp-table">
            <thead>
                <tr>
                    <th>Time</th>
                    <th>Patient</th>
                    <th>Doctor</th>
                    <th>Department</th>
                    <th>Reason</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-brand-50">
                @forelse($appointments as $appointment)
                    <tr>
                        <td class="font-semibold">{{ $appointment->appointment_time ? substr($appointment->appointment_time, 0, 5) : '—' }}</td>
                        <td>{{ $appointment->patient->full_name }}<br><span class="text-xs text-ink-700/60">{{ $appointment->patient->mrn }}</span></td>
                        <td>{{ $appointment->doctor->name ?? '—' }}</td>
                        <td>{{ $appointment->department->name ?? '—' }}</td>
                        <td>{{ Str::limit($appointment->reason, 40) ?? '—' }}</td>
                        <td><span class="mp-badge bg-brand-50 text-brand-800">{{ str_replace('_', ' ', $appointment->status) }}</span></td>
                        <td>
                            @if($appointment->status === 'scheduled')
                                <form method="POST" action="{{ route('appointments.update-status', $appointment) }}" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="checked_in">
                                    <button type="submit" class="text-sm font-semibold text-brand-700">Check in</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="py-8 text-center text-ink-700/60">No appointments for this date.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($appointments->hasPages())
        <div class="mt-4">{{ $appointments->links() }}</div>
    @endif
</div>
@endsection
