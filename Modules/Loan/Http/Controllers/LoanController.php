<?php

namespace Modules\Loan\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Loan\Entities\Loan;
use Modules\Loan\Entities\LoanPayment;
use App\BusinessLocation;

class LoanController extends Controller
{
    /**
     * Display dashboard for Loan module with enhanced statistics and filters.
     *
     * @return \Illuminate\View\View
     */
    public function dashboard(Request $request)
    {
        $business_id = auth()->user()->business_id;

        // Apply filters
        $query = Loan::where('business_id', $business_id)
                     ->with('customer', 'user', 'location', 'payments');

        if ($request->filled('start_date')) {
            $query->whereDate('start_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('start_date', '<=', $request->end_date);
        }

        if ($request->filled('location_id')) {
            $query->where('location_id', $request->location_id);
        }

        if ($request->filled('loan_type')) {
            $query->where('loan_type', $request->loan_type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Statistics
        $total_loans = $query->count();
        $total_payments = LoanPayment::whereHas('loan', function ($q) use ($business_id, $request) {
            $q->where('business_id', $business_id);
            if ($request->filled('start_date')) {
                $q->whereDate('start_date', '>=', $request->start_date);
            }
            if ($request->filled('end_date')) {
                $q->whereDate('start_date', '<=', $request->end_date);
            }
            if ($request->filled('location_id')) {
                $q->where('location_id', $request->location_id);
            }
            if ($request->filled('loan_type')) {
                $q->where('loan_type', $request->loan_type);
            }
            if ($request->filled('status')) {
                $q->where('status', $request->status);
            }
        })->sum('amount');

        $total_interest = $query->selectRaw('SUM(total_amount - amount) as interest')->value('interest');
        $total_outstanding = $query->selectRaw('SUM(total_amount - COALESCE((SELECT SUM(amount) FROM loan_payments WHERE loan_payments.loan_id = loans.id), 0)) as outstanding')->value('outstanding');
        $total_active_loans = $query->clone()->where('status', 'active')->count();
        $total_fully_paid_loans = $query->clone()->where('status', 'fully_paid')->count();
        $total_partially_paid_loans = $query->clone()->where('status', 'partially_paid')->count();

        // Loans by type
        $loans_by_type = $query->clone()
            ->selectRaw('loan_type, COUNT(*) as count')
            ->groupBy('loan_type')
            ->get();

        // Payments by month (for line chart)
        $payments_by_month = LoanPayment::whereHas('loan', function ($q) use ($business_id, $request) {
            $q->where('business_id', $business_id);
            if ($request->filled('start_date')) {
                $q->whereDate('start_date', '>=', $request->start_date);
            }
            if ($request->filled('end_date')) {
                $q->whereDate('start_date', '<=', $request->end_date);
            }
            if ($request->filled('location_id')) {
                $q->where('location_id', $request->location_id);
            }
            if ($request->filled('loan_type')) {
                $q->where('loan_type', $request->loan_type);
            }
        })
            ->selectRaw('DATE_FORMAT(payment_date, "%Y-%m") as month, SUM(amount) as total')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Chart data
        $chart_data = [
            'status_labels' => ['Active', 'Partially Paid', 'Fully Paid'],
            'status_counts' => [$total_active_loans, $total_partially_paid_loans, $total_fully_paid_loans],
            'type_labels' => $loans_by_type->pluck('loan_type')->map(function ($type) {
                return __('Loan::lang.' . $type . '_loan');
            })->toArray(),
            'type_counts' => $loans_by_type->pluck('count')->toArray(),
            'payment_months' => $payments_by_month->pluck('month')->toArray(),
            'payment_totals' => $payments_by_month->pluck('total')->toArray(),
        ];

        // Branches for filter dropdown
        $locations = BusinessLocation::forDropdown($business_id);

        return view('Loan::Loan.dashboard', compact(
            'total_loans',
            'total_payments',
            'total_interest',
            'total_outstanding',
            'total_active_loans',
            'total_fully_paid_loans',
            'total_partially_paid_loans',
            'loans_by_type',
            'chart_data',
            'locations'
        ));
    }

    /**
     * Display the list of clients.
     * @return Renderable
     */
    public function clients()
    {
        return view('Loan::clients.index');
    }

    /**
     * Display the list of loans.
     * @return Renderable
     */
    public function loans()
    {
        return view('Loan::loans.index');
    }

    /**
     * Display the list of payments.
     * @return Renderable
     */
    public function payments()
    {
        return view('Loan::payments.index');
    }

    /**
     * Display the reports section.
     * @return Renderable
     */
    public function reports()
    {
        return view('Loan::reports.index');
    }

    /**
     * Display the settings page.
     * @return Renderable
     */
    public function settings()
    {
        return view('Loan::settings.index');
    }

    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        return view('Loan::index');
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('Loan::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return view('Loan::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return view('Loan::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        //
    }
}