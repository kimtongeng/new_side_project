<?php

namespace App;

use App\Utils\Util;
use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Account extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'account_details' => 'array',
        'location_id' => 'array',
    ];

    public static function forDropdown($business_id, $prepend_none, $closed = false, $show_balance = false, $location_id = null, $include_account_ids = [])
    {
        $query = Account::where('business_id', $business_id);

        if (! empty($location_id)) {
            $location_account_ids = [];
            if ($location_id !== 'all_locations_only') {
                $loc = BusinessLocation::find($location_id);
                if ($loc && ! empty($loc->default_payment_accounts)) {
                    $default_payment_accounts = json_decode($loc->default_payment_accounts, true);
                    if (is_array($default_payment_accounts)) {
                        foreach ($default_payment_accounts as $acc_setting) {
                            if (! empty($acc_setting['is_enabled']) && ! empty($acc_setting['account'])) {
                                $location_account_ids[] = $acc_setting['account'];
                            }
                        }
                    }
                }
            }

            $merged_include_ids = array_unique(array_merge((array)$include_account_ids, $location_account_ids));

            if ($location_id === 'all_locations_only') {
                $query->where(function ($q) use ($merged_include_ids) {
                    $q->whereNull('accounts.location_id');
                    if (! empty($merged_include_ids)) {
                        $q->orWhereIn('accounts.id', $merged_include_ids);
                    }
                });
            } else {
                $query->where(function ($q) use ($location_id, $merged_include_ids) {
                    $q->whereNull('accounts.location_id')
                      ->orWhereIn('accounts.location_id', (array)$location_id);
                    foreach ((array)$location_id as $loc_id) {
                        $q->orWhereJsonContains('accounts.location_id', (string)$loc_id)
                          ->orWhereJsonContains('accounts.location_id', (int)$loc_id);
                    }
                    if (! empty($merged_include_ids)) {
                        $q->orWhereIn('accounts.id', $merged_include_ids);
                    }
                });
            }
        }

        $user = auth()->user();
        $permitted_locations = $user ? $user->permitted_locations() : 'all';
        $account_ids = [];
        if ($user && $permitted_locations != 'all') {
            $locations = BusinessLocation::where('business_id', $business_id)
                            ->whereIn('id', $permitted_locations)
                            ->get();

            foreach ($locations as $location) {
                if (! empty($location->default_payment_accounts)) {
                    $default_payment_accounts = json_decode($location->default_payment_accounts, true);
                    foreach ($default_payment_accounts as $key => $account) {
                        if (! empty($account['is_enabled']) && ! empty($account['account'])) {
                            $account_ids[] = $account['account'];
                        }
                    }
                }
            }

            $account_ids = array_unique($account_ids);
        }

        $moduleUtil = new \App\Utils\ModuleUtil;
        $is_admin = $user ? $moduleUtil->is_admin($user, $business_id) : false;

        if ($user && ! $is_admin) {
            if ($permitted_locations != 'all') {
                $query->where(function ($q) use ($permitted_locations, $account_ids) {
                    $q->whereNull('accounts.location_id')
                      ->orWhereIn('accounts.location_id', $permitted_locations);
                    foreach ((array)$permitted_locations as $loc_id) {
                        $q->orWhereJsonContains('accounts.location_id', (string)$loc_id)
                          ->orWhereJsonContains('accounts.location_id', (int)$loc_id);
                    }
                    if (!empty($account_ids)) {
                        $q->orWhereIn('accounts.id', $account_ids);
                    }
                });
            }

            $user_role_ids = $user->roles()->pluck('id')->toArray();
            $query->where(function ($q) use ($user_role_ids) {
                $q->whereIn('accounts.user_level', $user_role_ids)
                  ->orWhereNull('accounts.user_level');
            });
        }

        $can_access_account = auth()->user()->can('account.access');
        if ($can_access_account && $show_balance) {
            // $query->leftjoin('account_transactions as AT', function ($join) {
            //     $join->on('AT.account_id', '=', 'accounts.id');
            //     $join->whereNull('AT.deleted_at');
            // })
            $query->select('accounts.name',
                    'accounts.id',
                    DB::raw("(SELECT SUM( IF(account_transactions.type='credit', amount, -1*amount) ) as balance from account_transactions where account_transactions.account_id = accounts.id AND deleted_at is NULL) as balance")
                );
        }

        if (! $closed) {
            $query->where('is_closed', 0);
        }

        $accounts = $query->get();

        $dropdown = [];
        if ($prepend_none) {
            $dropdown[''] = __('lang_v1.none');
        }

        $commonUtil = new Util;
        foreach ($accounts as $account) {
            $name = $account->name;

            if ($can_access_account && $show_balance) {
                $name .= ' ('.__('lang_v1.balance').': '.$commonUtil->num_f($account->balance).')';
            }

            $dropdown[$account->id] = $name;
        }

        return $dropdown;
    }

    /**
     * Scope a query to only include not closed accounts.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeNotClosed($query)
    {
        return $query->where('is_closed', 0);
    }

    /**
     * Scope a query to only include non capital accounts.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    // public function scopeNotCapital($query)
    // {
    //     return $query->where(function ($q) {
    //         $q->where('account_type', '!=', 'capital');
    //         $q->orWhereNull('account_type');
    //     });
    // }

    public static function accountTypes()
    {
        return [
            '' => __('account.not_applicable'),
            'saving_current' => __('account.saving_current'),
            'capital' => __('account.capital'),
        ];
    }

    public function account_type()
    {
        return $this->belongsTo(\App\AccountType::class, 'account_type_id');
    }
}
