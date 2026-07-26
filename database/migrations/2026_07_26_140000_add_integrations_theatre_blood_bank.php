<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->string('nhif_member_name')->nullable()->after('nhif_card_no');
            $table->string('nhif_status', 40)->nullable()->after('nhif_member_name');
            $table->timestamp('nhif_verified_at')->nullable()->after('nhif_status');
            $table->json('nhif_response')->nullable()->after('nhif_verified_at');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->string('gepg_control_no', 60)->nullable()->after('reference');
            $table->enum('gepg_status', ['none', 'pending', 'paid', 'expired', 'cancelled'])->default('none')->after('gepg_control_no');
            $table->timestamp('gepg_paid_at')->nullable()->after('gepg_status');
            $table->json('gepg_response')->nullable()->after('gepg_paid_at');
        });

        Schema::create('gepg_bills', function (Blueprint $table) {
            $table->id();
            $table->string('bill_id', 40)->unique();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 14, 2);
            $table->string('control_number', 60)->nullable()->unique();
            $table->enum('status', ['pending', 'paid', 'expired', 'cancelled'])->default('pending');
            $table->string('payer_phone', 20)->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->json('request_payload')->nullable();
            $table->json('response_payload')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('theatre_rooms', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('name');
            $table->string('name_sw')->nullable();
            $table->enum('status', ['available', 'occupied', 'cleaning', 'maintenance'])->default('available');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('theatre_cases', function (Blueprint $table) {
            $table->id();
            $table->string('case_no', 30)->unique();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('visit_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('admission_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('theatre_room_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('surgeon_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('anaesthetist_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('procedure_name');
            $table->enum('urgency', ['elective', 'urgent', 'emergency'])->default('elective');
            $table->enum('asa_class', ['I', 'II', 'III', 'IV', 'V', 'E'])->nullable();
            $table->enum('status', ['scheduled', 'pre_op', 'in_theatre', 'recovery', 'completed', 'cancelled'])->default('scheduled');
            $table->dateTime('scheduled_at')->nullable();
            $table->dateTime('started_at')->nullable();
            $table->dateTime('ended_at')->nullable();
            $table->string('diagnosis')->nullable();
            $table->text('pre_op_notes')->nullable();
            $table->text('operative_notes')->nullable();
            $table->text('post_op_notes')->nullable();
            $table->string('anaesthesia_type')->nullable();
            $table->decimal('estimated_blood_loss_ml', 8, 1)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('blood_units', function (Blueprint $table) {
            $table->id();
            $table->string('unit_no', 40)->unique();
            $table->enum('blood_group', ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-']);
            $table->enum('component', ['whole_blood', 'packed_rbc', 'fresh_frozen_plasma', 'platelets', 'cryoprecipitate'])->default('whole_blood');
            $table->unsignedInteger('volume_ml')->default(450);
            $table->string('donor_name')->nullable();
            $table->date('collected_at')->nullable();
            $table->date('expiry_date');
            $table->enum('status', ['available', 'reserved', 'issued', 'expired', 'discarded'])->default('available');
            $table->string('storage_location')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('blood_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_no', 30)->unique();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('visit_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('theatre_case_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('requested_by')->constrained('users')->cascadeOnDelete();
            $table->enum('blood_group', ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-']);
            $table->enum('component', ['whole_blood', 'packed_rbc', 'fresh_frozen_plasma', 'platelets', 'cryoprecipitate'])->default('whole_blood');
            $table->unsignedInteger('units_requested')->default(1);
            $table->unsignedInteger('units_issued')->default(0);
            $table->enum('priority', ['routine', 'urgent', 'emergency'])->default('routine');
            $table->enum('status', ['pending', 'partial', 'fulfilled', 'cancelled'])->default('pending');
            $table->string('indication')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('blood_issues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blood_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('blood_unit_id')->constrained()->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('issued_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('issued_at');
            $table->string('crossmatch_result')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blood_issues');
        Schema::dropIfExists('blood_requests');
        Schema::dropIfExists('blood_units');
        Schema::dropIfExists('theatre_cases');
        Schema::dropIfExists('theatre_rooms');
        Schema::dropIfExists('gepg_bills');

        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['gepg_control_no', 'gepg_status', 'gepg_paid_at', 'gepg_response']);
        });

        Schema::table('patients', function (Blueprint $table) {
            $table->dropColumn(['nhif_member_name', 'nhif_status', 'nhif_verified_at', 'nhif_response']);
        });
    }
};
