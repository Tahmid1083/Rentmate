<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index(Request $request)
    {
        $setting = Setting::for($request->user());

        $currencies = [
            'USD' => 'USD ($)', 'EUR' => 'EUR (€)', 'GBP' => 'GBP (£)',
            'CAD' => 'CAD ($)', 'AUD' => 'AUD ($)', 'INR' => 'INR (₹)',
            'BDT' => 'BDT (৳)', 'PKR' => 'PKR (₨)', 'NGN' => 'NGN (₦)',
            'AED' => 'AED (د.إ)', 'ZAR' => 'ZAR (R)',
        ];

        return view('settings.index', compact('setting', 'currencies'));
    }

    public function update(Request $request)
    {
        $setting = Setting::for($request->user());

        $data = $request->validate([
            'business_name'             => ['nullable', 'string', 'max:160'],
            'business_email'            => ['nullable', 'email', 'max:255'],
            'phone'                     => ['nullable', 'string', 'max:40'],
            'currency'                  => ['required', 'string', 'max:8'],
            'timezone'                  => ['required', 'string'],
            'locale'                    => ['required', 'string', 'max:10'],
            'late_fee_grace_days'       => ['required', 'integer', 'min:0', 'max:60'],
            'late_fee_flat'             => ['required', 'numeric', 'min:0'],
            'late_fee_percent'          => ['required', 'numeric', 'min:0', 'max:100'],
            'notify_invoice_created'    => ['nullable', 'boolean'],
            'notify_payment_received'   => ['nullable', 'boolean'],
            'notify_maintenance_status' => ['nullable', 'boolean'],
            'notify_lease_expiring'     => ['nullable', 'boolean'],
            'notify_overdue_reminder'   => ['nullable', 'boolean'],
        ]);

        // Convert checkbox absent → false
        foreach (['notify_invoice_created', 'notify_payment_received',
                  'notify_maintenance_status', 'notify_lease_expiring',
                  'notify_overdue_reminder'] as $k) {
            $data[$k] = isset($data[$k]) ? (bool) $data[$k] : false;
        }

        $setting->update($data);

        return redirect()->route('settings.index')
            ->with('success', 'Settings saved.');
    }
}
