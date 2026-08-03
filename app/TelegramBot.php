<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TelegramBot extends Model
{
    protected $guarded = ['id'];

    public function groups()
    {
        return $this->hasMany(TelegramGroup::class, 'telegram_bot_id');
    }
}
