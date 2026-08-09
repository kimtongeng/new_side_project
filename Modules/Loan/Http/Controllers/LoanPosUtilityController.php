<?php

namespace Modules\Loan\Http\Controllers;

use App\Account;
use App\Business;
use App\BusinessLocation;
use App\Contact;
use App\Product;
use App\TaxRate;
use App\Transaction;
use App\TransactionSellLine;
use App\Utils\BusinessUtil;
use App\Utils\CashRegisterUtil;
use App\Utils\ContactUtil;
use App\Utils\ModuleUtil;
use App\Utils\NotificationUtil;
use App\Utils\ProductUtil;
use App\Utils\TransactionUtil;
use App\Variation;
use App\Warranty;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;

class LoanPosUtilityController extends Controller
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
     * Returns the HTML row for a product in POS.
     */
    public function getProductRow($variation_id, $location_id)
    {
        $output = [];

        try {
            $row_count = request()->get('product_row', 0) + 1;
            $quantity = request()->get('quantity', 1);
            $weighing_barcode = request()->get('weighing_scale_barcode', null);

            $is_direct_sell = request()->get('is_direct_sell') == 'true';
            $is_sales_order = request()->has('is_sales_order') && request()->input('is_sales_order') == 'true';
            $is_draft = request()->has('is_draft') && request()->input('is_draft') == 'true';

            if ($variation_id == 'null' && !empty($weighing_barcode)) {
                $product_details = $this->parseWeighingBarcode($weighing_barcode);
                if ($product_details['success']) {
                    $variation_id = $product_details['variation_id'];
                    $quantity = $product_details['qty'];
                } else {
                    return ['success' => false, 'msg' => $product_details['msg']];
                }
            }

            $business_id = request()->session()->get('user.business_id');
            $business_details = $this->businessUtil->getDetails($business_id);
            $pos_settings = empty($business_details->pos_settings) ? $this->businessUtil->defaultPosSettings() : json_decode($business_details->pos_settings, true);

            $check_qty = !empty($pos_settings['allow_overselling']) ? false : true;
            if ($is_sales_order || $is_draft) {
                $check_qty = false;
            }

            if (request()->input('disable_qty_alert') === 'true') {
                $pos_settings['allow_overselling'] = true;
            }

            $product = $this->productUtil->getDetailsFromVariation($variation_id, $business_id, $location_id, $check_qty);
            if (!isset($product->quantity_ordered)) {
                $product->quantity_ordered = $quantity;
            }

            $product->secondary_unit_quantity = !isset($product->secondary_unit_quantity) ? 0 : $product->secondary_unit_quantity;
            $product->formatted_qty_available = $this->productUtil->num_f($product->qty_available, false, null, true);

            $sub_units = $this->productUtil->getSubUnits($business_id, $product->unit_id, false, $product->product_id);
            $customer_id = request()->get('customer_id', null);
            $cg = $this->contactUtil->getCustomerGroup($business_id, $customer_id);
            $percent = (empty($cg) || empty($cg->amount) || $cg->price_calculation_type != 'percentage') ? 0 : $cg->amount;
            $product->default_sell_price += ($percent * $product->default_sell_price / 100);
            $product->sell_price_inc_tax += ($percent * $product->sell_price_inc_tax / 100);

            $tax_dropdown = TaxRate::forBusinessDropdown($business_id, true, true);
            $enabled_modules = $this->transactionUtil->allModulesEnabled();
            $lot_numbers = [];
            if (request()->session()->get('business.enable_lot_number') == 1 || request()->session()->get('business.enable_product_expiry') == 1) {
                $lot_number_obj = $this->transactionUtil->getLotNumbersFromVariation($variation_id, $business_id, $location_id, true);
                foreach ($lot_number_obj as $lot_number) {
                    $lot_number->qty_formated = $this->productUtil->num_f($lot_number->qty_available);
                    $lot_numbers[] = $lot_number;
                }
            }
            $product->lot_numbers = $lot_numbers;

            $purchase_line_id = request()->get('purchase_line_id');
            $price_group = request()->input('price_group');
            if (!empty($price_group)) {
                $variation_group_prices = $this->productUtil->getVariationGroupPrice($variation_id, $price_group, $product->tax_id);
                if (!empty($variation_group_prices['price_inc_tax'])) {
                    $product->sell_price_inc_tax = $variation_group_prices['price_inc_tax'];
                    $product->default_sell_price = $variation_group_prices['price_exc_tax'];
                }
            }

            $warranties = $this->getWarranties();
            $output['success'] = true;
            $output['enable_sr_no'] = $product->enable_sr_no;

            $waiters = $this->productUtil->isModuleEnabled('service_staff') && !empty($pos_settings['inline_service_staff']) ? $this->productUtil->serviceStaffDropdown($business_id, $location_id) : [];
            $last_sell_line = $is_direct_sell ? $this->getLastSellLineForCustomer($variation_id, $customer_id, $location_id) : null;

            $is_cg = !empty($cg->id);
            $discount = $this->productUtil->getProductDiscount($product, $business_id, $location_id, $is_cg, $price_group, $variation_id);
            $edit_discount = $is_direct_sell ? auth()->user()->can('edit_product_discount_from_sale_screen') : auth()->user()->can('edit_product_discount_from_pos_screen');
            $edit_price = $is_direct_sell ? auth()->user()->can('edit_product_price_from_sale_screen') : auth()->user()->can('edit_product_price_from_pos_screen');

            $output['html_content'] = view('Loan::pos.product_row')
                ->with(compact(
                    'product',
                    'row_count',
                    'tax_dropdown',
                    'enabled_modules',
                    'pos_settings',
                    'sub_units',
                    'discount',
                    'waiters',
                    'edit_discount',
                    'edit_price',
                    'purchase_line_id',
                    'warranties',
                    'quantity',
                    'is_direct_sell',
                    'is_sales_order',
                    'last_sell_line'
                ))
                ->render();

            if ($this->transactionUtil->isModuleEnabled('modifiers') && !$is_direct_sell) {
                $variation = Variation::find($variation_id);
                $this_product = Product::where('business_id', $business_id)
                    ->with(['modifier_sets'])
                    ->find($variation->product_id);
                if (count($this_product->modifier_sets) > 0) {
                    $product_ms = $this_product->modifier_sets;
                    $output['html_modifier'] = view('restaurant.product_modifier_set.modifier_for_product')
                        ->with(compact('product_ms', 'row_count'))->render();
                }
            }
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            $output = ['success' => false, 'msg' => __('lang_v1.item_out_of_stock')];
        }

        return $output;
    }

    /**
     * Returns the HTML row for a payment in POS.
     */
    public function getPaymentRow(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');
        $row_index = $request->input('row_index');
        $location_id = $request->input('location_id');
        $removable = true;
        $payment_types = $this->productUtil->payment_types($location_id, true);
        $payment_line = $this->dummyPaymentLine;

        $accounts = $this->moduleUtil->isModuleEnabled('account') ? Account::forDropdown($business_id, true, false, true, $location_id) : [];

        return view('Loan::pos.partials.payment_row')
            ->with(compact('payment_types', 'row_index', 'removable', 'payment_line', 'accounts'));
    }

    /**
     * Returns recent transactions.
     */
    public function getRecentTransactions(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        $user_id = $request->session()->get('user.id');
        $transaction_status = $request->get('status');

        $register = $this->cashRegisterUtil->getCurrentCashRegister($user_id);

        $query = Transaction::where('business_id', $business_id)
            ->where('transactions.created_by', $user_id)
            ->where('transactions.type', 'sell')
            ->where('is_direct_sale', 0);

        if ($transaction_status == 'quotation') {
            $query->where('transactions.status', 'draft')
                ->where('sub_status', 'quotation');
        } elseif ($transaction_status == 'draft') {
            $query->where('transactions.status', 'draft')
                ->whereNull('sub_status');
        } else {
            $query->where('transactions.status', $transaction_status);
        }

        $transaction_sub_type = $request->get('transaction_sub_type');
        if (!empty($transaction_sub_type)) {
            $query->where('transactions.sub_type', $transaction_sub_type);
        } else {
            $query->where('transactions.sub_type', null);
        }

        $transactions = $query->orderBy('transactions.created_at', 'desc')
            ->groupBy('transactions.id')
            ->select('transactions.*')
            ->with(['contact', 'table'])
            ->limit(10)
            ->get();

        return view('Loan::pos.partials.recent_transactions')
            ->with(compact('transactions', 'transaction_sub_type'));
    }

    /**
     * Gives suggestions for products based on category.
     */
    public function getProductSuggestion(Request $request)
    {
        if ($request->ajax()) {
            $category_id = $request->get('category_id');
            $brand_id = $request->get('brand_id');
            $location_id = $request->get('location_id');
            $term = $request->get('term');

            $check_qty = false;
            $business_id = $request->session()->get('user.business_id');
            $business = $request->session()->get('business');
            $pos_settings = empty($business->pos_settings) ? $this->businessUtil->defaultPosSettings() : json_decode($business->pos_settings, true);

            $products = Variation::join('products as p', 'variations.product_id', '=', 'p.id')
                ->join('product_locations as pl', 'pl.product_id', '=', 'p.id')
                ->join('units as u', 'p.unit_id', '=', 'u.id')
                ->leftJoin('variation_location_details AS VLD', function ($join) use ($location_id) {
                    $join->on('variations.id', '=', 'VLD.variation_id');
                    if (!empty($location_id)) {
                        $join->where(function ($query) use ($location_id) {
                            $query->where('VLD.location_id', '=', $location_id)
                                ->orWhereNull('VLD.location_id');
                        });
                    }
                })
                ->where('p.business_id', $business_id)
                ->where('p.type', '!=', 'modifier')
                ->where('p.is_inactive', 0)
                ->where('p.not_for_selling', 0)
                ->where('pl.location_id', $location_id);

            if (!empty($term)) {
                $products->where(function ($query) use ($term) {
                    $query->where('p.name', 'like', '%' . $term . '%')
                        ->orWhere('sku', 'like', '%' . $term . '%')
                        ->orWhere('sub_sku', 'like', '%' . $term . '%');
                });
            }

            if ($check_qty) {
                $products->where('VLD.qty_available', '>', 0);
            }

            if (!empty($category_id) && $category_id != 'all') {
                $products->where(function ($query) use ($category_id) {
                    $query->where('p.category_id', $category_id)
                        ->orWhere('p.sub_category_id', $category_id);
                });
            }
            if (!empty($brand_id) && $brand_id != 'all') {
                $products->where('p.brand_id', $brand_id);
            }

            if (!empty($request->get('is_enabled_stock'))) {
                $is_enabled_stock = $request->get('is_enabled_stock') == 'product' ? 1 : 0;
                $products->where('p.enable_stock', $is_enabled_stock);
            }

            $products = $products->select(
                'p.id as product_id',
                'p.name',
                'p.type',
                'p.enable_stock',
                'p.image as product_image',
                'variations.id',
                'variations.name as variation',
                'VLD.qty_available',
                'variations.default_sell_price as selling_price',
                'variations.sub_sku',
                'u.short_name as unit'
            )
                ->with(['media', 'group_prices'])
                ->orderBy('p.name', 'asc')
                ->paginate(50);

            $price_groups = SellingPriceGroup::where('business_id', $business_id)->active()->pluck('name', 'id');
            $allowed_group_prices = [];
            foreach ($price_groups as $key => $value) {
                if (auth()->user()->can('selling_price_group.' . $key)) {
                    $allowed_group_prices[$key] = $value;
                }
            }

            $show_prices = !empty($pos_settings['show_pricing_on_product_sugesstion']);

            return view('Loan::pos.partials.product_list')
                ->with(compact('products', 'allowed_group_prices', 'show_prices'));
        }
    }

    /**
     * Finds last sell line of a variation for the customer for a location.
     */
    private function getLastSellLineForCustomer($variation_id, $customer_id, $location_id)
    {
        return TransactionSellLine::join('transactions as t', 't.id', '=', 'transaction_sell_lines.transaction_id')
            ->where('t.location_id', $location_id)
            ->where('t.contact_id', $customer_id)
            ->where('t.type', 'sell')
            ->where('t.status', 'final')
            ->where('transaction_sell_lines.variation_id', $variation_id)
            ->orderBy('t.transaction_date', 'desc')
            ->select('transaction_sell_lines.*')
            ->first();
    }

    /**
     * Parse the weighing barcode.
     */
    private function parseWeighingBarcode($scale_barcode)
    {
        $business_id = session()->get('user.business_id');
        $scale_setting = session()->get('business.weighing_scale_setting');
        $error_msg = trans('messages.something_went_wrong');

        if ((strlen($scale_setting['label_prefix']) == 0) || Str::startsWith($scale_barcode, $scale_setting['label_prefix'])) {
            $scale_barcode = substr($scale_barcode, strlen($scale_setting['label_prefix']));
            $sku = ltrim(substr($scale_barcode, 0, $scale_setting['product_sku_length'] + 1), '0');
            $qty_int = substr($scale_barcode, $scale_setting['product_sku_length'] + 1, $scale_setting['qty_length'] + 1);
            $qty_decimal = '0.' . substr($scale_barcode, $scale_setting['product_sku_length'] + $scale_setting['qty_length'] + 2, $scale_setting['qty_length_decimal'] + 1);
            $qty = (float)$qty_int + (float)$qty_decimal;

            $result = $this->productUtil->filterProduct($business_id, $sku, null, false, null, [], ['sub_sku'], false, 'exact')->first();
            if (!empty($result)) {
                return [
                    'variation_id' => $result->variation_id,
                    'qty' => $qty,
                    'success' => true
                ];
            } else {
                $error_msg = trans('lang_v1.sku_not_match', ['sku' => $sku]);
            }
        } else {
            $error_msg = trans('lang_v1.prefix_did_not_match');
        }

        return [
            'success' => false,
            'msg' => $error_msg
        ];
    }

    /**
     * Get warranties.
     */
    private function getWarranties()
    {
        $business_id = session()->get('user.business_id');
        $common_settings = session()->get('business.common_settings');
        $is_warranty_enabled = !empty($common_settings['enable_product_warranty']);
        return $is_warranty_enabled ? Warranty::forDropdown($business_id) : [];
    }
}