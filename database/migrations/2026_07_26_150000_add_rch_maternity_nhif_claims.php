<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rch_pregnancies', function (Blueprint $table) {
            $table->id();
            $table->string('anc_no', 30)->unique();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->date('lmp')->nullable();
            $table->date('edd')->nullable();
            $table->unsignedTinyInteger('gravida')->nullable();
            $table->unsignedTinyInteger('para')->nullable();
            $table->unsignedTinyInteger('abortions')->default(0);
            $table->string('blood_group', 5)->nullable();
            $table->boolean('hiv_tested')->default(false);
            $table->string('hiv_status', 20)->nullable();
            $table->boolean('tt_given')->default(false);
            $table->text('risk_factors')->nullable();
            $table->enum('status', ['active', 'delivered', 'referred', 'closed'])->default('active');
            $table->foreignId('registered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('rch_anc_visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rch_pregnancy_id')->constrained()->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('visit_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedTinyInteger('visit_number')->default(1);
            $table->date('visit_date');
            $table->unsignedTinyInteger('gestational_weeks')->nullable();
            $table->decimal('weight_kg', 6, 2)->nullable();
            $table->unsignedSmallInteger('bp_systolic')->nullable();
            $table->unsignedSmallInteger('bp_diastolic')->nullable();
            $table->decimal('hb_gdl', 4, 1)->nullable();
            $table->boolean('fetal_heart_heard')->nullable();
            $table->string('urine_protein')->nullable();
            $table->boolean('ipt_given')->default(false);
            $table->boolean('iron_folate_given')->default(false);
            $table->boolean('mosquito_net_given')->default(false);
            $table->text('notes')->nullable();
            $table->foreignId('attended_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('rch_pnc_visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('delivery_id')->nullable();
            $table->foreignId('visit_id')->nullable()->constrained()->nullOnDelete();
            $table->date('visit_date');
            $table->unsignedSmallInteger('days_postpartum')->nullable();
            $table->string('mother_condition')->nullable();
            $table->string('baby_condition')->nullable();
            $table->boolean('breastfeeding')->nullable();
            $table->boolean('family_planning_counselled')->default(false);
            $table->text('notes')->nullable();
            $table->foreignId('attended_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('rch_immunizations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->string('vaccine_code', 30);
            $table->string('vaccine_name');
            $table->string('dose', 20)->nullable();
            $table->date('given_at');
            $table->string('batch_no')->nullable();
            $table->date('next_due')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('given_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();
            $table->string('delivery_no', 30)->unique();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('rch_pregnancy_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('admission_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('visit_id')->nullable()->constrained()->nullOnDelete();
            $table->dateTime('delivered_at');
            $table->enum('delivery_type', ['spontaneous_vaginal', 'assisted_vaginal', 'caesarean', 'breech'])->default('spontaneous_vaginal');
            $table->enum('place', ['labour_ward', 'theatre', 'home_brought', 'other'])->default('labour_ward');
            $table->unsignedTinyInteger('gestational_weeks')->nullable();
            $table->enum('outcome', ['live_birth', 'stillbirth', 'neonatal_death'])->default('live_birth');
            $table->unsignedTinyInteger('babies_count')->default(1);
            $table->boolean('mother_alive')->default(true);
            $table->string('complications')->nullable();
            $table->foreignId('attendant_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::table('rch_pnc_visits', function (Blueprint $table) {
            $table->foreign('delivery_id')->references('id')->on('deliveries')->nullOnDelete();
        });

        Schema::create('newborns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_id')->constrained()->cascadeOnDelete();
            $table->foreignId('mother_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignId('patient_id')->nullable()->constrained('patients')->nullOnDelete();
            $table->string('name')->nullable();
            $table->enum('sex', ['male', 'female', 'unknown'])->default('unknown');
            $table->decimal('birth_weight_kg', 5, 3)->nullable();
            $table->unsignedTinyInteger('apgar_1')->nullable();
            $table->unsignedTinyInteger('apgar_5')->nullable();
            $table->enum('status', ['alive', 'stillbirth', 'died'])->default('alive');
            $table->boolean('breastfeeding_initiated')->default(false);
            $table->boolean('bcg_given')->default(false);
            $table->boolean('opv0_given')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('nhif_authorizations', function (Blueprint $table) {
            $table->id();
            $table->string('auth_no', 40)->unique();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('visit_id')->nullable()->constrained()->nullOnDelete();
            $table->string('card_no', 40);
            $table->string('authorization_code', 60)->nullable();
            $table->string('diagnosis')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'used', 'expired'])->default('pending');
            $table->decimal('approved_amount', 14, 2)->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->json('response_payload')->nullable();
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('nhif_claim_batches', function (Blueprint $table) {
            $table->id();
            $table->string('batch_no', 40)->unique();
            $table->date('period_from');
            $table->date('period_to');
            $table->enum('status', ['open', 'submitted', 'paid', 'rejected'])->default('open');
            $table->unsignedInteger('claims_count')->default(0);
            $table->decimal('total_amount', 14, 2)->default(0);
            $table->timestamp('submitted_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('nhif_claims', function (Blueprint $table) {
            $table->id();
            $table->string('claim_no', 40)->unique();
            $table->foreignId('nhif_claim_batch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('visit_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('invoice_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('nhif_authorization_id')->nullable()->constrained()->nullOnDelete();
            $table->string('card_no', 40)->nullable();
            $table->string('diagnosis')->nullable();
            $table->decimal('amount', 14, 2)->default(0);
            $table->enum('status', ['draft', 'ready', 'submitted', 'paid', 'rejected'])->default('draft');
            $table->timestamp('submitted_at')->nullable();
            $table->json('items_snapshot')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nhif_claims');
        Schema::dropIfExists('nhif_claim_batches');
        Schema::dropIfExists('nhif_authorizations');
        Schema::dropIfExists('newborns');
        Schema::table('rch_pnc_visits', function (Blueprint $table) {
            $table->dropForeign(['delivery_id']);
        });
        Schema::dropIfExists('deliveries');
        Schema::dropIfExists('rch_immunizations');
        Schema::dropIfExists('rch_pnc_visits');
        Schema::dropIfExists('rch_anc_visits');
        Schema::dropIfExists('rch_pregnancies');
    }
};
