<?php

namespace Modules\Loan\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Loan\Entities\LoanSetting;

class SettingsController extends Controller
{
    /**
     * Display the settings page.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index()
    {
        $business_id = auth()->user()->business_id;

        // Fetch settings specific to the business
        $settings = LoanSetting::firstOrCreate(['business_id' => $business_id], [
            'interest_rate' => 0,
            'loan_limit' => 0,
            'max_loan_duration' => 12, // Default to 12 months
            'administrative_fee' => 0, // Default to 0
            'interest_type' => 'none', // Default to no interest
            'allow_early_payment' => true, // Default to allow early payment
        ]);

        return view('Loan::settings.index', compact('settings'));
    }

    /**
     * Update the settings for the loan module.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        $business_id = auth()->user()->business_id;

        // Validate request
        $request->validate([
            'interest_rate' => 'required|numeric',
            'loan_limit' => 'required|numeric',
            'max_loan_duration' => 'required|integer',
            'administrative_fee' => 'required|numeric',
            'interest_type' => 'required|string|in:none,simple,compound',
            'allow_early_payment' => 'required|boolean',
        ]);

        // Update settings
        LoanSetting::updateOrCreate(
            ['business_id' => $business_id],
            $request->only([
                'interest_rate',
                'loan_limit',
                'max_loan_duration',
                'administrative_fee',
                'interest_type',
                'allow_early_payment',
            ])
        );

        return redirect()->back()->with('success', __('Loan::lang.settings_updated'));
    }
}
