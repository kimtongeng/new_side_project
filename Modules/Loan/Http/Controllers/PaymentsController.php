<?php

namespace Modules\Loan\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Loan\Entities\LoanPayment;
use Modules\Loan\Entities\Loan;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Loan\Exports\PaymentsExport;
use App\Contact;
use App\User;
use App\BusinessLocation;

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

        if ($request->filled('location_id')) {
            $query->whereHas('loan', function ($q) use ($request) {
                $q->where('location_id', $request->location_id);
            });
        }

        if ($request->filled('payment_start_date')) {
            $query->whereDate('payment_date', '>=', $request->payment_start_date);
        }

        if ($request->filled('payment_end_date')) {
            $query->whereDate('payment_date', '<=', $request->payment_end_date);
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

        // Build combined recipients dropdown: customers + users (name => name for LIKE filter)
        $business_id = auth()->user()->business_id;

        $customer_recipients = Contact::where('business_id', $business_id)
            ->where('type', 'customer')
            ->orderBy('name')
            ->pluck('name', 'name')
            ->toArray();

        $user_recipients = User::where('business_id', $business_id)
            ->selectRaw("id, TRIM(CONCAT(COALESCE(surname,''), ' ', COALESCE(first_name,''), ' ', COALESCE(last_name,''))) as full_name")
            ->get()
            ->pluck('full_name', 'full_name')
            ->filter()
            ->toArray();

        $recipients = $customer_recipients + $user_recipients;

        $business_locations = BusinessLocation::forDropdown($business_id);

        return view('Loan::payments.index', compact('payments', 'request', 'recipients', 'business_locations'));
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