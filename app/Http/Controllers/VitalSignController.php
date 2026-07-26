<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Visit;
use App\Models\VitalSign;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class VitalSignController extends Controller
{
    public function store(Request $request, Visit $visit): RedirectResponse
    {
        $validated = $request->validate([
            'temperature_c' => ['nullable', 'numeric', 'min:30', 'max:45'],
            'pulse' => ['nullable', 'integer', 'min:30', 'max:250'],
            'respiratory_rate' => ['nullable', 'integer', 'min:5', 'max:60'],
            'bp_systolic' => ['nullable', 'integer', 'min:60', 'max:260'],
            'bp_diastolic' => ['nullable', 'integer', 'min:40', 'max:180'],
            'spo2' => ['nullable', 'integer', 'min:50', 'max:100'],
            'weight_kg' => ['nullable', 'numeric', 'min:0.5', 'max:500'],
            'height_cm' => ['nullable', 'numeric', 'min:30', 'max:250'],
            'pain_score' => ['nullable', 'integer', 'min:0', 'max:10'],
            'notes' => ['nullable', 'string'],
        ]);

        if (
            isset($validated['bp_systolic'], $validated['bp_diastolic'])
            && $validated['bp_systolic'] < $validated['bp_diastolic']
        ) {
            return back()->withInput()->withErrors(['bp_systolic' => 'Systolic BP must be greater than diastolic BP.']);
        }

        try {
            $bmi = null;
            if (! empty($validated['weight_kg']) && ! empty($validated['height_cm'])) {
                $heightM = (float) $validated['height_cm'] / 100;
                if ($heightM > 0) {
                    $bmi = round((float) $validated['weight_kg'] / ($heightM * $heightM), 2);
                }
            }

            VitalSign::create([
                ...$validated,
                'visit_id' => $visit->id,
                'patient_id' => $visit->patient_id,
                'recorded_by' => auth()->id(),
                'bmi' => $bmi,
            ]);

            return back()->with('success', 'Vital signs recorded successfully.');
        } catch (\Throwable $e) {
            return back()->withInput()->withErrors(['error' => 'Failed to record vital signs.']);
        }
    }
}
