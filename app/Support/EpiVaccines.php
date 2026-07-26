<?php

declare(strict_types=1);

namespace App\Support;

class EpiVaccines
{
    /**
     * Tanzania EPI schedule (simplified facility checklist).
     *
     * @return array<int, array{code: string, name: string, dose: string, due_weeks: ?int}>
     */
    public static function schedule(): array
    {
        return [
            ['code' => 'BCG', 'name' => 'BCG', 'dose' => '1', 'due_weeks' => 0],
            ['code' => 'OPV0', 'name' => 'Oral Polio Vaccine', 'dose' => '0 (birth)', 'due_weeks' => 0],
            ['code' => 'OPV1', 'name' => 'Oral Polio Vaccine', 'dose' => '1', 'due_weeks' => 6],
            ['code' => 'PENTA1', 'name' => 'Pentavalent', 'dose' => '1', 'due_weeks' => 6],
            ['code' => 'PCV1', 'name' => 'Pneumococcal (PCV)', 'dose' => '1', 'due_weeks' => 6],
            ['code' => 'ROTA1', 'name' => 'Rotavirus', 'dose' => '1', 'due_weeks' => 6],
            ['code' => 'OPV2', 'name' => 'Oral Polio Vaccine', 'dose' => '2', 'due_weeks' => 10],
            ['code' => 'PENTA2', 'name' => 'Pentavalent', 'dose' => '2', 'due_weeks' => 10],
            ['code' => 'PCV2', 'name' => 'Pneumococcal (PCV)', 'dose' => '2', 'due_weeks' => 10],
            ['code' => 'ROTA2', 'name' => 'Rotavirus', 'dose' => '2', 'due_weeks' => 10],
            ['code' => 'OPV3', 'name' => 'Oral Polio Vaccine', 'dose' => '3', 'due_weeks' => 14],
            ['code' => 'PENTA3', 'name' => 'Pentavalent', 'dose' => '3', 'due_weeks' => 14],
            ['code' => 'PCV3', 'name' => 'Pneumococcal (PCV)', 'dose' => '3', 'due_weeks' => 14],
            ['code' => 'IPV', 'name' => 'Inactivated Polio (IPV)', 'dose' => '1', 'due_weeks' => 14],
            ['code' => 'MR1', 'name' => 'Measles-Rubella', 'dose' => '1', 'due_weeks' => 36],
            ['code' => 'MR2', 'name' => 'Measles-Rubella', 'dose' => '2', 'due_weeks' => 72],
            ['code' => 'VITA', 'name' => 'Vitamin A', 'dose' => 'supplement', 'due_weeks' => 24],
        ];
    }
}
