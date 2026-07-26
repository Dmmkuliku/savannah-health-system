@extends('layouts.hospital')

@section('title', $theatreCase->case_no)
@section('eyebrow', __('hospital.theatre.eyebrow'))
@section('heading', $theatreCase->case_no.' — '.$theatreCase->procedure_name)

@section('actions')
    <a href="{{ route('theatre.index') }}" class="mp-btn-secondary">{{ __('hospital.theatre.cases') }}</a>
    @if(!in_array($theatreCase->status, ['completed', 'cancelled']))
        <a href="{{ route('blood.requests.create', ['theatre_case_id' => $theatreCase->id]) }}" class="mp-btn-secondary">{{ __('hospital.blood.request') }}</a>
    @endif
@endsection

@section('content')
<div class="mp-card mb-6">
    <dl class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4 text-sm">
        <div><dt class="text-ink-700/60">Patient</dt><dd class="font-semibold">{{ $theatreCase->patient->full_name }} ({{ $theatreCase->patient->mrn }})</dd></div>
        <div><dt class="text-ink-700/60">{{ __('hospital.theatre.rooms') }}</dt><dd>{{ $theatreCase->theatreRoom->code ?? '—' }}</dd></div>
        <div><dt class="text-ink-700/60">{{ __('hospital.theatre.surgeon') }}</dt><dd>{{ $theatreCase->surgeon->name ?? '—' }}</dd></div>
        <div><dt class="text-ink-700/60">{{ __('hospital.theatre.anaesthetist') }}</dt><dd>{{ $theatreCase->anaesthetist->name ?? '—' }}</dd></div>
        <div><dt class="text-ink-700/60">{{ __('hospital.theatre.urgency') }}</dt><dd class="capitalize">{{ $theatreCase->urgency }}</dd></div>
        <div><dt class="text-ink-700/60">Status</dt><dd><span class="mp-badge bg-brand-50 text-brand-800">{{ str_replace('_', ' ', $theatreCase->status) }}</span></dd></div>
        <div><dt class="text-ink-700/60">Scheduled</dt><dd>{{ $theatreCase->scheduled_at?->format('d M Y H:i') ?? '—' }}</dd></div>
        <div><dt class="text-ink-700/60">Started / Ended</dt><dd>{{ $theatreCase->started_at?->format('d M Y H:i') ?? '—' }} / {{ $theatreCase->ended_at?->format('d M Y H:i') ?? '—' }}</dd></div>
        @if($theatreCase->diagnosis)
            <div class="sm:col-span-2"><dt class="text-ink-700/60">Diagnosis</dt><dd>{{ $theatreCase->diagnosis }}</dd></div>
        @endif
        @if($theatreCase->pre_op_notes)
            <div class="sm:col-span-2 lg:col-span-4"><dt class="text-ink-700/60">Pre-op notes</dt><dd>{{ $theatreCase->pre_op_notes }}</dd></div>
        @endif
    </dl>
</div>

@php
    $nextActions = [
        'scheduled' => ['pre_op' => 'Move to pre-op'],
        'pre_op' => ['in_theatre' => __('hospital.theatre.start')],
        'in_theatre' => ['recovery' => 'Move to recovery'],
        'recovery' => ['completed' => __('hospital.theatre.complete')],
    ];
    $next = $nextActions[$theatreCase->status] ?? null;
@endphp

@if($next && !in_array($theatreCase->status, ['completed', 'cancelled']))
    <div class="mp-card mb-6">
        <h2 class="font-display text-lg text-ink-900">Update status</h2>
        <form method="POST" action="{{ route('theatre.update-status', $theatreCase) }}" class="mt-4">
            @csrf
            @foreach($next as $status => $label)
                <input type="hidden" name="status" value="{{ $status }}">
                @if($status === 'completed')
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mp-label" for="anaesthesia_type">Anaesthesia type</label>
                            <input class="mp-input" type="text" name="anaesthesia_type" id="anaesthesia_type" value="{{ old('anaesthesia_type', $theatreCase->anaesthesia_type) }}">
                        </div>
                        <div>
                            <label class="mp-label" for="estimated_blood_loss_ml">Estimated blood loss (ml)</label>
                            <input class="mp-input" type="number" step="0.1" min="0" name="estimated_blood_loss_ml" id="estimated_blood_loss_ml" value="{{ old('estimated_blood_loss_ml', $theatreCase->estimated_blood_loss_ml) }}">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="mp-label" for="operative_notes">Operative notes</label>
                            <textarea class="mp-input" name="operative_notes" id="operative_notes" rows="4">{{ old('operative_notes', $theatreCase->operative_notes) }}</textarea>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="mp-label" for="post_op_notes">Post-op notes</label>
                            <textarea class="mp-input" name="post_op_notes" id="post_op_notes" rows="3">{{ old('post_op_notes', $theatreCase->post_op_notes) }}</textarea>
                        </div>
                    </div>
                @endif
                <div class="mt-4 flex flex-wrap gap-3">
                    <button type="submit" class="mp-btn">{{ $label }}</button>
                </div>
            @endforeach
        </form>

        <form method="POST" action="{{ route('theatre.update-status', $theatreCase) }}" class="mt-4" onsubmit="return confirm('Cancel this case?')">
            @csrf
            <input type="hidden" name="status" value="cancelled">
            <button type="submit" class="text-sm font-semibold text-red-700">Cancel case</button>
        </form>
    </div>
@endif

@if(in_array($theatreCase->status, ['completed', 'cancelled']))
    <div class="mp-card mb-6">
        <h2 class="font-display text-lg text-ink-900">Case summary</h2>
        <dl class="mt-4 grid gap-3 sm:grid-cols-2 text-sm">
            @if($theatreCase->anaesthesia_type)
                <div><dt class="text-ink-700/60">Anaesthesia</dt><dd>{{ $theatreCase->anaesthesia_type }}</dd></div>
            @endif
            @if($theatreCase->estimated_blood_loss_ml !== null)
                <div><dt class="text-ink-700/60">Blood loss</dt><dd>{{ $theatreCase->estimated_blood_loss_ml }} ml</dd></div>
            @endif
            @if($theatreCase->operative_notes)
                <div class="sm:col-span-2"><dt class="text-ink-700/60">Operative notes</dt><dd>{{ $theatreCase->operative_notes }}</dd></div>
            @endif
            @if($theatreCase->post_op_notes)
                <div class="sm:col-span-2"><dt class="text-ink-700/60">Post-op notes</dt><dd>{{ $theatreCase->post_op_notes }}</dd></div>
            @endif
        </dl>
    </div>
@endif

@if($theatreCase->bloodRequests->isNotEmpty())
    <div class="mp-card">
        <h2 class="font-display text-lg text-ink-900">{{ __('hospital.blood.requests') }}</h2>
        <table class="mp-table mt-4">
            <thead>
                <tr>
                    <th>Request No</th>
                    <th>{{ __('hospital.blood.blood_group') }}</th>
                    <th>{{ __('hospital.blood.component') }}</th>
                    <th>Units</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-brand-50">
                @foreach($theatreCase->bloodRequests as $request)
                    <tr>
                        <td class="font-semibold text-brand-800">{{ $request->request_no }}</td>
                        <td>{{ $request->blood_group }}</td>
                        <td>{{ str_replace('_', ' ', $request->component) }}</td>
                        <td>{{ $request->units_issued }}/{{ $request->units_requested }}</td>
                        <td><span class="mp-badge bg-brand-50 text-brand-800">{{ $request->status }}</span></td>
                        <td class="text-right"><a href="{{ route('blood.requests.show', $request) }}" class="font-semibold text-brand-700">View</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
@endsection
