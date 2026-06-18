<?php

namespace Modules\Loan\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Loan\Entities\LoanPayment;
use Modules\Loan\Entities\Loan;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Loan\Exports\PaymentsExport;

class PaymentsController extends Controller
{
    /**
     * Display a listing of payments with filters.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = LoanPayment::with('loan.customer', 'loan.user', 'loan.location');

        // Apply filters if present
        if ($request->filled('recipient_name')) {
            $query->whereHas('loan', function ($q) use ($request) {
                $q->whereHas('customer', function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->recipient_name . '%');
                })->orWhereHas('user', function ($q) use ($request) {
                    $q->whereRaw("CONCAT(COALESCE(surname, ''), ' ', COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) LIKE ?", ['%' . $request->recipient_name . '%']);
                });
            });
        }

        if ($request->filled('loan_id')) {
            $query->where('loan_id', $request->loan_id);
        }

        if ($request->filled('payment_date')) {
            $query->whereDate('payment_date', $request->payment_date);
        }

        if ($request->filled('total_amount_min') || $request->filled('total_amount_max')) {
            $query->whereHas('loan', function ($q) use ($request) {
                if ($request->filled('total_amount_min')) {
                    $q->where('total_amount', '>=', $request->total_amount_min);
                }
                if ($request->filled('total_amount_max')) {
                    $q->where('total_amount', '<=', $request->total_amount_max);
                }
            });
        }

        $payments = $query->paginate(10);

        return view('Loan::payments.index', compact('payments', 'request'));
    }

    /**
     * Export payments to Excel.
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function exportExcel()
    {
        return Excel::download(new PaymentsExport, 'payments.xlsx');
    }

    /**
     * Show payment details.
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $payment = LoanPayment::with('loan.customer', 'loan.user', 'loan.location', 'loan.payments')->findOrFail($id);

        return view('Loan::payments.show', compact('payment'));
    }
}