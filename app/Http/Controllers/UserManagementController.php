<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\User;
use App\Support\Hospital;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    public function index(): View
    {
        abort_unless(auth()->user()?->isAdmin(), 403, 'Administrator access required.');
        $users = User::with('department')->orderBy('name')->paginate(20);

        return view('users.index', [
            'users' => $users,
            'roles' => Hospital::roles(),
        ]);
    }

    public function create(): View
    {
        abort_unless(auth()->user()?->isAdmin(), 403, 'Administrator access required.');

        return view('users.create', [
            'departments' => Department::where('is_active', true)->orderBy('name')->get(),
            'roles' => Hospital::roles(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()?->isAdmin(), 403, 'Administrator access required.');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:50', 'unique:users,username'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'in:'.implode(',', array_keys(Hospital::roles()))],
            'employee_no' => ['nullable', 'string', 'max:40'],
            'specialty' => ['nullable', 'string', 'max:255'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        try {
            User::create([
                ...$validated,
                'is_active' => $request->boolean('is_active', true),
            ]);

            return redirect()
                ->route('users.index')
                ->with('success', 'Staff user created successfully.');
        } catch (\Throwable $e) {
            return back()->withInput()->withErrors(['error' => 'Failed to create user.']);
        }
    }

    public function edit(User $user): View
    {
        abort_unless(auth()->user()?->isAdmin(), 403, 'Administrator access required.');

        return view('users.edit', [
            'user' => $user,
            'departments' => Department::where('is_active', true)->orderBy('name')->get(),
            'roles' => Hospital::roles(),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        abort_unless(auth()->user()?->isAdmin(), 403, 'Administrator access required.');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:50', 'unique:users,username,'.$user->id],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'in:'.implode(',', array_keys(Hospital::roles()))],
            'employee_no' => ['nullable', 'string', 'max:40'],
            'specialty' => ['nullable', 'string', 'max:255'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        try {
            $data = collect($validated)->except(['password'])->all();

            if (! empty($validated['password'])) {
                $data['password'] = $validated['password'];
            }

            $data['is_active'] = $request->boolean('is_active', true);

            $user->update($data);

            return redirect()
                ->route('users.index')
                ->with('success', 'Staff user updated successfully.');
        } catch (\Throwable $e) {
            return back()->withInput()->withErrors(['error' => 'Failed to update user.']);
        }
    }
}
