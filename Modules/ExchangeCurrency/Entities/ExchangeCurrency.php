<?php

namespace Modules\ExchangeCurrency\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ExchangeCurrency extends Model
{
    use HasFactory;

    
    protected static function newFactory()
    {
        return \Modules\ExchangeCurrency\Database\factories\ExchangeCurrencyFactory::new();
    }
    public const PERMISSION = "exchange_currency";
    public const COUNTRY = "country";
    public const CURRENCY = "currency";
    public const CODE = "code";
    public const SYMBOL = "symbol";
    public const EXCHANGE_RATE = "exchange_rate";
    public const TABLE = "exchange_currency";
    public const IS_USE = "is_use";
    public const BUSINESS_ID = 'business_id';
    protected $table = self::TABLE;
    protected $fillable = [
        self::COUNTRY,
        self::CURRENCY,
        self::CODE,
        self::SYMBOL,
        self::EXCHANGE_RATE,
        self::IS_USE
    ];
    public function setData($data){
        isset($data[self::COUNTRY]) && $this->{self::COUNTRY}= $data[self::COUNTRY]; 
        isset($data[self::CURRENCY]) && $this->{self::CURRENCY} = $data[self::CURRENCY]; 
        isset($data[self::CODE]) && $this->{self::CODE} = $data[self::CODE]; 
        isset($data[self::SYMBOL]) && $this->{self::SYMBOL} = $data[self::SYMBOL]; 
        isset($data[self::EXCHANGE_RATE]) && $this->{self::EXCHANGE_RATE} = $data[self::EXCHANGE_RATE]; 
        isset($data[self::IS_USE]) && $this->{self::IS_USE} = $data[self::IS_USE];
    }
}
