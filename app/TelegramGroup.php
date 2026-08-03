<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TelegramGroup extends Model
{
    protected $guarded = ['id'];

    public function bot()
    {
        return $this->belongsTo(TelegramBot::class, 'telegram_bot_id');
    }

    public function topics()
    {
        return $this->hasMany(TelegramTopic::class, 'telegram_group_id');
    }
}
