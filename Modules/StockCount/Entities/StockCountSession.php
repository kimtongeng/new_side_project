<?php

namespace Modules\StockCount\Entities;

use Illuminate\Database\Eloquent\Model;

class StockCountSession extends Model
{
    protected $table = 'stock_count_sessions';

    protected $guarded = ['id'];

    protected $casts = [
        'blind_count' => 'boolean',
        'filters' => 'array',
        'completed_at' => 'datetime',
    ];

    public function location()
    {
        return $this->belongsTo(\App\BusinessLocation::class, 'location_id');
    }

    public function creator()
    {
        return $this->belongsTo(\App\User::class, 'created_by');
    }

    public function completer()
    {
        return $this->belongsTo(\App\User::class, 'completed_by');
    }

    public function lines()
    {
        return $this->hasMany(StockCountLine::class, 'stock_count_session_id');
    }
}
