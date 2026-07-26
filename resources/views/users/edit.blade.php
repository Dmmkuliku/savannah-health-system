@extends('layouts.hospital')

@section('title', 'Edit Staff User')
@section('eyebrow', 'Administration')
@section('heading', 'Edit — '.$user->name)

@section('actions')
    <a href="{{ route('users.index') }}" class="mp-btn-secondary">Back</a>
@endsection

@section('content')
<div class="mp-card max-w-2xl">
    <form method="POST" action="{{ route('users.update', $user) }}">
        @csrf
        @method('PUT')
        <div class="grid gap-4 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <label class="mp-label" for="name">Full name *</label>
                <input class="mp-input" type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required>
            </div>
            <div>
                <label class="mp-label" for="username">Username *</label>
                <input class="mp-input" type="text" name="username" id="username" value="{{ old('username', $user->username) }}" required>
            </div>
            <div>
                <label class="mp-label" for="email">Email *</label>
                <input class="mp-input" type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required>
            </div>
            <div>
                <label class="mp-label" for="phone">Phone</label>
                <input class="mp-input" type="text" name="phone" id="phone" value="{{ old('phone', $user->phone) }}">
            </div>
            <div>
                <label class="mp-label" for="employee_no">Employee no.</label>
                <input class="mp-input" type="text" name="employee_no" id="employee_no" value="{{ old('employee_no', $user->employee_no) }}">
            </div>
            <div>
                <label class="mp-label" for="role">Role *</label>
                <select class="mp-input" name="role" id="role" required>
                    @foreach($roles as $key => $label)
                        <option value="{{ $key }}" @selected(old('role', $user->role) === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mp-label" for="department_id">Department</label>
                <select class="mp-input" name="department_id" id="department_id">
                    <option value="">— None —</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" @selected(old('department_id', $user->department_id) == $dept->id)>{{ $dept->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mp-label" for="specialty">Specialty</label>
                <input class="mp-input" type="text" name="specialty" id="specialty" value="{{ old('specialty', $user->specialty) }}">
            </div>
            <div>
                <label class="mp-label" for="password">New password</label>
                <input class="mp-input" type="password" name="password" id="password" placeholder="Leave blank to keep current">
            </div>
            <div>
                <label class="mp-label" for="password_confirmation">Confirm new password</label>
                <input class="mp-input" type="password" name="password_confirmation" id="password_confirmation">
            </div>
            <div class="sm:col-span-2">
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $user->is_active)) class="rounded border-brand-300 text-brand-700">
                    Active account
                </label>
            </div>
        </div>
        <div class="mt-6 flex gap-3">
            <button type="submit" class="mp-btn">Update user</button>
            <a href="{{ route('users.index') }}" class="mp-btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
