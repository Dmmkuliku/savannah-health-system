@extends('layouts.hospital')

@section('title', 'Visits / OPD')
@section('eyebrow', 'Outpatient')
@section('heading', 'Visits & OPD Queue')

@section('actions')
    <a href="{{ route('visits.create') }}" class="mp-btn">New visit</a>
@endsection

@section('content')
<div class="mp-card">
    <form method="GET" class="mb-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
        <div>
            <label class="mp-label" for="date">Date</label>
            <input class="mp-input" type="date" name="date" id="date" value="{{ $filters['date'] }}">
        </div>
        <div>
            <label class="mp-label" for="status">Status</label>
            <select class="mp-input" name="status" id="status">
                <option value="">All statuses</option>
                @foreach(['registered','waiting','in_consultation','investigations','pharmacy','billing','admitted','discharged','completed','cancelled'] as $s)
                    <option value="{{ $s }}" @selected($filters['status'] === $s)>{{ str_replace('_', ' ', ucfirst($s)) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="mp-label" for="department_id">Department</label>
            <select class="mp-input" name="department_id" id="department_id">
                <option value="">All departments</option>
                @foreach($departments as $dept)
                    <option value="{{ $dept->id }}" @selected($filters['department_id'] == $dept->id)>{{ $dept->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="mp-label" for="visit_type">Visit type</label>
            <select class="mp-input" name="visit_type" id="visit_type">
                <option value="">All types</option>
                @foreach(['opd','ipd','emergency','rch','dental','eye','specialist'] as $t)
                    <option value="{{ $t }}" @selected($filters['visit_type'] === $t)>{{ strtoupper($t) }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-end">
            <button type="submit" class="mp-btn-secondary w-full">Filter</button>
        </div>
    </form>

    <div class="overflow-x-auto">
        <table class="mp-table">
            <thead>
                <tr>
                    <th>Visit No</th>
                    <th>Patient</th>
                    <th>Type</th>
                    <th>Department</th>
                    <th>Status</th>
                    <th>Time</th>
                    <th></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-brand-50">
                @forelse($visits as $visit)
                    <tr>
                        <td class="font-semibold text-brand-800">{{ $visit->visit_no }}</td>
                        <td>{{ $visit->patient->full_name }}<br><span class="text-xs text-ink-700/60">{{ $visit->patient->mrn }}</span></td>
                        <td class="uppercase">{{ $visit->visit_type }}</td>
                        <td>{{ $visit->department->name ?? '—' }}</td>
                        <td><span class="mp-badge bg-brand-50 text-brand-800">{{ str_replace('_', ' ', $visit->status) }}</span></td>
                        <td>{{ $visit->visited_at?->format('H:i') }}</td>
                        <td class="text-right"><a href="{{ route('visits.show', $visit) }}" class="font-semibold text-brand-700">Open</a></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="py-8 text-center text-ink-700/60">No visits for selected filters.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($visits->hasPages())
        <div class="mt-4">{{ $visits->links() }}</div>
    @endif
</div>
@endsection
