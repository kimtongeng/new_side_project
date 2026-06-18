<?php

namespace Modules\Loan\Entities;

use Illuminate\Database\Eloquent\Model;

class LoanPayment extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'loan_payments';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['loan_id', 'account_id', 'payment_date', 'amount'];

    /**
     * Relationship with the Loan model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function loan()
    {
        return $this->belongsTo(Loan::class, 'loan_id', 'id');
    }

    /**
     * Relationship with the Account model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function account()
    {
        return $this->belongsTo(\App\Account::class, 'account_id', 'id');
    }

    /**
     * Scope to filter payments by date range.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|null $startDate
     * @param string|null $endDate
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFilterByDate($query, $startDate = null, $endDate = null)
    {
        if ($startDate) {
            $query->where('payment_date', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('payment_date', '<=', $endDate);
        }

        return $query;
    }

    /**
     * Scope to get payments for a specific loan.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $loanId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForLoan($query, $loanId)
    {
        return $query->where('loan_id', $loanId);
    }

    /**
     * Scope to filter payments by total amount range of the associated loan.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param float|null $minAmount
     * @param float|null $maxAmount
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFilterByTotalAmount($query, $minAmount = null, $maxAmount = null)
    {
        return $query->whereHas('loan', function ($q) use ($minAmount, $maxAmount) {
            if ($minAmount !== null) {
                $q->where('total_amount', '>=', $minAmount);
            }
            if ($maxAmount !== null) {
                $q->where('total_amount', '<=', $maxAmount);
            }
        });
    }
}