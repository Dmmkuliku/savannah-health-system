@extends('layouts.hospital')

@section('title', __('hospital.theatre.title'))
@section('eyebrow', __('hospital.theatre.eyebrow'))
@section('heading', __('hospital.theatre.cases'))

@section('actions')
    <a href="{{ route('theatre.create') }}" class="mp-btn">{{ __('hospital.theatre.schedule') }}</a>
@endsection

@section('content')
<div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
    @foreach(['available', 'occupied', 'cleaning', 'maintenance'] as $roomStatus)
        <div class="mp-card rounded-xl p-4">
            <p class="text-xs font-semibold uppercase tracking-wide text-ink-700/60">{{ ucfirst(str_replace('_', ' ', $roomStatus)) }}</p>
            <p class="mt-1 font-display text-2xl text-brand-800">{{ $roomStatusCounts[$roomStatus] ?? 0 }}</p>
        </div>
    @endforeach
</div>

<div class="mb-6 grid gap-4 lg:grid-cols-3">
    @foreach($rooms as $room)
        <div class="mp-card rounded-xl p-4">
            <div class="flex items-start justify-between gap-2">
                <div>
                    <p class="font-semibold text-brand-900">{{ $room->code }}</p>
                    <p class="text-sm text-ink-700">{{ $room->name_sw ?? $room->name }}</p>
                </div>
                <span class="mp-badge bg-brand-50 text-brand-800">{{ $room->status }}</span>
            </div>
            <p class="mt-2 text-xs text-ink-700/60">{{ $room->active_cases_count }} active case(s)</p>
        </div>
    @endforeach
</div>

<div class="mp-card">
    <div class="mb-5 flex flex-wrap gap-2">
        <a href="{{ route('theatre.index') }}"
           class="mp-badge {{ empty($status) ? 'bg-brand-700 text-white' : 'bg-brand-50 text-brand-800' }}">All</a>
        @foreach(['scheduled', 'pre_op', 'in_theatre', 'recovery', 'completed', 'cancelled'] as $key)
            <a href="{{ route('theatre.index', ['status' => $key]) }}"
               class="mp-badge {{ $status === $key ? 'bg-brand-700 text-white' : 'bg-brand-50 text-brand-800' }}">{{ str_replace('_', ' ', $key) }}</a>
        @endforeach
    </div>

    <div class="overflow-x-auto">
        <table class="mp-table">
            <thead>
                <tr>
                    <th>Case No</th>
                    <th>{{ __('hospital.theatre.procedure') }}</th>
                    <th>Patient</th>
                    <th>Room</th>
                    <th>{{ __('hospital.theatre.surgeon') }}</th>
                    <th>{{ __('hospital.theatre.urgency') }}</th>
                    <th>Scheduled</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-brand-50">
                @forelse($cases as $case)
                    <tr>
                        <td class="font-semibold text-brand-800">{{ $case->case_no }}</td>
                        <td>{{ $case->procedure_name }}</td>
                        <td>{{ $case->patient->full_name }}<br><span class="text-xs text-ink-700/60">{{ $case->patient->mrn }}</span></td>
                        <td>{{ $case->theatreRoom->code ?? '—' }}</td>
                        <td>{{ $case->surgeon->name ?? '—' }}</td>
                        <td class="capitalize">{{ $case->urgency }}</td>
                        <td>{{ $case->scheduled_at?->format('d M Y H:i') ?? '—' }}</td>
                        <td><span class="mp-badge bg-brand-50 text-brand-800">{{ str_replace('_', ' ', $case->status) }}</span></td>
                        <td class="text-right"><a href="{{ route('theatre.show', $case) }}" class="font-semibold text-brand-700">View</a></td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="py-8 text-center text-ink-700/60">No theatre cases found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($cases->hasPages())
        <div class="mt-4">{{ $cases->links() }}</div>
    @endif
</div>
@endsection
