@extends('layouts.hospital')

@section('title', 'Admit Patient')
@section('eyebrow', 'Inpatient')
@section('heading', 'Admit — '.$visit->visit_no)

@section('actions')
    <a href="{{ route('visits.show', $visit) }}" class="mp-btn-secondary">Back to visit</a>
@endsection

@section('content')
<div class="mp-card mb-4 rounded-xl bg-brand-50/60 p-4 text-sm">
    <strong>{{ $visit->patient->full_name }}</strong> · {{ $visit->patient->mrn }} · Visit {{ $visit->visit_no }}
</div>

<div class="mp-card max-w-2xl">
    <form method="POST" action="{{ route('admissions.store', $visit) }}" id="admit-form">
        @csrf
        <div class="space-y-4">
            <div>
                <label class="mp-label" for="ward_id">Ward *</label>
                <select class="mp-input" name="ward_id" id="ward_id" required>
                    <option value="">— Select ward —</option>
                    @foreach($wards as $ward)
                        <option value="{{ $ward->id }}" @selected(old('ward_id') == $ward->id)>
                            {{ $ward->name }} ({{ $ward->beds->count() }} beds available)
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mp-label" for="bed_id">Bed *</label>
                <select class="mp-input" name="bed_id" id="bed_id" required>
                    <option value="">— Select ward first —</option>
                </select>
            </div>
            <div>
                <label class="mp-label" for="admission_reason">Admission reason</label>
                <textarea class="mp-input" name="admission_reason" id="admission_reason" rows="3">{{ old('admission_reason', $visit->chief_complaint) }}</textarea>
            </div>
        </div>
        <div class="mt-6 flex gap-3">
            <button type="submit" class="mp-btn">Admit patient</button>
            <a href="{{ route('visits.show', $visit) }}" class="mp-btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<script>
const wards = @json($wards->map(fn($w) => ['id' => $w->id, 'beds' => $w->beds->map(fn($b) => ['id' => $b->id, 'bed_number' => $b->bed_number])]));
const wardEl = document.getElementById('ward_id');
const bedEl = document.getElementById('bed_id');
const oldBed = @json(old('bed_id'));

function populateBeds() {
    bedEl.innerHTML = '<option value="">— Select bed —</option>';
    const ward = wards.find(w => w.id == wardEl.value);
    if (!ward) return;
    ward.beds.forEach(b => {
        const opt = document.createElement('option');
        opt.value = b.id;
        opt.textContent = 'Bed ' + b.bed_number;
        if (oldBed == b.id) opt.selected = true;
        bedEl.appendChild(opt);
    });
}
wardEl.addEventListener('change', populateBeds);
populateBeds();
</script>
@endsection
