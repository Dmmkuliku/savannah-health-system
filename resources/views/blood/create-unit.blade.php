@extends('layouts.hospital')

@section('title', __('hospital.blood.add_unit'))
@section('eyebrow', __('hospital.blood.eyebrow'))
@section('heading', __('hospital.blood.add_unit'))

@section('actions')
    <a href="{{ route('blood.index') }}" class="mp-btn-secondary">Back</a>
@endsection

@section('content')
<div class="mp-card max-w-3xl">
    <form method="POST" action="{{ route('blood.units.store') }}">
        @csrf
        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="mp-label" for="blood_group">{{ __('hospital.blood.blood_group') }} *</label>
                <select class="mp-input" name="blood_group" id="blood_group" required>
                    @foreach(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $group)
                        <option value="{{ $group }}" @selected(old('blood_group') === $group)>{{ $group }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mp-label" for="component">{{ __('hospital.blood.component') }} *</label>
                <select class="mp-input" name="component" id="component" required>
                    @foreach(['whole_blood', 'packed_rbc', 'fresh_frozen_plasma', 'platelets', 'cryoprecipitate'] as $component)
                        <option value="{{ $component }}" @selected(old('component', 'whole_blood') === $component)>{{ str_replace('_', ' ', ucfirst($component)) }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mp-label" for="volume_ml">Volume (ml)</label>
                <input class="mp-input" type="number" min="1" name="volume_ml" id="volume_ml" value="{{ old('volume_ml', 450) }}">
            </div>

            <div>
                <label class="mp-label" for="expiry_date">{{ __('hospital.blood.expiry') }} *</label>
                <input class="mp-input" type="date" name="expiry_date" id="expiry_date" value="{{ old('expiry_date') }}" required>
            </div>

            <div>
                <label class="mp-label" for="collected_at">Collected at</label>
                <input class="mp-input" type="date" name="collected_at" id="collected_at" value="{{ old('collected_at', now()->toDateString()) }}">
            </div>

            <div>
                <label class="mp-label" for="donor_name">Donor name</label>
                <input class="mp-input" type="text" name="donor_name" id="donor_name" value="{{ old('donor_name') }}">
            </div>

            <div class="sm:col-span-2">
                <label class="mp-label" for="storage_location">Storage location</label>
                <input class="mp-input" type="text" name="storage_location" id="storage_location" value="{{ old('storage_location', 'Fridge A') }}">
            </div>

            <div class="sm:col-span-2">
                <label class="mp-label" for="notes">Notes</label>
                <textarea class="mp-input" name="notes" id="notes" rows="3">{{ old('notes') }}</textarea>
            </div>
        </div>

        <div class="mt-6 flex gap-3">
            <button type="submit" class="mp-btn">{{ __('hospital.blood.add_unit') }}</button>
            <a href="{{ route('blood.index') }}" class="mp-btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
