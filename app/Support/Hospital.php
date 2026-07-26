<?php

namespace App\Support;

use App\Models\FacilitySetting;
use App\Models\Patient;
use App\Models\Visit;
use Illuminate\Support\Carbon;

class Hospital
{
    public static function facilityName(): string
    {
        return FacilitySetting::getValue('facility_name', 'Savannah Health System');
    }

    public static function facilityCode(): string
    {
        return FacilitySetting::getValue('facility_code', 'SHS');
    }

    public static function currency(): string
    {
        return FacilitySetting::getValue('currency', 'TZS');
    }

    public static function money(float|int|string|null $amount): string
    {
        return self::currency().' '.number_format((float) $amount, 0, '.', ',');
    }

    public static function nextMrn(): string
    {
        $year = now()->format('Y');
        $prefix = self::facilityCode().'-'.$year.'-';
        $last = Patient::withTrashed()
            ->where('mrn', 'like', $prefix.'%')
            ->orderByDesc('id')
            ->value('mrn');

        $seq = 1;
        if ($last && preg_match('/(\d+)$/', $last, $m)) {
            $seq = ((int) $m[1]) + 1;
        }

        return $prefix.str_pad((string) $seq, 5, '0', STR_PAD_LEFT);
    }

    public static function nextVisitNo(): string
    {
        $prefix = 'V'.now()->format('ymd');
        $count = Visit::whereDate('created_at', Carbon::today())->count() + 1;

        return $prefix.'-'.str_pad((string) $count, 4, '0', STR_PAD_LEFT);
    }

    public static function nextNumber(string $prefix): string
    {
        return $prefix.now()->format('ymdHis').random_int(10, 99);
    }

    public static function paymentCategories(): array
    {
        return [
            'cash' => 'Cash (Fedha taslimu)',
            'nhif' => 'NHIF',
            'exemption' => 'Exemption (Msamaha)',
            'corporate' => 'Corporate / Company',
            'insurance' => 'Other Insurance',
        ];
    }

    public static function exemptionTypes(): array
    {
        return [
            'under_five' => 'Under 5 years',
            'pregnant' => 'Pregnant woman',
            'elderly' => 'Elderly (60+)',
            'disability' => 'Person with disability',
            'staff' => 'Hospital staff',
            'other' => 'Other exemption',
        ];
    }

    public static function tanzaniaRegions(): array
    {
        return [
            'Arusha', 'Dar es Salaam', 'Dodoma', 'Geita', 'Iringa', 'Kagera', 'Katavi',
            'Kigoma', 'Kilimanjaro', 'Lindi', 'Manyara', 'Mara', 'Mbeya', 'Morogoro',
            'Mtwara', 'Mwanza', 'Njombe', 'Pwani', 'Rukwa', 'Ruvuma', 'Shinyanga',
            'Simiyu', 'Singida', 'Songwe', 'Tabora', 'Tanga', 'Zanzibar North',
            'Zanzibar South', 'Zanzibar Urban/West', 'Pemba North', 'Pemba South',
        ];
    }

    public static function roles(): array
    {
        return [
            'admin' => 'System Administrator',
            'receptionist' => 'Reception / Registration',
            'doctor' => 'Medical Doctor',
            'nurse' => 'Nurse',
            'lab_technician' => 'Laboratory Technician',
            'pharmacist' => 'Pharmacist',
            'cashier' => 'Cashier / Billing',
            'radiologist' => 'Radiologist',
            'records' => 'Medical Records',
        ];
    }
}
