@extends('layouts.hospital')

@section('title', $bloodRequest->request_no)
@section('eyebrow', __('hospital.blood.eyebrow'))
@section('heading', $bloodRequest->request_no)

@section('actions')
    <a href="{{ route('blood.index') }}" class="mp-btn-secondary">Back</a>
@endsection

@section('content')
<div class="mp-card mb-6">
    <dl class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4 text-sm">
        <div><dt class="text-ink-700/60">Patient</dt><dd class="font-semibold">{{ $bloodRequest->patient->full_name }} ({{ $bloodRequest->patient->mrn }})</dd></div>
        <div><dt class="text-ink-700/60">{{ __('hospital.blood.blood_group') }}</dt><dd>{{ $bloodRequest->blood_group }}</dd></div>
        <div><dt class="text-ink-700/60">{{ __('hospital.blood.component') }}</dt><dd>{{ str_replace('_', ' ', $bloodRequest->component) }}</dd></div>
        <div><dt class="text-ink-700/60">Units</dt><dd>{{ $bloodRequest->units_issued }}/{{ $bloodRequest->units_requested }}</dd></div>
        <div><dt class="text-ink-700/60">Priority</dt><dd class="capitalize">{{ $bloodRequest->priority }}</dd></div>
        <div><dt class="text-ink-700/60">Status</dt><dd><span class="mp-badge bg-brand-50 text-brand-800">{{ $bloodRequest->status }}</span></dd></div>
        <div><dt class="text-ink-700/60">Requested by</dt><dd>{{ $bloodRequest->requestedBy->name ?? '—' }}</dd></div>
        <div><dt class="text-ink-700/60">Theatre case</dt><dd>{{ $bloodRequest->theatreCase->case_no ?? '—' }}</dd></div>
        @if($bloodRequest->indication)
            <div class="sm:col-span-2"><dt class="text-ink-700/60">Indication</dt><dd>{{ $bloodRequest->indication }}</dd></div>
        @endif
        @if($bloodRequest->notes)
            <div class="sm:col-span-2 lg:col-span-4"><dt class="text-ink-700/60">Notes</dt><dd>{{ $bloodRequest->notes }}</dd></div>
        @endif
    </dl>
</div>

@if(!in_array($bloodRequest->status, ['fulfilled', 'cancelled']) && $bloodRequest->units_issued < $bloodRequest->units_requested)
    <div class="mp-card mb-6">
        <h2 class="font-display text-lg text-ink-900">{{ __('hospital.blood.issue') }}</h2>
        @if($availableUnits->isEmpty())
            <p class="mt-3 text-sm text-red-700">No matching available units for {{ $bloodRequest->blood_group }} / {{ str_replace('_', ' ', $bloodRequest->component) }}.</p>
        @else
            <form method="POST" action="{{ route('blood.requests.issue', $bloodRequest) }}" class="mt-4">
                @csrf
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="mp-label" for="blood_unit_id">Blood unit</label>
                        <select class="mp-input" name="blood_unit_id" id="blood_unit_id">
                            <option value="">— Auto-select earliest expiry —</option>
                            @foreach($availableUnits as $unit)
                                <option value="{{ $unit->id }}" @selected(old('blood_unit_id') == $unit->id)>
                                    {{ $unit->unit_no }} · {{ $unit->blood_group }} · {{ __('hospital.blood.expiry') }} {{ $unit->expiry_date->format('d M Y') }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mp-label" for="crossmatch_result">Crossmatch result</label>
                        <input class="mp-input" type="text" name="crossmatch_result" id="crossmatch_result" value="{{ old('crossmatch_result') }}">
                    </div>
                    <div>
                        <label class="mp-label" for="notes">Issue notes</label>
                        <input class="mp-input" type="text" name="notes" id="notes" value="{{ old('notes') }}">
                    </div>
                </div>
                <button type="submit" class="mp-btn mt-4">{{ __('hospital.blood.issue') }}</button>
            </form>
        @endif
    </div>
@endif

<div class="mp-card">
    <h2 class="font-display text-lg text-ink-900">Issue history</h2>
    <table class="mp-table mt-4">
        <thead>
            <tr>
                <th>Unit No</th>
                <th>{{ __('hospital.blood.blood_group') }}</th>
                <th>Issued at</th>
                <th>Issued by</th>
                <th>Crossmatch</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-brand-50">
            @forelse($bloodRequest->issues as $issue)
                <tr>
                    <td class="font-semibold text-brand-800">{{ $issue->bloodUnit->unit_no }}</td>
                    <td>{{ $issue->bloodUnit->blood_group }}</td>
                    <td>{{ $issue->issued_at->format('d M Y H:i') }}</td>
                    <td>{{ $issue->issuedBy->name ?? '—' }}</td>
                    <td>{{ $issue->crossmatch_result ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="py-6 text-center text-ink-700/60">No units issued yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
