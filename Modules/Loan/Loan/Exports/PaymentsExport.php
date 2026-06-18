<?php

namespace Modules\Loan\Exports;

use Modules\Loan\Entities\LoanPayment;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PaymentsExport implements FromCollection, WithHeadings
{
    /**
     * Fetch all payments data.
     *
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return LoanPayment::select('payment_date', 'amount', 'loan_id')
            ->with('loan.customer', 'loan.user', 'loan.payments')
            ->get()
            ->map(function ($payment) {
                return [
                    'payment_date' => $payment->payment_date,
                    'amount' => number_format($payment->amount, 2),
                    'recipient_name' => $payment->loan->recipient_name,
                    'loan_id' => $payment->loan->id,
                    'total_amount' => number_format($payment->loan->total_amount, 2),
                    'total_paid' => number_format($payment->loan->total_paid, 2),
                    'remaining_balance' => number_format($payment->loan->remaining_balance, 2),
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
            'Payment Date',
            'Amount',
            'Recipient Name',
            'Loan ID',
            'Total Amount',
            'Total Paid',
            'Remaining Balance',
        ];
    }
}