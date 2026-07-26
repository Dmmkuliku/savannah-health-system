@php $p = $patient ?? null; @endphp
<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
    <div>
        <label class="mp-label" for="first_name">First name *</label>
        <input class="mp-input" type="text" name="first_name" id="first_name" value="{{ old('first_name', $p?->first_name) }}" required>
    </div>
    <div>
        <label class="mp-label" for="middle_name">Middle name</label>
        <input class="mp-input" type="text" name="middle_name" id="middle_name" value="{{ old('middle_name', $p?->middle_name) }}">
    </div>
    <div>
        <label class="mp-label" for="last_name">Last name *</label>
        <input class="mp-input" type="text" name="last_name" id="last_name" value="{{ old('last_name', $p?->last_name) }}" required>
    </div>
    <div>
        <label class="mp-label" for="gender">Gender *</label>
        <select class="mp-input" name="gender" id="gender" required>
            @foreach(['male' => 'Male', 'female' => 'Female', 'other' => 'Other'] as $val => $label)
                <option value="{{ $val }}" @selected(old('gender', $p?->gender) === $val)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="mp-label" for="date_of_birth">Date of birth</label>
        <input class="mp-input" type="date" name="date_of_birth" id="date_of_birth" value="{{ old('date_of_birth', $p?->date_of_birth?->format('Y-m-d')) }}">
    </div>
    <div>
        <label class="mp-label" for="age_years">Age (years)</label>
        <input class="mp-input" type="number" name="age_years" id="age_years" min="0" max="150" value="{{ old('age_years', $p?->age_years) }}">
    </div>
    <div>
        <label class="mp-label" for="phone">Phone</label>
        <input class="mp-input" type="text" name="phone" id="phone" value="{{ old('phone', $p?->phone) }}" placeholder="+255...">
    </div>
    <div>
        <label class="mp-label" for="email">Email</label>
        <input class="mp-input" type="email" name="email" id="email" value="{{ old('email', $p?->email) }}">
    </div>
    <div>
        <label class="mp-label" for="national_id">National ID (NIDA)</label>
        <input class="mp-input" type="text" name="national_id" id="national_id" value="{{ old('national_id', $p?->national_id) }}">
    </div>
    <div>
        <label class="mp-label" for="nhif_card_no">NHIF card no.</label>
        <input class="mp-input" type="text" name="nhif_card_no" id="nhif_card_no" value="{{ old('nhif_card_no', $p?->nhif_card_no) }}">
    </div>
    <div>
        <label class="mp-label" for="payment_category">Payment category *</label>
        <select class="mp-input" name="payment_category" id="payment_category" required>
            @foreach($paymentCategories as $key => $label)
                <option value="{{ $key }}" @selected(old('payment_category', $p?->payment_category ?? 'cash') === $key)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="mp-label" for="exemption_type">Exemption type (Msamaha)</label>
        <select class="mp-input" name="exemption_type" id="exemption_type">
            <option value="">— None —</option>
            @foreach($exemptionTypes as $key => $label)
                <option value="{{ $key }}" @selected(old('exemption_type', $p?->exemption_type) === $key)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="mp-label" for="blood_group">Blood group</label>
        <input class="mp-input" type="text" name="blood_group" id="blood_group" value="{{ old('blood_group', $p?->blood_group) }}" placeholder="e.g. O+">
    </div>
    <div>
        <label class="mp-label" for="marital_status">Marital status</label>
        <select class="mp-input" name="marital_status" id="marital_status">
            @foreach(['unknown' => 'Unknown', 'single' => 'Single', 'married' => 'Married', 'divorced' => 'Divorced', 'widowed' => 'Widowed'] as $val => $label)
                <option value="{{ $val }}" @selected(old('marital_status', $p?->marital_status ?? 'unknown') === $val)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="mp-label" for="occupation">Occupation</label>
        <input class="mp-input" type="text" name="occupation" id="occupation" value="{{ old('occupation', $p?->occupation) }}">
    </div>
    <div>
        <label class="mp-label" for="region">Region</label>
        <select class="mp-input" name="region" id="region">
            <option value="">— Select region —</option>
            @foreach($regions as $region)
                <option value="{{ $region }}" @selected(old('region', $p?->region) === $region)>{{ $region }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="mp-label" for="district">District</label>
        <input class="mp-input" type="text" name="district" id="district" value="{{ old('district', $p?->district) }}">
    </div>
    <div>
        <label class="mp-label" for="ward_village">Ward / Village</label>
        <input class="mp-input" type="text" name="ward_village" id="ward_village" value="{{ old('ward_village', $p?->ward_village) }}">
    </div>
    <div class="sm:col-span-2">
        <label class="mp-label" for="street_address">Street address</label>
        <input class="mp-input" type="text" name="street_address" id="street_address" value="{{ old('street_address', $p?->street_address) }}">
    </div>
    <div>
        <label class="mp-label" for="next_of_kin">Next of kin</label>
        <input class="mp-input" type="text" name="next_of_kin" id="next_of_kin" value="{{ old('next_of_kin', $p?->next_of_kin) }}">
    </div>
    <div>
        <label class="mp-label" for="next_of_kin_phone">Next of kin phone</label>
        <input class="mp-input" type="text" name="next_of_kin_phone" id="next_of_kin_phone" value="{{ old('next_of_kin_phone', $p?->next_of_kin_phone) }}">
    </div>
    <div class="sm:col-span-2 lg:col-span-3">
        <label class="mp-label" for="allergies">Allergies</label>
        <textarea class="mp-input" name="allergies" id="allergies" rows="2">{{ old('allergies', $p?->allergies) }}</textarea>
    </div>
    <div class="sm:col-span-2 lg:col-span-3">
        <label class="mp-label" for="chronic_conditions">Chronic conditions</label>
        <textarea class="mp-input" name="chronic_conditions" id="chronic_conditions" rows="2">{{ old('chronic_conditions', $p?->chronic_conditions) }}</textarea>
    </div>
</div>
