<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Admission;
use App\Models\Bed;
use App\Models\Department;
use Illuminate\View\View;

class DepartmentController extends Controller
{
    public function index(): View
    {
        $departments = Department::query()
            ->with(['wards.beds'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(function (Department $department) {
                $wardStats = $department->wards->map(function ($ward) {
                    $totalBeds = $ward->beds->count();
                    $occupiedBeds = $ward->beds->where('status', 'occupied')->count();
                    $availableBeds = $ward->beds->where('status', 'available')->count();
                    $activeAdmissions = Admission::where('ward_id', $ward->id)
                        ->where('status', 'admitted')
                        ->count();

                    return [
                        'ward' => $ward,
                        'total_beds' => $totalBeds,
                        'occupied_beds' => $occupiedBeds,
                        'available_beds' => $availableBeds,
                        'active_admissions' => $activeAdmissions,
                        'occupancy_rate' => $totalBeds > 0 ? round(($occupiedBeds / $totalBeds) * 100, 1) : 0,
                    ];
                });

                return [
                    'department' => $department,
                    'wards' => $wardStats,
                    'total_beds' => $department->wards->sum(fn ($w) => $w->beds->count()),
                    'occupied_beds' => Bed::whereIn('ward_id', $department->wards->pluck('id'))
                        ->where('status', 'occupied')
                        ->count(),
                ];
            });

        return view('departments.index', compact('departments'));
    }
}
