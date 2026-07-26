@extends('layouts.hospital')

@section('title', 'Register Patient')
@section('eyebrow', 'Registration')
@section('heading', 'Register New Patient')

@section('actions')
    <a href="{{ route('patients.index') }}" class="mp-btn-secondary">Back to list</a>
@endsection

@section('content')
<div class="mp-card max-w-5xl">
    <form method="POST" action="{{ route('patients.store') }}">
        @csrf
        @include('patients._form')
        <div class="mt-6 flex gap-3">
            <button type="submit" class="mp-btn">Save patient</button>
            <a href="{{ route('patients.index') }}" class="mp-btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
