@extends('layouts.hospital')

@section('title', 'IPD / Wards')
@section('eyebrow', 'Inpatient')
@section('heading', 'Admissions & Wards')

@section('content')
<div class="mp-card">
    <div class="mb-5 flex flex-wrap gap-2">
        @foreach(['admitted' => 'Currently admitted', 'discharged' => 'Discharged'] as $key => $label)
            <a href="{{ route('admissions.index', ['status' => $key]) }}"
               class="mp-badge {{ $status === $key ? 'bg-brand-700 text-white' : 'bg-brand-50 text-brand-800' }}">{{ $label }}</a>
        @endforeach
    </div>

    <div class="overflow-x-auto">
        <table class="mp-table">
            <thead>
                <tr>
                    <th>Admission No</th>
                    <th>Patient</th>
                    <th>Ward / Bed</th>
                    <th>Doctor</th>
                    <th>Admitted</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-brand-50">
                @forelse($admissions as $admission)
                    <tr>
                        <td class="font-semibold text-brand-800">{{ $admission->admission_no }}</td>
                        <td>{{ $admission->patient->full_name }}<br><span class="text-xs text-ink-700/60">{{ $admission->patient->mrn }}</span></td>
                        <td>{{ $admission->ward->name ?? '—' }} / {{ $admission->bed->bed_number ?? '—' }}</td>
                        <td>{{ $admission->admittingDoctor->name ?? '—' }}</td>
                        <td>{{ $admission->admitted_at?->format('d M Y H:i') }}</td>
                        <td><span class="mp-badge bg-brand-50 text-brand-800">{{ $admission->status }}</span></td>
                        <td class="text-right">
                            @if($admission->status === 'admitted')
                                <form method="POST" action="{{ route('admissions.discharge', $admission) }}" class="inline" onsubmit="return confirm('Discharge this patient?')">
                                    @csrf
                                    <button type="submit" class="text-sm font-semibold text-brand-700">Discharge</button>
                                </form>
                            @else
                                —
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="py-8 text-center text-ink-700/60">No admissions found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($admissions->hasPages())
        <div class="mt-4">{{ $admissions->links() }}</div>
    @endif
</div>
@endsection
