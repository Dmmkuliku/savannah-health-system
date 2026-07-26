<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->unique()->nullable()->after('id');
            $table->string('phone', 20)->nullable()->after('email');
            $table->string('role', 40)->default('receptionist')->after('password')->index();
            $table->string('employee_no', 40)->nullable()->after('role');
            $table->string('specialty')->nullable()->after('employee_no');
            $table->foreignId('department_id')->nullable()->after('specialty');
            $table->boolean('is_active')->default(true)->after('department_id');
        });

        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('name');
            $table->string('name_sw')->nullable();
            $table->enum('type', ['clinical', 'diagnostic', 'support', 'admin'])->default('clinical');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreign('department_id')->references('id')->on('departments')->nullOnDelete();
        });

        Schema::create('wards', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('name');
            $table->enum('gender', ['male', 'female', 'mixed', 'pediatric'])->default('mixed');
            $table->unsignedInteger('capacity')->default(0);
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('daily_rate', 12, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('beds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ward_id')->constrained()->cascadeOnDelete();
            $table->string('bed_number', 20);
            $table->enum('status', ['available', 'occupied', 'maintenance', 'reserved'])->default('available');
            $table->timestamps();
            $table->unique(['ward_id', 'bed_number']);
        });

        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->string('mrn', 30)->unique();
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->enum('gender', ['male', 'female', 'other']);
            $table->date('date_of_birth')->nullable();
            $table->unsignedSmallInteger('age_years')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('email')->nullable();
            $table->string('national_id', 30)->nullable()->index();
            $table->string('nhif_card_no', 40)->nullable()->index();
            $table->enum('payment_category', ['cash', 'nhif', 'exemption', 'corporate', 'insurance'])->default('cash');
            $table->string('exemption_type')->nullable();
            $table->string('next_of_kin')->nullable();
            $table->string('next_of_kin_phone', 20)->nullable();
            $table->string('region')->nullable();
            $table->string('district')->nullable();
            $table->string('ward_village')->nullable();
            $table->string('street_address')->nullable();
            $table->string('occupation')->nullable();
            $table->enum('marital_status', ['single', 'married', 'divorced', 'widowed', 'unknown'])->default('unknown');
            $table->string('blood_group', 5)->nullable();
            $table->text('allergies')->nullable();
            $table->text('chronic_conditions')->nullable();
            $table->foreignId('registered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('visits', function (Blueprint $table) {
            $table->id();
            $table->string('visit_no', 30)->unique();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->enum('visit_type', ['opd', 'ipd', 'emergency', 'rch', 'dental', 'eye', 'specialist'])->default('opd');
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('payment_category', ['cash', 'nhif', 'exemption', 'corporate', 'insurance'])->default('cash');
            $table->string('chief_complaint')->nullable();
            $table->enum('status', ['registered', 'waiting', 'in_consultation', 'investigations', 'pharmacy', 'billing', 'admitted', 'discharged', 'completed', 'cancelled'])->default('registered');
            $table->foreignId('doctor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('visited_at')->useCurrent();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('queues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visit_id')->constrained()->cascadeOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->string('queue_no', 20);
            $table->enum('priority', ['normal', 'urgent', 'emergency'])->default('normal');
            $table->enum('status', ['waiting', 'called', 'serving', 'done', 'skipped'])->default('waiting');
            $table->unsignedInteger('position')->default(0);
            $table->timestamp('called_at')->nullable();
            $table->timestamps();
        });

        Schema::create('vital_signs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visit_id')->constrained()->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('temperature_c', 4, 1)->nullable();
            $table->unsignedSmallInteger('pulse')->nullable();
            $table->unsignedSmallInteger('respiratory_rate')->nullable();
            $table->unsignedSmallInteger('bp_systolic')->nullable();
            $table->unsignedSmallInteger('bp_diastolic')->nullable();
            $table->unsignedSmallInteger('spo2')->nullable();
            $table->decimal('weight_kg', 6, 2)->nullable();
            $table->decimal('height_cm', 6, 2)->nullable();
            $table->decimal('bmi', 5, 2)->nullable();
            $table->unsignedSmallInteger('pain_score')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('consultations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visit_id')->constrained()->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('doctor_id')->constrained('users')->cascadeOnDelete();
            $table->text('history_of_present_illness')->nullable();
            $table->text('past_medical_history')->nullable();
            $table->text('examination_findings')->nullable();
            $table->text('diagnosis_summary')->nullable();
            $table->text('icd10_codes')->nullable();
            $table->text('treatment_plan')->nullable();
            $table->text('advice')->nullable();
            $table->enum('outcome', ['treated', 'referred', 'admitted', 'follow_up', 'discharged'])->default('treated');
            $table->date('follow_up_date')->nullable();
            $table->timestamps();
        });

        Schema::create('admissions', function (Blueprint $table) {
            $table->id();
            $table->string('admission_no', 30)->unique();
            $table->foreignId('visit_id')->constrained()->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ward_id')->constrained()->cascadeOnDelete();
            $table->foreignId('bed_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('admitting_doctor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('admission_reason')->nullable();
            $table->enum('status', ['admitted', 'transferred', 'discharged', 'absconded', 'deceased'])->default('admitted');
            $table->timestamp('admitted_at');
            $table->timestamp('discharged_at')->nullable();
            $table->text('discharge_summary')->nullable();
            $table->timestamps();
        });

        Schema::create('nursing_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admission_id')->constrained()->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('nurse_id')->constrained('users')->cascadeOnDelete();
            $table->text('note');
            $table->enum('note_type', ['general', 'medication', 'observation', 'procedure'])->default('general');
            $table->timestamps();
        });

        Schema::create('lab_tests', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name');
            $table->string('category')->nullable();
            $table->string('sample_type')->nullable();
            $table->decimal('price', 12, 2)->default(0);
            $table->string('unit')->nullable();
            $table->string('normal_range')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('lab_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_no', 30)->unique();
            $table->foreignId('visit_id')->constrained()->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ordered_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('priority', ['routine', 'urgent', 'stat'])->default('routine');
            $table->enum('status', ['pending', 'sample_collected', 'processing', 'completed', 'cancelled'])->default('pending');
            $table->text('clinical_notes')->nullable();
            $table->timestamp('collected_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('lab_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lab_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lab_test_id')->constrained()->cascadeOnDelete();
            $table->string('result')->nullable();
            $table->string('result_flag', 20)->nullable();
            $table->text('remarks')->nullable();
            $table->enum('status', ['pending', 'completed', 'cancelled'])->default('pending');
            $table->decimal('price', 12, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('radiology_services', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name');
            $table->enum('modality', ['xray', 'ultrasound', 'ct', 'mri', 'other'])->default('xray');
            $table->decimal('price', 12, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('radiology_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_no', 30)->unique();
            $table->foreignId('visit_id')->constrained()->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('radiology_service_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ordered_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('reported_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['pending', 'in_progress', 'completed', 'cancelled'])->default('pending');
            $table->text('clinical_info')->nullable();
            $table->text('findings')->nullable();
            $table->text('impression')->nullable();
            $table->decimal('price', 12, 2)->default(0);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('medicines', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name');
            $table->string('generic_name')->nullable();
            $table->string('category')->nullable();
            $table->string('form')->nullable();
            $table->string('strength')->nullable();
            $table->string('unit', 30)->default('tablet');
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->unsignedInteger('stock_qty')->default(0);
            $table->unsignedInteger('reorder_level')->default(10);
            $table->date('expiry_date')->nullable();
            $table->string('batch_no')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medicine_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['in', 'out', 'adjustment', 'return', 'expire']);
            $table->integer('quantity');
            $table->unsignedInteger('balance_after');
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('prescriptions', function (Blueprint $table) {
            $table->id();
            $table->string('prescription_no', 30)->unique();
            $table->foreignId('visit_id')->constrained()->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('doctor_id')->constrained('users')->cascadeOnDelete();
            $table->enum('status', ['pending', 'partial', 'dispensed', 'cancelled'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('prescription_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prescription_id')->constrained()->cascadeOnDelete();
            $table->foreignId('medicine_id')->constrained()->cascadeOnDelete();
            $table->string('dosage')->nullable();
            $table->string('frequency')->nullable();
            $table->string('duration')->nullable();
            $table->unsignedInteger('quantity');
            $table->unsignedInteger('quantity_dispensed')->default(0);
            $table->text('instructions')->nullable();
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('dispensings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prescription_id')->constrained()->cascadeOnDelete();
            $table->foreignId('prescription_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('medicine_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pharmacist_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedInteger('quantity');
            $table->decimal('total_price', 12, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('service_charges', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name');
            $table->string('category')->nullable();
            $table->decimal('price', 12, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_no', 30)->unique();
            $table->foreignId('visit_id')->constrained()->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->enum('payment_category', ['cash', 'nhif', 'exemption', 'corporate', 'insurance'])->default('cash');
            $table->decimal('subtotal', 14, 2)->default(0);
            $table->decimal('discount', 14, 2)->default(0);
            $table->decimal('total', 14, 2)->default(0);
            $table->decimal('paid_amount', 14, 2)->default(0);
            $table->decimal('balance', 14, 2)->default(0);
            $table->enum('status', ['unpaid', 'partial', 'paid', 'waived', 'cancelled'])->default('unpaid');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->string('item_type');
            $table->unsignedBigInteger('item_id')->nullable();
            $table->string('description');
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('receipt_no', 30)->unique();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 14, 2);
            $table->enum('method', ['cash', 'mobile_money', 'bank', 'nhif', 'exemption', 'card'])->default('cash');
            $table->string('reference')->nullable();
            $table->string('mobile_provider')->nullable();
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('doctor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->date('appointment_date');
            $table->time('appointment_time')->nullable();
            $table->string('reason')->nullable();
            $table->enum('status', ['scheduled', 'checked_in', 'completed', 'cancelled', 'no_show'])->default('scheduled');
            $table->timestamps();
        });

        Schema::create('facility_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('facility_settings');
        Schema::dropIfExists('appointments');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('service_charges');
        Schema::dropIfExists('dispensings');
        Schema::dropIfExists('prescription_items');
        Schema::dropIfExists('prescriptions');
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('medicines');
        Schema::dropIfExists('radiology_orders');
        Schema::dropIfExists('radiology_services');
        Schema::dropIfExists('lab_order_items');
        Schema::dropIfExists('lab_orders');
        Schema::dropIfExists('lab_tests');
        Schema::dropIfExists('nursing_notes');
        Schema::dropIfExists('admissions');
        Schema::dropIfExists('consultations');
        Schema::dropIfExists('vital_signs');
        Schema::dropIfExists('queues');
        Schema::dropIfExists('visits');
        Schema::dropIfExists('patients');
        Schema::dropIfExists('beds');
        Schema::dropIfExists('wards');
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropColumn(['username', 'phone', 'role', 'employee_no', 'specialty', 'department_id', 'is_active']);
        });
        Schema::dropIfExists('departments');
    }
};
