<?php

namespace Modules\Loan\Http\Controllers;

use App\Account;
use App\Business;
use App\BusinessLocation;
use App\Contact;
use App\CustomerGroup;
use App\InvoiceLayout;
use App\InvoiceScheme;
use App\Media;
use App\Product;
use App\SellingPriceGroup;
use App\TaxRate;
use App\Transaction;
use App\TransactionSellLine;
use App\User;
use App\Utils\BusinessUtil;
use App\Utils\CashRegisterUtil;
use App\Utils\ContactUtil;
use App\Utils\ModuleUtil;
use App\Utils\NotificationUtil;
use App\Utils\ProductUtil;
use App\Utils\TransactionUtil;
use App\Variation;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

class LoanPosEditController extends Controller
{
    protected $contactUtil;
    protected $productUtil;
    protected $businessUtil;
    protected $transactionUtil;
    protected $cashRegisterUtil;
    protected $moduleUtil;
    protected $notificationUtil;

    public function __construct(
        ContactUtil $contactUtil,
        ProductUtil $productUtil,
        BusinessUtil $businessUtil,
        TransactionUtil $transactionUtil,
        CashRegisterUtil $cashRegisterUtil,
        ModuleUtil $moduleUtil,
        NotificationUtil $notificationUtil
    ) {
        $this->contactUtil = $contactUtil;
        $this->productUtil = $productUtil;
        $this->businessUtil = $businessUtil;
        $this->transactionUtil = $transactionUtil;
        $this->cashRegisterUtil = $cashRegisterUtil;
        $this->moduleUtil = $moduleUtil;
        $this->notificationUtil = $notificationUtil;

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
     * Show the form for editing a POS sale.
     */
    public function edit($id)
    {
        $business_id = request()->session()->get('user.business_id');

        if (!(auth()->user()->can('superadmin') || auth()->user()->can('sell.update') || auth()->user()->can('edit_pos_payment'))) {
            abort(403, 'Unauthorized action.');
        }

        $edit_days = request()->session()->get('business.transaction_edit_days');
        if (!$this->transactionUtil->canBeEdited($id, $edit_days)) {
            return back()->with('status', ['success' => 0, 'msg' => __('messages.transaction_edit_not_allowed', ['days' => $edit_days])]);
        }

        if ($this->cashRegisterUtil->countOpenedRegister() == 0) {
            return redirect()->action([\App\Http\Controllers\CashRegisterController::class, 'create']);
        }

        if ($this->transactionUtil->isReturnExist($id)) {
            return back()->with('status', ['success' => 0, 'msg' => __('lang_v1.return_exist')]);
        }

        $walk_in_customer = $this->contactUtil->getWalkInCustomer($business_id);
        $business_details = $this->businessUtil->getDetails($business_id);
        $taxes = TaxRate::forBusinessDropdown($business_id, true, true);

        $transaction = Transaction::where('business_id', $business_id)
            ->where('type', 'sell')
            ->with(['price_group', 'types_of_service'])
            ->findOrFail($id);

        $location_id = $transaction->location_id;
        $business_location = BusinessLocation::find($location_id);
        $payment_types = $this->productUtil->payment_types($business_location, true);
        $location_printer_type = $business_location->receipt_printer_type;

        $sell_details = TransactionSellLine::join('products AS p', 'transaction_sell_lines.product_id', '=', 'p.id')
            ->join('variations AS variations', 'transaction_sell_lines.variation_id', '=', 'variations.id')
            ->join('product_variations AS pv', 'variations.product_variation_id', '=', 'pv.id')
            ->leftJoin('variation_location_details AS vld', function ($join) use ($location_id) {
                $join->on('variations.id', '=', 'vld.variation_id')->where('vld.location_id', '=', $location_id);
            })
            ->leftJoin('units', 'units.id', '=', 'p.unit_id')
            ->leftJoin('units as u', 'p.secondary_unit_id', '=', 'u.id')
            ->where('transaction_sell_lines.transaction_id', $id)
            ->with(['warranties'])
            ->select(
                DB::raw("IF(pv.is_dummy = 0, CONCAT(p.name, ' (', pv.name, ':',variations.name, ')'), p.name) AS product_name"),
                'p.id as product_id',
                'p.enable_stock',
                'p.image as product_image',
                'p.name as product_actual_name',
                'p.type as product_type',
                'pv.name as product_variation_name',
                'pv.is_dummy as is_dummy',
                'variations.name as variation_name',
                'variations.sub_sku',
                'p.barcode_type',
                'p.enable_sr_no',
                'variations.id as variation_id',
                'units.short_name as unit',
                'units.allow_decimal as unit_allow_decimal',
                'u.short_name as second_unit',
                'transaction_sell_lines.secondary_unit_quantity',
                'transaction_sell_lines.tax_id as tax_id',
                'transaction_sell_lines.item_tax as item_tax',
                'transaction_sell_lines.unit_price as default_sell_price',
                'transaction_sell_lines.unit_price_before_discount as unit_price_before_discount',
                'transaction_sell_lines.unit_price_inc_tax as sell_price_inc_tax',
                'transaction_sell_lines.id as transaction_sell_lines_id',
                'transaction_sell_lines.quantity as quantity_ordered',
                'transaction_sell_lines.sell_line_note as sell_line_note',
                'transaction_sell_lines.parent_sell_line_id',
                'transaction_sell_lines.lot_no_line_id',
                'transaction_sell_lines.line_discount_type',
                'transaction_sell_lines.line_discount_amount',
                'transaction_sell_lines.res_service_staff_id',
                'units.id as unit_id',
                'transaction_sell_lines.sub_unit_id',
                DB::raw('IF(vld.qty_available > 0, vld.qty_available + transaction_sell_lines.quantity, transaction_sell_lines.quantity) AS qty_available')
            )
            ->get();

        foreach ($sell_details as $key => $value) {
            $variation = Variation::with('media')->findOrFail($value->variation_id);
            $sell_details[$key]->media = $variation->media;

            if (!empty($sell_details[$key]->parent_sell_line_id)) {
                unset($sell_details[$key]);
            } else {
                if ($transaction->status != 'final') {
                    $actual_qty_avlbl = $value->qty_available - $value->quantity_ordered;
                    $sell_details[$key]->qty_available = $actual_qty_avlbl;
                    $value->qty_available = $actual_qty_avlbl;
                }

                $sell_details[$key]->formatted_qty_available = $this->productUtil->num_f($value->qty_available, false, null, true);

                $lot_numbers = [];
                if (request()->session()->get('business.enable_lot_number') == 1 || request()->session()->get('business.enable_product_expiry') == 1) {
                    $lot_number_obj = $this->transactionUtil->getLotNumbersFromVariation($value->variation_id, $business_id, $location_id);
                    foreach ($lot_number_obj as $lot_number) {
                        if ($value->lot_no_line_id == $lot_number->purchase_line_id) {
                            $lot_number->qty_available += $value->quantity_ordered;
                        }
                        $lot_number->qty_formated = $this->productUtil->num_f($lot_number->qty_available);
                        $lot_numbers[] = $lot_number;
                    }
                }
                $sell_details[$key]->lot_numbers = $lot_numbers;

                if (!empty($value->sub_unit_id)) {
                    $value = $this->productUtil->changeSellLineUnit($business_id, $value);
                    $sell_details[$key] = $value;
                }

                if ($this->transactionUtil->isModuleEnabled('modifiers')) {
                    $sell_line_modifiers = TransactionSellLine::where('parent_sell_line_id', $sell_details[$key]->transaction_sell_lines_id)
                        ->where('children_type', 'modifier')
                        ->get();
                    $modifiers_ids = [];
                    if (count($sell_line_modifiers) > 0) {
                        $sell_details[$key]->modifiers = $sell_line_modifiers;
                        foreach ($sell_line_modifiers as $sell_line_modifier) {
                            $modifiers_ids[] = $sell_line_modifier->variation_id;
                        }
                    }
                    $sell_details[$key]->modifiers_ids = $modifiers_ids;

                    $this_product = Product::find($sell_details[$key]->product_id);
                    if (count($this_product->modifier_sets) > 0) {
                        $sell_details[$key]->product_ms = $this_product->modifier_sets;
                    }
                }

                if ($sell_details[$key]->product_type == 'combo') {
                    $sell_line_combos = TransactionSellLine::where('parent_sell_line_id', $sell_details[$key]->transaction_sell_lines_id)
                        ->where('children_type', 'combo')
                        ->get()
                        ->toArray();
                    if (!empty($sell_line_combos)) {
                        $sell_details[$key]->combo_products = $sell_line_combos;
                    }

                    $combo_variations = [];
                    foreach ($sell_line_combos as $combo_line) {
                        $combo_variations[] = [
                            'variation_id' => $combo_line['variation_id'],
                            'quantity' => $combo_line['quantity'] / $sell_details[$key]->quantity_ordered,
                            'unit_id' => null
                        ];
                    }
                    $sell_details[$key]->qty_available = $this->productUtil->calculateComboQuantity($location_id, $combo_variations);

                    if ($transaction->status == 'final') {
                        $sell_details[$key]->qty_available += $sell_details[$key]->quantity_ordered;
                    }

                    $sell_details[$key]->formatted_qty_available = $this->productUtil->num_f($sell_details[$key]->qty_available, false, null, true);
                }
            }
        }

        $featured_products = $business_location->getFeaturedProducts();
        $payment_lines = $this->transactionUtil->getPaymentDetails($id);
        if (empty($payment_lines)) {
            $payment_lines[] = $this->dummyPaymentLine;
        }

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
        $brands = (request()->session()->get('business.enable_brand') == 1) ? Brands::forDropdown($business_id)
            ->prepend(__('lang_v1.all_brands'), 'all') : false;

        $change_return = $this->dummyPaymentLine;
        $types = [];
        if (auth()->user()->can('supplier.create')) {
            $types['supplier'] = __('report.supplier');
        }
        if (auth()->user()->can('customer.create')) {
            $types['customer'] = __('report.customer');
        }
        if (auth()->user()->can('supplier.create') && auth()->user()->can('customer.create')) {
            $types['both'] = __('lang_v1.both_supplier_customer');
        }
        $customer_groups = CustomerGroup::forDropdown($business_id);
        $accounts = $this->moduleUtil->isModuleEnabled('account') ? Account::forDropdown($business_id, true, false, true, $transaction->location_id) : [];
        $waiters = $this->productUtil->isModuleEnabled('service_staff') && !empty($pos_settings['inline_service_staff']) ? $this->productUtil->serviceStaffDropdown($business_id) : [];
        $redeem_details = request()->session()->get('business.enable_rp') == 1 ? $this->transactionUtil->getRewardRedeemDetails($business_id, $transaction->contact_id) : [];
        if (!empty($redeem_details)) {
            $redeem_details['points'] += $transaction->rp_redeemed;
            $redeem_details['points'] -= $transaction->rp_earned;
        }

        $edit_discount = auth()->user()->can('edit_product_discount_from_pos_screen');
        $edit_price = auth()->user()->can('edit_product_price_from_pos_screen');
        $shipping_statuses = $this->transactionUtil->shipping_statuses();
        $warranties = $this->getWarranties();
        $sub_type = request()->get('sub_type');
        $pos_module_data = $this->moduleUtil->getModuleData('get_pos_screen_view', ['sub_type' => $sub_type]);
        $invoice_schemes = $transaction->status == 'draft' ? InvoiceScheme::forDropdown($business_id) : [];
        $default_invoice_schemes = $transaction->status == 'draft' ? InvoiceScheme::getDefault($business_id) : null;
        $invoice_layouts = InvoiceLayout::forDropdown($business_id);
        $customer_due = $this->transactionUtil->getContactDue($transaction->contact_id, $transaction->business_id);
        $customer_due = $customer_due != 0 ? $this->transactionUtil->num_f($customer_due, true) : '';
        $users = config('constants.enable_contact_assign') ? User::forDropdown($business_id, false, false, false, true) : [];
        $only_payment = request()->segment(2) == 'payment';

        return view('Loan::pos.edit')->with(compact(
            'business_details',
            'taxes',
            'payment_types',
            'walk_in_customer',
            'sell_details',
            'transaction',
            'payment_lines',
            'location_printer_type',
            'shortcuts',
            'commission_agent',
            'categories',
            'pos_settings',
            'change_return',
            'types',
            'customer_groups',
            'brands',
            'accounts',
            'waiters',
            'redeem_details',
            'edit_price',
            'edit_discount',
            'shipping_statuses',
            'warranties',
            'sub_type',
            'pos_module_data',
            'invoice_schemes',
            'default_invoice_schemes',
            'invoice_layouts',
            'featured_products',
            'customer_due',
            'users',
            'only_payment'
        ));
    }

    /**
     * Update a POS sale.
     */
    public function update(Request $request, $id)
    {
        if (!auth()->user()->can('sell.update') && !auth()->user()->can('direct_sell.access') && !auth()->user()->can('edit_pos_payment')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $input = $request->except('_token');
            $input['is_quotation'] = $input['status'] == 'quotation' ? 1 : ($input['status'] == 'proforma' ? 0 : 0);
            if ($input['status'] == 'quotation') {
                $input['status'] = 'draft';
                $input['sub_status'] = 'quotation';
            } elseif ($input['status'] == 'proforma') {
                $input['status'] = 'draft';
                $input['sub_status'] = 'proforma';
            } else {
                $input['sub_status'] = null;
                $input['is_quotation'] = 0;
            }

            $is_direct_sale = false;
            $transaction_before = Transaction::find($id);
            $status_before = $transaction_before->status;
            $rp_earned_before = $transaction_before->rp_earned;
            $rp_redeemed_before = $transaction_before->rp_redeemed;

            if ($transaction_before->is_direct_sale == 1) {
                $is_direct_sale = true;
            }

            $sales_order_ids = $transaction_before->sales_order_ids ?? [];
            $change_return = $this->dummyPaymentLine;
            if (!empty($input['payment']['change_return'])) {
                $change_return = $input['payment']['change_return'];
                unset($input['payment']['change_return']);
            }

            $is_credit_limit_exeeded = $transaction_before->type == 'sell' ? $this->transactionUtil->isCustomerCreditLimitExeeded($input, $id) : false;
            if ($is_credit_limit_exeeded !== false) {
                $credit_limit_amount = $this->transactionUtil->num_f($is_credit_limit_exeeded, true);
                $output = [
                    'success' => 0,
                    'msg' => __('lang_v1.cutomer_credit_limit_exeeded', ['credit_limit' => $credit_limit_amount])
                ];
                return $is_direct_sale ? redirect()->action([\Modules\Loan\Http\Controllers\LoanPosController::class, 'index'])->with('status', $output) : $output;
            }

            if (!$is_direct_sale && $this->cashRegisterUtil->countOpenedRegister() == 0) {
                return redirect()->action([\App\Http\Controllers\CashRegisterController::class, 'create']);
            }

            $business_id = $request->session()->get('user.business_id');
            $user_id = $request->session()->get('user.id');
            $commsn_agnt_setting = $request->session()->get('business.sales_cmsn_agnt');

            $discount = [
                'discount_type' => $input['discount_type'],
                'discount_amount' => $input['discount_amount']
            ];
            $invoice_total = $this->productUtil->calculateInvoiceTotal($input['products'], $input['tax_rate_id'], $discount);

            if (!empty($request->input('transaction_date'))) {
                $input['transaction_date'] = $this->productUtil->uf_date($request->input('transaction_date'), true);
            }

            $input['commission_agent'] = !empty($request->input('commission_agent')) ? $request->input('commission_agent') : ($commsn_agnt_setting == 'logged_in_user' ? $user_id : null);
            $input['exchange_rate'] = isset($input['exchange_rate']) && $this->transactionUtil->num_uf($input['exchange_rate']) == 0 ? 1 : $input['exchange_rate'];

            $contact_id = $request->get('contact_id', null);
            $cg = $this->contactUtil->getCustomerGroup($business_id, $contact_id);
            $input['customer_group_id'] = (empty($cg) || empty($cg->id)) ? null : $cg->id;

            $price_group_id = $request->has('price_group') ? $request->input('price_group') : null;
            $input['is_suspend'] = isset($input['is_suspend']) && $input['is_suspend'] == 1 ? 1 : 0;
            if ($input['is_suspend']) {
                $input['sale_note'] = !empty($input['additional_notes']) ? $input['additional_notes'] : null;
            }

            if ($status_before == 'draft' && !empty($request->input('invoice_scheme_id'))) {
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

            $document_name = $this->transactionUtil->uploadFile($request, 'sell_document', 'documents');
            if (!empty($document_name)) {
                $input['document'] = $document_name;
            }

            for ($i = 1; $i <= 4; $i++) {
                if ($request->input("additional_expense_value_$i") != '') {
                    $input["additional_expense_key_$i"] = $request->input("additional_expense_key_$i");
                    $input["additional_expense_value_$i"] = $request->input("additional_expense_value_$i");
                }
            }

            $only_payment = !$is_direct_sale && !auth()->user()->can('sell.update') && auth()->user()->can('edit_pos_payment');
            if ($only_payment) {
                DB::beginTransaction();
                $this->onlyUpdatePayment($transaction_before, $input);
                DB::commit();

                $can_print_invoice = auth()->user()->can('print_invoice');
                $invoice_layout_id = $request->input('invoice_layout_id');
                $receipt = $this->receiptContent($business_id, $input['location_id'], $transaction_before->id, null, false, true, $invoice_layout_id);
                $msg = trans('purchase.payment_updated_success');

                $output = ['success' => 1, 'msg' => $msg, 'receipt' => $receipt];
                return $output;
            }

            DB::beginTransaction();

            $transaction = $this->transactionUtil->updateSellTransaction($id, $business_id, $input, $invoice_total, $user_id);

            if (!$is_direct_sale && $transaction->status == 'final') {
                foreach ($input['products'] as $product_line) {
                    if (!empty($product_line['res_service_staff_id'])) {
                        $product = Product::find($product_line['product_id']);
                        if (!empty($product->preparation_time_in_minutes)) {
                            $quantity = $this->transactionUtil->num_uf($product_line['quantity']);
                            if (!empty($product_line['transaction_sell_lines_id'])) {
                                $sl = TransactionSellLine::find($product_line['transaction_sell_lines_id']);
                                if ($sl->quantity >= $quantity && $sl->res_service_staff_id == $product_line['res_service_staff_id']) {
                                    continue;
                                }
                                if ($sl->res_service_staff_id == $product_line['res_service_staff_id']) {
                                    $quantity = $quantity - $sl->quantity;
                                }
                            }

                            $service_staff = User::find($product_line['res_service_staff_id']);
                            $base_time = \Carbon::parse($transaction->transaction_date);
                            if ($base_time->lt(\Carbon::now())) {
                                $base_time = \Carbon::now();
                            }
                            if (!empty($service_staff->available_at) && \Carbon::parse($service_staff->available_at)->gt(\Carbon::now())) {
                                $base_time = \Carbon::parse($service_staff->available_at);
                            }
                            $total_minutes = $product->preparation_time_in_minutes * $quantity;
                            $service_staff->available_at = $base_time->addMinutes($total_minutes);
                            $service_staff->save();
                        }
                    }
                }
            }

            $deleted_lines = $this->transactionUtil->createOrUpdateSellLines($transaction, $input['products'], $input['location_id'], true, $status_before);
            $is_credit_sale = isset($input['is_credit_sale']) && $input['is_credit_sale'] == 1;

            $new_sales_order_ids = $transaction->sales_order_ids ?? [];
            $sales_order_ids = array_unique(array_merge($sales_order_ids, $new_sales_order_ids));
            if (!empty($sales_order_ids)) {
                $this->transactionUtil->updateSalesOrderStatus($sales_order_ids);
            }

            if (!$transaction->is_suspend && !$is_credit_sale) {
                $change_return['amount'] = $input['change_return'] ?? 0;
                $change_return['is_return'] = 1;
                if (!empty($input['change_return_id'])) {
                    $change_return['payment_id'] = $input['change_return_id'];
                }
                $input['payment'][] = $change_return;

                if (!$is_direct_sale || auth()->user()->can('sell.payments')) {
                    $this->transactionUtil->createOrUpdatePaymentLines($transaction, $input['payment']);
                    if (!$is_direct_sale) {
                        $this->cashRegisterUtil->updateSellPayments($status_before, $transaction, $input['payment']);
                    }
                }
            }

            if ($request->session()->get('business.enable_rp') == 1) {
                $this->transactionUtil->updateCustomerRewardPoints($contact_id, $transaction->rp_earned, $rp_earned_before, $transaction->rp_redeemed, $rp_redeemed_before);
            }

            Media::uploadMedia($business_id, $transaction, $request, 'shipping_documents', false, 'shipping_document');

            if ($transaction->type == 'sell') {
                $payment_status = $this->transactionUtil->updatePaymentStatus($transaction->id, $transaction->final_total);
                $transaction->payment_status = $payment_status;

                $this->productUtil->adjustProductStockForInvoice($status_before, $transaction, $input);
                $business_details = $this->businessUtil->getDetails($business_id);
                $pos_settings = empty($business_details->pos_settings) ? $this->businessUtil->defaultPosSettings() : json_decode($business_details->pos_settings, true);
                $business = [
                    'id' => $business_id,
                    'accounting_method' => $request->session()->get('business.accounting_method'),
                    'location_id' => $input['location_id'],
                    'pos_settings' => $pos_settings
                ];
                $this->transactionUtil->adjustMappingPurchaseSell($status_before, $transaction, $business, $deleted_lines);

                $whatsapp_link = $this->notificationUtil->autoSendNotification($business_id, 'new_sale', $transaction, $transaction->contact);
            }

            $log_properties = [];
            if (isset($input['repair_completed_on'])) {
                $completed_on = !empty($input['repair_completed_on']) ? $this->transactionUtil->uf_date($input['repair_completed_on'], true) : null;
                if ($transaction->repair_completed_on != $completed_on) {
                    $log_properties['completed_on_from'] = $transaction->repair_completed_on;
                    $log_properties['completed_on_to'] = $completed_on;
                }
            }

            $this->moduleUtil->getModuleData('after_sale_saved', ['transaction' => $transaction, 'input' => $input]);
            Media::uploadMedia($business_id, $transaction, $request, 'documents');
            $this->transactionUtil->activityLog($transaction, 'edited', $transaction_before);

            DB::commit();

            if ($request->input('is_save_and_print') == 1) {
                $url = $this->transactionUtil->getInvoiceUrl($id, $business_id);
                return redirect()->to($url . '?print_on_load=true');
            }

            $msg = __('lang_v1.updated_success');
            $receipt = '';
            $can_print_invoice = auth()->user()->can('print_invoice');
            $invoice_layout_id = $request->input('invoice_layout_id');

            if ($input['status'] == 'draft' && $input['is_quotation'] == 0) {
                $msg = trans('sale.draft_added');
            } elseif ($input['status'] == 'draft' && $input['is_quotation'] == 1) {
                $msg = trans('lang_v1.quotation_updated');
                if (!$is_direct_sale && $can_print_invoice) {
                    $receipt = $this->receiptContent($business_id, $input['location_id'], $transaction->id, null, false, true, $invoice_layout_id);
                }
            } elseif ($input['status'] == 'final') {
                $msg = trans('sale.pos_sale_updated');
                if (!$is_direct_sale && $can_print_invoice) {
                    $receipt = $this->receiptContent($business_id, $input['location_id'], $transaction->id, null, false, true, $invoice_layout_id);
                }
            }

            $output = ['success' => 1, 'msg' => $msg, 'receipt' => $receipt];
            if (!empty($whatsapp_link)) {
                $output['whatsapp_link'] = $whatsapp_link;
            }
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            $output = ['success' => 0, 'msg' => __('messages.something_went_wrong')];
        }

        return $is_direct_sale ? redirect()->action([\Modules\Loan\Http\Controllers\LoanPosController::class, 'index'])->with('status', $output) : $output;
    }

    /**
     * Update payment only for a POS sale.
     */
    private function onlyUpdatePayment($transaction, $input)
    {
        $change_return = $this->dummyPaymentLine;
        if (!empty($input['payment']['change_return'])) {
            $change_return = $input['payment']['change_return'];
            unset($input['payment']['change_return']);
        }

        $change_return['amount'] = $input['change_return'] ?? 0;
        $change_return['is_return'] = 1;
        if (!empty($input['change_return_id'])) {
            $change_return['payment_id'] = $input['change_return_id'];
        }
        $input['payment'][] = $change_return;

        $this->transactionUtil->createOrUpdatePaymentLines($transaction, $input['payment']);
        $this->cashRegisterUtil->updateSellPayments($transaction->status, $transaction, $input['payment']);

        $payment_status = $this->transactionUtil->updatePaymentStatus($transaction->id, $transaction->final_total);
        $transaction_before = $transaction;
        $transaction->payment_status = $payment_status;

        if ($payment_status == 'paid') {
            $transaction->is_suspend = 0;
            $transaction->save();
        }

        $this->transactionUtil->activityLog($transaction, 'payment_edited', $transaction_before);
    }

    /**
     * Get warranties.
     */
    private function getWarranties()
    {
        $business_id = session()->get('user.business_id');
        $common_settings = session()->get('business.common_settings');
        $is_warranty_enabled = !empty($common_settings['enable_product_warranty']) ? true : false;
        return $is_warranty_enabled ? Warranty::forDropdown($business_id) : [];
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