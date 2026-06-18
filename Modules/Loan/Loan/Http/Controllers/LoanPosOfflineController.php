<?php

namespace Modules\Loan\Http\Controllers;

use App\Business;
use App\Transaction;
use App\Utils\BusinessUtil;
use App\Utils\CashRegisterUtil;
use App\Utils\ContactUtil;
use App\Utils\ModuleUtil;
use App\Utils\NotificationUtil;
use App\Utils\ProductUtil;
use App\Utils\TransactionUtil;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Loan\Utils\LoanUtil;

class LoanPosOfflineController extends Controller
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
    }

    /**
     * Sync offline transactions with the server.
     */
    public function syncOfflineTransactions(Request $request)
    {
        if (!auth()->user()->can('sell.create')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $business_id = $request->session()->get('user.business_id');
            $user_id = $request->session()->get('user.id');
            $offline_transactions = $request->input('offline_transactions', []);

            if (empty($offline_transactions)) {
                return ['success' => true, 'msg' => __('lang_v1.no_offline_transactions_to_sync')];
            }

            DB::beginTransaction();

            $synced_transactions = [];
            foreach ($offline_transactions as $offline_transaction) {
                if (!$this->moduleUtil->isSubscribed($business_id)) {
                    DB::rollBack();
                    return $this->moduleUtil->expiredResponse();
                } elseif (!$this->moduleUtil->isQuotaAvailable('invoices', $business_id)) {
                    DB::rollBack();
                    return $this->moduleUtil->quotaExpiredResponse('invoices', $business_id, action([\Modules\Loan\Http\Controllers\LoanPosController::class, 'index']));
                }

                $input = $offline_transaction;
                $input['transaction_date'] = $this->productUtil->uf_date($input['transaction_date'], true);
                $input['is_direct_sale'] = 0; // Offline transactions are not direct sales

                $discount = [
                    'discount_type' => $input['discount_type'] ?? 'fixed',
                    'discount_amount' => $input['discount_amount'] ?? 0
                ];
                $invoice_total = $this->productUtil->calculateInvoiceTotal($input['products'], $input['tax_rate_id'] ?? null, $discount);

                $input['commission_agent'] = !empty($input['commission_agent']) ? $input['commission_agent'] : ($request->session()->get('business.sales_cmsn_agnt') == 'logged_in_user' ? $user_id : null);
                $input['exchange_rate'] = isset($input['exchange_rate']) && $this->transactionUtil->num_uf($input['exchange_rate']) == 0 ? 1 : $input['exchange_rate'];

                $contact_id = $input['contact_id'] ?? null;
                $cg = $this->contactUtil->getCustomerGroup($business_id, $contact_id);
                $input['customer_group_id'] = (empty($cg) || empty($cg->id)) ? null : $cg->id;

                $input['is_suspend'] = isset($input['is_suspend']) && $input['is_suspend'] == 1 ? 1 : 0;
                if ($input['is_suspend']) {
                    $input['sale_note'] = !empty($input['additional_notes']) ? $input['additional_notes'] : null;
                }

                $transaction = $this->transactionUtil->createSellTransaction($business_id, $input, $invoice_total, $user_id);
                $this->transactionUtil->createOrUpdateSellLines($transaction, $input['products'], $input['location_id']);

                $change_return = [
                    'method' => 'cash',
                    'amount' => $input['change_return'] ?? 0,
                    'note' => '',
                    'is_return' => 1
                ];
                $input['payment'][] = $change_return;

                $is_credit_sale = isset($input['is_credit_sale']) && $input['is_credit_sale'] == 1;
                if (!$transaction->is_suspend && !empty($input['payment']) && !$is_credit_sale) {
                    $this->transactionUtil->createOrUpdatePaymentLines($transaction, $input['payment']);
                    $this->cashRegisterUtil->addSellPayments($transaction, $input['payment']);
                }

                if ($input['status'] == 'final') {
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

                    $this->notificationUtil->autoSendNotification($business_id, 'new_sale', $transaction, $transaction->contact);
                }

                $this->transactionUtil->activityLog($transaction, 'added');
                $synced_transactions[] = [
                    'offline_id' => $input['offline_id'] ?? null,
                    'transaction_id' => $transaction->id,
                    'invoice_no' => $transaction->invoice_no
                ];
            }

            DB::commit();

            return [
                'success' => true,
                'msg' => __('lang_v1.offline_transactions_synced'),
                'synced_transactions' => $synced_transactions
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            return [
                'success' => false,
                'msg' => __('messages.something_went_wrong')
            ];
        }
    }

    /**
     * Check server connectivity status.
     */
    public function checkConnectivity()
    {
        try {
            // Simple check by attempting to fetch a small resource or ping the server
            $response = \Http::timeout(5)->get(url('/'));
            return [
                'success' => true,
                'is_online' => $response->successful(),
                'msg' => $response->successful() ? __('lang_v1.connected_to_server') : __('lang_v1.no_server_connection')
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'is_online' => false,
                'msg' => __('lang_v1.no_server_connection')
            ];
        }
    }
}