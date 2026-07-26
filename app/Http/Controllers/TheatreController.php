<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\TheatreCase;
use App\Models\TheatreRoom;
use App\Models\User;
use App\Models\Visit;
use App\Support\Hospital;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TheatreController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status');

        $cases = TheatreCase::query()
            ->with(['patient', 'theatreRoom', 'surgeon', 'anaesthetist'])
            ->when($status, fn ($q) => $q->where('status', $status))
            ->latest('scheduled_at')
            ->paginate(20)
            ->withQueryString();

        $rooms = TheatreRoom::query()
            ->where('is_active', true)
            ->withCount([
                'theatreCases as active_cases_count' => fn ($q) => $q->whereNotIn('status', ['completed', 'cancelled']),
            ])
            ->orderBy('code')
            ->get();

        $roomStatusCounts = TheatreRoom::query()
            ->where('is_active', true)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('theatre.index', compact('cases', 'rooms', 'roomStatusCounts', 'status'));
    }

    public function create(Request $request): View
    {
        $visitId = $request->query('visit_id');
        $visit = $visitId ? Visit::with('patient')->find($visitId) : null;

        $patients = Patient::query()
            ->orderBy('first_name')
            ->limit(200)
            ->get();

        $doctors = User::query()
            ->where('role', 'doctor')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $rooms = TheatreRoom::query()
            ->where('is_active', true)
            ->where('status', 'available')
            ->orderBy('code')
            ->get();

        return view('theatre.create', compact('patients', 'doctors', 'rooms', 'visit'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'procedure_name' => ['required', 'string', 'max:255'],
            'patient_id' => ['required', 'exists:patients,id'],
            'visit_id' => ['nullable', 'exists:visits,id'],
            'admission_id' => ['nullable', 'exists:admissions,id'],
            'theatre_room_id' => ['nullable', 'exists:theatre_rooms,id'],
            'surgeon_id' => ['nullable', 'exists:users,id'],
            'anaesthetist_id' => ['nullable', 'exists:users,id'],
            'urgency' => ['required', 'in:elective,urgent,emergency'],
            'asa_class' => ['nullable', 'in:I,II,III,IV,V,E'],
            'scheduled_at' => ['nullable', 'date'],
            'diagnosis' => ['nullable', 'string', 'max:255'],
            'pre_op_notes' => ['nullable', 'string'],
        ]);

        $theatreCase = TheatreCase::create([
            ...$validated,
            'case_no' => Hospital::nextNumber('OT'),
            'status' => 'scheduled',
            'created_by' => auth()->id(),
        ]);

        return redirect()
            ->route('theatre.show', $theatreCase)
            ->with('success', __('hospital.theatre.schedule').' — '.$theatreCase->case_no);
    }

    public function show(TheatreCase $theatreCase): View
    {
        $theatreCase->load([
            'patient',
            'visit',
            'admission',
            'theatreRoom',
            'surgeon',
            'anaesthetist',
            'createdBy',
            'bloodRequests.requestedBy',
        ]);

        return view('theatre.show', compact('theatreCase'));
    }

    public function updateStatus(Request $request, TheatreCase $theatreCase): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pre_op,in_theatre,recovery,completed,cancelled'],
            'operative_notes' => ['nullable', 'string'],
            'anaesthesia_type' => ['nullable', 'string', 'max:255'],
            'estimated_blood_loss_ml' => ['nullable', 'numeric', 'min:0'],
            'post_op_notes' => ['nullable', 'string'],
        ]);

        $nextStatus = $validated['status'];
        $allowed = [
            'scheduled' => ['pre_op', 'cancelled'],
            'pre_op' => ['in_theatre', 'cancelled'],
            'in_theatre' => ['recovery', 'cancelled'],
            'recovery' => ['completed', 'cancelled'],
        ];

        if (! in_array($nextStatus, $allowed[$theatreCase->status] ?? [], true)) {
            return back()->withErrors(['error' => 'Invalid status transition from '.$theatreCase->status.' to '.$nextStatus.'.']);
        }

        try {
            DB::transaction(function () use ($validated, $nextStatus, $theatreCase) {
                $updates = ['status' => $nextStatus];

                if ($nextStatus === 'in_theatre') {
                    $updates['started_at'] = now();

                    if ($theatreCase->theatre_room_id) {
                        TheatreRoom::where('id', $theatreCase->theatre_room_id)
                            ->update(['status' => 'occupied']);
                    }
                }

                if (in_array($nextStatus, ['completed', 'cancelled'], true)) {
                    $updates['ended_at'] = now();

                    if ($theatreCase->theatre_room_id) {
                        TheatreRoom::where('id', $theatreCase->theatre_room_id)
                            ->update(['status' => 'available']);
                    }
                }

                if ($nextStatus === 'completed') {
                    $updates['operative_notes'] = $validated['operative_notes'] ?? $theatreCase->operative_notes;
                    $updates['anaesthesia_type'] = $validated['anaesthesia_type'] ?? $theatreCase->anaesthesia_type;
                    $updates['estimated_blood_loss_ml'] = $validated['estimated_blood_loss_ml'] ?? $theatreCase->estimated_blood_loss_ml;
                    $updates['post_op_notes'] = $validated['post_op_notes'] ?? $theatreCase->post_op_notes;
                }

                $theatreCase->update($updates);
            });

            return redirect()
                ->route('theatre.show', $theatreCase)
                ->with('success', 'Case status updated to '.$nextStatus.'.');
        } catch (\Throwable $e) {
            return back()->withErrors(['error' => 'Failed to update case status.']);
        }
    }
}
