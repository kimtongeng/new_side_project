<?php

namespace Modules\Loan\Entities;

use Illuminate\Database\Eloquent\Model;

class Loan extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'loans';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'start_date' => 'date',
        'amount' => 'float',
        'total_amount' => 'float',
        'duration' => 'integer',
        'interest_rate' => 'float',
        'description' => 'string',
        'loan_type' => 'string',
        'status' => 'string',
    ];

    /**
     * Get the business associated with the loan.
     */
    public function business()
    {
        return $this->belongsTo(\App\Business::class);
    }

    /**
     * Get the customer associated with the loan.
     */
    public function customer()
    {
        return $this->belongsTo(\App\Contact::class, 'customer_id');
    }

    /**
     * Get the user associated with the loan.
     */
    public function user()
    {
        return $this->belongsTo(\App\User::class, 'user_id');
    }

    /**
     * Get the location associated with the loan.
     */
    public function location()
    {
        return $this->belongsTo(\App\BusinessLocation::class, 'location_id');
    }

    /**
     * Get the account associated with the loan.
     */
    public function account()
    {
        return $this->belongsTo(\App\Account::class, 'account_id');
    }

    /**
     * Get the payments associated with the loan.
     */
    public function payments()
    {
        return $this->hasMany(\Modules\Loan\Entities\LoanPayment::class, 'loan_id');
    }

    /**
     * Check if the loan is fully paid.
     *
     * @return bool
     */
    public function isFullyPaid()
    {
        // Ensure the payments relationship is loaded
        if (!$this->relationLoaded('payments')) {
            $this->load('payments');
        }

        // Calculate the total amount paid
        $totalPaid = $this->payments->sum('amount');

        // Check if the total paid is equal to or greater than the total amount
        return $totalPaid >= $this->total_amount;
    }

    /**
     * Get the total paid for the loan.
     *
     * @return float
     */
    public function getTotalPaidAttribute()
    {
        // Calculate the total paid for the loan
        return $this->payments()->sum('amount');
    }

    /**
     * Get the remaining balance for the loan.
     *
     * @return float
     */
    public function getRemainingBalanceAttribute()
    {
        // Calculate the remaining balance
        return max(0, $this->total_amount - $this->getTotalPaidAttribute());
    }

    /**
     * Get the recipient name (customer or user).
     *
     * @return string
     */
    public function getRecipientNameAttribute()
    {
        if ($this->user_id) {
            return $this->user->user_full_name ?? 'Unknown User';
        }
        return $this->customer->name ?? 'Unknown Customer';
    }
}