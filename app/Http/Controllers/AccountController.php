<?php

namespace App\Http\Controllers;

use App\Account;
use App\AccountTransaction;
use App\AccountType;
use App\BusinessLocation;
use App\Media;
use App\Notifications\TelegramNotification;
use App\TransactionPayment;
use App\Utils\ModuleUtil;
use App\Utils\Util;
use DB;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;

class AccountController extends Controller
{
    protected $commonUtil;

    protected $moduleUtil;

    /**
     * Constructor
     *
     * @param  Util  $commonUtil
     * @return void
     */
    public function __construct(Util $commonUtil, ModuleUtil $moduleUtil)
    {
        $this->commonUtil = $commonUtil;
        $this->moduleUtil = $moduleUtil;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        if (! auth()->user()->can('account.access')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = session()->get('user.business_id');
        if (request()->ajax()) {
            $accounts = Account::leftjoin('account_transactions as AT', function ($join) {
                $join->on('AT.account_id', '=', 'accounts.id')
                    ->whereNull('AT.deleted_at')
                    ->where(function ($q) {
                        $q->whereNull('AT.status')->orWhere('AT.status', 'final');
                    });
            })
                ->leftjoin(
                    'account_types as ats',
                    'accounts.account_type_id',
                    '=',
                    'ats.id'
                )
                ->leftjoin(
                    'account_types as pat',
                    'ats.parent_account_type_id',
                    '=',
                    'pat.id'
                )
                ->leftJoin('users AS u', 'accounts.created_by', '=', 'u.id')
                ->where('accounts.business_id', $business_id)
                ->select([
                    'accounts.name',
                    'accounts.account_number',
                    'accounts.note',
                    'accounts.id',
                    'accounts.account_type_id',
                    'ats.name as account_type_name',
                    'pat.name as parent_account_type_name',
                    'accounts.account_details',
                    'is_closed',
                    'accounts.location_id',
                    'accounts.user_level',
                    DB::raw("SUM( IF(AT.type='credit', amount, -1*amount) ) as balance"),
                    DB::raw("CONCAT(COALESCE(u.surname, ''),' ',COALESCE(u.first_name, ''),' ',COALESCE(u.last_name,'')) as added_by"),
                ]);

            //check account permissions basaed on location
            $permitted_locations = auth()->user()->permitted_locations();
            $account_ids = [];
            if ($permitted_locations != 'all') {
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

            if ($permitted_locations != 'all') {
                $accounts->where(function ($q) use ($permitted_locations) {
                    $q->whereIn('accounts.location_id', (array)$permitted_locations);
                    foreach ((array)$permitted_locations as $loc_id) {
                        $q->orWhere('accounts.location_id', (string)$loc_id)
                            ->orWhereRaw("accounts.location_id LIKE ?", ['%"' . $loc_id . '"%'])
                            ->orWhereRaw("(JSON_VALID(accounts.location_id) = 1 AND (JSON_CONTAINS(accounts.location_id, ?) OR JSON_CONTAINS(accounts.location_id, ?)))", [json_encode((string)$loc_id), json_encode((int)$loc_id)]);
                    }
                });
            }

            if (! $this->moduleUtil->is_admin(auth()->user(), $business_id)) {
                $user_role_ids = auth()->user()->roles()->pluck('id')->toArray();
                $accounts->where(function ($q) use ($user_role_ids) {
                    $q->whereNull('accounts.user_level')
                      ->orWhere('accounts.user_level', '0')
                      ->orWhere('accounts.user_level', '[]')
                      ->orWhere('accounts.user_level', '[""]');
                    foreach ((array)$user_role_ids as $r_id) {
                        $q->orWhere('accounts.user_level', $r_id)
                          ->orWhere('accounts.user_level', (string)$r_id)
                          ->orWhereRaw("accounts.user_level LIKE ?", ['%"' . $r_id . '"%'])
                          ->orWhereRaw("(JSON_VALID(accounts.user_level) = 1 AND (JSON_CONTAINS(accounts.user_level, ?) OR JSON_CONTAINS(accounts.user_level, ?)))", [json_encode((string)$r_id), json_encode((int)$r_id)]);
                    }
                });
            }

            $is_closed = request()->input('account_status') == 'closed' ? 1 : 0;
            $accounts->where('is_closed', $is_closed);

            if (! empty(request()->input('location_id'))) {
                $loc_filter = request()->input('location_id');
                $accounts->where(function ($q) use ($loc_filter) {
                    $q->where('accounts.location_id', $loc_filter)
                      ->orWhere('accounts.location_id', (string)$loc_filter)
                      ->orWhereIn('accounts.location_id', (array)$loc_filter)
                      ->orWhereRaw("accounts.location_id LIKE ?", ['%"' . $loc_filter . '"%'])
                      ->orWhereRaw("(JSON_VALID(accounts.location_id) = 1 AND (JSON_CONTAINS(accounts.location_id, ?) OR JSON_CONTAINS(accounts.location_id, ?)))", [json_encode((string)$loc_filter), json_encode((int)$loc_filter)]);
                });
            }

            if (! empty(request()->input('account_type'))) {
                $acc_type = request()->input('account_type');
                if ($acc_type == 'capital') {
                    $accounts->where(function ($q) {
                        $q->where('ats.name', 'Capital')
                          ->orWhere('pat.name', 'Capital');
                    });
                } elseif ($acc_type == 'other') {
                    $accounts->where(function ($q) {
                        $q->where(function ($q2) {
                            $q2->whereNull('ats.name')
                               ->orWhere('ats.name', '!=', 'Capital');
                        })->where(function ($q3) {
                            $q3->whereNull('pat.name')
                               ->orWhere('pat.name', '!=', 'Capital');
                        });
                    });
                }
            }

            if (! empty(request()->input('account_type_id'))) {
                $acc_type_filter = request()->input('account_type_id');
                $accounts->where(function ($q) use ($acc_type_filter) {
                    $q->where('accounts.account_type_id', $acc_type_filter)
                      ->orWhere('ats.parent_account_type_id', $acc_type_filter);
                });
            }

            if (! empty(request()->input('created_by'))) {
                $accounts->where('accounts.created_by', request()->input('created_by'));
            }

            if (! empty(request()->input('user_level'))) {
                $u_filter = request()->input('user_level');
                $accounts->where(function ($q) use ($u_filter) {
                    $q->whereNull('accounts.user_level')
                      ->orWhere('accounts.user_level', '0')
                      ->orWhere('accounts.user_level', $u_filter)
                      ->orWhere('accounts.user_level', (string)$u_filter)
                      ->orWhereRaw("accounts.user_level LIKE ?", ['%"' . $u_filter . '"%'])
                      ->orWhereRaw("(JSON_VALID(accounts.user_level) = 1 AND (JSON_CONTAINS(accounts.user_level, ?) OR JSON_CONTAINS(accounts.user_level, ?)))", [json_encode((string)$u_filter), json_encode((int)$u_filter)]);
                });
            }

            $accounts->groupBy('accounts.id');

            return DataTables::of($accounts)
                ->addColumn('action', function ($row) {
                    $html = '';

                    // Edit
                    if (auth()->user()->can('account.edit') || auth()->user()->can('edit_account')) {
                        $html .= '<button data-href="' . action([\App\Http\Controllers\AccountController::class, 'edit'], [$row->id]) . '" data-container=".account_model" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline tw-dw-btn-primary btn-modal"><i class="glyphicon glyphicon-edit"></i> ' . __('messages.edit') . '</button>';
                    }

                    // Account Book
                    if (auth()->user()->can('account.show') || auth()->user()->can('view_account_book')) {
                        $html .= '<a href="' . action([\App\Http\Controllers\AccountController::class, 'show'], [$row->id]) . '" class="tw-dw-btn tw-dw-btn-outline tw-dw-btn-xs tw-dw-btn-warning btn-xs"><i class="fa fa-book"></i> ' . __('account.account_book') . '</a>';
                    }

                    $pending_count = AccountTransaction::where('account_id', $row->id)
                        ->where('status', 'pending')
                        ->whereNull('deleted_at')
                        ->count();

                    if ($pending_count > 0) {
                        $html .= '<button data-href="' . action([\App\Http\Controllers\AccountController::class, 'getPendingTransfers'], [$row->id]) . '" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-warning btn-modal" style="background-color: #f39c12 !important; color: #ffffff !important; border: 1px solid #e08e0b !important;" data-container=".view_modal"><i class="fa fa-clock-o"></i> ' . __('account.pending') . ' (' . $pending_count . ')</button>';
                    }

                    if ($row->is_closed == 0) {
                        // Fund Transfer
                        if (auth()->user()->can('account.fund_transfer') || auth()->user()->can('fund_transfer')) {
                            $html .= '<button data-href="' . action([\App\Http\Controllers\AccountController::class, 'getFundTransfer'], [$row->id]) . '" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline tw-dw-btn-info btn-modal" data-container=".view_modal"><i class="fas fa-calculator"></i> ' . __('account.fund_transfer') . '</button>';
                        }

                        // Deposit
                        if (auth()->user()->can('account.deposit') || auth()->user()->can('deposit')) {
                            $html .= '<button data-href="' . action([\App\Http\Controllers\AccountController::class, 'getDeposit'], [$row->id]) . '" class="tw-dw-btn tw-dw-btn-outline tw-dw-btn-xs tw-dw-btn-success btn-modal" data-container=".view_modal"><i class="fas fa-money-bill-alt"></i> ' . __('account.deposit') . '</button>';
                        }

                        // Close
                        if (auth()->user()->can('account.close') || auth()->user()->can('close_account')) {
                            $html .= '<button data-url="' . action([\App\Http\Controllers\AccountController::class, 'close'], [$row->id]) . '" class="tw-dw-btn tw-dw-btn-outline tw-dw-btn-xs tw-dw-btn-error close_account"><i class="fa fa-power-off"></i> ' . __('messages.close') . '</button>';
                        }
                    } elseif ($row->is_closed == 1) {
                        // Activate
                        if (auth()->user()->can('account.activate') || auth()->user()->can('activate_account')) {
                            $html .= '<button data-url="' . action([\App\Http\Controllers\AccountController::class, 'activate'], [$row->id]) . '" class="tw-dw-btn tw-dw-btn-outline tw-dw-btn-xs tw-dw-btn-success activate_account"><i class="fa fa-power-off"></i> ' . __('messages.activate') . '</button>';
                        }
                    }

                    return '<div class="tw-flex tw-items-center tw-gap-1 tw-flex-wrap">' . $html . '</div>';
                })
                ->editColumn('name', function ($row) {
                    if ($row->is_closed == 1) {
                        return $row->name . ' <small class="label pull-right bg-red no-print">' . __('account.closed') . '</small><span class="print_section">(' . __('account.closed') . ')</span>';
                    } else {
                        return $row->name;
                    }
                })
                ->editColumn('note', function ($row) {
                    $note_html = $row->note ?? '';
                    $pending_count = AccountTransaction::where('account_id', $row->id)
                        ->where('status', 'pending')
                        ->whereNull('deleted_at')
                        ->count();

                    if ($pending_count > 0) {
                        $pending_badge = '<a data-href="' . action([\App\Http\Controllers\AccountController::class, 'getPendingTransfers'], [$row->id]) . '" class="label bg-yellow no-print btn-modal" data-container=".view_modal" title="' . __('account.pending') . '" style="font-size: 11px; cursor: pointer; display: inline-block;"><i class="fa fa-clock-o"></i> ' . __('account.pending') . ' (' . $pending_count . ')</a>';
                        $note_html = !empty($note_html) ? $note_html . ' ' . $pending_badge : $pending_badge;
                    }

                    return $note_html;
                })
                ->editColumn('location_name', function ($row) {
                    if (empty($row->location_id)) {
                        return __('lang_v1.none');
                    }
                    $loc_ids = is_array($row->location_id) ? $row->location_id : json_decode($row->location_id, true);
                    if (!empty($loc_ids) && is_array($loc_ids)) {
                        $loc_names = BusinessLocation::whereIn('id', $loc_ids)->pluck('name')->toArray();
                        return !empty($loc_names) ? implode(', ', $loc_names) : __('lang_v1.none');
                    }
                    return $row->location_name ?: __('lang_v1.none');
                })
                ->editColumn('role_name', function ($row) use ($business_id) {
                    $u_levels = $row->user_level;
                    if (!is_array($u_levels) && !empty($u_levels)) {
                        $u_levels = json_decode($u_levels, true) ?: [$u_levels];
                    }
                    if (!empty($u_levels) && is_array($u_levels)) {
                        $role_names = Role::whereIn('id', $u_levels)->pluck('name')->toArray();
                        $formatted_roles = array_map(function ($r) use ($business_id) {
                            $r = str_replace('#' . $business_id, '', $r);
                            return in_array($r, ['Admin', 'Cashier']) ? __('lang_v1.' . $r) : $r;
                        }, $role_names);
                        return !empty($formatted_roles) ? implode(', ', $formatted_roles) : __('messages.all');
                    }
                    if (empty($row->role_name)) {
                        return __('messages.all');
                    }
                    $role = str_replace('#' . $business_id, '', $row->role_name);
                    if (in_array($role, ['Admin', 'Cashier'])) {
                        $role = __('lang_v1.' . $role);
                    }
                    return $role;
                })
                ->editColumn('balance', function ($row) {
                    return '<span class="balance" data-orig-value="' . $row->balance . '">' . $this->commonUtil->num_f($row->balance, true) . '</span>';
                })
                ->editColumn('account_type', function ($row) {
                    $account_type = '';
                    if (! empty($row->account_type->parent_account)) {
                        $account_type .= $row->account_type->parent_account->name . ' - ';
                    }
                    if (! empty($row->account_type)) {
                        $account_type .= $row->account_type->name;
                    }

                    return $account_type;
                })
                ->editColumn('parent_account_type_name', function ($row) {
                    $parent_account_type_name = empty($row->parent_account_type_name) ? $row->account_type_name : $row->parent_account_type_name;

                    return $parent_account_type_name;
                })
                ->editColumn('account_type_name', function ($row) {
                    $account_type_name = empty($row->parent_account_type_name) ? '' : $row->account_type_name;

                    return $account_type_name;
                })
                ->editColumn('account_details', function ($row) {
                    $html = '';
                    if (! empty($row->account_details)) {
                        foreach ($row->account_details as $account_detail) {
                            if (! empty($account_detail['label']) && ! empty($account_detail['value'])) {
                                $html .= $account_detail['label'] . ' : ' . $account_detail['value'] . '<br>';
                            }
                        }
                    }

                    return $html;
                })
                ->removeColumn('id')
                ->removeColumn('is_closed')
                ->rawColumns(['action', 'balance', 'name', 'account_details', 'note'])
                ->make(true);
        }

        $not_linked_payments = TransactionPayment::leftjoin(
            'transactions as T',
            'transaction_payments.transaction_id',
            '=',
            'T.id'
        )
            ->whereNull('transaction_payments.parent_id')
            ->where('method', '!=', 'advance')
            ->where('transaction_payments.business_id', $business_id)
            ->whereNull('account_id')
            ->count();

        // $capital_account_count = Account::where('business_id', $business_id)
        //                             ->NotClosed()
        //                             ->where('account_type', 'capital')
        //                             ->count();

        $account_types = AccountType::where('business_id', $business_id)
            ->whereNull('parent_account_type_id')
            ->with(['sub_types'])
            ->get();

        $business_locations = BusinessLocation::forDropdown($business_id, true);

        $account_types_dropdown = AccountType::where('business_id', $business_id)
            ->whereNull('parent_account_type_id')
            ->pluck('name', 'id');

        $users = \App\User::forDropdown($business_id, false);

        $roles_raw = Role::where('business_id', $business_id)->get();
        $user_levels = [];
        foreach ($roles_raw as $role_item) {
            $r_name = str_replace('#' . $business_id, '', $role_item->name);
            if (in_array($r_name, ['Admin', 'Cashier'])) {
                $r_name = __('lang_v1.' . $r_name);
            }
            $user_levels[$role_item->id] = $r_name;
        }

        return view('account.index')
            ->with(compact('not_linked_payments', 'account_types', 'business_locations', 'account_types_dropdown', 'users', 'user_levels'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        if (! auth()->user()->can('account.create') && ! auth()->user()->can('add_account')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = session()->get('user.business_id');
        $account_types = AccountType::where('business_id', $business_id)
            ->whereNull('parent_account_type_id')
            ->with(['sub_types'])
            ->get();

        $business_locations = BusinessLocation::forDropdown($business_id);

        $roles_array = Role::where('business_id', $business_id)->get()->pluck('name', 'id');
        $roles = [];
        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);
        foreach ($roles_array as $key => $value) {
            if (! $is_admin && $value == 'Admin#' . $business_id) {
                continue;
            }
            $roles[$key] = str_replace('#' . $business_id, '', $value);
        }

        return view('account.create')
            ->with(compact('account_types', 'business_locations', 'roles'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return Response
     */
    public function store(Request $request)
    {
        if (! auth()->user()->can('account.create') && ! auth()->user()->can('add_account')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            try {
                $input = $request->only(['name', 'account_number', 'note', 'account_type_id', 'account_details', 'location_id', 'user_level']);
                $business_id = $request->session()->get('user.business_id');
                $user_id = $request->session()->get('user.id');
                $input['business_id'] = $business_id;
                $input['created_by'] = $user_id;

                $filtered_loc = is_array($input['location_id']) ? array_values(array_filter($input['location_id'], function ($v) { return $v !== null && $v !== ''; })) : [];
                $input['location_id'] = ! empty($filtered_loc) ? $filtered_loc : null;

                $filtered_user = is_array($input['user_level']) ? array_values(array_filter($input['user_level'], function ($v) { return $v !== null && $v !== ''; })) : [];
                $input['user_level'] = ! empty($filtered_user) ? $filtered_user : null;

                if (empty($input['account_type_id']) || !is_numeric($input['account_type_id'])) {
                    $input['account_type_id'] = null;
                }

                $account = Account::create($input);

                //Opening Balance
                $opening_bal = $request->input('opening_balance');

                if (! empty($opening_bal)) {
                    $ob_transaction_data = [
                        'amount'         => $this->commonUtil->num_uf($opening_bal),
                        'account_id'     => $account->id,
                        'type'           => 'credit',
                        'sub_type'       => 'opening_balance',
                        'operation_date' => \Carbon::now(),
                        'created_by'     => $user_id,
                    ];

                    AccountTransaction::createAccountTransaction($ob_transaction_data);
                }

                // ── Telegram Notification ──────────────────────────
                try {
                    $account->load(['account_type', 'account_type.parent_account']);

                    $location_code = BusinessLocation::where('business_id', $business_id)
                        ->value('location_id') ?? 'PT1001';

                    // Build all_account list
                    // $all_account = Account::where('business_id', $business_id)
                    //     ->select(
                    //         'accounts.name',
                    //         'accounts.id',
                    //         DB::raw("(SELECT SUM(IF(account_transactions.type='credit', amount, -1*amount))
                    //               FROM account_transactions
                    //               WHERE account_transactions.account_id = accounts.id
                    //               AND deleted_at IS NULL) as balance")
                    //     )
                    //     ->get()
                    //     ->map(fn($item) => [
                    //         'name'    => $item->name,
                    //         'id'      => $item->id,
                    //         'balance' => $this->commonUtil->num_f($item->balance, true),
                    //     ])
                    //     ->toArray();

                    TelegramNotification::addAccountMessage(
                        $account,
                        $opening_bal,
                        // $all_account,
                        'payment_accoun',
                        $location_code
                    );
                } catch (\Exception $te) {
                    \Log::warning('Telegram account notification failed: ' . $te->getMessage());
                }
                // ── End Telegram ───────────────────────────────────

                $output = [
                    'success' => true,
                    'msg' => __('account.account_created_success'),
                ];
            } catch (\Exception $e) {
                \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

                $output = [
                    'success' => false,
                    'msg' => __('messages.something_went_wrong'),
                ];
            }

            return $output;
        }
    }

    /**
     * Get accounts dropdown for a specific location
     *
     * @param  int|null  $location_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAccountsByLocation($location_id = null)
    {
        $business_id = session()->get('user.business_id');
        $accounts = $this->moduleUtil->accountsDropdown($business_id, true, false, false, $location_id ?: null);

        return response()->json($accounts);
    }

    /**
     * Show the specified resource.
     *
     * @return Response
     */
    public function show($id)
    {
        if (! auth()->user()->can('account.show') && ! auth()->user()->can('view_account_book')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $is_admin = $this->commonUtil->is_admin(auth()->user(), $business_id) || auth()->user()->can('superadmin');
        $current_user_id = auth()->id();

        if (request()->ajax()) {
            $start_date = request()->input('start_date');
            $end_date = request()->input('end_date');

            $before_bal_query = AccountTransaction::join(
                'accounts as A',
                'account_transactions.account_id',
                '=',
                'A.id'
            )
                ->where('A.business_id', $business_id)
                ->where('A.id', $id)
                ->select([
                    DB::raw('SUM(IF(account_transactions.type="credit", account_transactions.amount, -1 * account_transactions.amount)) as prev_bal'),
                ])
                ->where('account_transactions.operation_date', '<', $start_date)
                ->whereNull('account_transactions.deleted_at')
                ->where(function ($q) {
                    $q->whereNull('account_transactions.status')->orWhere('account_transactions.status', 'final');
                });
            if (! empty(request()->input('type'))) {
                $before_bal_query->where('account_transactions.type', request()->input('type'));
            }
            $bal_before_start_date = $before_bal_query->first()->prev_bal;

            $accounts = AccountTransaction::join(
                'accounts as A',
                'account_transactions.account_id',
                '=',
                'A.id'
            )
                ->leftJoin('transaction_payments AS tp', 'account_transactions.transaction_payment_id', '=', 'tp.id')
                ->leftJoin('contacts AS c', 'tp.payment_for', '=', 'c.id')
                ->leftJoin('users AS u', 'account_transactions.created_by', '=', 'u.id')
                ->leftjoin(
                    'transaction_payments as child_payments',
                    'tp.id',
                    '=',
                    'child_payments.parent_id'
                )
                ->leftjoin(
                    'transactions as child_sells',
                    'child_sells.id',
                    '=',
                    'child_payments.transaction_id'
                )
                ->with(['transaction', 'transaction.contact', 'transfer_transaction', 'transaction.transaction_for'])
                ->where('A.business_id', $business_id)
                ->where('A.id', $id)
                ->with(['transaction', 'transaction.contact', 'transfer_transaction', 'media', 'transfer_transaction.media'])
                ->select([
                    'account_transactions.type',
                    'account_transactions.amount',
                    'operation_date',
                    'account_transactions.sub_type',
                    'transfer_transaction_id',
                    'account_transactions.status',
                    'A.id as account_id',
                    'account_transactions.transaction_id',
                    'account_transactions.id',
                    'account_transactions.note',
                    'tp.is_advance',
                    'tp.is_return',
                    'tp.payment_ref_no',
                    'tp.method',
                    'tp.transaction_no',
                    'tp.card_transaction_number',
                    'tp.card_number',
                    'tp.card_type',
                    'tp.card_holder_name',
                    'tp.card_month',
                    'tp.card_year',
                    'tp.card_security',
                    'tp.cheque_number',
                    'tp.bank_account_number',
                    DB::raw("CONCAT(COALESCE(u.surname, ''),' ',COALESCE(u.first_name, ''),' ',COALESCE(u.last_name,'')) as added_by"),
                    'c.name as payment_for_contact',
                    'c.type as payment_for_type',
                    'c.supplier_business_name as payment_for_business_name',
                    DB::raw('SUM(child_payments.amount) total_recovered'),
                    DB::raw('GROUP_CONCAT(child_sells.invoice_no) as child_sells'),
                ])
                ->groupBy('account_transactions.id')
                //->orderBy('account_transactions.id', 'asc')
                ->orderBy('account_transactions.operation_date', 'asc');
            if (! empty(request()->input('type'))) {
                $accounts->where('account_transactions.type', request()->input('type'));
            }

            if (! empty($start_date) && ! empty($end_date)) {
                $accounts->whereDate('operation_date', '>=', $start_date)
                    ->whereDate('operation_date', '<=', $end_date);
            }

            $payment_types = $this->commonUtil->payment_types(null, true, $business_id);

            return DataTables::of($accounts)
                ->editColumn('method', function ($row) use ($payment_types) {
                    if (! empty($row->method) && isset($payment_types[$row->method])) {
                        return $payment_types[$row->method];
                    } else {
                        return '';
                    }
                })
                ->addColumn('payment_details', function ($row) {
                    $arr = [];
                    if (! empty($row->transaction_no)) {
                        $arr[] = '<b>' . __('lang_v1.transaction_no') . '</b>: ' . $row->transaction_no;
                    }

                    if ($row->method == 'card' && ! empty($row->card_transaction_number)) {
                        $arr[] = '<b>' . __('lang_v1.card_transaction_no') . '</b>: ' . $row->card_transaction_number;
                    }

                    if ($row->method == 'card' && ! empty($row->card_number)) {
                        $arr[] = '<b>' . __('lang_v1.card_no') . '</b>: ' . $row->card_number;
                    }
                    if ($row->method == 'card' && ! empty($row->card_type)) {
                        $arr[] = '<b>' . __('lang_v1.card_type') . '</b>: ' . $row->card_type;
                    }
                    if ($row->method == 'card' && ! empty($row->card_holder_name)) {
                        $arr[] = '<b>' . __('lang_v1.card_holder_name') . '</b>: ' . $row->card_holder_name;
                    }
                    if ($row->method == 'card' && ! empty($row->card_month)) {
                        $arr[] = '<b>' . __('lang_v1.month') . '</b>: ' . $row->card_month;
                    }
                    if ($row->method == 'card' && ! empty($row->card_year)) {
                        $arr[] = '<b>' . __('lang_v1.year') . '</b>: ' . $row->card_year;
                    }
                    if ($row->method == 'card' && ! empty($row->card_security)) {
                        $arr[] = '<b>' . __('lang_v1.security_code') . '</b>: ' . $row->card_security;
                    }
                    if (! empty($row->cheque_number)) {
                        $arr[] = '<b>' . __('lang_v1.cheque_no') . '</b>: ' . $row->cheque_number;
                    }
                    if (! empty($row->bank_account_number)) {
                        $arr[] = '<b>' . __('lang_v1.card_no') . '</b>: ' . $row->bank_account_number;
                    }

                    return implode(', ', $arr);
                })
                ->addColumn('debit', function ($row) {
                    if ($row->type == 'debit') {
                        return '<span class="debit" data-orig-value="' . $row->amount . '">' . $this->commonUtil->num_f($row->amount, true) . '</span>';
                    }

                    return '';
                })
                ->addColumn('credit', function ($row) {
                    if ($row->type == 'credit') {
                        return '<span class="credit"  data-orig-value="' . $row->amount . '">' . $this->commonUtil->num_f($row->amount, true) . '</span>';
                    }

                    return '';
                })
                ->addColumn('balance', function ($row) use ($bal_before_start_date, $start_date) {
                    //TODO:: Need to fix same balance showing for transactions having same operation date
                    $current_bal = AccountTransaction::where(
                        'account_id',
                        $row->account_id
                    )
                        ->where('operation_date', '>=', $start_date)
                        ->where('operation_date', '<=', $row->operation_date)
                        ->whereNull('deleted_at')
                        ->where(function ($q) {
                            $q->whereNull('status')->orWhere('status', 'final');
                        })
                        ->select(DB::raw("SUM(IF(type='credit', amount, -1 * amount)) as balance"))
                        ->first()->balance;
                    $bal = $bal_before_start_date + $current_bal;

                    return '<span class="balance" data-orig-value="' . $bal . '">' . $this->commonUtil->num_f($bal, true) . '</span>';
                })
                ->editColumn('operation_date', function ($row) {
                    return $this->commonUtil->format_date($row->operation_date, true);
                })
                ->editColumn('sub_type', function ($row) {
                    $details = $this->__getPaymentDetails($row);
                    if (! empty($row->status) && $row->status == 'pending') {
                        $details .= ' <span class="label label-warning" style="margin-left: 5px;">' . __('account.pending') . '</span>';
                    } elseif (! empty($row->transfer_transaction) && ! empty($row->transfer_transaction->status) && $row->transfer_transaction->status == 'pending') {
                        $details .= ' <span class="label label-warning" style="margin-left: 5px;">' . __('account.pending') . '</span>';
                    }
                    return $details;
                })
                ->editColumn('action', function ($row) use ($is_admin, $current_user_id) {
                    $action = '';

                    $is_pending = ($row->status == 'pending') || (! empty($row->transfer_transaction) && $row->transfer_transaction->status == 'pending');
                    $is_creator = ($row->created_by == $current_user_id) || (! empty($row->transfer_transaction) && $row->transfer_transaction->created_by == $current_user_id);
                    $can_manage_pending = $is_admin || ! $is_creator;

                    if ($is_pending && $can_manage_pending && (auth()->user()->can('account.fund_transfer') || auth()->user()->can('fund_transfer') || auth()->user()->can('superadmin'))) {
                        $action .= '<button type="button" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline tw-dw-btn-success change_transfer_status" data-href="' . action([\App\Http\Controllers\AccountController::class, 'changeTransferStatus'], [$row->id]) . '?status=final" style="display: inline-flex; align-items: center; gap: 4px;"><svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-check" width="14" height="14" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0;"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l5 5l10 -10" /></svg> ' . __('account.approve') . '</button> ';
                        $action .= '<button type="button" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline tw-dw-btn-error change_transfer_status" data-href="' . action([\App\Http\Controllers\AccountController::class, 'changeTransferStatus'], [$row->id]) . '?status=rejected" style="display: inline-flex; align-items: center; gap: 4px;"><svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-x" width="14" height="14" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0;"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M18 6l-12 12" /><path d="M6 6l12 12" /></svg> ' . __('account.reject') . '</button> ';
                    }

                    if (auth()->user()->can('delete_account_transaction')) {
                        if ($row->sub_type == 'fund_transfer' || $row->sub_type == 'deposit') {
                            if (! $is_pending || $can_manage_pending) {
                                $action .= '<button type="button" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline  tw-dw-btn-error delete_account_transaction" data-href="' . action([\App\Http\Controllers\AccountController::class, 'destroyAccountTransaction'], [$row->id]) . '"><i class="fa fa-trash"></i> ' . __('messages.delete') . '</button>';
                            }
                        }
                    }
                    if (auth()->user()->can('edit_account_transaction')) {
                        if ($row->sub_type == 'fund_transfer' || $row->sub_type == 'deposit' || $row->sub_type == 'opening_balance') {
                            if (! $is_pending || $can_manage_pending) {
                                $action .= ' <button type="button" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline  tw-dw-btn-primary btn-modal" data-container="#edit_account_transaction" data-href="' . action([\App\Http\Controllers\AccountController::class, 'editAccountTransaction'], [$row->id]) . '"><i class="fa fa-edit"></i> ' . __('messages.edit') . '</button>';
                            }
                        }
                    }

                    if (! empty($row->media->first()) || (! empty($row->transfer_transaction && ! empty($row->transfer_transaction->media->first())))) {
                        $display_url = ! empty($row->media->first()) ? $row->media->first()->display_url : $row->transfer_transaction->media->first()->display_url;

                        $display_name = ! empty($row->media->first()) ? $row->media->first()->display_name : $row->transfer_transaction->media->first()->display_name;

                        $action .= '&nbsp; <a class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline  tw-dw-btn-accent" href="' . $display_url . '" download="' . $display_name . '"><i class="fa fa-download"></i> ' . __('purchase.download_document') . '</a>';
                    }

                    return $action;
                })
                ->filterColumn('added_by', function ($query, $keyword) {
                    $query->whereRaw("CONCAT(COALESCE(u.surname, ''), ' ', COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, '')) like ?", ["%{$keyword}%"]);
                })
                ->removeColumn('id')
                ->removeColumn('is_closed')
                ->rawColumns(['credit', 'debit', 'balance', 'sub_type', 'action', 'payment_details'])
                ->make(true);
        }
        $account = Account::where('business_id', $business_id)
            ->with(['account_type', 'account_type.parent_account'])
            ->findOrFail($id);


        return view('account.show')
            ->with(compact('account'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return Response
     */
    public function edit($id)
    {
        if (! auth()->user()->can('account.edit') && ! auth()->user()->can('edit_account')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
            $account = Account::where('business_id', $business_id)
                ->find($id);

            $account_types = AccountType::where('business_id', $business_id)
                ->whereNull('parent_account_type_id')
                ->with(['sub_types'])
                ->get();

            $business_locations = BusinessLocation::forDropdown($business_id);

            $roles_array = Role::where('business_id', $business_id)->get()->pluck('name', 'id');
            $roles = [];
            $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);
            foreach ($roles_array as $key => $value) {
                if (! $is_admin && $value == 'Admin#' . $business_id) {
                    continue;
                }
                $roles[$key] = str_replace('#' . $business_id, '', $value);
            }

            return view('account.edit')
                ->with(compact('account', 'account_types', 'business_locations', 'roles'));
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request  $request
     * @return Response
     */
    public function update(Request $request, $id)
    {
        if (! auth()->user()->can('account.edit') && ! auth()->user()->can('edit_account')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            try {
                $input = $request->only(['name', 'account_number', 'note', 'account_type_id', 'account_details', 'location_id', 'user_level']);

                $business_id = request()->session()->get('user.business_id');

                $account = Account::where('business_id', $business_id)
                    ->findOrFail($id);

                // ── Snapshot BEFORE update ─────────────────────────
                $old_account = $account->replicate();
                $old_account->load(['account_type', 'account_type.parent_account']);

                $filtered_loc = is_array($input['location_id']) ? array_values(array_filter($input['location_id'], function ($v) { return $v !== null && $v !== ''; })) : [];
                $input['location_id'] = ! empty($filtered_loc) ? $filtered_loc : null;

                $filtered_user = is_array($input['user_level']) ? array_values(array_filter($input['user_level'], function ($v) { return $v !== null && $v !== ''; })) : [];
                $input['user_level'] = ! empty($filtered_user) ? $filtered_user : null;

                if (empty($input['account_type_id']) || !is_numeric($input['account_type_id'])) {
                    $input['account_type_id'] = null;
                }

                $account->name            = $input['name'];
                $account->account_number  = $input['account_number'];
                $account->note            = $input['note'];
                $account->account_type_id = $input['account_type_id'];
                $account->account_details = $input['account_details'];
                $account->location_id     = $input['location_id'];
                $account->user_level      = $input['user_level'];
                $account->save();

                // ── Telegram Notification ──────────────────────────
                try {
                    $account->load(['account_type', 'account_type.parent_account']);

                    $location_code = BusinessLocation::where('business_id', $business_id)
                        ->value('location_id') ?? 'PT1001';

                    // Build all_account list
                    // $all_account = Account::where('business_id', $business_id)
                    //     ->select(
                    //         'accounts.name',
                    //         'accounts.id',
                    //         DB::raw("(SELECT SUM(IF(account_transactions.type='credit', amount, -1*amount))
                    //               FROM account_transactions
                    //               WHERE account_transactions.account_id = accounts.id
                    //               AND deleted_at IS NULL) as balance")
                    //     )
                    //     ->get()
                    //     ->map(fn($item) => [
                    //         'name'    => $item->name,
                    //         'id'      => $item->id,
                    //         'balance' => $this->commonUtil->num_f($item->balance, true),
                    //     ])
                    //     ->toArray();

                    TelegramNotification::updateAccountMessage(
                        $account,
                        $old_account,
                        // $all_account,
                        'payment_accoun',
                        $location_code
                    );
                } catch (\Exception $te) {
                    \Log::warning('Telegram account update notification failed: ' . $te->getMessage());
                }
                // ── End Telegram ───────────────────────────────────

                $output = [
                    'success' => true,
                    'msg' => __('account.account_updated_success'),
                ];
            } catch (\Exception $e) {
                \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

                $output = [
                    'success' => false,
                    'msg' => __('messages.something_went_wrong'),
                ];
            }

            return $output;
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return Response
     */
    public function destroyAccountTransaction($id)
    {
        if (! auth()->user()->can('delete_account_transaction')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            try {
                $business_id = request()->session()->get('user.business_id');

                $account_transaction = AccountTransaction::findOrFail($id);

                if (in_array($account_transaction->sub_type, ['fund_transfer', 'deposit'])) {
                    //Delete transfer transaction for fund transfer
                    if (! empty($account_transaction->transfer_transaction_id)) {
                        $transfer_transaction = AccountTransaction::findOrFail($account_transaction->transfer_transaction_id);
                        $transfer_transaction->delete();
                    }
                    $account_transaction->delete();
                }

                $output = [
                    'success' => true,
                    'msg' => __('lang_v1.deleted_success'),
                ];
            } catch (\Exception $e) {
                \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

                $output = [
                    'success' => false,
                    'msg' => __('messages.something_went_wrong'),
                ];
            }

            return $output;
        }
    }

    /**
     * Closes the specified account.
     *
     * @return Response
     */
    public function close($id)
    {
        if (! auth()->user()->can('account.close') && ! auth()->user()->can('close_account')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            try {
                $business_id = session()->get('user.business_id');

                $account = Account::where('business_id', $business_id)
                    ->findOrFail($id);

                $account->load(['account_type', 'account_type.parent_account']);

                $account->is_closed = 1;
                $account->save();

                // ── Telegram Notification ──────────────────────────
                try {
                    $location_code = BusinessLocation::where('business_id', $business_id)
                        ->value('location_id') ?? 'PT1001';

                    // Build all_account list
                    // $all_account = Account::where('business_id', $business_id)
                    //     ->select(
                    //         'accounts.name',
                    //         'accounts.id',
                    //         DB::raw("(SELECT SUM(IF(account_transactions.type='credit', amount, -1*amount))
                    //               FROM account_transactions
                    //               WHERE account_transactions.account_id = accounts.id
                    //               AND deleted_at IS NULL) as balance")
                    //     )
                    //     ->get()
                    //     ->map(fn($item) => [
                    //         'name'    => $item->name,
                    //         'id'      => $item->id,
                    //         'balance' => $this->commonUtil->num_f($item->balance, true),
                    //     ])
                    //     ->toArray();

                    TelegramNotification::closeAccountMessage(
                        $account,
                        // $all_account,
                        'payment_accoun',
                        $location_code
                    );
                } catch (\Exception $te) {
                    \Log::warning('Telegram account close notification failed: ' . $te->getMessage());
                }
                // ── End Telegram ───────────────────────────────────

                $output = [
                    'success' => true,
                    'msg' => __('account.account_closed_success'),
                ];
            } catch (\Exception $e) {
                \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

                $output = [
                    'success' => false,
                    'msg' => __('messages.something_went_wrong'),
                ];
            }

            return $output;
        }
    }

    /**
     * Shows form to transfer fund.
     *
     * @param  int  $id
     * @return Response
     */
    public function getFundTransfer($id)
    {
        if (! auth()->user()->can('account.fund_transfer') && ! auth()->user()->can('fund_transfer')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $business_id = session()->get('user.business_id');

            $from_account = Account::where('business_id', $business_id)
                ->NotClosed()
                ->find($id);

            $accounts_query = Account::where('business_id', $business_id)
                ->NotClosed();

            $user = auth()->user();
            $permitted_locations = $user ? $user->permitted_locations() : 'all';
            $is_admin = $user ? $this->moduleUtil->is_admin($user, $business_id) : false;

            if ($user && $permitted_locations != 'all') {
                $accounts_query->where(function ($q) use ($permitted_locations) {
                    $q->whereIn('accounts.location_id', (array)$permitted_locations);
                    foreach ((array)$permitted_locations as $loc_id) {
                        $q->orWhere('accounts.location_id', (string)$loc_id)
                            ->orWhereRaw("accounts.location_id LIKE ?", ['%"' . $loc_id . '"%'])
                            ->orWhereRaw("(JSON_VALID(accounts.location_id) = 1 AND (JSON_CONTAINS(accounts.location_id, ?) OR JSON_CONTAINS(accounts.location_id, ?)))", [json_encode((string)$loc_id), json_encode((int)$loc_id)]);
                    }
                });
            }

            if ($user && ! $is_admin) {
                $user_role_ids = $user->roles()->pluck('id')->toArray();
                $accounts_query->where(function ($q) use ($user_role_ids) {
                    $q->whereNull('accounts.user_level')
                        ->orWhere('accounts.user_level', '0');
                    foreach ((array)$user_role_ids as $r_id) {
                        $q->orWhere('accounts.user_level', $r_id)
                            ->orWhere('accounts.user_level', (string)$r_id)
                            ->orWhereRaw("accounts.user_level LIKE ?", ['%"' . $r_id . '"%'])
                            ->orWhereRaw("(JSON_VALID(accounts.user_level) = 1 AND (JSON_CONTAINS(accounts.user_level, ?) OR JSON_CONTAINS(accounts.user_level, ?)))", [json_encode((string)$r_id), json_encode((int)$r_id)]);
                    }
                });
            }

            $accounts = $accounts_query->get();

            $accounts_data = [];
            $all_accounts_dropdown = [];
            foreach ($accounts as $acc) {
                $all_accounts_dropdown[$acc->id] = $acc->name;
                $locs = $acc->location_id;
                if (!is_array($locs) && !empty($locs)) {
                    $locs = json_decode($locs, true) ?: [$locs];
                }
                $accounts_data[$acc->id] = [
                    'id'           => $acc->id,
                    'name'         => $acc->name,
                    'location_ids' => !empty($locs) ? array_map('strval', (array)$locs) : null,
                ];
            }

            $from_locs = !empty($accounts_data[$from_account->id]['location_ids'])
                ? $accounts_data[$from_account->id]['location_ids']
                : null;

            $to_accounts = [];
            foreach ($accounts as $acc) {
                $acc_locs = $accounts_data[$acc->id]['location_ids'];

                $is_compatible = false;
                if (empty($from_locs) || empty($acc_locs)) {
                    $is_compatible = true;
                } else {
                    $intersection = array_intersect($from_locs, $acc_locs);
                    if (!empty($intersection)) {
                        $is_compatible = true;
                    }
                }

                if ($is_compatible) {
                    $to_accounts[$acc->id] = $acc->name;
                }
            }

            $is_admin = $this->commonUtil->is_admin(auth()->user(), $business_id) || auth()->user()->can('superadmin');

            return view('account.transfer')
                ->with(compact('from_account', 'to_accounts', 'all_accounts_dropdown', 'accounts_data', 'is_admin'));
        }
    }

    /**
     * Transfers fund from one account to another.
     *
     * @return Response
     */
    public function postFundTransfer(Request $request)
    {
        if (! auth()->user()->can('account.fund_transfer') && ! auth()->user()->can('fund_transfer')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $business_id = session()->get('user.business_id');
            $amount      = $this->commonUtil->num_uf($request->input('amount'));
            $from_id     = $request->input('from_account');
            $to_id       = $request->input('to_account');
            $note        = $request->input('note');

            $from_acc = Account::where('business_id', $business_id)->find($from_id);
            $to_acc   = Account::where('business_id', $business_id)->find($to_id);

            if (! empty($from_acc->location_id) && ! empty($to_acc->location_id)) {
                $f_locs = (array)$from_acc->location_id;
                $t_locs = (array)$to_acc->location_id;
                if (empty(array_intersect($f_locs, $t_locs))) {
                    return [
                        'success' => false,
                        'msg'     => __('messages.something_went_wrong'),
                    ];
                }
            }

            $user = auth()->user();
            $is_admin = $this->commonUtil->is_admin($user, $business_id) || ($user && $user->can('superadmin'));

            $has_pending_transfer = false;
            $has_immediate_credit = false;

            if ($user) {
                $user_roles = $user->roles()->with('permissions')->get();
                foreach ($user_roles as $role) {
                    $p_names = $role->permissions->pluck('name')->toArray();
                    if (in_array('account.enable_pending_transfer', $p_names)) {
                        $has_pending_transfer = true;
                    }
                    if (in_array('account.enable_immediate_credit_pending_transfer', $p_names)) {
                        $has_immediate_credit = true;
                    }
                }
                $direct_p_names = $user->permissions->pluck('name')->toArray();
                if (in_array('account.enable_pending_transfer', $direct_p_names)) {
                    $has_pending_transfer = true;
                }
                if (in_array('account.enable_immediate_credit_pending_transfer', $direct_p_names)) {
                    $has_immediate_credit = true;
                }
            }

            $transfer_type = $request->input('transfer_type');

            if ($has_immediate_credit) {
                $transfer_type = 'immediate_credit_pending';
            } elseif ($has_pending_transfer) {
                $transfer_type = 'pending_transfer';
            }

            $debit_status = 'final';
            $credit_status = 'final';

            if ($transfer_type == 'pending_transfer') {
                $debit_status = 'final';
                $credit_status = 'pending';
            } elseif ($transfer_type == 'immediate_credit_pending') {
                $debit_status = 'pending';
                $credit_status = 'final';
            }

            if (! empty($amount)) {
                $debit_data = [
                    'amount'              => $amount,
                    'account_id'          => $from_id,
                    'type'                => 'debit',
                    'sub_type'            => 'fund_transfer',
                    'created_by'          => session()->get('user.id'),
                    'note'                => $note,
                    'transfer_account_id' => $to_id,
                    'operation_date'      => $this->commonUtil->uf_date($request->input('operation_date'), true),
                    'status'              => $debit_status,
                ];

                DB::beginTransaction();

                $debit = AccountTransaction::createAccountTransaction($debit_data);

                $credit_data = [
                    'amount'                 => $amount,
                    'account_id'             => $to_id,
                    'type'                   => 'credit',
                    'sub_type'               => 'fund_transfer',
                    'created_by'             => session()->get('user.id'),
                    'note'                   => $note,
                    'transfer_account_id'    => $from_id,
                    'transfer_transaction_id' => $debit->id,
                    'operation_date'         => $this->commonUtil->uf_date($request->input('operation_date'), true),
                    'status'                 => $credit_status,
                ];

                $credit = AccountTransaction::createAccountTransaction($credit_data);

                $debit->transfer_transaction_id = $credit->id;
                $debit->save();

                Media::uploadMedia($business_id, $debit, $request, 'document');

                DB::commit();

                // ── Telegram Notification ──────────────────────────
                try {
                    $from_account = Account::where('business_id', $business_id)
                        ->with(['account_type', 'account_type.parent_account'])
                        ->find($from_id);

                    $to_account = Account::where('business_id', $business_id)
                        ->with(['account_type', 'account_type.parent_account'])
                        ->find($to_id);

                    // New balances after transfer
                    $from_balance = AccountTransaction::where('account_id', $from_id)
                        ->whereNull('deleted_at')
                        ->selectRaw("SUM(IF(type='credit', amount, -1 * amount)) as balance")
                        ->first()->balance ?? 0;

                    $to_balance = AccountTransaction::where('account_id', $to_id)
                        ->whereNull('deleted_at')
                        ->selectRaw("SUM(IF(type='credit', amount, -1 * amount)) as balance")
                        ->first()->balance ?? 0;

                    $location_code = BusinessLocation::where('business_id', $business_id)
                        ->value('location_id') ?? 'PT1001';

                    // $all_account = Account::where('business_id', $business_id)
                    //     ->select(
                    //         'accounts.name',
                    //         'accounts.id',
                    //         DB::raw("(SELECT SUM(IF(account_transactions.type='credit', amount, -1*amount))
                    //               FROM account_transactions
                    //               WHERE account_transactions.account_id = accounts.id
                    //               AND deleted_at IS NULL) as balance")
                    //     )
                    //     ->get()
                    //     ->map(fn($item) => [
                    //         'name'    => $item->name,
                    //         'id'      => $item->id,
                    //         'balance' => $this->commonUtil->num_f($item->balance, true),
                    //     ])
                    //     ->toArray();
                    TelegramNotification::fundTransferMessage(
                        $from_account,
                        $to_account,
                        $amount,
                        $from_balance,
                        $to_balance,
                        $debit_data['operation_date'],
                        $note,
                        // $all_account,
                        'payment_accoun',
                        $location_code
                    );
                } catch (\Exception $te) {
                    \Log::warning('Telegram fund transfer notification failed: ' . $te->getMessage());
                }
                // ── End Telegram ───────────────────────────────────
            }

            $output = [
                'success' => true,
                'msg'     => __('account.fund_transfered_success'),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = [
                'success' => false,
                'msg'     => __('messages.something_went_wrong'),
            ];
        }

        return redirect()->action([\App\Http\Controllers\AccountController::class, 'index'])->with('status', $output);
    }

    /**
     * Shows deposit form.
     *
     * @param  int  $id
     * @return Response
     */
    public function getDeposit($id)
    {
        if (! auth()->user()->can('account.deposit') && ! auth()->user()->can('deposit')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $business_id = session()->get('user.business_id');

            $account = Account::where('business_id', $business_id)
                ->NotClosed()
                ->find($id);

            $from_accounts = Account::forDropdown($business_id, true, false);

            return view('account.deposit')
                ->with(compact('account', 'account', 'from_accounts'));
        }
    }

    /**
     * Deposits amount.
     *
     * @param  Request  $request
     * @return json
     */
    public function postDeposit(Request $request)
    {
        if (! auth()->user()->can('account.deposit') && ! auth()->user()->can('deposit')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $business_id = session()->get('user.business_id');

            $amount     = $this->commonUtil->num_uf($request->input('amount'));
            $account_id = $request->input('account_id');
            $note       = $request->input('note');

            $account = Account::where('business_id', $business_id)
                ->findOrFail($account_id);

            if (! empty($amount)) {
                $credit_data = [
                    'amount'         => $amount,
                    'account_id'     => $account_id,
                    'type'           => 'credit',
                    'sub_type'       => 'deposit',
                    'operation_date' => $this->commonUtil->uf_date($request->input('operation_date'), true),
                    'created_by'     => session()->get('user.id'),
                    'note'           => $note,
                ];
                $credit = AccountTransaction::createAccountTransaction($credit_data);

                $from_account_id = $request->input('from_account');
                $from_account    = null;

                if (! empty($from_account_id)) {
                    $debit_data                            = $credit_data;
                    $debit_data['type']                    = 'debit';
                    $debit_data['account_id']              = $from_account_id;
                    $debit_data['transfer_transaction_id'] = $credit->id;

                    $debit = AccountTransaction::createAccountTransaction($debit_data);

                    $credit->transfer_transaction_id = $debit->id;
                    $credit->save();

                    $from_account = Account::where('business_id', $business_id)
                        ->find($from_account_id);
                }

                // ── Telegram Notification ──────────────────────────
                try {
                    $account->load(['account_type', 'account_type.parent_account']);

                    // Recalculate balance after deposit
                    $new_balance = AccountTransaction::where('account_id', $account_id)
                        ->whereNull('deleted_at')
                        ->selectRaw("SUM(IF(type='credit', amount, -1 * amount)) as balance")
                        ->first()->balance ?? 0;

                    $location_code = BusinessLocation::where('business_id', $business_id)
                        ->value('location_id') ?? 'PT1001';

                    // Build all_account list
                    // $all_account = Account::where('business_id', $business_id)
                    //     ->select(
                    //         'accounts.name',
                    //         'accounts.id',
                    //         DB::raw("(SELECT SUM(IF(account_transactions.type='credit', amount, -1*amount))
                    //               FROM account_transactions
                    //               WHERE account_transactions.account_id = accounts.id
                    //               AND deleted_at IS NULL) as balance")
                    //     )
                    //     ->get()
                    //     ->map(fn($item) => [
                    //         'name'    => $item->name,
                    //         'id'      => $item->id,
                    //         'balance' => $this->commonUtil->num_f($item->balance, true),
                    //     ])
                    //     ->toArray();

                    TelegramNotification::depositAccountMessage(
                        $account,
                        $amount,
                        $new_balance,
                        $from_account,
                        $credit_data['operation_date'],
                        $note,
                        // $all_account,
                        'payment_accoun',
                        $location_code
                    );
                } catch (\Exception $te) {
                    \Log::warning('Telegram deposit notification failed: ' . $te->getMessage());
                }
                // ── End Telegram ───────────────────────────────────
            }

            $output = [
                'success' => true,
                'msg'     => __('account.deposited_successfully'),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = [
                'success' => false,
                'msg'     => __('messages.something_went_wrong'),
            ];
        }

        return $output;
    }

    /**
     * Calculates account current balance.
     *
     * @param  int  $id
     * @return json
     */
    public function getAccountBalance($id)
    {
        if (! auth()->user()->can('account.access')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = session()->get('user.business_id');
        $account = Account::leftjoin(
            'account_transactions as AT',
            'AT.account_id',
            '=',
            'accounts.id'
        )
            ->whereNull('AT.deleted_at')
            ->where(function ($q) {
                $q->whereNull('AT.status')->orWhere('AT.status', 'final');
            })
            ->where('accounts.business_id', $business_id)
            ->where('accounts.id', $id)
            ->select('accounts.*', DB::raw("SUM( IF(AT.type='credit', amount, -1 * amount) ) as balance"))
            ->first();

        return $account;
    }

    /**
     * Show the specified resource.
     *
     * @return Response
     */
    public function cashFlow()
    {
        if (! auth()->user()->can('account.access')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');

        if (request()->ajax()) {
            $accounts = AccountTransaction::join(
                'accounts as A',
                'account_transactions.account_id',
                '=',
                'A.id'
            )
                ->leftjoin(
                    'transaction_payments as TP',
                    'account_transactions.transaction_payment_id',
                    '=',
                    'TP.id'
                )
                ->leftjoin(
                    'transaction_payments as child_payments',
                    'TP.id',
                    '=',
                    'child_payments.parent_id'
                )
                ->leftjoin(
                    'transactions as child_sells',
                    'child_sells.id',
                    '=',
                    'child_payments.transaction_id'
                )
                ->leftJoin('users AS u', 'account_transactions.created_by', '=', 'u.id')
                ->leftJoin('contacts AS c', 'TP.payment_for', '=', 'c.id')
                ->where('A.business_id', $business_id)
                ->with(['transaction', 'transaction.contact', 'transfer_transaction', 'transaction.transaction_for'])
                ->select([
                    'account_transactions.type',
                    'account_transactions.amount',
                    'operation_date',
                    'account_transactions.sub_type',
                    'transfer_transaction_id',
                    'account_transactions.transaction_id',
                    'account_transactions.id',
                    'A.name as account_name',
                    'TP.payment_ref_no as payment_ref_no',
                    'TP.is_return',
                    'TP.is_advance',
                    'TP.method',
                    'TP.transaction_no',
                    'TP.card_transaction_number',
                    'TP.card_number',
                    'TP.card_type',
                    'TP.card_holder_name',
                    'TP.card_month',
                    'TP.card_year',
                    'TP.card_security',
                    'TP.cheque_number',
                    'TP.bank_account_number',
                    'account_transactions.account_id',
                    DB::raw("CONCAT(COALESCE(u.surname, ''),' ',COALESCE(u.first_name, ''),' ',COALESCE(u.last_name,'')) as added_by"),
                    'c.name as payment_for_contact',
                    'c.type as payment_for_type',
                    'c.supplier_business_name as payment_for_business_name',
                    DB::raw('SUM(child_payments.amount) total_recovered'),
                    DB::raw("GROUP_CONCAT(child_sells.invoice_no SEPARATOR ', ') as child_sells"),
                ])
                ->groupBy('account_transactions.id')
                ->orderBy('account_transactions.operation_date', 'asc');
            if (! empty(request()->input('type'))) {
                $accounts->where('account_transactions.type', request()->input('type'));
            }

            $permitted_locations = auth()->user()->permitted_locations();
            $account_ids = [];
            if ($permitted_locations != 'all') {
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

            if ($permitted_locations != 'all') {
                $accounts->whereIn('A.id', $account_ids);
            }

            $location_id = request()->input('location_id');
            if (! empty($location_id)) {
                $location = BusinessLocation::find($location_id);
                if (! empty($location->default_payment_accounts)) {
                    $default_payment_accounts = json_decode($location->default_payment_accounts, true);
                    $account_ids = [];
                    foreach ($default_payment_accounts as $key => $account) {
                        if (! empty($account['is_enabled']) && ! empty($account['account'])) {
                            $account_ids[] = $account['account'];
                        }
                    }

                    $accounts->whereIn('A.id', $account_ids);
                }
            }

            if (! empty(request()->input('account_id'))) {
                $accounts->where('A.id', request()->input('account_id'));
            }

            $start_date = request()->input('start_date');
            $end_date = request()->input('end_date');

            if (! empty($start_date) && ! empty($end_date)) {
                $accounts->whereBetween(DB::raw('date(operation_date)'), [$start_date, $end_date]);
            }

            if (request()->has('only_payment_recovered')) {
                //payment date is today and transaction date is less than today
                $accounts->leftJoin('transactions AS t', 'TP.transaction_id', '=', 't.id')
                    ->whereDate('operation_date', '=', \Carbon::now()->format('Y-m-d'))
                    ->where(function ($q) {
                        $q->whereDate(
                            't.transaction_date',
                            '<',
                            \Carbon::now()->format('Y-m-d')
                        )
                            ->orWhere('TP.is_advance', 1);
                    });
            }

            $payment_types = $this->commonUtil->payment_types(null, true, $business_id);

            return DataTables::of($accounts)
                ->editColumn('method', function ($row) use ($payment_types) {
                    if (! empty($row->method) && isset($payment_types[$row->method])) {
                        return $payment_types[$row->method];
                    } else {
                        return '';
                    }
                })
                ->addColumn('payment_details', function ($row) {
                    $arr = [];
                    if (! empty($row->transaction_no)) {
                        $arr[] = '<b>' . __('lang_v1.transaction_no') . '</b>: ' . $row->transaction_no;
                    }

                    if ($row->method == 'card' && ! empty($row->card_transaction_number)) {
                        $arr[] = '<b>' . __('lang_v1.card_transaction_no') . '</b>: ' . $row->card_transaction_number;
                    }

                    if ($row->method == 'card' && ! empty($row->card_number)) {
                        $arr[] = '<b>' . __('lang_v1.card_no') . '</b>: ' . $row->card_number;
                    }
                    if ($row->method == 'card' && ! empty($row->card_type)) {
                        $arr[] = '<b>' . __('lang_v1.card_type') . '</b>: ' . $row->card_type;
                    }
                    if ($row->method == 'card' && ! empty($row->card_holder_name)) {
                        $arr[] = '<b>' . __('lang_v1.card_holder_name') . '</b>: ' . $row->card_holder_name;
                    }
                    if ($row->method == 'card' && ! empty($row->card_month)) {
                        $arr[] = '<b>' . __('lang_v1.month') . '</b>: ' . $row->card_month;
                    }
                    if ($row->method == 'card' && ! empty($row->card_year)) {
                        $arr[] = '<b>' . __('lang_v1.year') . '</b>: ' . $row->card_year;
                    }
                    if ($row->method == 'card' && ! empty($row->card_security)) {
                        $arr[] = '<b>' . __('lang_v1.security_code') . '</b>: ' . $row->card_security;
                    }
                    if (! empty($row->cheque_number)) {
                        $arr[] = '<b>' . __('lang_v1.cheque_no') . '</b>: ' . $row->cheque_number;
                    }
                    if (! empty($row->bank_account_number)) {
                        $arr[] = '<b>' . __('lang_v1.card_no') . '</b>: ' . $row->bank_account_number;
                    }

                    return implode(', ', $arr);
                })
                ->addColumn('debit', '@if($type == "debit")<span class="debit" data-orig-value="{{$amount}}">@format_currency($amount)</span>@endif')
                ->addColumn('credit', '@if($type == "credit")<span class="debit" data-orig-value="{{$amount}}">@format_currency($amount)</span>@endif')
                ->addColumn('balance', function ($row) {
                    $balance = AccountTransaction::where(
                        'account_id',
                        $row->account_id
                    )
                        ->where('operation_date', '<=', $row->operation_date)
                        ->whereNull('deleted_at')
                        ->select(DB::raw("SUM(IF(type='credit', amount, -1 * amount)) as balance"))
                        ->first()->balance;

                    return '<span class="balance" data-orig-value="' . $balance . '">' . $this->commonUtil->num_f($balance, true) . '</span>';
                })
                ->addColumn('total_balance', function ($row) use ($business_id, $account_ids, $permitted_locations) {
                    $query = AccountTransaction::join(
                        'accounts as A',
                        'account_transactions.account_id',
                        '=',
                        'A.id'
                    )
                        ->where('A.business_id', $business_id)
                        ->where('operation_date', '<=', $row->operation_date)
                        ->whereNull('account_transactions.deleted_at')
                        ->select(DB::raw("SUM(IF(type='credit', amount, -1 * amount)) as balance"));

                    if (! empty(request()->input('type'))) {
                        $query->where('type', request()->input('type'));
                    }
                    if ($permitted_locations != 'all' || ! empty(request()->input('location_id'))) {
                        $query->whereIn('A.id', $account_ids);
                    }

                    if (! empty(request()->input('account_id'))) {
                        $query->where('A.id', request()->input('account_id'));
                    }

                    $balance = $query->first()->balance;

                    return '<span class="total_balance" data-orig-value="' . $balance . '">' . $this->commonUtil->num_f($balance, true) . '</span>';
                })
                ->editColumn('operation_date', function ($row) {
                    return $this->commonUtil->format_date($row->operation_date, true);
                })
                ->editColumn('sub_type', function ($row) {
                    return $this->__getPaymentDetails($row);
                })
                ->removeColumn('id')
                ->rawColumns(['credit', 'debit', 'balance', 'sub_type', 'total_balance', 'payment_details'])
                ->make(true);
        }
        $accounts = Account::forDropdown($business_id, false);

        $business_locations = BusinessLocation::forDropdown($business_id, true);

        return view('account.cash_flow')
            ->with(compact('accounts', 'business_locations'));
    }

    public function __getPaymentDetails($row)
    {
        $details = '';
        if (! empty($row->sub_type)) {
            $details = __('account.' . $row->sub_type);
            if (in_array($row->sub_type, ['fund_transfer', 'deposit']) && ! empty($row->transfer_transaction)) {
                if ($row->type == 'credit') {
                    $details .= ' ( ' . __('account.from') . ': ' . $row->transfer_transaction->account->name . ')';
                } else {
                    $details .= ' ( ' . __('account.to') . ': ' . $row->transfer_transaction->account->name . ')';
                }
            }
        } else {
            if (! empty($row->transaction->type)) {
                if ($row->transaction->type == 'purchase') {
                    $details = __('lang_v1.purchase') . '<br><b>' . __('purchase.supplier') . ':</b> ' . $row->transaction->contact->full_name_with_business . '<br><b>' .
                        __('purchase.ref_no') . ':</b> <a href="#" data-href="' . action([\App\Http\Controllers\PurchaseController::class, 'show'], [$row->transaction->id]) . '" class="btn-modal" data-container=".view_modal">' . $row->transaction->ref_no . '</a>';
                } elseif ($row->transaction->type == 'expense') {
                    $details = __('lang_v1.expense') . '<br><b>' . __('purchase.ref_no') . ':</b>' . $row->transaction->ref_no;
                } elseif ($row->transaction->type == 'sell') {
                    $is_return = $row->is_return == 1 ? ' (' . __('lang_v1.change_return') . ')' : '';
                    $details = __('sale.sale') . $is_return . '<br><b>' . __('contact.customer') . ':</b> ' . $row->transaction->contact->full_name_with_business . '<br><b>' .
                        __('sale.invoice_no') . ':</b> <a href="#" data-href="' . action([\App\Http\Controllers\SellController::class, 'show'], [$row->transaction->id]) . '" class="btn-modal" data-container=".view_modal">' . $row->transaction->invoice_no . '</a>';
                }
            } else {
                //for contact payment which is not advance
                if ($row->is_advance != 1) {
                    if ($row->payment_for_type == 'supplier') {
                        $details .= '<b>' . __('purchase.supplier') . ':</b> ';
                    } elseif ($row->payment_for_type == 'customer') {
                        $details .= '<b>' . __('contact.customer') . ':</b> ';
                    } else {
                        $details .= '<b>' . __('account.payment_for') . ':</b> ';
                    }

                    if (! empty($row->payment_for_business_name)) {
                        $details .= $row->payment_for_business_name . ', ';
                    }
                    if (! empty($row->payment_for_contact)) {
                        $details .= $row->payment_for_contact;
                    }
                }
            }
        }

        if (! empty($row->payment_ref_no)) {
            if (! empty($details)) {
                $details .= '<br/>';
            }

            $details .= '<b>' . __('lang_v1.pay_reference_no') . ':</b> ' . $row->payment_ref_no;
        }
        if (! empty($row->transaction->contact) && $row->transaction->type == 'expense') {
            if (! empty($details)) {
                $details .= '<br/>';
            }

            $details .= '<b>';
            $details .= __('lang_v1.expense_for_contact');
            $details .= ':</b> ' . $row->transaction->contact->full_name_with_business;
        }

        if (! empty($row->transaction->transaction_for)) {
            if (! empty($details)) {
                $details .= '<br/>';
            }

            $details .= '<b>' . __('expense.expense_for') . ':</b> ' . $row->transaction->transaction_for->user_full_name;
        }

        if ($row->is_advance == 1) {
            $total_advance = $row->amount - $row->total_recovered;
            $details .= '<br>';

            if ($total_advance > 0) {
                $details .= '<b>' . __('lang_v1.advance_payment') . '</b>: ' . $this->commonUtil->num_f($total_advance, true) . '<br>';
            }

            if (! empty($row->child_sells)) {
                $details .= '<b>' . __('lang_v1.payments_recovered_for') . '</b>: ' . $row->child_sells . '<br>';
            }

            if ($row->payment_for_type == 'supplier') {
                $details .= '<b>' . __('purchase.supplier') . ':</b> ';
            } elseif ($row->payment_for_type == 'customer') {
                $details .= '<b>' . __('contact.customer') . ':</b> ';
            } else {
                $details .= '<b>' . __('account.payment_for') . ':</b> ';
            }

            if (! empty($row->payment_for_business_name)) {
                $details .= $row->payment_for_business_name . ', ';
            }
            if (! empty($row->payment_for_contact)) {
                $details .= $row->payment_for_contact;
            }
        }

        if (! empty($row->added_by)) {
            $details .= '<br><b>' . __('lang_v1.added_by') . ':</b> ' . $row->added_by;
        }

        return $details;
    }

    /**
     * activate the specified account.
     *
     * @return Response
     */
    public function activate($id)
    {
        if (! auth()->user()->can('account.close') && ! auth()->user()->can('close_account') && ! auth()->user()->can('account.activate') && ! auth()->user()->can('activate_account')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            try {
                $business_id = session()->get('user.business_id');

                $account = Account::where('business_id', $business_id)
                    ->findOrFail($id);

                $account->is_closed = 0;
                $account->save();

                $output = [
                    'success' => true,
                    'msg' => __('lang_v1.success'),
                ];
            } catch (\Exception $e) {
                \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

                $output = [
                    'success' => false,
                    'msg' => __('messages.something_went_wrong'),
                ];
            }

            return $output;
        }
    }

    /**
     * Edit the specified resource from storage.
     *
     * @return Response
     */
    public function editAccountTransaction($id)
    {
        $business_id = request()->session()->get('user.business_id');
        $account_transaction = AccountTransaction::with(['account', 'transfer_transaction'])->findOrFail($id);

        $is_pending = ($account_transaction->status == 'pending') || (! empty($account_transaction->transfer_transaction) && $account_transaction->transfer_transaction->status == 'pending');

        if ($is_pending) {
            if (! auth()->user()->can('account.edit_pending_transfer') && ! auth()->user()->can('superadmin')) {
                abort(403, 'Unauthorized action.');
            }
        } else {
            if (! auth()->user()->can('edit_account_transaction') && ! auth()->user()->can('superadmin')) {
                abort(403, 'Unauthorized action.');
            }
        }

        $accounts = Account::where('business_id', $business_id)
            ->NotClosed()
            ->pluck('name', 'id');

        return view('account.edit_account_transaction')
            ->with(compact('accounts', 'account_transaction'));
    }

    public function updateAccountTransaction(Request $request, $id)
    {
        $business_id = request()->session()->get('user.business_id');
        $account_transaction = AccountTransaction::with(['transfer_transaction'])->findOrFail($id);

        $is_pending = ($account_transaction->status == 'pending') || (! empty($account_transaction->transfer_transaction) && $account_transaction->transfer_transaction->status == 'pending');

        if ($is_pending) {
            if (! auth()->user()->can('account.edit_pending_transfer') && ! auth()->user()->can('superadmin')) {
                abort(403, 'Unauthorized action.');
            }
        } else {
            if (! auth()->user()->can('edit_account_transaction') && ! auth()->user()->can('superadmin')) {
                abort(403, 'Unauthorized action.');
            }
        }

        try {
            $current_user_id = auth()->id();

            DB::beginTransaction();

            $amount = $this->commonUtil->num_uf($request->input('amount'));
            $note = $request->input('note');

            if (! \Illuminate\Support\Facades\Schema::hasColumn('account_transactions', 'last_edited_by')) {
                \Illuminate\Support\Facades\Schema::table('account_transactions', function ($table) {
                    $table->integer('last_edited_by')->nullable()->after('created_by');
                });
            }

            $account_transaction->amount = $amount;
            $account_transaction->operation_date = $this->commonUtil->uf_date($request->input('operation_date'), true);
            $account_transaction->note = $note;
            $account_transaction->last_edited_by = $current_user_id;

            if ($request->input('account_id')) {
                $account_transaction->account_id = $request->input('account_id');
            }

            $account_transaction->save();

            if (! empty($account_transaction->transfer_transaction)) {
                $transfer_transaction = $account_transaction->transfer_transaction;

                $transfer_transaction->amount = $amount;
                $transfer_transaction->operation_date = $account_transaction->operation_date;
                $transfer_transaction->note = $account_transaction->note;
                $transfer_transaction->last_edited_by = $current_user_id;

                if ($account_transaction->sub_type == 'deposit') {
                    $transfer_transaction->account_id = $request->input('from_account');
                }
                if ($account_transaction->sub_type == 'fund_transfer') {
                    $transfer_transaction->account_id = $request->input('to_account');
                }

                $transfer_transaction->save();
            }

            DB::commit();

            $output = [
                'success' => true,
                'msg' => __('lang_v1.success'),
            ];
        } catch (\Exception $e) {
            DB::rollBack();

            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return $output;
    }

    /**
     * Get Pending Transfers modal for an account
     *
     * @param int $id
     * @return Response
     */
    public function getPendingTransfers($id)
    {
        if (! auth()->user()->can('account.access')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = session()->get('user.business_id');
        $account = Account::where('business_id', $business_id)->findOrFail($id);

        $pending_transactions = AccountTransaction::where('account_id', $id)
            ->where('status', 'pending')
            ->whereNull('deleted_at')
            ->with(['transfer_transaction', 'transfer_transaction.account', 'user', 'last_editor', 'transfer_transaction.last_editor'])
            ->orderBy('operation_date', 'desc')
            ->get();

        return view('account.pending_transfers_modal')
            ->with(compact('account', 'pending_transactions'));
    }

    /**
     * Change transfer transaction status (Approve / Reject)
     *
     * @param  Request  $request
     * @param  int  $id
     * @return Response
     */
    public function changeTransferStatus(Request $request, $id)
    {
        $status = $request->input('status');

        if ($status == 'final') {
            if (! auth()->user()->can('account.accept_pending_transfer') && ! auth()->user()->can('superadmin')) {
                abort(403, 'Unauthorized action.');
            }
        } elseif ($status == 'rejected') {
            if (! auth()->user()->can('account.reject_pending_transfer') && ! auth()->user()->can('superadmin')) {
                abort(403, 'Unauthorized action.');
            }
        } else {
            abort(403, 'Unauthorized action.');
        }

        try {
            $business_id = session()->get('user.business_id');

            if (! in_array($status, ['final', 'rejected'])) {
                return [
                    'success' => false,
                    'msg' => __('messages.something_went_wrong'),
                ];
            }

            DB::beginTransaction();

            $transaction = AccountTransaction::with(['transfer_transaction'])->findOrFail($id);

            $is_pending = ($transaction->status == 'pending') || (! empty($transaction->transfer_transaction) && $transaction->transfer_transaction->status == 'pending');
            $current_user_id = auth()->id();
            $is_creator = ($transaction->created_by == $current_user_id) || (! empty($transaction->transfer_transaction) && $transaction->transfer_transaction->created_by == $current_user_id);
            $is_last_editor = (! empty($transaction->last_edited_by) && $transaction->last_edited_by == $current_user_id) || (! empty($transaction->transfer_transaction) && ! empty($transaction->transfer_transaction->last_edited_by) && $transaction->transfer_transaction->last_edited_by == $current_user_id);

            if ($is_pending && $is_creator) {
                return [
                    'success' => false,
                    'msg' => __('account.creator_cannot_approve'),
                ];
            }

            if ($is_pending && $is_last_editor) {
                return [
                    'success' => false,
                    'msg' => __('account.editor_cannot_approve'),
                ];
            }

            $transfer_transaction = ! empty($transaction->transfer_transaction_id) 
                ? AccountTransaction::find($transaction->transfer_transaction_id) 
                : null;

            $transaction->status = $status;
            $transaction->save();

            if (! empty($transfer_transaction)) {
                $transfer_transaction->status = $status;
                $transfer_transaction->save();
            }

            DB::commit();

            $msg = ($status == 'final') 
                ? __('account.transfer_approved_success') 
                : __('account.transfer_rejected_success');

            $output = [
                'success' => true,
                'msg' => $msg,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return $output;
    }
}
