<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Consultation;
use App\Models\Delivery;
use App\Models\Invoice;
use App\Models\Medicine;
use App\Models\Payment;
use App\Models\RchAncVisit;
use App\Models\RchImmunization;
use App\Models\RchPregnancy;
use App\Models\Visit;
use App\Support\Hospital;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        [$from, $to] = $this->resolveDateRange($request);
        $data = $this->buildMtuhaData($from, $to);

        return view('reports.index', array_merge($data, [
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'facilityName' => Hospital::facilityName(),
            'paymentCategories' => Hospital::paymentCategories(),
        ]));
    }

    public function mtuhaPrint(Request $request): View
    {
        [$from, $to] = $this->resolveDateRange($request);
        $data = $this->buildMtuhaData($from, $to);

        return view('reports.mtuha-print', array_merge($data, [
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'facilityName' => Hospital::facilityName(),
            'paymentCategories' => Hospital::paymentCategories(),
        ]));
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    protected function resolveDateRange(Request $request): array
    {
        $from = $request->filled('from')
            ? Carbon::parse($request->query('from'))->startOfDay()
            : now()->startOfMonth()->startOfDay();

        $to = $request->filled('to')
            ? Carbon::parse($request->query('to'))->endOfDay()
            : now()->endOfMonth()->endOfDay();

        if ($from->gt($to)) {
            [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
        }

        return [$from, $to];
    }

    protected function buildMtuhaData(Carbon $from, Carbon $to): array
    {
        $opdAttendance = $this->opdAttendanceBySexAndAge($from, $to);

        $topDiagnoses = Consultation::query()
            ->whereNotNull('diagnosis_summary')
            ->where('diagnosis_summary', '!=', '')
            ->whereBetween('created_at', [$from, $to])
            ->select('diagnosis_summary', DB::raw('COUNT(*) as cases'))
            ->groupBy('diagnosis_summary')
            ->orderByDesc('cases')
            ->limit(15)
            ->get();

        $ancVisitsCount = RchAncVisit::query()
            ->whereBetween('visit_date', [$from->toDateString(), $to->toDateString()])
            ->count();

        $newPregnanciesCount = RchPregnancy::query()
            ->whereBetween('created_at', [$from, $to])
            ->count();

        $deliveriesByType = Delivery::query()
            ->whereBetween('delivered_at', [$from, $to])
            ->select('delivery_type', DB::raw('COUNT(*) as total'))
            ->groupBy('delivery_type')
            ->pluck('total', 'delivery_type');

        $deliveriesByOutcome = Delivery::query()
            ->whereBetween('delivered_at', [$from, $to])
            ->select('outcome', DB::raw('COUNT(*) as total'))
            ->groupBy('outcome')
            ->pluck('total', 'outcome');

        $immunizationsByVaccine = RchImmunization::query()
            ->whereBetween('given_at', [$from->toDateString(), $to->toDateString()])
            ->select('vaccine_code', 'vaccine_name', DB::raw('COUNT(*) as total'))
            ->groupBy('vaccine_code', 'vaccine_name')
            ->orderByDesc('total')
            ->get();

        $tracerMedicines = Medicine::query()
            ->where('is_active', true)
            ->whereColumn('stock_qty', '<=', 'reorder_level')
            ->orderBy('stock_qty')
            ->get();

        $revenueByCategory = Payment::query()
            ->join('invoices', 'payments.invoice_id', '=', 'invoices.id')
            ->whereBetween('payments.created_at', [$from, $to])
            ->select('invoices.payment_category', DB::raw('SUM(payments.amount) as total'))
            ->groupBy('invoices.payment_category')
            ->pluck('total', 'payment_category')
            ->map(fn ($v) => (float) $v);

        $waivedExemptionAmount = (float) Invoice::query()
            ->whereBetween('created_at', [$from, $to])
            ->where(function ($q) {
                $q->where('status', 'waived')
                    ->orWhere('payment_category', 'exemption');
            })
            ->sum('total');

        if ($waivedExemptionAmount > 0) {
            $revenueByCategory['exemption'] = ($revenueByCategory['exemption'] ?? 0) + $waivedExemptionAmount;
        }

        $totalRevenue = $revenueByCategory->sum();

        $exemptionVisitsCount = Visit::query()
            ->whereBetween('visited_at', [$from, $to])
            ->where('payment_category', 'exemption')
            ->count();

        $exemptionInvoicesCount = Invoice::query()
            ->whereBetween('created_at', [$from, $to])
            ->where(function ($q) {
                $q->where('payment_category', 'exemption')
                    ->orWhere('status', 'waived');
            })
            ->count();

        return [
            'opdAttendance' => $opdAttendance,
            'topDiagnoses' => $topDiagnoses,
            'ancVisitsCount' => $ancVisitsCount,
            'newPregnanciesCount' => $newPregnanciesCount,
            'deliveriesByType' => $deliveriesByType,
            'deliveriesByOutcome' => $deliveriesByOutcome,
            'immunizationsByVaccine' => $immunizationsByVaccine,
            'tracerMedicines' => $tracerMedicines,
            'revenueByCategory' => $revenueByCategory,
            'waivedExemptionAmount' => $waivedExemptionAmount,
            'totalRevenue' => $totalRevenue,
            'totalRevenueFormatted' => Hospital::money($totalRevenue),
            'exemptionVisitsCount' => $exemptionVisitsCount,
            'exemptionInvoicesCount' => $exemptionInvoicesCount,
        ];
    }

    protected function opdAttendanceBySexAndAge(Carbon $from, Carbon $to): array
    {
        $bands = ['under_5', '5_14', '15_49', '50_plus', 'unknown'];
        $genders = ['male', 'female', 'unknown'];

        $matrix = [];
        foreach ($bands as $band) {
            foreach ($genders as $gender) {
                $matrix[$band][$gender] = 0;
            }
            $matrix[$band]['total'] = 0;
        }

        $visits = Visit::query()
            ->with('patient')
            ->whereBetween('visited_at', [$from, $to])
            ->whereIn('visit_type', ['opd', 'emergency', 'rch', 'dental', 'eye', 'specialist'])
            ->get();

        foreach ($visits as $visit) {
            $patient = $visit->patient;
            if (! $patient) {
                continue;
            }

            $band = $this->ageBand($patient, $visit->visited_at ?? $visit->created_at);
            $gender = in_array($patient->gender, ['male', 'female'], true)
                ? $patient->gender
                : 'unknown';

            $matrix[$band][$gender]++;
            $matrix[$band]['total']++;
        }

        $matrix['totals'] = ['male' => 0, 'female' => 0, 'unknown' => 0, 'total' => 0];
        foreach ($bands as $band) {
            foreach ($genders as $gender) {
                $matrix['totals'][$gender] += $matrix[$band][$gender];
            }
            $matrix['totals']['total'] += $matrix[$band]['total'];
        }

        return $matrix;
    }

    protected function ageBand(object $patient, Carbon $referenceDate): string
    {
        $age = null;

        if ($patient->date_of_birth) {
            $age = $patient->date_of_birth->diffInYears($referenceDate);
        } elseif ($patient->age_years !== null) {
            $age = (int) $patient->age_years;
        }

        if ($age === null) {
            return 'unknown';
        }

        if ($age < 5) {
            return 'under_5';
        }

        if ($age <= 14) {
            return '5_14';
        }

        if ($age <= 49) {
            return '15_49';
        }

        return '50_plus';
    }
}
