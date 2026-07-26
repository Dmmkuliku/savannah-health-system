<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Patient;
use App\Models\Queue;
use App\Models\ServiceCharge;
use App\Models\Visit;
use App\Services\ExemptionService;
use App\Support\Hospital;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class VisitController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status');
        $departmentId = $request->query('department_id');
        $visitType = $request->query('visit_type');
        $date = $request->query('date', now()->toDateString());

        $visits = Visit::query()
            ->with(['patient', 'department', 'doctor'])
            ->whereDate('visited_at', $date)
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($departmentId, fn ($q) => $q->where('department_id', $departmentId))
            ->when($visitType, fn ($q) => $q->where('visit_type', $visitType))
            ->latest('visited_at')
            ->paginate(20)
            ->withQueryString();

        return view('visits.index', [
            'visits' => $visits,
            'departments' => Department::where('is_active', true)->orderBy('name')->get(),
            'filters' => [
                'status' => $status,
                'department_id' => $departmentId,
                'visit_type' => $visitType,
                'date' => $date,
            ],
        ]);
    }

    public function create(Request $request): View
    {
        $patient = null;
        if ($request->filled('patient_id')) {
            $patient = Patient::find($request->query('patient_id'));
        }

        return view('visits.create', [
            'patient' => $patient,
            'patients' => Patient::orderBy('first_name')->limit(100)->get(),
            'departments' => Department::where('is_active', true)->orderBy('name')->get(),
            'paymentCategories' => Hospital::paymentCategories(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'patient_id' => ['required', 'exists:patients,id'],
            'visit_type' => ['required', 'in:opd,ipd,emergency,rch,dental,eye,specialist'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'payment_category' => ['required', 'in:cash,nhif,exemption,corporate,insurance'],
            'chief_complaint' => ['nullable', 'string', 'max:500'],
            'priority' => ['nullable', 'in:normal,urgent,emergency'],
            'charge_consultation_fee' => ['nullable', 'boolean'],
        ]);

        try {
            $visit = DB::transaction(function () use ($validated, $request) {
                $patient = Patient::findOrFail($validated['patient_id']);
                $departmentId = $validated['department_id'] ?? null;

                $exemption = app(ExemptionService::class)->applyToPatient($patient);
                $patient->refresh();
                $paymentCategory = $exemption['eligible']
                    ? 'exemption'
                    : $validated['payment_category'];

                $visit = Visit::create([
                    'visit_no' => Hospital::nextVisitNo(),
                    'patient_id' => $patient->id,
                    'visit_type' => $validated['visit_type'],
                    'department_id' => $departmentId,
                    'payment_category' => $paymentCategory,
                    'chief_complaint' => $validated['chief_complaint'] ?? null,
                    'status' => 'waiting',
                    'created_by' => auth()->id(),
                    'visited_at' => now(),
                ]);

                $queuePosition = Queue::where('department_id', $departmentId)
                    ->whereDate('created_at', today())
                    ->count() + 1;

                Queue::create([
                    'visit_id' => $visit->id,
                    'department_id' => $departmentId,
                    'queue_no' => Hospital::nextNumber('Q'),
                    'priority' => $validated['priority'] ?? 'normal',
                    'status' => 'waiting',
                    'position' => $queuePosition,
                ]);

                if ($request->boolean('charge_consultation_fee')) {
                    $consultationCharge = ServiceCharge::where('code', 'CONS-OPD')->first();

                    if ($consultationCharge) {
                        $waive = $exemption['eligible'];
                        $amount = $waive ? 0 : (float) $consultationCharge->price;

                        $invoice = Invoice::create([
                            'invoice_no' => Hospital::nextNumber('INV'),
                            'visit_id' => $visit->id,
                            'patient_id' => $patient->id,
                            'payment_category' => $paymentCategory,
                            'subtotal' => $consultationCharge->price,
                            'discount' => $waive ? $consultationCharge->price : 0,
                            'total' => $amount,
                            'paid_amount' => 0,
                            'balance' => $amount,
                            'status' => $waive ? 'waived' : 'unpaid',
                            'created_by' => auth()->id(),
                        ]);

                        InvoiceItem::create([
                            'invoice_id' => $invoice->id,
                            'item_type' => 'service_charge',
                            'item_id' => $consultationCharge->id,
                            'description' => $consultationCharge->name.($waive ? ' (Exempt / Msamaha)' : ''),
                            'quantity' => 1,
                            'unit_price' => $consultationCharge->price,
                            'total' => $consultationCharge->price,
                        ]);
                    }
                }

                return $visit;
            });

            return redirect()
                ->route('visits.show', $visit)
                ->with('success', 'Visit registered. Visit No: '.$visit->visit_no);
        } catch (\Throwable $e) {
            return back()->withInput()->withErrors(['error' => 'Failed to register visit.']);
        }
    }

    public function show(Visit $visit): View
    {
        $visit->load([
            'patient',
            'department',
            'doctor',
            'consultation',
            'vitalSigns',
            'labOrders.items.labTest',
            'prescriptions.items.medicine',
            'invoices.items',
            'queues',
        ]);

        return view('visits.show', compact('visit'));
    }

    public function updateStatus(Request $request, Visit $visit): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:registered,waiting,in_consultation,investigations,pharmacy,billing,admitted,discharged,completed,cancelled'],
            'doctor_id' => ['nullable', 'exists:users,id'],
        ]);

        try {
            $visit->update([
                'status' => $validated['status'],
                'doctor_id' => $validated['doctor_id'] ?? $visit->doctor_id,
                'completed_at' => in_array($validated['status'], ['completed', 'discharged', 'cancelled'], true)
                    ? now()
                    : $visit->completed_at,
            ]);

            return back()->with('success', 'Visit status updated to '.str_replace('_', ' ', $validated['status']).'.');
        } catch (\Throwable $e) {
            return back()->withErrors(['error' => 'Failed to update visit status.']);
        }
    }
}
