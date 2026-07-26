<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Admission;
use App\Models\Bed;
use App\Models\LabOrder;
use App\Models\Medicine;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\Queue;
use App\Models\Visit;
use App\Support\Hospital;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $today = now()->toDateString();

        $todayPatients = Patient::whereDate('created_at', $today)->count();

        $todayOpdVisits = Visit::whereDate('visited_at', $today)
            ->whereIn('visit_type', ['opd', 'emergency', 'rch', 'dental', 'eye', 'specialist'])
            ->count();

        $todayIpdAdmissions = Admission::whereDate('admitted_at', $today)->count();

        $todayRevenue = Payment::whereDate('created_at', $today)->sum('amount');

        $lowStockMedicines = Medicine::query()
            ->where('is_active', true)
            ->whereColumn('stock_qty', '<=', 'reorder_level')
            ->orderBy('stock_qty')
            ->limit(10)
            ->get();

        $pendingLabOrders = LabOrder::whereIn('status', ['pending', 'sample_collected', 'processing'])->count();

        $waitingQueue = Queue::where('status', 'waiting')
            ->whereHas('visit', fn ($q) => $q->whereDate('visited_at', $today))
            ->count();

        $totalBeds = Bed::count();
        $occupiedBeds = Bed::where('status', 'occupied')->count();

        return view('dashboard', [
            'facilityName' => Hospital::facilityName(),
            'todayPatients' => $todayPatients,
            'todayOpdVisits' => $todayOpdVisits,
            'todayIpdAdmissions' => $todayIpdAdmissions,
            'todayRevenue' => $todayRevenue,
            'todayRevenueFormatted' => Hospital::money($todayRevenue),
            'lowStockMedicines' => $lowStockMedicines,
            'pendingLabOrders' => $pendingLabOrders,
            'waitingQueue' => $waitingQueue,
            'totalBeds' => $totalBeds,
            'occupiedBeds' => $occupiedBeds,
        ]);
    }
}
