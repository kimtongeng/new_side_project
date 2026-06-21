<?php

namespace Modules\Loan\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Loan\Entities\Loan;
use Modules\Loan\Entities\LoanPayment;
use App\Contact;
use App\BusinessLocation;
use App\Account;
use App\AccountTransaction;
use App\User;

class LoanManagementController extends Controller
{
    /**
     * Display a listing of loans with filters.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $business_id = auth()->user()->business_id;

        $query = Loan::where('business_id', $business_id)
            ->with(['customer', 'user', 'location']);

        if ($request->filled('recipient_name')) {
            $query->where(function ($q) use ($request) {
                $q->whereHas('customer', function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->recipient_name . '%');
                })->orWhereHas('user', function ($q) use ($request) {
                    $q->whereRaw("CONCAT(COALESCE(surname, ''), ' ', COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) LIKE ?", ['%' . $request->recipient_name . '%']);
                });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('loan_type')) {
            $query->where('loan_type', $request->loan_type);
        }

        $loans = $query->paginate(10);

        // Build combined recipients dropdown: customers + users (name => name for LIKE filter)
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

        return view('Loan::loans.index', compact('loans', 'recipients'));
    }

    /**
     * Show the form for creating a new loan.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $business_id = auth()->user()->business_id;

        $customers = Contact::customersDropdown($business_id);
        $users = User::forDropdown($business_id, false);
        $locations = BusinessLocation::forDropdown($business_id);
        $accounts = Account::forDropdown($business_id, false, false, true);
        $loan_types = [
            'personal' => __('Loan::lang.personal_loan'),
            'business' => __('Loan::lang.business_loan'),
        ];
        $loan_statuses = [
            'active' => __('Loan::lang.active'),
            'partially_paid' => __('Loan::lang.partially_paid'),
            'fully_paid' => __('Loan::lang.fully_paid'),
        ];

        return view('Loan::loans.create', compact('customers', 'users', 'locations', 'accounts', 'loan_types', 'loan_statuses'));
    }

    /**
     * Show the form for editing a loan.
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $business_id = auth()->user()->business_id;

        $loan = Loan::where('business_id', $business_id)->findOrFail($id);

        $customers = Contact::customersDropdown($business_id);
        $users = User::forDropdown($business_id, false);
        $locations = BusinessLocation::forDropdown($business_id);
        $accounts = Account::forDropdown($business_id, false, false, true);
        $loan_types = [
            'personal' => __('Loan::lang.personal_loan'),
            'business' => __('Loan::lang.business_loan'),
        ];
        $loan_statuses = [
            'active' => __('Loan::lang.active'),
            'partially_paid' => __('Loan::lang.partially_paid'),
            'fully_paid' => __('Loan::lang.fully_paid'),
        ];

        return view('Loan::loans.edit', compact('loan', 'customers', 'users', 'locations', 'accounts', 'loan_types', 'loan_statuses'));
    }

    /**
     * Store a newly created loan in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        \Log::info('Start storing loan', $request->all());

        $business_id = auth()->user()->business_id;

        $request->validate([
            'start_date' => 'required|date',
            'recipient_type' => 'required|in:customer,user',
            'customer_id' => 'required_if:recipient_type,customer|nullable|exists:contacts,id',
            'user_id' => 'required_if:recipient_type,user|nullable|exists:users,id',
            'location_id' => 'required|exists:business_locations,id',
            'amount' => 'required|numeric|min:0',
            'duration' => 'required|integer|min:1',
            'interest_rate' => 'required|numeric|min:0',
            'loan_type' => 'required|string|in:personal,business',
            'account_id' => 'required|exists:accounts,id',
            'description' => 'nullable|string',
            'status' => 'required|string|in:active,partially_paid,fully_paid',
        ]);

        \Log::info('Validation passed.');

        $account = Account::findOrFail($request->account_id);

        $total_balance = AccountTransaction::where('account_id', $request->account_id)
            ->selectRaw("SUM(CASE WHEN type = 'credit' THEN amount ELSE -amount END) as balance")
            ->value('balance') ?? 0;

        \Log::info('Retrieved account balance', ['account_id' => $account->id, 'balance' => $total_balance]);

        // Calculate total amount (principal + simple interest)
        $amount = $request->amount;
        $interest_rate = $request->interest_rate;
        $total_amount = $amount + ($amount * ($interest_rate / 100));

        if ($total_balance < $amount) {
            \Log::error('Insufficient balance in account', ['account_id' => $request->account_id, 'balance' => $total_balance]);
            return redirect()->back()->with('error', __('Loan::lang.insufficient_balance'));
        }

        \Log::info('Sufficient balance available.', ['account_balance' => $total_balance]);

        try {
            AccountTransaction::createAccountTransaction([
                'amount' => $amount,
                'account_id' => $request->account_id,
                'type' => 'debit',
                'operation_date' => $request->start_date,
                'created_by' => auth()->id(),
                'note' => __('Loan::lang.loan_disbursed'),
            ]);
            \Log::info('Account transaction created successfully.');
        } catch (\Exception $e) {
            \Log::error('Failed to create account transaction', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', __('Loan::lang.transaction_failed'));
        }

        try {
            Loan::create([
                'business_id' => $business_id,
                'customer_id' => $request->recipient_type == 'customer' ? $request->customer_id : null,
                'user_id' => $request->recipient_type == 'user' ? $request->user_id : null,
                'location_id' => $request->location_id,
                'account_id' => $request->account_id,
                'start_date' => $request->start_date,
                'amount' => $amount,
                'total_amount' => $total_amount,
                'duration' => $request->duration,
                'interest_rate' => $interest_rate,
                'loan_type' => $request->loan_type,
                'description' => $request->description,
                'status' => $request->status,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            \Log::info('Loan created successfully.', [
                'business_id' => $business_id,
                'customer_id' => $request->customer_id,
                'user_id' => $request->user_id,
                'account_id' => $request->account_id,
                'total_amount' => $total_amount,
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to create loan', [
                'error' => $e->getMessage(),
                'account_id' => $request->account_id,
                'customer_id' => $request->customer_id,
                'user_id' => $request->user_id,
            ]);

            return redirect()->back()->with('error', __('Loan::lang.loan_creation_failed'));
        }

        \Log::info('Loan storing process completed successfully.');

        return redirect()->route('Loan.loans.index')->with('success', __('Loan::lang.loan_created_successfully'));
    }

    /**
     * Show details of a loan.
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $loan = Loan::with('customer', 'user', 'location', 'payments')->findOrFail($id);

        $business_id = auth()->user()->business_id;
        $accounts = Account::forDropdown($business_id, false, false, true);

        return view('Loan::loans.show', compact('loan', 'accounts'));
    }

    /**
     * Store a payment for a loan.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $loanId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storePayment(Request $request, $loanId)
    {
        \Log::info('Start storing payment', [
            'loan_id' => $loanId,
            'request_data' => $request->all(),
        ]);

        try {
            $loan = Loan::with('payments')->findOrFail($loanId);
            \Log::info('Loan found', ['loan_id' => $loan->id]);
        } catch (\Exception $e) {
            \Log::error('Loan not found', ['loan_id' => $loanId, 'error' => $e->getMessage()]);
            return redirect()->back()->with('error', __('Loan::lang.loan_not_found'));
        }

        if ($loan->isFullyPaid()) {
            \Log::info('Loan is fully paid', ['loan_id' => $loan->id]);
            return redirect()->route('Loan.loans.show', $loan->id)->with('error', __('Loan::lang.loan_already_fully_paid'));
        }

        $validatedData = $request->validate([
            'payment_date' => 'required|date',
            'amount' => 'required|numeric|min:1',
            'account_id' => 'required|exists:accounts,id',
        ]);

        \Log::info('Request validated successfully', ['validated_data' => $validatedData]);

        try {
            $account = Account::findOrFail($request->account_id);
            \Log::info('Account found', ['account_id' => $account->id]);
        } catch (\Exception $e) {
            \Log::error('Account not found', ['account_id' => $request->account_id, 'error' => $e->getMessage()]);
            return redirect()->back()->with('error', __('Loan::lang.account_not_found'));
        }

        $total_balance = AccountTransaction::where('account_id', $request->account_id)
            ->selectRaw("SUM(CASE WHEN type = 'credit' THEN amount ELSE -amount END) as balance")
            ->value('balance') ?? 0;

        \Log::info('Retrieved account balance', ['account_id' => $account->id, 'balance' => $total_balance]);

        try {
            $transaction = AccountTransaction::createAccountTransaction([
                'amount' => $request->amount,
                'account_id' => $request->account_id,
                'type' => 'credit',
                'operation_date' => $request->payment_date,
                'created_by' => auth()->id(),
                'note' => __('Loan::lang.payment_received'),
            ]);
            \Log::info('Account transaction created successfully', ['transaction_id' => $transaction->id]);
        } catch (\Exception $e) {
            \Log::error('Failed to create account transaction', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', __('Loan::lang.transaction_failed'));
        }

        try {
            $payment = $loan->payments()->create([
                'payment_date' => $request->payment_date,
                'amount' => $request->amount,
                'account_id' => $request->account_id,
            ]);

            if (!$payment) {
                throw new \Exception('Failed to create payment record in loan_payments table.');
            }

            \Log::info('Loan payment created successfully', [
                'payment_id' => $payment->id,
                'loan_id' => $loan->id,
                'amount' => $payment->amount,
            ]);

            // Update loan status based on total payments
            $total_paid = $loan->payments()->sum('amount');
            if ($total_paid >= $loan->total_amount) {
                $loan->update(['status' => 'fully_paid']);
            } elseif ($total_paid > 0) {
                $loan->update(['status' => 'partially_paid']);
            }
        } catch (\Exception $e) {
            \Log::error('Failed to store payment in loan_payments', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', __('Loan::lang.payment_failed'));
        }

        return redirect()->route('Loan.loans.show', $loan->id)->with('success', __('Loan::lang.payment_added_successfully'));
    }

    /**
     * Update a loan in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $business_id = auth()->user()->business_id;

        $request->validate([
            'start_date' => 'required|date',
            'recipient_type' => 'required|in:customer,user',
            'customer_id' => 'required_if:recipient_type,customer|nullable|exists:contacts,id',
            'user_id' => 'required_if:recipient_type,user|nullable|exists:users,id',
            'location_id' => 'required|exists:business_locations,id',
            'amount' => 'required|numeric|min:0',
            'duration' => 'required|integer|min:1',
            'interest_rate' => 'required|numeric|min:0',
            'loan_type' => 'required|string|in:personal,business',
            'description' => 'nullable|string',
            'status' => 'required|string|in:active,partially_paid,fully_paid',
        ]);

        $loan = Loan::where('business_id', $business_id)->findOrFail($id);

        // Calculate total amount (principal + simple interest)
        $amount = $request->amount;
        $interest_rate = $request->interest_rate;
        $total_amount = $amount + ($amount * ($interest_rate / 100));

        $loan->update([
            'start_date' => $request->start_date,
            'customer_id' => $request->recipient_type == 'customer' ? $request->customer_id : null,
            'user_id' => $request->recipient_type == 'user' ? $request->user_id : null,
            'location_id' => $request->location_id,
            'amount' => $amount,
            'total_amount' => $total_amount,
            'duration' => $request->duration,
            'interest_rate' => $request->interest_rate,
            'loan_type' => $request->loan_type,
            'description' => $request->description,
            'status' => $request->status,
        ]);

        return redirect()->route('Loan.loans.index')->with('success', __('Loan::lang.loan_updated_successfully'));
    }

    /**
     * Delete a loan from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $business_id = auth()->user()->business_id;

        $loan = Loan::where('business_id', $business_id)->findOrFail($id);
        $loan->delete();

        return redirect()->route('Loan.loans.index')->with('success', __('Loan::lang.loan_deleted_successfully'));
    }
}
