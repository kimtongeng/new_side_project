<?php

namespace Modules\Loan\Exports;

use Modules\Loan\Entities\Loan;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class LoansExport implements FromCollection, WithHeadings
{
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    /**
     * Fetch filtered loans data.
     *
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $business_id = auth()->user()->business_id;
        $query = Loan::where('business_id', $business_id)
            ->with('customer', 'user', 'location', 'payments');

        if ($this->request->filled('start_date')) {
            $query->whereDate('start_date', '>=', $this->request->start_date);
        }

        if ($this->request->filled('end_date')) {
            $query->whereDate('start_date', '<=', $this->request->end_date);
        }

        if ($this->request->filled('loan_type')) {
            $query->where('loan_type', $this->request->loan_type);
        }

        if ($this->request->filled('status')) {
            $query->where('status', $this->request->status);
        }

        if ($this->request->filled('location_id')) {
            $query->where('location_id', $this->request->location_id);
        }

        if ($this->request->filled('total_amount_min')) {
            $query->where('total_amount', '>=', $this->request->total_amount_min);
        }

        if ($this->request->filled('total_amount_max')) {
            $query->where('total_amount', '<=', $this->request->total_amount_max);
        }

        return $query->get()->map(function ($loan) {
            return [
                'recipient_name' => $loan->recipient_name,
                'start_date' => $loan->start_date,
                'amount' => number_format($loan->amount, 2),
                'total_amount' => number_format($loan->total_amount, 2),
                'total_paid' => number_format($loan->total_paid, 2),
                'remaining_balance' => number_format($loan->remaining_balance, 2),
                'duration' => $loan->duration . ' ' . __('Loan::lang.months'),
                'interest_rate' => $loan->interest_rate . '%',
                'loan_type' => __('Loan::lang.' . $loan->loan_type . '_loan'),
                'status' => __('Loan::lang.' . $loan->status),
                'branch' => $loan->location->name,
            ];
        });
    }

    /**
     * Define headings for the export file.
     *
     * @return array
     */
    public function headings(): array
    {
        return [
            'Recipient',
            'Start Date',
            'Amount',
            'Total Amount',
            'Total Paid',
            'Remaining Balance',
            'Duration',
            'Interest Rate',
            'Loan Type',
            'Status',
            'Branch',
        ];
    }
}