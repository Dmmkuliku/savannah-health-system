@extends('layouts.hospital')

@section('title', 'Staff Users')
@section('eyebrow', 'Administration')
@section('heading', 'Staff Users')

@section('actions')
    <a href="{{ route('users.create') }}" class="mp-btn">Add staff user</a>
@endsection

@section('content')
<div class="mp-card">
    <div class="overflow-x-auto">
        <table class="mp-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Department</th>
                    <th>Phone</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-brand-50">
                @forelse($users as $user)
                    <tr>
                        <td class="font-semibold">{{ $user->name }}</td>
                        <td>{{ $user->username }}</td>
                        <td>{{ $roles[$user->role] ?? $user->role }}</td>
                        <td>{{ $user->department->name ?? '—' }}</td>
                        <td>{{ $user->phone ?? '—' }}</td>
                        <td>
                            <span class="mp-badge {{ $user->is_active ? 'bg-brand-50 text-brand-800' : 'bg-red-100 text-red-700' }}">
                                {{ $user->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="text-right"><a href="{{ route('users.edit', $user) }}" class="font-semibold text-brand-700">Edit</a></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="py-8 text-center text-ink-700/60">No users found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($users->hasPages())
        <div class="mt-4">{{ $users->links() }}</div>
    @endif
</div>
@endsection
