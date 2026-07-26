@extends('layouts.hospital')

@section('title', 'Patients')
@section('eyebrow', 'Registration')
@section('heading', 'Patient Registry')

@section('actions')
    <a href="{{ route('patients.create') }}" class="mp-btn">Register patient</a>
@endsection

@section('content')
<div class="mp-card">
    <form method="GET" action="{{ route('patients.index') }}" class="mb-5 flex flex-wrap items-end gap-3">
        <div class="min-w-[240px] flex-1">
            <label class="mp-label" for="search">Search MRN, name, phone, NHIF</label>
            <input class="mp-input" type="search" name="search" id="search" value="{{ $search }}" placeholder="Search patients...">
        </div>
        <button type="submit" class="mp-btn-secondary">Search</button>
        @if($search)
            <a href="{{ route('patients.index') }}" class="mp-btn-secondary">Clear</a>
        @endif
    </form>

    <div class="overflow-x-auto">
        <table class="mp-table">
            <thead>
                <tr>
                    <th>MRN</th>
                    <th>Name</th>
                    <th>Gender</th>
                    <th>Phone</th>
                    <th>Payment</th>
                    <th>Registered</th>
                    <th></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-brand-50">
                @forelse($patients as $patient)
                    <tr>
                        <td class="font-semibold text-brand-800">{{ $patient->mrn }}</td>
                        <td>{{ $patient->full_name }}</td>
                        <td class="capitalize">{{ $patient->gender }}</td>
                        <td>{{ $patient->phone ?? '—' }}</td>
                        <td>
                            <span class="mp-badge bg-brand-50 text-brand-800">{{ $paymentCategories[$patient->payment_category] ?? $patient->payment_category }}</span>
                        </td>
                        <td>{{ $patient->created_at->format('d M Y') }}</td>
                        <td class="text-right">
                            <a href="{{ route('patients.show', $patient) }}" class="text-sm font-semibold text-brand-700 hover:text-brand-900">View</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="py-8 text-center text-ink-700/60">No patients found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($patients->hasPages())
        <div class="mt-4">{{ $patients->links() }}</div>
    @endif
</div>
@endsection
