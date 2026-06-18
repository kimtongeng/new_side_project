<?php

namespace Modules\Loan\Entities;

use Illuminate\Database\Eloquent\Model;

class LoanSetting extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'loan_settings';

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
        'interest_rate' => 'float', // نسبة الفائدة
        'loan_limit' => 'float', // الحد الأقصى للقرض
        'max_loan_duration' => 'integer', // مدة القرض القصوى
        'administrative_fee' => 'float', // الرسوم الإدارية
        'interest_type' => 'string', // نوع الفائدة
        'allow_early_payment' => 'boolean', // السماح بالدفع المسبق
    ];
}
