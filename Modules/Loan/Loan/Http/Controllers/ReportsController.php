<?php

namespace Modules\Loan\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Loan\Entities\Loan;
use Modules\Loan\Entities\LoanPayment;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Loan\Exports\LoansExport;

class ReportsController extends Controller
{
    /**
     * Display the reports page with enhanced statistics and filters.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $business_id = auth()->user()->business_id;

        // Total loans amount (principal)
        $total_loans = Loan::where('business_id', $business_id)->sum('amount');

        // Total payments received
        $total_payments = LoanPayment::whereHas('loan', function ($query) use ($business_id) {
            $query->where('business_id', $business_id);
        })->sum('amount');

        // Total interest earned (total_amount - amount)
        $total_interest = Loan::where('business_id', $business_id)
            ->selectRaw('SUM(total_amount - amount) as interest')
            ->value('interest');

        // Total outstanding balance (sum of remaining_balance)
        $total_outstanding = Loan::where('business_id', $business_id)
            ->selectRaw('SUM(total_amount - COALESCE((SELECT SUM(amount) FROM loan_payments WHERE loan_payments.loan_id = loans.id), 0)) as outstanding')
            ->value('outstanding');

        // Average loan amount and duration by loan type
        $avg_loan_stats = Loan::where('business_id', $business_id)
            ->selectRaw('loan_type, AVG(amount) as avg_amount, AVG(duration) as avg_duration')
            ->groupBy('loan_type')
            ->get();

        // Loans by status (for table and pie chart)
        $loans_by_status = Loan::where('business_id', $business_id)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get();

        // Loans by type (for table and bar chart)
        $loans_by_type = Loan::where('business_id', $business_id)
            ->selectRaw('loan_type, COUNT(*) as count')
            ->groupBy('loan_type')
            ->get();

        // Payments by month (for line chart)
        $payments_by_month = LoanPayment::whereHas('loan', function ($query) use ($business_id) {
            $query->where('business_id', $business_id);
        })
            ->selectRaw('DATE_FORMAT(payment_date, "%Y-%m") as month, SUM(amount) as total')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Detailed loans with filters
        $query = Loan::where('business_id', $business_id)
            ->with('customer', 'user', 'location', 'payments');

        if ($request->filled('start_date')) {
            $query->whereDate('start_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('start_date', '<=', $request->end_date);
        }

        if ($request->filled('loan_type')) {
            $query->where('loan_type', $request->loan_type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('location_id')) {
            $query->where('location_id', $request->location_id);
        }

        if ($request->filled('total_amount_min')) {
            $query->where('total_amount', '>=', $request->total_amount_min);
        }

        if ($request->filled('total_amount_max')) {
            $query->where('total_amount', '<=', $request->total_amount_max);
        }

        $detailed_loans = $query->paginate(10);

        // Data for charts
        $chart_data = [
            'status_labels' => $loans_by_status->pluck('status')->map(function ($status) {
                return __('Loan::lang.' . $status);
            })->toArray(),
            'status_counts' => $loans_by_status->pluck('count')->toArray(),
            'type_labels' => $loans_by_type->pluck('loan_type')->map(function ($type) {
                return __('Loan::lang.' . $type . '_loan');
            })->toArray(),
            'type_counts' => $loans_by_type->pluck('count')->toArray(),
            'payment_months' => $payments_by_month->pluck('month')->toArray(),
            'payment_totals' => $payments_by_month->pluck('total')->toArray(),
        ];

        // Get branches for filter dropdown
        $locations = \App\BusinessLocation::forDropdown($business_id);

        return view('Loan::reports.index', compact(
            'total_loans',
            'total_payments',
            'total_interest',
            'total_outstanding',
            'avg_loan_stats',
            'loans_by_status',
            'loans_by_type',
            'detailed_loans',
            'chart_data',
            'locations'
        ));
    }

    /**
     * Export detailed loans to Excel.
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function exportLoans(Request $request)
    {
        return Excel::download(new LoansExport($request), 'detailed_loans.xlsx');
    }
}