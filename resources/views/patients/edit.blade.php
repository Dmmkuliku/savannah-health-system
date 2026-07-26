@extends('layouts.hospital')

@section('title', 'Edit Patient')
@section('eyebrow', 'Registration')
@section('heading', 'Edit Patient — '.$patient->mrn)

@section('actions')
    <a href="{{ route('patients.show', $patient) }}" class="mp-btn-secondary">View record</a>
@endsection

@section('content')
<div class="mp-card max-w-5xl">
    <form method="POST" action="{{ route('patients.update', $patient) }}">
        @csrf
        @method('PUT')
        @include('patients._form', ['patient' => $patient])
        <div class="mt-6 flex gap-3">
            <button type="submit" class="mp-btn">Update patient</button>
            <a href="{{ route('patients.show', $patient) }}" class="mp-btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
