@extends('layouts.hospital')

@section('title', __('hospital.maternity.record_delivery'))
@section('eyebrow', __('hospital.maternity.eyebrow'))
@section('heading', __('hospital.maternity.record_delivery'))

@section('actions')
    <a href="{{ route('maternity.index') }}" class="mp-btn-secondary">{{ __('hospital.rch.back') }}</a>
@endsection

@section('content')
<div class="mp-card max-w-4xl">
    <form method="POST" action="{{ route('maternity.store') }}" id="delivery-form">
        @csrf
        <div class="grid gap-4 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <label class="mp-label" for="patient_id">{{ __('hospital.rch.mother') }} *</label>
                <select class="mp-input" name="patient_id" id="patient_id" required>
                    <option value="">— {{ __('hospital.rch.select_mother') }} —</option>
                    @foreach($patients as $patient)
                        <option value="{{ $patient->id }}" @selected(old('patient_id', $selectedPatient?->id) == $patient->id)>
                            {{ $patient->full_name }} ({{ $patient->mrn }})
                        </option>
                    @endforeach
                </select>
            </div>

            @if($activePregnancies->isNotEmpty() || old('rch_pregnancy_id'))
                <div class="sm:col-span-2">
                    <label class="mp-label" for="rch_pregnancy_id">{{ __('hospital.maternity.linked_pregnancy') }}</label>
                    <select class="mp-input" name="rch_pregnancy_id" id="rch_pregnancy_id">
                        <option value="">— {{ __('hospital.maternity.no_pregnancy') }} —</option>
                        @foreach($activePregnancies as $pregnancy)
                            <option value="{{ $pregnancy->id }}" @selected(old('rch_pregnancy_id', request('rch_pregnancy_id')) == $pregnancy->id)>
                                {{ $pregnancy->anc_no }} · EDD {{ $pregnancy->edd?->format('d M Y') ?? '—' }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div>
                <label class="mp-label" for="delivered_at">{{ __('hospital.maternity.delivered_at') }} *</label>
                <input class="mp-input" type="datetime-local" name="delivered_at" id="delivered_at" value="{{ old('delivered_at', now()->format('Y-m-d\TH:i')) }}" required>
            </div>

            <div>
                <label class="mp-label" for="gestational_weeks">{{ __('hospital.rch.gestational_weeks') }}</label>
                <input class="mp-input" type="number" name="gestational_weeks" id="gestational_weeks" min="20" max="45" value="{{ old('gestational_weeks') }}">
            </div>

            <div>
                <label class="mp-label" for="delivery_type">{{ __('hospital.maternity.delivery_type') }} *</label>
                <select class="mp-input" name="delivery_type" id="delivery_type" required>
                    @foreach(['spontaneous_vaginal', 'assisted_vaginal', 'caesarean', 'breech'] as $type)
                        <option value="{{ $type }}" @selected(old('delivery_type', 'spontaneous_vaginal') === $type)>{{ ucfirst(str_replace('_', ' ', $type)) }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mp-label" for="place">{{ __('hospital.maternity.place') }} *</label>
                <select class="mp-input" name="place" id="place" required>
                    @foreach(['labour_ward', 'theatre', 'home_brought', 'other'] as $place)
                        <option value="{{ $place }}" @selected(old('place', 'labour_ward') === $place)>{{ ucfirst(str_replace('_', ' ', $place)) }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mp-label" for="outcome">{{ __('hospital.maternity.outcome') }} *</label>
                <select class="mp-input" name="outcome" id="outcome" required>
                    @foreach(['live_birth', 'stillbirth', 'neonatal_death'] as $outcome)
                        <option value="{{ $outcome }}" @selected(old('outcome', 'live_birth') === $outcome)>{{ ucfirst(str_replace('_', ' ', $outcome)) }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mp-label" for="babies_count">{{ __('hospital.maternity.babies_count') }} *</label>
                <input class="mp-input" type="number" name="babies_count" id="babies_count" min="1" max="5" value="{{ old('babies_count', 1) }}" required>
            </div>

            <div class="sm:col-span-2">
                <label class="mp-label" for="complications">{{ __('hospital.maternity.complications') }}</label>
                <input class="mp-input" type="text" name="complications" id="complications" value="{{ old('complications') }}">
            </div>

            <div class="sm:col-span-2">
                <label class="inline-flex items-center gap-2 text-sm">
                    <input type="checkbox" name="mother_alive" value="1" @checked(old('mother_alive', true))>
                    {{ __('hospital.maternity.mother_alive') }}
                </label>
            </div>

            <div class="sm:col-span-2">
                <label class="mp-label" for="notes">{{ __('hospital.rch.notes') }}</label>
                <textarea class="mp-input" name="notes" id="notes" rows="2">{{ old('notes') }}</textarea>
            </div>
        </div>

        <div class="mt-8 border-t border-brand-100 pt-6">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="font-display text-lg text-ink-900">{{ __('hospital.maternity.newborns') }}</h2>
                <button type="button" id="add-newborn" class="mp-btn-secondary text-sm">{{ __('hospital.maternity.add_newborn') }}</button>
            </div>
            <div id="newborns-container" class="space-y-6"></div>
        </div>

        <div class="mt-6 flex gap-3">
            <button type="submit" class="mp-btn">{{ __('hospital.maternity.record_delivery') }}</button>
            <a href="{{ route('maternity.index') }}" class="mp-btn-secondary">{{ __('hospital.rch.cancel') }}</a>
        </div>
    </form>
</div>

<template id="newborn-template">
    <div class="newborn-block rounded-xl border border-brand-100 bg-brand-50/40 p-4">
        <div class="mb-3 flex items-center justify-between">
            <h3 class="font-semibold text-ink-900">{{ __('hospital.maternity.newborn') }} <span class="newborn-index"></span></h3>
            <button type="button" class="remove-newborn text-sm font-semibold text-red-700">{{ __('hospital.maternity.remove') }}</button>
        </div>
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <div>
                <label class="mp-label">{{ __('hospital.maternity.sex') }} *</label>
                <select class="mp-input" name="newborns[__INDEX__][sex]" required>
                    @foreach(['male', 'female', 'unknown'] as $sex)
                        <option value="{{ $sex }}">{{ ucfirst($sex) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mp-label">{{ __('hospital.maternity.birth_weight') }}</label>
                <input class="mp-input" type="number" step="0.001" name="newborns[__INDEX__][birth_weight_kg]" min="0" max="10">
            </div>
            <div>
                <label class="mp-label">{{ __('hospital.maternity.status') }} *</label>
                <select class="mp-input" name="newborns[__INDEX__][status]" required>
                    @foreach(['alive', 'stillbirth', 'died'] as $status)
                        <option value="{{ $status }}">{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mp-label">Apgar 1</label>
                <input class="mp-input" type="number" name="newborns[__INDEX__][apgar_1]" min="0" max="10">
            </div>
            <div>
                <label class="mp-label">Apgar 5</label>
                <input class="mp-input" type="number" name="newborns[__INDEX__][apgar_5]" min="0" max="10">
            </div>
            <div>
                <label class="mp-label">{{ __('hospital.maternity.newborn_name') }}</label>
                <input class="mp-input" type="text" name="newborns[__INDEX__][name]">
            </div>
            <div class="sm:col-span-2 lg:col-span-3 flex flex-wrap gap-6">
                <label class="inline-flex items-center gap-2 text-sm">
                    <input type="checkbox" name="newborns[__INDEX__][breastfeeding_initiated]" value="1">
                    {{ __('hospital.maternity.breastfeeding_initiated') }}
                </label>
                <label class="inline-flex items-center gap-2 text-sm">
                    <input type="checkbox" name="newborns[__INDEX__][bcg_given]" value="1">
                    BCG
                </label>
                <label class="inline-flex items-center gap-2 text-sm">
                    <input type="checkbox" name="newborns[__INDEX__][opv0_given]" value="1">
                    OPV0
                </label>
            </div>
        </div>
    </div>
</template>

<script>
const container = document.getElementById('newborns-container');
const template = document.getElementById('newborn-template');
const babiesCountEl = document.getElementById('babies_count');
let newbornIndex = 0;

function addNewborn() {
    const html = template.innerHTML.replace(/__INDEX__/g, newbornIndex);
    const wrapper = document.createElement('div');
    wrapper.innerHTML = html;
    const block = wrapper.firstElementChild;
    block.querySelector('.newborn-index').textContent = newbornIndex + 1;
    block.querySelector('.remove-newborn').addEventListener('click', () => {
        block.remove();
        reindex();
    });
    container.appendChild(block);
    newbornIndex++;
}

function reindex() {
    newbornIndex = 0;
    container.querySelectorAll('.newborn-block').forEach((block, i) => {
        block.querySelector('.newborn-index').textContent = i + 1;
        block.querySelectorAll('[name^="newborns["]').forEach(el => {
            el.name = el.name.replace(/newborns\[\d+\]/, 'newborns[' + i + ']');
        });
    });
    newbornIndex = container.querySelectorAll('.newborn-block').length;
}

document.getElementById('add-newborn').addEventListener('click', addNewborn);
babiesCountEl.addEventListener('change', () => {
    const target = parseInt(babiesCountEl.value, 10) || 1;
    while (container.querySelectorAll('.newborn-block').length < target) addNewborn();
    while (container.querySelectorAll('.newborn-block').length > target) {
        container.lastElementChild.remove();
    }
    reindex();
});

addNewborn();
</script>
@endsection
