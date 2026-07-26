<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Department;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AppointmentController extends Controller
{
    public function index(Request $request): View
    {
        $date = $request->query('date', now()->toDateString());
        $status = $request->query('status');

        $appointments = Appointment::query()
            ->with(['patient', 'doctor', 'department'])
            ->whereDate('appointment_date', $date)
            ->when($status, fn ($q) => $q->where('status', $status))
            ->orderBy('appointment_time')
            ->paginate(20)
            ->withQueryString();

        return view('appointments.index', [
            'appointments' => $appointments,
            'date' => $date,
            'status' => $status,
        ]);
    }

    public function create(): View
    {
        return view('appointments.create', [
            'patients' => Patient::orderBy('first_name')->limit(100)->get(),
            'doctors' => User::where('role', 'doctor')->where('is_active', true)->orderBy('name')->get(),
            'departments' => Department::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'patient_id' => ['required', 'exists:patients,id'],
            'doctor_id' => ['nullable', 'exists:users,id'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'appointment_date' => ['required', 'date', 'after_or_equal:today'],
            'appointment_time' => ['nullable', 'date_format:H:i'],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            Appointment::create([
                ...$validated,
                'status' => 'scheduled',
            ]);

            return redirect()
                ->route('appointments.index', ['date' => $validated['appointment_date']])
                ->with('success', 'Appointment scheduled successfully.');
        } catch (\Throwable $e) {
            return back()->withInput()->withErrors(['error' => 'Failed to schedule appointment.']);
        }
    }

    public function updateStatus(Request $request, Appointment $appointment): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:scheduled,checked_in,completed,cancelled,no_show'],
        ]);

        try {
            $appointment->update(['status' => $validated['status']]);

            return back()->with('success', 'Appointment status updated.');
        } catch (\Throwable $e) {
            return back()->withErrors(['error' => 'Failed to update appointment status.']);
        }
    }
}
