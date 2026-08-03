<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TelegramTopic extends Model
{
    protected $guarded = ['id'];

    public function group()
    {
        return $this->belongsTo(TelegramGroup::class, 'telegram_group_id');
    }
}
