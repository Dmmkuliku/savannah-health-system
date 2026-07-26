<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\BloodUnit;
use App\Models\TheatreRoom;
use App\Models\User;
use Illuminate\Database\Seeder;

class TheatreBloodSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedTheatreRooms();
        $this->seedBloodUnits();
    }

    private function seedTheatreRooms(): void
    {
        $rooms = [
            ['code' => 'OT-1', 'name' => 'Operating Theatre 1', 'name_sw' => 'Chumba cha Upasuaji 1'],
            ['code' => 'OT-2', 'name' => 'Operating Theatre 2', 'name_sw' => 'Chumba cha Upasuaji 2'],
            ['code' => 'MINOR-OT', 'name' => 'Minor OT', 'name_sw' => 'Upasuaji Mdogo'],
        ];

        foreach ($rooms as $room) {
            TheatreRoom::updateOrCreate(
                ['code' => $room['code']],
                [
                    'name' => $room['name'],
                    'name_sw' => $room['name_sw'],
                    'status' => 'available',
                    'is_active' => true,
                ]
            );
        }
    }

    private function seedBloodUnits(): void
    {
        $receivedBy = User::where('role', 'admin')->value('id')
            ?? User::query()->value('id');

        $units = [
            ['unit_no' => 'BU-SEED-001', 'blood_group' => 'O+', 'component' => 'whole_blood'],
            ['unit_no' => 'BU-SEED-002', 'blood_group' => 'O+', 'component' => 'packed_rbc'],
            ['unit_no' => 'BU-SEED-003', 'blood_group' => 'A+', 'component' => 'whole_blood'],
            ['unit_no' => 'BU-SEED-004', 'blood_group' => 'A+', 'component' => 'packed_rbc'],
            ['unit_no' => 'BU-SEED-005', 'blood_group' => 'B+', 'component' => 'whole_blood'],
            ['unit_no' => 'BU-SEED-006', 'blood_group' => 'AB-', 'component' => 'packed_rbc'],
        ];

        foreach ($units as $unit) {
            BloodUnit::updateOrCreate(
                ['unit_no' => $unit['unit_no']],
                [
                    'blood_group' => $unit['blood_group'],
                    'component' => $unit['component'],
                    'volume_ml' => 450,
                    'collected_at' => now()->subDays(7)->toDateString(),
                    'expiry_date' => now()->addDays(28)->toDateString(),
                    'status' => 'available',
                    'storage_location' => 'Fridge A',
                    'received_by' => $receivedBy,
                ]
            );
        }
    }
}
