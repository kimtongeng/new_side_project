<?php

namespace Modules\Loan\Http\Controllers;

use App\Account;
use App\Brands;
use App\Business;
use App\BusinessLocation;
use App\Category;
use App\Contact;
use App\CustomerGroup;
use App\InvoiceLayout;
use App\InvoiceScheme;
use App\Media;
use App\Product;
use App\SellingPriceGroup;
use App\TaxRate;
use App\Transaction;
use App\TypesOfService;
use App\User;
use App\Utils\BusinessUtil;
use App\Utils\CashRegisterUtil;
use App\Utils\ContactUtil;
use App\Utils\ModuleUtil;
use App\Utils\NotificationUtil;
use App\Utils\ProductUtil;
use App\Utils\TransactionUtil;
use App\Warranty;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Loan\Utils\LoanUtil;

class LoanPosController extends Controller
{
    protected $contactUtil;
    protected $productUtil;
    protected $businessUtil;
    protected $transactionUtil;
    protected $cashRegisterUtil;
    protected $moduleUtil;
    protected $notificationUtil;
    protected $loanUtil;

    public function __construct(
        ContactUtil $contactUtil,
        ProductUtil $productUtil,
        BusinessUtil $businessUtil,
        TransactionUtil $transactionUtil,
        CashRegisterUtil $cashRegisterUtil,
        ModuleUtil $moduleUtil,
        NotificationUtil $notificationUtil,
        LoanUtil $loanUtil
    ) {
        $this->contactUtil = $contactUtil;
        $this->productUtil = $productUtil;
        $this->businessUtil = $businessUtil;
        $this->transactionUtil = $transactionUtil;
        $this->cashRegisterUtil = $cashRegisterUtil;
        $this->moduleUtil = $moduleUtil;
        $this->notificationUtil = $notificationUtil;
        $this->loanUtil = $loanUtil;

        $this->dummyPaymentLine = [
            'method' => 'cash',
            'amount' => 0,
            'note' => '',
            'card_transaction_number' => '',
            'card_number' => '',
            'card_type' => '',
            'card_holder_name' => '',
            'card_month' => '',
            'card_year' => '',
            'card_security' => '',
            'cheque_number' => '',
            'bank_account_number' => '',
            'is_return' => 0,
            'transaction_no' => ''
        ];
    }

    /**
     * Display the POS page.
     */
    public function index()
    {
        if (!auth()->user()->can('sell.view') && !auth()->user()->can('sell.create')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $business_locations = BusinessLocation::forDropdown($business_id, false);
        $customers = Contact::customersDropdown($business_id, false);
        $sales_representative = User::forDropdown($business_id, false, false, true);

        $is_cmsn_agent_enabled = request()->session()->get('business.sales_cmsn_agnt');
        $commission_agents = [];
        if (!empty($is_cmsn_agent_enabled)) {
            $commission_agents = User::forDropdown($business_id, false, true, true);
        }

        $is_tables_enabled = $this->transactionUtil->isModuleEnabled('tables');
        $is_service_staff_enabled = $this->transactionUtil->isModuleEnabled('service_staff');
        $service_staffs = $is_service_staff_enabled ? $this->productUtil->serviceStaffDropdown($business_id) : null;
        $is_types_service_enabled = $this->moduleUtil->isModuleEnabled('types_of_service');
        $shipping_statuses = $this->transactionUtil->shipping_statuses();

        return view('Loan::pos.index')->with(compact(
            'business_locations',
            'customers',
            'sales_representative',
            'is_cmsn_agent_enabled',
            'commission_agents',
            'service_staffs',
            'is_tables_enabled',
            'is_service_staff_enabled',
            'is_types_service_enabled',
            'shipping_statuses'
        ));
    }

    /**
     * Show the form for creating a new POS sale.
     */
    public function create()
    {
        $business_id = request()->session()->get('user.business_id');

        if (!(auth()->user()->can('superadmin') || auth()->user()->can('sell.create'))) {
            abort(403, 'Unauthorized action.');
        }

        if (!$this->moduleUtil->isSubscribed($business_id)) {
            return $this->moduleUtil->expiredResponse(action([\Modules\Loan\Http\Controllers\LoanPosController::class, 'index']));
        } elseif (!$this->moduleUtil->isQuotaAvailable('invoices', $business_id)) {
            return $this->moduleUtil->quotaExpiredResponse('invoices', $business_id, action([\Modules\Loan\Http\Controllers\LoanPosController::class, 'index']));
        }

        if ($this->cashRegisterUtil->countOpenedRegister() == 0) {
            return redirect()->action([\App\Http\Controllers\CashRegisterController::class, 'create']);
        }

        $register_details = $this->cashRegisterUtil->getCurrentCashRegister(auth()->user()->id);
        $walk_in_customer = $this->contactUtil->getWalkInCustomer($business_id);
        $business_details = $this->businessUtil->getDetails($business_id);
        $taxes = TaxRate::forBusinessDropdown($business_id, true, true);
        $payment_lines[] = $this->dummyPaymentLine;
        $default_location = !empty($register_details->location_id) ? BusinessLocation::findOrFail($register_details->location_id) : null;

        $business_locations = BusinessLocation::forDropdown($business_id, false, true);
        $bl_attributes = $business_locations['attributes'];
        $business_locations = $business_locations['locations'];

        if (empty($default_location)) {
            foreach ($business_locations as $id => $name) {
                $default_location = BusinessLocation::findOrFail($id);
                break;
            }
        }

        $payment_types = $this->productUtil->payment_types(null, true, $business_id);
        $shortcuts = json_decode($business_details->keyboard_shortcuts, true);
        $pos_settings = empty($business_details->pos_settings) ? $this->businessUtil->defaultPosSettings() : json_decode($business_details->pos_settings, true);

        $commsn_agnt_setting = $business_details->sales_cmsn_agnt;
        $commission_agent = [];
        if ($commsn_agnt_setting == 'user') {
            $commission_agent = User::forDropdown($business_id, false);
        } elseif ($commsn_agnt_setting == 'cmsn_agnt') {
            $commission_agent = User::saleCommissionAgentsDropdown($business_id, false);
        }

        $categories = (request()->session()->get('business.enable_category') == 1) ? Category::catAndSubCategories($business_id) : false;
        $brands = (request()->session()->get('business.enable_brand') == 1) ? Brands::forDropdown($business_id)->prepend(__('lang_v1.all_brands'), 'all') : false;
        $change_return = $this->dummyPaymentLine;
        $types = Contact::getContactTypes();
        $customer_groups = CustomerGroup::forDropdown($business_id);
        $accounts = $this->moduleUtil->isModuleEnabled('account') ? Account::forDropdown($business_id, true, false, true, !empty($default_location->id) ? $default_location->id : null) : [];
        $price_groups = SellingPriceGroup::forDropdown($business_id);
        $default_price_group_id = !empty($default_location->selling_price_group_id) && array_key_exists($default_location->selling_price_group_id, $price_groups) ? $default_location->selling_price_group_id : null;

        $types_of_service = $this->moduleUtil->isModuleEnabled('types_of_service') ? TypesOfService::forDropdown($business_id) : [];
        $shipping_statuses = $this->transactionUtil->shipping_statuses();
        $default_datetime = $this->businessUtil->format_date('now', true);
        $featured_products = !empty($default_location) ? $default_location->getFeaturedProducts() : [];
        $invoice_layouts = InvoiceLayout::forDropdown($business_id);
        $invoice_schemes = InvoiceScheme::forDropdown($business_id);
        $default_invoice_schemes = InvoiceScheme::getDefault($business_id);

        $edit_discount = auth()->user()->can('edit_product_discount_from_pos_screen');
        $edit_price = auth()->user()->can('edit_product_price_from_pos_screen');
        $users = config('constants.enable_contact_assign') ? User::forDropdown($business_id, false, false, false, true) : [];

        return view('Loan::pos.create')->with(compact(
            'edit_discount',
            'edit_price',
            'business_locations',
            'bl_attributes',
            'business_details',
            'taxes',
            'payment_types',
            'walk_in_customer',
            'payment_lines',
            'default_location',
            'shortcuts',
            'commission_agent',
            'categories',
            'brands',
            'pos_settings',
            'change_return',
            'types',
            'customer_groups',
            'accounts',
            'price_groups',
            'types_of_service',
            'default_price_group_id',
            'shipping_statuses',
            'default_datetime',
            'featured_products',
            'invoice_schemes',
            'default_invoice_schemes',
            'invoice_layouts',
            'users'
        ));
    }

    /**
     * Store a newly created POS sale.
     */
    public function store(Request $request)
    {
        if (!auth()->user()->can('sell.create') && !auth()->user()->can('direct_sell.access')) {
            abort(403, 'Unauthorized action.');
        }

        $is_direct_sale = !empty($request->input('is_direct_sale'));
        if (!$is_direct_sale && $this->cashRegisterUtil->countOpenedRegister() == 0) {
            return redirect()->action([\App\Http\Controllers\CashRegisterController::class, 'create']);
        }

        try {
            $input = $request->except('_token');
            $input['is_quotation'] = $input['status'] == 'quotation' ? 1 : 0;
            if ($input['status'] == 'quotation') {
                $input['status'] = 'draft';
                $input['sub_status'] = 'quotation';
            } elseif ($input['status'] == 'proforma') {
                $input['status'] = 'draft';
                $input['sub_status'] = 'proforma';
            }

            $change_return = $this->dummyPaymentLine;
            if (!empty($input['payment']['change_return'])) {
                $change_return = $input['payment']['change_return'];
                unset($input['payment']['change_return']);
            }

            $is_credit_limit_exeeded = $this->transactionUtil->isCustomerCreditLimitExeeded($input);
            if ($is_credit_limit_exeeded !== false) {
                $credit_limit_amount = $this->transactionUtil->num_f($is_credit_limit_exeeded, true);
                $output = [
                    'success' => 0,
                    'msg' => __('lang_v1.cutomer_credit_limit_exeeded', ['credit_limit' => $credit_limit_amount])
                ];
                return $is_direct_sale ? redirect()->action([\Modules\Loan\Http\Controllers\LoanPosController::class, 'index'])->with('status', $output) : $output;
            }

            if (!empty($input['products'])) {
                $business_id = $request->session()->get('user.business_id');
                if (!$this->moduleUtil->isSubscribed($business_id)) {
                    return $this->moduleUtil->expiredResponse();
                } elseif (!$this->moduleUtil->isQuotaAvailable('invoices', $business_id)) {
                    return $this->moduleUtil->quotaExpiredResponse('invoices', $business_id, action([\Modules\Loan\Http\Controllers\LoanPosController::class, 'index']));
                }

                $user_id = $request->session()->get('user.id');
                $discount = [
                    'discount_type' => $input['discount_type'],
                    'discount_amount' => $input['discount_amount']
                ];
                $invoice_total = $this->productUtil->calculateInvoiceTotal($input['products'], $input['tax_rate_id'], $discount);

                DB::beginTransaction();

                $input['transaction_date'] = empty($request->input('transaction_date')) ? \Carbon::now() : $this->productUtil->uf_date($request->input('transaction_date'), true);
                if ($is_direct_sale) {
                    $input['is_direct_sale'] = 1;
                }

                $input['commission_agent'] = !empty($request->input('commission_agent')) ? $request->input('commission_agent') : ($request->session()->get('business.sales_cmsn_agnt') == 'logged_in_user' ? $user_id : null);
                $input['exchange_rate'] = isset($input['exchange_rate']) && $this->transactionUtil->num_uf($input['exchange_rate']) == 0 ? 1 : $input['exchange_rate'];

                $contact_id = $request->get('contact_id', null);
                $cg = $this->contactUtil->getCustomerGroup($business_id, $contact_id);
                $input['customer_group_id'] = (empty($cg) || empty($cg->id)) ? null : $cg->id;

                $price_group_id = $request->has('price_group') ? $request->input('price_group') : ($request->has('default_price_group') ? $request->input('default_price_group') : null);
                $input['is_suspend'] = isset($input['is_suspend']) && $input['is_suspend'] == 1 ? 1 : 0;
                if ($input['is_suspend']) {
                    $input['sale_note'] = !empty($input['additional_notes']) ? $input['additional_notes'] : null;
                }

                if (!empty($input['is_recurring'])) {
                    $ref_count = $this->transactionUtil->setAndGetReferenceCount('subscription');
                    $input['subscription_no'] = $this->transactionUtil->generateReferenceNumber('subscription', $ref_count);
                }

                if (!empty($request->input('invoice_scheme_id'))) {
                    $input['invoice_scheme_id'] = $request->input('invoice_scheme_id');
                }

                if ($this->moduleUtil->isModuleEnabled('types_of_service')) {
                    $input['types_of_service_id'] = $request->input('types_of_service_id');
                    $price_group_id = !empty($request->input('types_of_service_price_group')) ? $request->input('types_of_service_price_group') : $price_group_id;
                    $input['packing_charge'] = !empty($request->input('packing_charge')) ? $this->transactionUtil->num_uf($request->input('packing_charge')) : 0;
                    $input['packing_charge_type'] = $request->input('packing_charge_type');
                    $input['service_custom_field_1'] = $request->input('service_custom_field_1') ?? null;
                    $input['service_custom_field_2'] = $request->input('service_custom_field_2') ?? null;
                    $input['service_custom_field_3'] = $request->input('service_custom_field_3') ?? null;
                    $input['service_custom_field_4'] = $request->input('service_custom_field_4') ?? null;
                    $input['service_custom_field_5'] = $request->input('service_custom_field_5') ?? null;
                    $input['service_custom_field_6'] = $request->input('service_custom_field_6') ?? null;
                }

                for ($i = 1; $i <= 4; $i++) {
                    if ($request->input("additional_expense_value_$i") != '') {
                        $input["additional_expense_key_$i"] = $request->input("additional_expense_key_$i");
                        $input["additional_expense_value_$i"] = $request->input("additional_expense_value_$i");
                    }
                }

                $input['selling_price_group_id'] = $price_group_id;
                if ($this->transactionUtil->isModuleEnabled('tables')) {
                    $input['res_table_id'] = $request->get('res_table_id');
                }
                if ($this->transactionUtil->isModuleEnabled('service_staff')) {
                    $input['res_waiter_id'] = $request->get('res_waiter_id');
                }
                if ($this->transactionUtil->isModuleEnabled('kitchen')) {
                    $input['is_kitchen_order'] = $request->get('is_kitchen_order');
                }

                $input['document'] = $this->transactionUtil->uploadFile($request, 'sell_document', 'documents');
                $transaction = $this->transactionUtil->createSellTransaction($business_id, $input, $invoice_total, $user_id);

                // Upload Shipping documents
                Media::uploadMedia($business_id, $transaction, $request, 'shipping_documents', false, 'shipping_document');

                $this->transactionUtil->createOrUpdateSellLines($transaction, $input['products'], $input['location_id']);
                $change_return['amount'] = $input['change_return'] ?? 0;
                $change_return['is_return'] = 1;
                $input['payment'][] = $change_return;

                $is_credit_sale = isset($input['is_credit_sale']) && $input['is_credit_sale'] == 1;
                if (!$transaction->is_suspend && !empty($input['payment']) && !$is_credit_sale) {
                    $this->transactionUtil->createOrUpdatePaymentLines($transaction, $input['payment']);
                }

                if ($input['status'] == 'final') {
                    if (!$is_direct_sale) {
                        foreach ($input['products'] as $product_line) {
                            if (!empty($product_line['res_service_staff_id'])) {
                                $product = Product::find($product_line['product_id']);
                                if (!empty($product->preparation_time_in_minutes)) {
                                    $service_staff = User::find($product_line['res_service_staff_id']);
                                    $base_time = \Carbon::parse($transaction->transaction_date);
                                    if (!empty($service_staff->available_at) && \Carbon::parse($service_staff->available_at)->gt(\Carbon::now())) {
                                        $base_time = \Carbon::parse($service_staff->available_at);
                                    }
                                    $total_minutes = $product->preparation_time_in_minutes * $this->transactionUtil->num_uf($product_line['quantity']);
                                    $service_staff->available_at = $base_time->addMinutes($total_minutes);
                                    $service_staff->save();
                                }
                            }
                        }
                    }

                    foreach ($input['products'] as $product) {
                        $decrease_qty = $this->productUtil->num_uf($product['quantity']);
                        if (!empty($product['base_unit_multiplier'])) {
                            $decrease_qty *= $product['base_unit_multiplier'];
                        }
                        if ($product['enable_stock']) {
                            $this->productUtil->decreaseProductQuantity(
                                $product['product_id'],
                                $product['variation_id'],
                                $input['location_id'],
                                $decrease_qty
                            );
                        }
                        if ($product['product_type'] == 'combo') {
                            $this->productUtil->decreaseProductQuantityCombo($product['combo'], $input['location_id']);
                        }
                    }

                    if (!$is_direct_sale && !$transaction->is_suspend && !empty($input['payment']) && !$is_credit_sale) {
                        $this->cashRegisterUtil->addSellPayments($transaction, $input['payment']);
                    }

                    $payment_status = $this->transactionUtil->updatePaymentStatus($transaction->id, $transaction->final_total);
                    $transaction->payment_status = $payment_status;

                    if ($request->session()->get('business.enable_rp') == 1) {
                        $redeemed = !empty($input['rp_redeemed']) ? $input['rp_redeemed'] : 0;
                        $this->transactionUtil->updateCustomerRewardPoints($contact_id, $transaction->rp_earned, 0, $redeemed);
                    }

                    $business_details = $this->businessUtil->getDetails($business_id);
                    $pos_settings = empty($business_details->pos_settings) ? $this->businessUtil->defaultPosSettings() : json_decode($business_details->pos_settings, true);
                    $business = [
                        'id' => $business_id,
                        'accounting_method' => $request->session()->get('business.accounting_method'),
                        'location_id' => $input['location_id'],
                        'pos_settings' => $pos_settings
                    ];
                    $this->transactionUtil->mapPurchaseSell($business, $transaction->sell_lines, 'purchase');

                    $whatsapp_link = $this->notificationUtil->autoSendNotification($business_id, 'new_sale', $transaction, $transaction->contact);
                }

                if (!empty($transaction->sales_order_ids)) {
                    $this->transactionUtil->updateSalesOrderStatus($transaction->sales_order_ids);
                }

                $this->moduleUtil->getModuleData('after_sale_saved', ['transaction' => $transaction, 'input' => $input]);
                Media::uploadMedia($business_id, $transaction, $request, 'documents');

                $this->transactionUtil->activityLog($transaction, 'added');

                DB::commit();

                if ($request->input('is_save_and_print') == 1) {
                    $url = $this->transactionUtil->getInvoiceUrl($transaction->id, $business_id);
                    return redirect()->to($url . '?print_on_load=true');
                }

                $msg = trans('sale.pos_sale_added');
                $receipt = '';
                $invoice_layout_id = $request->input('invoice_layout_id');
                $print_invoice = false;
                if (!$is_direct_sale) {
                    if ($input['status'] == 'draft') {
                        $msg = trans('sale.draft_added');
                        if ($input['is_quotation'] == 1) {
                            $msg = trans('lang_v1.quotation_added');
                            $print_invoice = true;
                        }
                    } elseif ($input['status'] == 'final') {
                        $print_invoice = true;
                    }
                }

                if ($transaction->is_suspend == 1 && empty($pos_settings['print_on_suspend'])) {
                    $print_invoice = false;
                }

                if (!auth()->user()->can('print_invoice')) {
                    $print_invoice = false;
                }

                if ($print_invoice) {
                    $receipt = $this->receiptContent($business_id, $input['location_id'], $transaction->id, null, false, true, $invoice_layout_id);
                }

                $output = ['success' => 1, 'msg' => $msg, 'receipt' => $receipt];
                if (!empty($whatsapp_link)) {
                    $output['whatsapp_link'] = $whatsapp_link;
                }
            } else {
                $output = ['success' => 0, 'msg' => trans('messages.something_went_wrong')];
            }
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            $msg = trans('messages.something_went_wrong');
            if (get_class($e) == \App\Exceptions\PurchaseSellMismatch::class) {
                $msg = $e->getMessage();
            }
            if (get_class($e) == \App\Exceptions\AdvanceBalanceNotAvailable::class) {
                $msg = $e->getMessage();
            }
            $output = ['success' => 0, 'msg' => $msg];
        }

        return $is_direct_sale ? redirect()->action([\Modules\Loan\Http\Controllers\LoanPosController::class, 'index'])->with('status', $output) : $output;
    }

    /**
     * Returns the content for the receipt
     */
    private function receiptContent($business_id, $location_id, $transaction_id, $printer_type = null, $is_package_slip = false, $from_pos_screen = true, $invoice_layout_id = null, $is_delivery_note = false)
    {
        $output = [
            'is_enabled' => false,
            'print_type' => 'browser',
            'html_content' => null,
            'printer_config' => [],
            'data' => []
        ];

        $business_details = $this->businessUtil->getDetails($business_id);
        $location_details = BusinessLocation::find($location_id);

        if ($from_pos_screen && $location_details->print_receipt_on_invoice != 1) {
            return $output;
        }

        $output['is_enabled'] = true;
        $invoice_layout_id = !empty($invoice_layout_id) ? $invoice_layout_id : $location_details->invoice_layout_id;
        $invoice_layout = $this->businessUtil->invoiceLayout($business_id, $invoice_layout_id);
        $receipt_printer_type = is_null($printer_type) ? $location_details->receipt_printer_type : $printer_type;

        $receipt_details = $this->transactionUtil->getReceiptDetails($transaction_id, $location_id, $invoice_layout, $business_details, $location_details, $receipt_printer_type);
        $currency_details = [
            'symbol' => $business_details->currency_symbol,
            'thousand_separator' => $business_details->thousand_separator,
            'decimal_separator' => $business_details->decimal_separator
        ];
        $receipt_details->currency = $currency_details;

        if ($is_package_slip) {
            $output['html_content'] = view('Loan::pos.receipts.packing_slip', compact('receipt_details'))->render();
            return $output;
        }

        if ($is_delivery_note) {
            $output['html_content'] = view('Loan::pos.receipts.delivery_note', compact('receipt_details'))->render();
            return $output;
        }

        $output['print_title'] = $receipt_details->invoice_no;
        if ($receipt_printer_type == 'printer') {
            $output['print_type'] = 'printer';
            $output['printer_config'] = $this->businessUtil->printerConfig($business_id, $location_details->printer_id);
            $output['data'] = $receipt_details;
        } else {
            $layout = !empty($receipt_details->design) ? 'Loan::pos.receipts.' . $receipt_details->design : 'Loan::pos.receipts.classic';
            $output['html_content'] = view($layout, compact('receipt_details'))->render();
        }

        return $output;
    }
}