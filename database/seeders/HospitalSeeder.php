<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Bed;
use App\Models\Department;
use App\Models\FacilitySetting;
use App\Models\LabTest;
use App\Models\Medicine;
use App\Models\Patient;
use App\Models\RadiologyService;
use App\Models\ServiceCharge;
use App\Models\User;
use App\Models\Ward;
use App\Support\Hospital;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class HospitalSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedFacilitySettings();
        $departments = $this->seedDepartments();
        $this->seedWardsAndBeds($departments);
        $this->seedLabTests();
        $this->seedRadiologyServices();
        $this->seedMedicines();
        $this->seedServiceCharges();
        $this->seedUsers($departments);
        // Patients and staff (except admin) are registered by the hospital administrator after install.
    }

    private function seedFacilitySettings(): void
    {
        FacilitySetting::setValue('facility_name', 'Savannah Health System');
        FacilitySetting::setValue('facility_code', 'SHS');
        FacilitySetting::setValue('region', 'Dodoma');
        FacilitySetting::setValue('currency', 'TZS');
        FacilitySetting::setValue('brand_color', 'tea_green');
    }

    /**
     * @return array<string, Department>
     */
    private function seedDepartments(): array
    {
        $definitions = [
            ['code' => 'OPD', 'name' => 'OPD', 'name_sw' => 'Kituo cha Nje', 'type' => 'clinical'],
            ['code' => 'EMR', 'name' => 'Casualty/Emergency', 'name_sw' => 'Dharura', 'type' => 'clinical'],
            ['code' => 'LAB', 'name' => 'Laboratory', 'name_sw' => 'Maabara', 'type' => 'diagnostic'],
            ['code' => 'PHM', 'name' => 'Pharmacy', 'name_sw' => 'Duka la Dawa', 'type' => 'support'],
            ['code' => 'RAD', 'name' => 'Radiology', 'name_sw' => 'Picha za Tiba', 'type' => 'diagnostic'],
            ['code' => 'MAT', 'name' => 'Maternity', 'name_sw' => 'Uzazi', 'type' => 'clinical'],
            ['code' => 'PED', 'name' => 'Pediatrics', 'name_sw' => 'Watoto', 'type' => 'clinical'],
            ['code' => 'IMD', 'name' => 'Internal Medicine', 'name_sw' => 'Dawa za Ndani', 'type' => 'clinical'],
            ['code' => 'SUR', 'name' => 'Surgery', 'name_sw' => 'Upasuaji', 'type' => 'clinical'],
            ['code' => 'RCH', 'name' => 'RCH', 'name_sw' => 'Afya ya Uzazi na Mtoto', 'type' => 'clinical'],
            ['code' => 'DEN', 'name' => 'Dental', 'name_sw' => 'Menyu', 'type' => 'clinical'],
            ['code' => 'EYE', 'name' => 'Eye', 'name_sw' => 'Macho', 'type' => 'clinical'],
            ['code' => 'CTC', 'name' => 'CTC', 'name_sw' => 'Kituo cha UKIMWI', 'type' => 'clinical'],
            ['code' => 'TBC', 'name' => 'TB Clinic', 'name_sw' => 'Kliniki ya Kifua Kikuu', 'type' => 'clinical'],
            ['code' => 'ADM', 'name' => 'Administration', 'name_sw' => 'Utawala', 'type' => 'admin'],
        ];

        $departments = [];

        foreach ($definitions as $def) {
            $departments[$def['code']] = Department::updateOrCreate(
                ['code' => $def['code']],
                [
                    'name' => $def['name'],
                    'name_sw' => $def['name_sw'],
                    'type' => $def['type'],
                    'is_active' => true,
                ]
            );
        }

        return $departments;
    }

    /**
     * @param  array<string, Department>  $departments
     */
    private function seedWardsAndBeds(array $departments): void
    {
        $wards = [
            ['code' => 'MM', 'name' => 'Male Medical', 'gender' => 'male', 'department' => 'IMD', 'daily_rate' => 25000, 'beds' => 12],
            ['code' => 'FM', 'name' => 'Female Medical', 'gender' => 'female', 'department' => 'IMD', 'daily_rate' => 25000, 'beds' => 12],
            ['code' => 'PED-W', 'name' => 'Pediatric', 'gender' => 'pediatric', 'department' => 'PED', 'daily_rate' => 20000, 'beds' => 10],
            ['code' => 'MAT-W', 'name' => 'Maternity', 'gender' => 'female', 'department' => 'MAT', 'daily_rate' => 30000, 'beds' => 10],
            ['code' => 'SUR-W', 'name' => 'Surgical', 'gender' => 'mixed', 'department' => 'SUR', 'daily_rate' => 35000, 'beds' => 10],
        ];

        foreach ($wards as $wardDef) {
            $ward = Ward::updateOrCreate(
                ['code' => $wardDef['code']],
                [
                    'name' => $wardDef['name'],
                    'gender' => $wardDef['gender'],
                    'capacity' => $wardDef['beds'],
                    'department_id' => $departments[$wardDef['department']]->id,
                    'daily_rate' => $wardDef['daily_rate'],
                    'is_active' => true,
                ]
            );

            for ($i = 1; $i <= $wardDef['beds']; $i++) {
                Bed::updateOrCreate(
                    ['ward_id' => $ward->id, 'bed_number' => (string) $i],
                    ['status' => 'available']
                );
            }
        }
    }

    private function seedLabTests(): void
    {
        $tests = [
            ['code' => 'MAL-RDT', 'name' => 'Malaria RDT', 'category' => 'Parasitology', 'sample_type' => 'Blood', 'price' => 3000, 'unit' => '', 'normal_range' => 'Negative'],
            ['code' => 'HB', 'name' => 'Hemoglobin (HB)', 'category' => 'Hematology', 'sample_type' => 'Blood', 'price' => 5000, 'unit' => 'g/dL', 'normal_range' => '12-16'],
            ['code' => 'FBP', 'name' => 'Full Blood Picture (FBP)', 'category' => 'Hematology', 'sample_type' => 'Blood', 'price' => 15000, 'unit' => '', 'normal_range' => 'Normal'],
            ['code' => 'BS', 'name' => 'Blood Sugar (Random)', 'category' => 'Biochemistry', 'sample_type' => 'Blood', 'price' => 4000, 'unit' => 'mmol/L', 'normal_range' => '3.9-7.8'],
            ['code' => 'UA', 'name' => 'Urinalysis', 'category' => 'Clinical Chemistry', 'sample_type' => 'Urine', 'price' => 5000, 'unit' => '', 'normal_range' => 'Normal'],
            ['code' => 'STOOL', 'name' => 'Stool Microscopy', 'category' => 'Parasitology', 'sample_type' => 'Stool', 'price' => 5000, 'unit' => '', 'normal_range' => 'No ova/cysts'],
            ['code' => 'HIV', 'name' => 'HIV Rapid Test', 'category' => 'Serology', 'sample_type' => 'Blood', 'price' => 10000, 'unit' => '', 'normal_range' => 'Non-reactive'],
            ['code' => 'HEPB', 'name' => 'Hepatitis B (HBsAg)', 'category' => 'Serology', 'sample_type' => 'Blood', 'price' => 8000, 'unit' => '', 'normal_range' => 'Non-reactive'],
            ['code' => 'CREAT', 'name' => 'Creatinine', 'category' => 'Biochemistry', 'sample_type' => 'Blood', 'price' => 6000, 'unit' => 'mg/dL', 'normal_range' => '0.6-1.2'],
            ['code' => 'WIDAL', 'name' => 'Widal Test', 'category' => 'Serology', 'sample_type' => 'Blood', 'price' => 7000, 'unit' => '', 'normal_range' => 'Negative'],
        ];

        foreach ($tests as $test) {
            LabTest::updateOrCreate(['code' => $test['code']], [...$test, 'is_active' => true]);
        }
    }

    private function seedRadiologyServices(): void
    {
        $services = [
            ['code' => 'CXR', 'name' => 'Chest X-Ray', 'modality' => 'xray', 'price' => 25000],
            ['code' => 'ABD-US', 'name' => 'Abdominal Ultrasound', 'modality' => 'ultrasound', 'price' => 35000],
            ['code' => 'OBS-US', 'name' => 'Obstetric Ultrasound', 'modality' => 'ultrasound', 'price' => 40000],
        ];

        foreach ($services as $service) {
            RadiologyService::updateOrCreate(['code' => $service['code']], [...$service, 'is_active' => true]);
        }
    }

    private function seedMedicines(): void
    {
        $medicines = [
            ['code' => 'PCM-500', 'name' => 'Paracetamol', 'generic_name' => 'Paracetamol', 'category' => 'Analgesic', 'form' => 'Tablet', 'strength' => '500mg', 'unit_price' => 100, 'stock_qty' => 5000, 'reorder_level' => 500],
            ['code' => 'AMOX-500', 'name' => 'Amoxicillin', 'generic_name' => 'Amoxicillin', 'category' => 'Antibiotic', 'form' => 'Capsule', 'strength' => '500mg', 'unit_price' => 300, 'stock_qty' => 2000, 'reorder_level' => 200],
            ['code' => 'ALU', 'name' => 'Artemether-Lumefantrine (ALU)', 'generic_name' => 'Artemether/Lumefantrine', 'category' => 'Antimalarial', 'form' => 'Tablet', 'strength' => '20/120mg', 'unit_price' => 500, 'stock_qty' => 1500, 'reorder_level' => 150],
            ['code' => 'ORS', 'name' => 'ORS', 'generic_name' => 'Oral Rehydration Salts', 'category' => 'Rehydration', 'form' => 'Sachet', 'strength' => 'Standard', 'unit' => 'sachet', 'unit_price' => 200, 'stock_qty' => 800, 'reorder_level' => 100],
            ['code' => 'METRO-400', 'name' => 'Metronidazole', 'generic_name' => 'Metronidazole', 'category' => 'Antibiotic', 'form' => 'Tablet', 'strength' => '400mg', 'unit_price' => 150, 'stock_qty' => 1200, 'reorder_level' => 120],
            ['code' => 'IBU-400', 'name' => 'Ibuprofen', 'generic_name' => 'Ibuprofen', 'category' => 'NSAID', 'form' => 'Tablet', 'strength' => '400mg', 'unit_price' => 120, 'stock_qty' => 900, 'reorder_level' => 100],
            ['code' => 'CET-10', 'name' => 'Cetirizine', 'generic_name' => 'Cetirizine', 'category' => 'Antihistamine', 'form' => 'Tablet', 'strength' => '10mg', 'unit_price' => 100, 'stock_qty' => 600, 'reorder_level' => 80],
            ['code' => 'DIC-50', 'name' => 'Diclofenac', 'generic_name' => 'Diclofenac', 'category' => 'NSAID', 'form' => 'Tablet', 'strength' => '50mg', 'unit_price' => 80, 'stock_qty' => 750, 'reorder_level' => 75],
            ['code' => 'ZINC-20', 'name' => 'Zinc Sulphate', 'generic_name' => 'Zinc', 'category' => 'Supplement', 'form' => 'Tablet', 'strength' => '20mg', 'unit_price' => 50, 'stock_qty' => 400, 'reorder_level' => 50],
            ['code' => 'FE-SO4', 'name' => 'Ferrous Sulfate', 'generic_name' => 'Iron', 'category' => 'Supplement', 'form' => 'Tablet', 'strength' => '200mg', 'unit_price' => 60, 'stock_qty' => 350, 'reorder_level' => 40],
        ];

        foreach ($medicines as $medicine) {
            Medicine::updateOrCreate(
                ['code' => $medicine['code']],
                [
                    ...$medicine,
                    'unit' => $medicine['unit'] ?? 'tablet',
                    'is_active' => true,
                    'expiry_date' => now()->addYears(2)->toDateString(),
                    'batch_no' => 'BATCH-'.strtoupper(substr($medicine['code'], 0, 4)),
                ]
            );
        }
    }

    private function seedServiceCharges(): void
    {
        $charges = [
            ['code' => 'CONS-OPD', 'name' => 'Consultation OPD', 'category' => 'Consultation', 'price' => 5000],
            ['code' => 'REG', 'name' => 'Registration Fee', 'category' => 'Registration', 'price' => 2000],
            ['code' => 'INJ', 'name' => 'Injection', 'category' => 'Procedure', 'price' => 3000],
            ['code' => 'DRS', 'name' => 'Dressing', 'category' => 'Procedure', 'price' => 2500],
            ['code' => 'BED-MM', 'name' => 'Bed Day Fee - Male Medical', 'category' => 'Inpatient', 'price' => 25000],
            ['code' => 'BED-FM', 'name' => 'Bed Day Fee - Female Medical', 'category' => 'Inpatient', 'price' => 25000],
            ['code' => 'BED-PED', 'name' => 'Bed Day Fee - Pediatric', 'category' => 'Inpatient', 'price' => 20000],
            ['code' => 'BED-MAT', 'name' => 'Bed Day Fee - Maternity', 'category' => 'Inpatient', 'price' => 30000],
            ['code' => 'BED-SUR', 'name' => 'Bed Day Fee - Surgical', 'category' => 'Inpatient', 'price' => 35000],
        ];

        foreach ($charges as $charge) {
            ServiceCharge::updateOrCreate(['code' => $charge['code']], [...$charge, 'is_active' => true]);
        }
    }

    /**
     * @param  array<string, Department>  $departments
     */
    private function seedUsers(array $departments): void
    {
        // Only the hospital administrator is created at install.
        // Admin registers doctors, nurses, cashiers and other workers from Staff Users.
        User::updateOrCreate(
            ['email' => 'admin@savannah.health'],
            [
                'name' => 'System Administrator',
                'username' => 'admin',
                'phone' => '0755000000',
                'password' => Hash::make('Savannah@Admin1'),
                'role' => 'admin',
                'employee_no' => 'SHS-001',
                'specialty' => null,
                'department_id' => $departments['ADM']->id,
                'is_active' => true,
            ]
        );
    }
}
