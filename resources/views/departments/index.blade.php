@extends('layouts.hospital')

@section('title', 'Departments')
@section('eyebrow', 'Facility')
@section('heading', 'Departments & Ward Capacity')

@section('content')
<div class="space-y-6">
    @foreach($departments as $deptData)
        @php $dept = $deptData['department']; @endphp
        <div class="mp-card">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h2 class="font-display text-xl text-ink-900">{{ $dept->name }}</h2>
                    @if($dept->code)
                        <p class="text-sm text-ink-700/60">{{ $dept->code }}</p>
                    @endif
                </div>
                <div class="text-right text-sm">
                    <p class="font-semibold text-brand-800">{{ $deptData['occupied_beds'] }} / {{ $deptData['total_beds'] }} beds occupied</p>
                </div>
            </div>

            @if($deptData['wards']->isNotEmpty())
                <div class="mt-4 overflow-x-auto">
                    <table class="mp-table">
                        <thead>
                            <tr>
                                <th>Ward</th>
                                <th>Total beds</th>
                                <th>Available</th>
                                <th>Occupied</th>
                                <th>Active admissions</th>
                                <th>Occupancy</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-brand-50">
                            @foreach($deptData['wards'] as $wardStat)
                                <tr>
                                    <td class="font-semibold">{{ $wardStat['ward']->name }}</td>
                                    <td>{{ $wardStat['total_beds'] }}</td>
                                    <td><span class="mp-badge bg-brand-50 text-brand-800">{{ $wardStat['available_beds'] }}</span></td>
                                    <td>{{ $wardStat['occupied_beds'] }}</td>
                                    <td>{{ $wardStat['active_admissions'] }}</td>
                                    <td>{{ $wardStat['occupancy_rate'] }}%</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="mt-3 text-sm text-ink-700/60">No wards configured for this department.</p>
            @endif
        </div>
    @endforeach
</div>
@endsection
