<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Services\NhifService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NhifController extends Controller
{
    public function verify(Request $request, Patient $patient, NhifService $nhif): RedirectResponse
    {
        $validated = $request->validate([
            'nhif_card_no' => ['nullable', 'string', 'max:40'],
        ]);

        $result = $nhif->verify($patient, $validated['nhif_card_no'] ?? null);

        if ($result['success']) {
            return back()->with('success', $result['message'].' Auth: '.($result['data']['authorization_code'] ?? ''));
        }

        return back()->withErrors(['nhif' => $result['message']]);
    }
}
