<?php

use App\Http\Controllers\AdmissionController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\BloodBankController;
use App\Http\Controllers\ConsultationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\GepgController;
use App\Http\Controllers\LabOrderController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\MaternityController;
use App\Http\Controllers\MedicineController;
use App\Http\Controllers\NhifClaimController;
use App\Http\Controllers\NhifController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\PharmacyController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RadiologyOrderController;
use App\Http\Controllers\RchController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TheatreController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\VisitController;
use App\Http\Controllers\VitalSignController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

Route::get('/locale/{locale}', [LocaleController::class, 'switch'])->name('locale.switch');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->middleware('role')
        ->name('dashboard');

    Route::middleware('role:admin,receptionist,doctor,nurse,records')->group(function () {
        Route::resource('patients', PatientController::class)->except(['destroy']);
        Route::post('/patients/{patient}/nhif/verify', [NhifController::class, 'verify'])->name('nhif.verify');
    });

    Route::middleware('role:admin,receptionist,doctor,nurse')->group(function () {
        Route::get('/visits', [VisitController::class, 'index'])->name('visits.index');
        Route::get('/visits/create', [VisitController::class, 'create'])->name('visits.create');
        Route::post('/visits', [VisitController::class, 'store'])->name('visits.store');
        Route::get('/visits/{visit}', [VisitController::class, 'show'])->name('visits.show');
        Route::patch('/visits/{visit}/status', [VisitController::class, 'updateStatus'])->name('visits.update-status');
        Route::post('/visits/{visit}/vital-signs', [VitalSignController::class, 'store'])->name('vital-signs.store');
    });

    Route::middleware('role:admin,doctor')->group(function () {
        Route::get('/visits/{visit}/consultation/create', [ConsultationController::class, 'create'])->name('consultations.create');
        Route::post('/visits/{visit}/consultation', [ConsultationController::class, 'store'])->name('consultations.store');
        Route::get('/visits/{visit}/consultation', [ConsultationController::class, 'show'])->name('consultations.show');
    });

    Route::middleware('role:admin,doctor,nurse')->group(function () {
        Route::get('/visits/{visit}/admit', [AdmissionController::class, 'create'])->name('admissions.create');
        Route::post('/visits/{visit}/admit', [AdmissionController::class, 'store'])->name('admissions.store');
        Route::get('/admissions', [AdmissionController::class, 'index'])->name('admissions.index');
        Route::post('/admissions/{admission}/discharge', [AdmissionController::class, 'discharge'])->name('admissions.discharge');
    });

    Route::middleware('role:admin,lab_technician,doctor')->group(function () {
        Route::get('/lab/orders', [LabOrderController::class, 'index'])->name('lab.orders.index');
        Route::get('/lab/orders/{labOrder}', [LabOrderController::class, 'show'])->name('lab.orders.show');
        Route::post('/lab/orders/{labOrder}/results', [LabOrderController::class, 'updateResults'])->name('lab.orders.update-results');
    });

    Route::middleware('role:admin,radiologist,doctor')->group(function () {
        Route::get('/radiology/orders', [RadiologyOrderController::class, 'index'])->name('radiology.orders.index');
        Route::get('/radiology/orders/{radiologyOrder}', [RadiologyOrderController::class, 'show'])->name('radiology.orders.show');
        Route::post('/radiology/orders/{radiologyOrder}/report', [RadiologyOrderController::class, 'updateReport'])->name('radiology.orders.update-report');
    });

    Route::middleware('role:admin,pharmacist,doctor')->group(function () {
        Route::get('/pharmacy', [PharmacyController::class, 'index'])->name('pharmacy.index');
        Route::get('/pharmacy/{prescription}', [PharmacyController::class, 'show'])->name('pharmacy.show');
        Route::post('/pharmacy/{prescription}/dispense', [PharmacyController::class, 'dispense'])->name('pharmacy.dispense');
    });

    Route::middleware('role:admin,pharmacist')->group(function () {
        Route::get('/medicines', [MedicineController::class, 'index'])->name('medicines.index');
        Route::get('/medicines/create', [MedicineController::class, 'create'])->name('medicines.create');
        Route::post('/medicines', [MedicineController::class, 'store'])->name('medicines.store');
        Route::get('/medicines/{medicine}/edit', [MedicineController::class, 'edit'])->name('medicines.edit');
        Route::put('/medicines/{medicine}', [MedicineController::class, 'update'])->name('medicines.update');
        Route::post('/medicines/{medicine}/stock-in', [MedicineController::class, 'stockIn'])->name('medicines.stock-in');
    });

    Route::middleware('role:admin,cashier,receptionist')->group(function () {
        Route::get('/billing', [BillingController::class, 'index'])->name('billing.index');
        Route::get('/billing/receipt/{payment}', [BillingController::class, 'printReceipt'])->name('billing.receipt');
        Route::get('/billing/{invoice}', [BillingController::class, 'show'])->name('billing.show');
        Route::post('/visits/{visit}/invoice', [BillingController::class, 'createFromVisit'])->name('billing.create-from-visit');
        Route::post('/billing/{invoice}/payments', [BillingController::class, 'storePayment'])->name('billing.store-payment');
        Route::post('/billing/{invoice}/gepg', [GepgController::class, 'generate'])->name('gepg.generate');
        Route::post('/gepg/{gepgBill}/simulate-pay', [GepgController::class, 'simulatePay'])->name('gepg.simulate-pay');
    });

    Route::middleware('role:admin,receptionist,doctor')->group(function () {
        Route::get('/appointments', [AppointmentController::class, 'index'])->name('appointments.index');
        Route::get('/appointments/create', [AppointmentController::class, 'create'])->name('appointments.create');
        Route::post('/appointments', [AppointmentController::class, 'store'])->name('appointments.store');
        Route::patch('/appointments/{appointment}/status', [AppointmentController::class, 'updateStatus'])->name('appointments.update-status');
    });

    Route::middleware('role:admin,doctor,nurse,receptionist')->group(function () {
        Route::get('/rch', [RchController::class, 'index'])->name('rch.index');
        Route::get('/rch/pregnancies/create', [RchController::class, 'createPregnancy'])->name('rch.pregnancies.create');
        Route::post('/rch/pregnancies', [RchController::class, 'storePregnancy'])->name('rch.pregnancies.store');
        Route::get('/rch/pregnancies/{pregnancy}', [RchController::class, 'showPregnancy'])->name('rch.pregnancies.show');
        Route::post('/rch/pregnancies/{pregnancy}/anc', [RchController::class, 'storeAncVisit'])->name('rch.anc.store');
        Route::get('/rch/immunizations/create', [RchController::class, 'createImmunization'])->name('rch.immunizations.create');
        Route::post('/rch/immunizations', [RchController::class, 'storeImmunization'])->name('rch.immunizations.store');
        Route::post('/rch/pnc', [RchController::class, 'storePncVisit'])->name('rch.pnc.store');

        Route::get('/maternity', [MaternityController::class, 'index'])->name('maternity.index');
        Route::get('/maternity/create', [MaternityController::class, 'create'])->name('maternity.create');
        Route::post('/maternity', [MaternityController::class, 'store'])->name('maternity.store');
        Route::get('/maternity/{delivery}', [MaternityController::class, 'show'])->name('maternity.show');
    });

    Route::middleware('role:admin,cashier,receptionist')->group(function () {
        Route::get('/nhif/claims', [NhifClaimController::class, 'index'])->name('nhif.claims.index');
        Route::get('/nhif/authorize', [NhifClaimController::class, 'authorizeForm'])->name('nhif.authorize.create');
        Route::post('/nhif/authorize', [NhifClaimController::class, 'storeAuthorization'])->name('nhif.authorize.store');
        Route::post('/nhif/claims/from-invoice', [NhifClaimController::class, 'createClaim'])->name('nhif.claims.from-invoice');
        Route::get('/nhif/claims/{claim}', [NhifClaimController::class, 'showClaim'])->name('nhif.claims.show');
        Route::get('/nhif/batches', [NhifClaimController::class, 'batchesIndex'])->name('nhif.batches.index');
        Route::get('/nhif/batches/create', [NhifClaimController::class, 'batchesCreate'])->name('nhif.batches.create');
        Route::post('/nhif/batches', [NhifClaimController::class, 'batchesStore'])->name('nhif.batches.store');
        Route::post('/nhif/batches/{batch}/submit', [NhifClaimController::class, 'submitBatch'])->name('nhif.batches.submit');
    });

    Route::middleware('role:admin,doctor,nurse')->group(function () {
        Route::get('/theatre', [TheatreController::class, 'index'])->name('theatre.index');
        Route::get('/theatre/create', [TheatreController::class, 'create'])->name('theatre.create');
        Route::post('/theatre', [TheatreController::class, 'store'])->name('theatre.store');
        Route::get('/theatre/{theatreCase}', [TheatreController::class, 'show'])->name('theatre.show');
        Route::patch('/theatre/{theatreCase}/status', [TheatreController::class, 'updateStatus'])->name('theatre.update-status');
    });

    Route::middleware('role:admin,lab_technician,doctor,nurse')->group(function () {
        Route::get('/blood-bank', [BloodBankController::class, 'index'])->name('blood.index');
        Route::get('/blood-bank/units/create', [BloodBankController::class, 'createUnit'])->name('blood.units.create');
        Route::post('/blood-bank/units', [BloodBankController::class, 'storeUnit'])->name('blood.units.store');
        Route::get('/blood-bank/requests/create', [BloodBankController::class, 'createRequest'])->name('blood.requests.create');
        Route::post('/blood-bank/requests', [BloodBankController::class, 'storeRequest'])->name('blood.requests.store');
        Route::get('/blood-bank/requests/{bloodRequest}', [BloodBankController::class, 'showRequest'])->name('blood.requests.show');
        Route::post('/blood-bank/requests/{bloodRequest}/issue', [BloodBankController::class, 'issue'])->name('blood.requests.issue');
    });

    Route::middleware('role:admin,records')->group(function () {
        Route::get('/departments', [DepartmentController::class, 'index'])->name('departments.index');
    });

    Route::middleware('role:admin,records,cashier')->group(function () {
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/mtuha-print', [ReportController::class, 'mtuhaPrint'])->name('reports.mtuha-print');
    });

    Route::middleware('role:admin')->group(function () {
        Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserManagementController::class, 'create'])->name('users.create');
        Route::post('/users', [UserManagementController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [UserManagementController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [UserManagementController::class, 'update'])->name('users.update');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
