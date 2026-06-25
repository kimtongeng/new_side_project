<?php

namespace Modules\StockCount\Entities;

use Illuminate\Database\Eloquent\Model;

class StockCountLine extends Model
{
    protected $table = 'stock_count_lines';

    protected $guarded = ['id'];

    protected $casts = [
        'book_quantity' => 'decimal:4',
        'counted_quantity' => 'decimal:4',
        'unit_price' => 'decimal:4',
        'counted_at' => 'datetime',
    ];

    public function session()
    {
        return $this->belongsTo(StockCountSession::class, 'stock_count_session_id');
    }

    public function product()
    {
        return $this->belongsTo(\App\Product::class, 'product_id');
    }

    public function variation()
    {
        return $this->belongsTo(\App\Variation::class, 'variation_id');
    }

    public function counter()
    {
        return $this->belongsTo(\App\User::class, 'counted_by');
    }
}
