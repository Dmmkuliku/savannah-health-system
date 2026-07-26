<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Consultation;
use App\Models\LabOrder;
use App\Models\LabOrderItem;
use App\Models\LabTest;
use App\Models\Medicine;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\RadiologyOrder;
use App\Models\RadiologyService;
use App\Models\Visit;
use App\Support\Hospital;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ConsultationController extends Controller
{
    public function create(Visit $visit): View
    {
        $visit->load('patient');

        return view('consultations.create', [
            'visit' => $visit,
            'medicines' => Medicine::where('is_active', true)->orderBy('name')->get(),
            'labTests' => LabTest::where('is_active', true)->orderBy('name')->get(),
            'radiologyServices' => RadiologyService::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request, Visit $visit): RedirectResponse
    {
        $validated = $request->validate([
            'history_of_present_illness' => ['nullable', 'string'],
            'past_medical_history' => ['nullable', 'string'],
            'examination_findings' => ['nullable', 'string'],
            'diagnosis_summary' => ['nullable', 'string', 'max:500'],
            'icd10_codes' => ['nullable', 'string', 'max:255'],
            'treatment_plan' => ['nullable', 'string'],
            'advice' => ['nullable', 'string'],
            'outcome' => ['required', 'in:treated,referred,admitted,follow_up,discharged'],
            'follow_up_date' => ['nullable', 'date', 'after_or_equal:today'],
            'visit_status' => ['nullable', 'in:investigations,pharmacy,billing,admitted,completed'],
            'prescription_items' => ['nullable', 'array'],
            'prescription_items.*.medicine_id' => ['required_with:prescription_items', 'exists:medicines,id'],
            'prescription_items.*.dosage' => ['nullable', 'string', 'max:100'],
            'prescription_items.*.frequency' => ['nullable', 'string', 'max:100'],
            'prescription_items.*.duration' => ['nullable', 'string', 'max:100'],
            'prescription_items.*.quantity' => ['required_with:prescription_items', 'integer', 'min:1'],
            'prescription_items.*.instructions' => ['nullable', 'string'],
            'lab_test_ids' => ['nullable', 'array'],
            'lab_test_ids.*' => ['exists:lab_tests,id'],
            'lab_priority' => ['nullable', 'in:routine,urgent,stat'],
            'lab_clinical_notes' => ['nullable', 'string'],
            'radiology_service_ids' => ['nullable', 'array'],
            'radiology_service_ids.*' => ['exists:radiology_services,id'],
            'radiology_clinical_info' => ['nullable', 'string'],
        ]);

        if ($visit->consultation) {
            return back()->withErrors(['error' => 'Consultation already recorded for this visit.']);
        }

        try {
            DB::transaction(function () use ($validated, $visit) {
                Consultation::create([
                    'visit_id' => $visit->id,
                    'patient_id' => $visit->patient_id,
                    'doctor_id' => auth()->id(),
                    'history_of_present_illness' => $validated['history_of_present_illness'] ?? null,
                    'past_medical_history' => $validated['past_medical_history'] ?? null,
                    'examination_findings' => $validated['examination_findings'] ?? null,
                    'diagnosis_summary' => $validated['diagnosis_summary'] ?? null,
                    'icd10_codes' => $validated['icd10_codes'] ?? null,
                    'treatment_plan' => $validated['treatment_plan'] ?? null,
                    'advice' => $validated['advice'] ?? null,
                    'outcome' => $validated['outcome'],
                    'follow_up_date' => $validated['follow_up_date'] ?? null,
                ]);

                if (! empty($validated['prescription_items'])) {
                    $prescription = Prescription::create([
                        'prescription_no' => Hospital::nextNumber('RX'),
                        'visit_id' => $visit->id,
                        'patient_id' => $visit->patient_id,
                        'doctor_id' => auth()->id(),
                        'status' => 'pending',
                    ]);

                    foreach ($validated['prescription_items'] as $item) {
                        $medicine = Medicine::findOrFail($item['medicine_id']);

                        PrescriptionItem::create([
                            'prescription_id' => $prescription->id,
                            'medicine_id' => $medicine->id,
                            'dosage' => $item['dosage'] ?? null,
                            'frequency' => $item['frequency'] ?? null,
                            'duration' => $item['duration'] ?? null,
                            'quantity' => $item['quantity'],
                            'instructions' => $item['instructions'] ?? null,
                            'unit_price' => $medicine->unit_price,
                        ]);
                    }
                }

                if (! empty($validated['lab_test_ids'])) {
                    $labOrder = LabOrder::create([
                        'order_no' => Hospital::nextNumber('LAB'),
                        'visit_id' => $visit->id,
                        'patient_id' => $visit->patient_id,
                        'ordered_by' => auth()->id(),
                        'priority' => $validated['lab_priority'] ?? 'routine',
                        'status' => 'pending',
                        'clinical_notes' => $validated['lab_clinical_notes'] ?? null,
                    ]);

                    foreach ($validated['lab_test_ids'] as $testId) {
                        $test = LabTest::findOrFail($testId);

                        LabOrderItem::create([
                            'lab_order_id' => $labOrder->id,
                            'lab_test_id' => $test->id,
                            'price' => $test->price,
                            'status' => 'pending',
                        ]);
                    }
                }

                if (! empty($validated['radiology_service_ids'])) {
                    foreach ($validated['radiology_service_ids'] as $serviceId) {
                        $service = RadiologyService::findOrFail($serviceId);

                        RadiologyOrder::create([
                            'order_no' => Hospital::nextNumber('RAD'),
                            'visit_id' => $visit->id,
                            'patient_id' => $visit->patient_id,
                            'radiology_service_id' => $service->id,
                            'ordered_by' => auth()->id(),
                            'status' => 'pending',
                            'clinical_info' => $validated['radiology_clinical_info'] ?? null,
                            'price' => $service->price,
                        ]);
                    }
                }

                $newStatus = $validated['visit_status'] ?? $this->resolveVisitStatus($validated);

                $visit->update([
                    'status' => $newStatus,
                    'doctor_id' => auth()->id(),
                ]);
            });

            return redirect()
                ->route('consultations.show', $visit)
                ->with('success', 'Consultation saved successfully.');
        } catch (\Throwable $e) {
            return back()->withInput()->withErrors(['error' => 'Failed to save consultation.']);
        }
    }

    public function show(Visit $visit): View
    {
        $visit->load([
            'patient',
            'consultation.doctor',
            'prescriptions.items.medicine',
            'labOrders.items.labTest',
        ]);

        return view('consultations.show', compact('visit'));
    }

    private function resolveVisitStatus(array $validated): string
    {
        if (! empty($validated['lab_test_ids']) || ! empty($validated['radiology_service_ids'])) {
            return 'investigations';
        }

        if (! empty($validated['prescription_items'])) {
            return 'pharmacy';
        }

        if ($validated['outcome'] === 'admitted') {
            return 'admitted';
        }

        return 'billing';
    }
}
