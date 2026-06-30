<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PurchaseLineReceipt extends Model
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    public function transaction()
    {
        return $this->belongsTo(\App\Transaction::class, 'transaction_id');
    }

    public function purchase_line()
    {
        return $this->belongsTo(\App\PurchaseLine::class, 'purchase_line_id');
    }
}
