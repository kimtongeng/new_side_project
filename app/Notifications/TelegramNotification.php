<?php

namespace App\Notifications;

use App\Account;
use App\AccountTransaction;
use App\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Modules\ExchangeCurrency\Entities\ExchangeCurrency;
use Modules\Repair\Entities\JobSheet;

class TelegramNotification
{
    // https://api.telegram.org/bot<YOUR_BOT_TOKEN>/getUpdates

    // private const BOT = [
    //     'token' => '7830977137:AAHF4T7P7B7rwm58je71F1pol0FKXn_O7zY',
    //     "group_pt1001" => [
    //         "sell" => [
    //             "id" => '-4868742952',
    //             "title" => 'sell',
    //             "key" => 'sell',
    //             "topic" => []
    //         ],
    //         "draft" => [
    //             "id" => '-4884958224',
    //             "title" => 'draft',
    //             "key" => 'draft',
    //             "topic" => []
    //         ],
    //         "quotation" => [
    //             "id" => '-4983142854',
    //             "title" => 'quotation',
    //             "key" => 'quotation',
    //             "topic" => []
    //         ]
    //     ],
    // ];

    private const LOCAL_BOT = '7830977137:AAHF4T7P7B7rwm58je71F1pol0FKXn_O7zY';
    private const LOCAL_GROUP = [
        'PT1001' => [
            "name" => "ultimate_pos_work",
            "chat_id" => "-1003642283651",
            "topic" => [
                "stock_adjustment" => 20,
                "home" => 30,
                "purchase" => 15,
                "repair" => 23,
                "payment_accoun" => 22,
                "expense" => 21,
                "transfer" => 19,
                "product" => 14,
                "sell" => 16,
                "quotation" => 18,
                "draft" => 17,
                'stock_count' => 2283
            ]
        ],
        'PT1002' => [
            "name" => "ultimate_pos_work_2",
            "chat_id" => "-1003712515829",
            "topic" => [
                "product" => 2,
            ]
        ],
    ];
    // private const PRODUCTION_BOT = '8152281759:AAFEN2PObxW-S8Jck251--mxQEEuNJYCanQ';
    private const PRODUCTION_BOT = '8841464720:AAGLHIGlPDTUhUP52LnAh_0XKfO6qDk8rKo';
    private const PRODUCTION_GROUP = [
        'PT1001' => [
            "name" => "PT1001",
            "chat_id" => "-1003663677436",
            "topic" => [
                "sell" => 2,
                "payment_account" => 23,
                "expense" => 5,
                "stock_adjustment" => 7,
                "transfer" => 9,
                "quotation" => 11,
                "draft" => 13,
                "home" => 15,
                "purchase" => 17,
                "repair" => 19,
                "product" => 21,
            ]
        ],
        'PT1002' => [
            "name" => "PT1002",
            "chat_id" => "-1003984033095",
            "topic" => [
                "stock_adjustment" => 6,
                "home" => 16,
                "purchase" => 18,
                "repair" => 20,
                "payment_account" => 24,
                "expense" => 4,
                "transfer" => 8,
                "product" => 22,
                "sell" => 2,
                "quotation" => 10,
                "draft" => 14,
            ],
        ],
        'PT1003' => [
            "name" => "PT1003",
            "chat_id" => "-1003979832282",
            "topic" => [
                "stock_adjustment" => 6,
                "home" => 14,
                "purchase" => 16,
                "repair" => 18,
                "payment_account" => 22,
                "expense" => 4,
                "transfer" => 8,
                "product" => 20,
                "sell" => 2,
                "quotation" => 10,
                "draft" => 12,
            ]
        ],
    ];

    // private const BOT = [
    //     'token' => '8152281759:AAFEN2PObxW-S8Jck251--mxQEEuNJYCanQ',
    //     "group_pt1001" => [
    //         "sell" => [
    //             "id" => '-1002658900346',
    //             "title" => 'sell',
    //             "key" => 'sell',
    //             "topic" => []
    //         ],
    //         "draft" => [
    //             "id" => '-1002731590375',
    //             "title" => 'draft',
    //             "key" => 'draft',
    //             "topic" => []
    //         ],
    //         "quotation" => [
    //             "id" => '-1002548125523',
    //             "title" => 'quotation',
    //             "key" => 'quotation',
    //             "topic" => []
    //         ]
    //     ],
    //     "group_pt1002" => [
    //         "sell" => [
    //             "id" => '-1002884292516',
    //             "title" => 'sell',
    //             "key" => 'sell',
    //             "topic" => []
    //         ],
    //         "draft" => [
    //             "id" => '-1003002593739',
    //             "title" => 'draft',
    //             "key" => 'draft',
    //             "topic" => []
    //         ],
    //         "quotation" => [
    //             "id" => '-1003067353221',
    //             "title" => 'quotation',
    //             "key" => 'quotation',
    //             "topic" => []
    //         ]
    //     ],
    //     "group_pt1003" => [
    //         "sell" => [
    //             "id" => '-1003146868305',
    //             "title" => 'sell',
    //             "key" => 'sell',
    //             "topic" => []
    //         ],
    //         "draft" => [
    //             "id" => '-1002996581417',
    //             "title" => 'draft',
    //             "key" => 'draft',
    //             "topic" => []
    //         ],
    //         "quotation" => [
    //             "id" => '-1002982963687',
    //             "title" => 'quotation',
    //             "key" => 'quotation',
    //             "topic" => []
    //         ]
    //     ]
    // ];

    private static function getUpdatedBy(): string
    {
        $user = auth()->user();
        if (!$user) {
            return 'System';
        }
        $name = trim(implode(' ', array_filter([$user->surname, $user->first_name, $user->last_name])));
        return !empty($name) ? $name : $user->username;
    }

    public static function sendMessage(string $message, string $to = '', $location_id = "PT1001"): void
    {
        return;
        // if (function_exists('fastcgi_finish_request')) {
        //     @fastcgi_finish_request();
        // }

        $botToken = self::LOCAL_BOT;
        $groups = self::LOCAL_GROUP;

        $group = $groups[$location_id] ?? null;
        if (! $group) return;

        $topic = $group["topic"] ?? [];
        $chat_id = $group["chat_id"] ?? '';
        $topic_id = $topic[$to] ?? null;

        if (empty($topic_id) || empty($chat_id)) {
            return;
        }

        try {
            Http::timeout(3)->get("https://api.telegram.org/bot{$botToken}/sendMessage", [
                'chat_id' => $chat_id,
                'message_thread_id' => $topic_id,
                'text' => $message,
                'parse_mode' => 'HTML',
            ]);
        } catch (\Exception $e) {
            \Log::warning("Telegram sendMessage error: " . $e->getMessage());
        }
    }

    public static function productCategoryUpdatedMessage(
        $product,
        $old_category_name,
        $old_sub_category_name,
        string $to = 'product',
        string $location_id = 'PT1001'
    ): void {
        try {
            $product->load(['category', 'sub_category']);
            $new_category_name = $product->category->name ?? 'None';
            $new_sub_category_name = $product->sub_category->name ?? 'None';
            $updated_by = self::getUpdatedBy();

            $msg = "<b>📂 PRODUCT CATEGORY UPDATED</b>\n\n";
            $msg .= "📦 <b>Product:</b> {$product->name}\n";
            $msg .= "🔢 <b>SKU:</b> {$product->sku}\n\n";

            $msg .= "📁 <b>Category:</b>\n";
            $msg .= "  • Old: {$old_category_name}\n";
            $msg .= "  • New: {$new_category_name}\n\n";

            if (!empty($old_sub_category_name) || !empty($new_sub_category_name)) {
                $msg .= "📂 <b>Sub Category:</b>\n";
                $msg .= "  • Old: " . (!empty($old_sub_category_name) ? $old_sub_category_name : 'None') . "\n";
                $msg .= "  • New: " . (!empty($new_sub_category_name) ? $new_sub_category_name : 'None') . "\n\n";
            }

            $msg .= "👤 <b>Updated By:</b> {$updated_by}\n";
            $msg .= "⏰ <b>Updated At:</b> " . now()->format('d/m/Y H:i') . "\n";
            $msg .= "✏️ <i>Updated via Shoper POS</i>";

            self::sendMessage($msg, $to, $location_id);
        } catch (\Exception $e) {
            \Log::warning("Telegram product category update notification error: " . $e->getMessage());
        }
    }

    public static function productDescriptionUpdatedMessage(
        $product,
        $old_description,
        string $to = 'product',
        string $location_id = 'PT1001'
    ): void {
        try {
            $updated_by = self::getUpdatedBy();
            $old_desc_text = !empty(trim(strip_tags($old_description))) ? trim(strip_tags($old_description)) : 'None';
            $new_desc_text = !empty(trim(strip_tags($product->product_description))) ? trim(strip_tags($product->product_description)) : 'None';

            $msg = "<b>📝 PRODUCT DESCRIPTION UPDATED</b>\n\n";
            $msg .= "📦 <b>Product:</b> {$product->name}\n";
            $msg .= "🔢 <b>SKU:</b> {$product->sku}\n\n";

            $msg .= "📄 <b>Old Description:</b>\n<i>{$old_desc_text}</i>\n\n";
            $msg .= "📝 <b>New Description:</b>\n<i>{$new_desc_text}</i>\n\n";

            $msg .= "👤 <b>Updated By:</b> {$updated_by}\n";
            $msg .= "⏰ <b>Updated At:</b> " . now()->format('d/m/Y H:i') . "\n";
            $msg .= "✏️ <i>Updated via Shoper POS</i>";

            self::sendMessage($msg, $to, $location_id);
        } catch (\Exception $e) {
            \Log::warning("Telegram product description update notification error: " . $e->getMessage());
        }
    }

    private static function getLocationAccountIds($location_id): array
    {
        $business_id = auth()->user()->business_id;
        $location = \App\BusinessLocation::where('business_id', $business_id)
            ->where('location_id', $location_id)
            ->first();

        $location_account_ids = [];
        if ($location && !empty($location->default_payment_accounts)) {
            $default_payment_accounts = json_decode($location->default_payment_accounts, true);
            if (is_array($default_payment_accounts)) {
                foreach ($default_payment_accounts as $pa) {
                    if (!empty($pa['is_enabled']) && !empty($pa['account'])) {
                        $location_account_ids[] = (int) $pa['account'];
                    }
                }
            }
        }
        return array_unique($location_account_ids);
    }
    private static function fetchAccounts($location_id = null): array
    {
        $business_id = auth()->user()->business_id;
        $query = Account::where('business_id', $business_id);

        if (!empty($location_id)) {
            $location_account_ids = self::getLocationAccountIds($location_id);
            $query->whereIn('accounts.id', $location_account_ids);
        }

        return $query->select(
            'accounts.name',
            'accounts.id',
            \DB::raw("(SELECT SUM(IF(account_transactions.type='credit', amount, -1*amount))
                      FROM account_transactions
                      WHERE account_transactions.account_id = accounts.id
                      AND deleted_at IS NULL) as balance")
        )
            ->get()
            ->map(fn($item) => [
                'name'    => $item->name,
                'id'      => $item->id,
                'balance' => number_format((float)$item->balance, 2),
            ])
            ->toArray();
    }
    // public static function sendErrorMessage(string $errorDescription, string $chatIdTried, string $targetKey, string $originalMessage): void
    // {
    //     $botToken = self::BOT["token"];
    //     $adminChatId = 1928945924; // Where to send error reports

    //     $text = "❌ <b>Failed to send message</b>\n"
    //         . "Chat ID: <code>{$chatIdTried}</code>\n"
    //         . "Target Key: <code>{$targetKey}</code>\n"
    //         . "Error: <i>{$errorDescription}</i>\n"
    //         . "Message Tried: \n<pre>{$originalMessage}</pre>\n"
    //         . "Time: " . date('Y-m-d H:i:s');

    //     Http::get("https://api.telegram.org/bot{$botToken}/sendMessage", [
    //         'chat_id' => $adminChatId,
    //         'text' => $text,
    //         'parse_mode' => 'HTML',
    //     ]);
    // }


    public static function addSaleMessage($receipt, string $to = '', $location_id = 'PT1001')
    {

        if (empty($receipt))
            return;
        // Shop info

        $msg = "<b>🏪 Shop:</b> {$receipt->display_name} ";

        if ($receipt->none_payment_account) {
            $msg .= "⚠️ <b>{$receipt->none_payment_account}</b>";
        }

        $msg .= "\n<b>📍 Address:</b> {$receipt->address}\n" .
            "<b>📱 Mobile:</b> {$receipt->contact}\n";
        if (isset($receipt->tax_info1)) {
            $msg .= "<b>🧾 VAT:</b> {$receipt->tax_info1}\n\n";
        }

        // Customer & Invoice
        $msg .= "<b>👤 Customer:</b> {$receipt->customer_name}\n" .
            "<b>📞 Mobile:</b> {$receipt->customer_mobile}\n\n" .
            "<b>🧾 Invoice No:</b> {$receipt->invoice_no}\n" .
            "<b>🕒 Date:</b> {$receipt->invoice_date}\n\n";


        if ($receipt->sub_type == "repair") {
            $to = "repair";
            $job_sheet = JobSheet::find($receipt->repair_job_sheet_id);
            $job_sheet->load([
                'customer',
                'technician',
                'status',
                'Brand',
                'Device',
                'deviceModel',
                'businessLocation',
            ]);
            info($job_sheet);
            // ── Repair Job Sheet Info ──────────────────────────────
            $statusName  = ($job_sheet->status && ($job_sheet->status_id ?? 0) != 0)
                ? ($job_sheet->status->name ?? 'N/A')
                : 'N/A';
            $brandName   = $job_sheet->Brand->name       ?? 'N/A';
            $deviceName  = $job_sheet->Device->name      ?? 'N/A';
            $modelName   = $job_sheet->deviceModel->name ?? 'N/A';
            $staffName   = $job_sheet->technician
                ? trim(
                    ($job_sheet->technician->surname    ?? '') . ' ' .
                        ($job_sheet->technician->first_name ?? '') . ' ' .
                        ($job_sheet->technician->last_name  ?? '')
                )
                : null;

            $jobSheetNo     = $job_sheet->job_sheet_no ?? 'N/A';
            $serviceType    = ucfirst(str_replace('_', ' ', $job_sheet->service_type ?? 'N/A'));
            $serialNo       = $job_sheet->serial_no ?? 'N/A';
            $securityPwd    = $job_sheet->security_pwd ?? null;
            $securityPattern = $job_sheet->security_pattern ?? null;
            $estimatedCost  = $job_sheet->estimated_cost
                ? number_format($job_sheet->estimated_cost, 2)
                : '0.00';
            $deliveryDate   = $job_sheet->delivery_date
                ? \Carbon\Carbon::parse($job_sheet->delivery_date)->format('d/m/Y H:i')
                : 'N/A';
            $productConfig  = self::decodeRepairField($job_sheet->product_configuration);
            $defects        = self::decodeRepairField($job_sheet->defects);
            $condition      = self::decodeRepairField($job_sheet->product_condition);
            $commentBySS    = $job_sheet->comment_by_ss ?? null;
            $pickUpAddr     = $job_sheet->pick_up_on_site_addr ?? null;

            $msg .= "━━━━━━━━━━━━━━━━━━━━\n";
            $msg .= "🔧 <b>REPAIR JOB SHEET INFO</b>\n";
            $msg .= "━━━━━━━━━━━━━━━━━━━━\n";

            $msg .= "<b>🔖 Job Sheet No:</b> #{$jobSheetNo}\n";
            $msg .= "<b>🛠️ Service Type:</b> {$serviceType}\n";
            $msg .= "<b>📌 Status:</b> {$statusName}\n";
            $msg .= "<b>📅 Due Date:</b> {$deliveryDate}\n";
            $msg .= "<b>💵 Estimated Cost:</b> \${$estimatedCost}\n\n";

            $msg .= "<b>📱 Brand:</b> {$brandName}\n";
            $msg .= "<b>📟 Device:</b> {$deviceName}\n";
            $msg .= "<b>🔩 Device Model:</b> {$modelName}\n";
            $msg .= "<b>🔢 Serial Number:</b> {$serialNo}\n";
            if ($securityPwd)      $msg .= "<b>🔑 Password:</b> {$securityPwd}\n";
            if ($securityPattern)  $msg .= "<b>🔐 Pattern Code:</b> {$securityPattern}\n";
            $msg .= "\n";

            if ($staffName)    $msg .= "<b>👨‍🔧 Technician:</b> {$staffName}\n";
            if ($commentBySS)  $msg .= "<b>💬 Technician Comment:</b> {$commentBySS}\n";
            if ($pickUpAddr)   $msg .= "<b>📍 Pick Up Address:</b> {$pickUpAddr}\n";
            if ($staffName || $commentBySS || $pickUpAddr) $msg .= "\n";

            if ($productConfig) $msg .= "<b>⚙️ Configuration:</b> {$productConfig}\n";
            if ($defects)       $msg .= "<b>🐛 Problem:</b> {$defects}\n";
            if ($condition)     $msg .= "<b>📋 Condition:</b> {$condition}\n";
            if ($productConfig || $defects || $condition) $msg .= "\n";

            // ── Checklist ──────────────────────────────────────────
            if (!empty($job_sheet->checklist)) {
                $checklist = is_array($job_sheet->checklist)
                    ? $job_sheet->checklist
                    : json_decode($job_sheet->checklist, true);

                if (!empty($checklist)) {
                    $msg .= "<b>✅ Pre Repair Checklist:</b>\n";
                    foreach ($checklist as $item => $value) {
                        $icon = $value == 1 ? '✅' : ($value == 0 ? '❌' : '➖');
                        $msg .= "  {$icon} {$item}\n";
                    }
                    $msg .= "\n";
                }
            }

            $msg .= "━━━━━━━━━━━━━━━━━━━━\n\n";
        }



        // Products
        $product_lines = '';
        $total_base = 0;
        foreach ($receipt->lines as $p) {
            $total_base += $p['line_total_uf'];
            $product_lines .= "<b>• {$p['name']} {$p['product_variation']} {$p['variation']}, {$p['sub_sku']}</b>\n" .
                "Remain stock: " . number_format($p['remain_stock'], 2) . " Pc(s)\n" .
                "Qty: {$p['quantity']} {$p['units']}\n" .
                "Unit: \${$p['unit_price_before_discount']}\n" .
                "Discount: {$p['line_discount']}\n";

            if ($p['line_discount'] != '0.00') {
                $subtotal = $p['line_total_uf'] + $p['total_line_discount'];
                $product_lines .= "Subtotal: \${$subtotal} (-{$p['total_line_discount']})\n";
            }

            $product_lines .= "Total: \${$p['line_total']}\n\n";
        }

        $msg .= "<b>🛒 Products:</b>\n{$product_lines}";



        preg_match('/([\d.]+)%/', $receipt->discount_label, $percentMatch);
        $discountPercent = (float) $percentMatch[1];


        preg_match('/([\d,]+\.?\d*)/', $receipt->discount, $amountMatch);
        $discountAmount = str_replace(',', '', $amountMatch[1] ?? '0');
        $discountAmount  = (float) $discountAmount;



        // Totals
        $discount = $receipt->total_line_discount != 0 ? $receipt->total_line_discount : $discountAmount;
        $discountLabel = $discountPercent != 0 ? "({$discountPercent}%)" : '';
        $msg .= "<b>🧾 Subtotal:</b> {$receipt->subtotal}\n" .
            "<b>🔻 Discount{$discountLabel}:</b> {$discount} $\n" .
            "<b>Total:</b> {$receipt->total}\n";

        // Exchange currencies
        $business_id = auth()->user()->business_id;
        $currencies = ExchangeCurrency::where('business_id', $business_id)
            ->where('is_use', 1)
            ->get();

        foreach ($currencies as $c) {
            $converted = number_format($receipt->total_unformatted * $c->exchange_rate, 2);
            $msg .= "<b>Total ({$c->symbol}):</b> {$c->symbol}{$converted}\n";
        }

        // Payments
        if (!empty($receipt->total_paid) && !empty($receipt->payments)) {
            foreach ($receipt->payments as $pay) {
                $msg .= "<b>💵 Payment:</b>\n" .
                    "Method: {$pay['method']}\n" .
                    "Amount: {$pay['amount']}\n" .
                    "Date: {$pay['date']}\n\n";
            }
        }

        // Final paid
        if (!empty($receipt->total_paid)) {
            $msg .= "<b>✅ Paid:</b> {$receipt->total_paid}\n";
        }

        $msg .= "🧾<b>PAYMENT ACCOUNT:</b>\n";

        if ($receipt->none_payment_account) {
            $msg .= "None : <b>{$receipt->none_payment_account}</b> \n";
        }

        if (filled($receipt->payment_account)) {

            foreach ($receipt->payment_account as $account) {
                $msg .= "{$account['name']}: {$account['balance']}\n";
            }
        }

        if (filled($receipt->all_account)) {
            $msg .= "🧾<b>LIST ACCOUNT:</b>\n";

            foreach ($receipt->all_account as $account) {
                $msg .= "{$account['name']}: {$account['balance']}\n";
            }
        }

        self::sendMessage($msg, $to, $location_id);
    }
    public static function updateSaleMessage($receipt, $old_receipt, string $to = '', $location_id = 'PT1001')
    {
        if (empty($receipt) || empty($old_receipt))
            return;

        $msg = "🔄 <b>Updated</b>\n\n";

        // 🧾 Basic Info
        $msg .= "<b>🏪 Shop:</b> {$receipt->display_name} ";
        if ($old_receipt->none_payment_account || $receipt->none_payment_account) {
            $msg = "⚠️ <s><b>{$old_receipt->none_payment_account}</b></s> →  <b>{$receipt->none_payment_account}</b>";
        }

        $msg .= "\n <b>🧾 Invoice No:</b> " . self::diff($old_receipt->invoice_no, $receipt->invoice_no) . "\n";
        $msg .= "<b>🕒 Date:</b> " . self::diff($old_receipt->invoice_date, $receipt->invoice_date) . "\n\n";


        $msg .= "<b>👤 Customer:</b> " . self::diff($old_receipt->customer_name, $receipt->customer_name) . "\n";
        $msg .= "<b>📞 Mobile:</b> " . self::diff($old_receipt->customer_mobile, $receipt->customer_mobile) . "\n\n";

        // 🛒 Product comparison
        $msg .= "<b>🛒 Products:</b>\n";


        $old_products = collect($old_receipt->lines)->keyBy('sub_sku');
        $new_products = collect($receipt->lines)->keyBy('sub_sku');


        foreach ($new_products as $sku => $new) {
            $old = $old_products->get($sku);

            if ($old) {
                $msg .= "<b>• {$new['name']} {$new['product_variation']} {$new['variation']}, {$new['sub_sku']}</b>\n";
                $msg .= "Remain stock: <s>" . number_format($old['remain_stock'], 2) . " Pc(s)</s> → " . number_format($new['remain_stock'], 2) . " Pc(s)\n";
                $msg .= "Qty: <s>{$old['quantity']}</s> → {$new['quantity']} {$new['units']}\n";
                $msg .= "Unit Price: " . self::money($old['unit_price_before_discount'], $new['unit_price_before_discount']) . "\n";
                $msg .= "Discount : " . self::diff($old['line_discount'], $new['line_discount']) . "\n";

                if ($old['line_discount'] != '0.00' || $new['line_discount'] != '0.00') {
                    $old_subtotal = $old['line_total_uf'] + $old['total_line_discount'];
                    $new_subtotal = $new['line_total_uf'] + $new['total_line_discount'];
                    $msg .= "Subtotal: " . self::money($old_subtotal, $new_subtotal) . "\n";
                }

                $msg .= "Total: " . self::money($old['line_total'], $new['line_total']) . "\n\n";
            } else {
                // 🆕 New Product
                $msg .= "<b>• {$new['name']} {$new['product_variation']} {$new['variation']}, {$new['sub_sku']}</b>\n";
                $msg .= "Remain stock: " . number_format($new['remain_stock'], 2) . " Pc(s)\n";
                $msg .= "Qty: {$new['quantity']} {$new['units']}\n";
                $msg .= "Unit Price: \${$new['unit_price_before_discount']}\n";
                $msg .= "Discount : {$new['line_discount']}\n";

                if ($new['line_discount'] != '0.00') {
                    $subtotal = $new['line_total_uf'] + $new['total_line_discount'];
                    $msg .= "Subtotal: \${$subtotal}\n";
                }

                $msg .= "Total: \${$new['line_total']}\n\n";
            }
        }
        // ❌ Removed products
        foreach ($old_products as $sku => $old) {
            if (!$new_products->has($sku)) {
                $msg .= "<b>•<s> {$old['name']} {$old['product_variation']} {$old['variation']}, {$old['sub_sku']}</s></b>\n";
                $msg .= "Remain stock: " . number_format($old['remain_stock'], 2) . " Pc(s)\n";
                $msg .= "Qty:<s> {$old['quantity']} {$old['units']}</s>\n";
                $msg .= "Unit Price:<s> \${$old['unit_price_before_discount']}</s>\n";
                $msg .= "Discount :<s> {$old['line_discount']}</s>\n";

                if ($old['line_discount'] != '0.00') {
                    $subtotal = $old['line_total_uf'] + $old['total_line_discount'];
                    $msg .= "<s>Subtotal: \${$subtotal}</s>\n";
                }

                $msg .= "Total:<s> \${$old['line_total']}</s>\n\n";
            }
        }

        preg_match('/([\d.]+)%/', $receipt->discount_label, $percentMatch);
        $discountPercent = (float) $percentMatch[1];


        preg_match('/([\d,]+\.?\d*)/', $receipt->discount, $amountMatch);
        $discountAmount = str_replace(',', '', $amountMatch[1] ?? '0');
        $discountAmount  = (float) $discountAmount;


        $discount = $receipt->total_line_discount != 0 ? $receipt->total_line_discount : $discountAmount;
        $discountLabel = $discountPercent != 0 ? "({$discountPercent}%)" : '';


        preg_match('/([\d.]+)%/', $old_receipt->discount_label, $percentMatch);
        $discountPercent = (float) $percentMatch[1];


        preg_match('/([\d,]+\.?\d*)/', $old_receipt->discount, $amountMatch);
        $oldDiscountAmount = str_replace(',', '', $amountMatch[1] ?? '0');
        $oldDiscountAmount  = (float) $oldDiscountAmount;


        $oldDiscount = $old_receipt->total_line_discount != 0 ? $old_receipt->total_line_discount : $oldDiscountAmount;
        $oldDiscountLabel = $discountPercent != 0 ? "({$discountPercent}%)" : '';


        // 💵 Totals`
        $msg .= "<b>🧾 Subtotal:</b> " . self::diff($old_receipt->subtotal, $receipt->subtotal) . "\n";
        $msg .= "<b>🔻 Discount" . self::diff($oldDiscountLabel, $discountLabel) . ":</b> " . self::diff($oldDiscount, $discount) . "\n";
        $msg .= "<b>Total:</b> " . self::diff($old_receipt->total, $receipt->total) . "\n";

        // 🌍 Exchange Currency Comparison
        $business_id = auth()->user()->business_id;
        $currencies = ExchangeCurrency::where('business_id', $business_id)->where('is_use', 1)->get();

        // $old_base = collect($old_receipt->lines)->sum('line_total_uf');
        // $new_base = collect($receipt->lines)->sum('line_total_uf');
        $old_base = $old_receipt->total_unformatted;
        $new_base = $receipt->total_unformatted;

        foreach ($currencies as $c) {
            $old_converted = number_format($old_base * $c->exchange_rate, 2);
            $new_converted = number_format($new_base * $c->exchange_rate, 2);

            if ($old_converted != $new_converted) {
                $msg .= "<b>Total ({$c->symbol}):</b> <s>{$c->symbol}{$old_converted}</s> → {$c->symbol}{$new_converted}\n";
            } else {
                $msg .= "<b>Total ({$c->symbol}):</b> {$c->symbol}{$new_converted}\n";
            }
        }

        // 💳 Payments
        if (!empty($receipt->payments)) {
            $msg .= "\n<b>💳 Payments:</b>\n";
            foreach ($receipt->payments as $i => $pay) {
                $old = $old_receipt->payments[$i] ?? null;
                if ($old) {
                    $msg .= "Method: " . self::diff($old['method'], $pay['method']) . "\n";
                    $msg .= "Amount: " . self::diff($old['amount'], $pay['amount']) . "\n";
                    $msg .= "Date: " . self::diff($old['date'], $pay['date']) . "\n\n";
                } else {
                    $msg .= "🆕 {$pay['method']} - {$pay['amount']} on {$pay['date']}\n\n";
                }
            }
        }

        // ✅ Paid
        if (isset($old_receipt->total_paid) || isset($receipt->total_paid)) {
            $old_paid = $old_receipt->total_paid ?? 0;
            $new_paid = $receipt->total_paid ?? 0;
            $msg .= "<b>✅ Paid:</b> " . self::diff($old_paid, $new_paid) . "\n";
        }

        $msg .= "🧾<b>PAYMENT ACCOUNT:</b>\n";

        if ($old_receipt->none_payment_account || $receipt->none_payment_account) {
            $msg .= "None : <s>{$old_receipt->none_payment_account}</s> → <b>{$receipt->none_payment_account}</b>";
        }


        if (filled($receipt->payment_account) || filled($old_receipt->payment_account)) {
            foreach ($old_receipt->payment_account as $index => $account) {
                $msg .= "{$account['name']}: <s>{$account['balance']}</s> → {$receipt->payment_account[$index]["balance"]}\n";
            }
        }



        if (filled($receipt->all_account) || filled($old_receipt->all_account)) {
            $msg .= "\n🧾<b>LIST ACCOUNT:</b>\n";

            foreach ($old_receipt->all_account as $index => $account) {
                $msg .= "{$account['name']}: <s>{$account['balance']}</s> → {$receipt->all_account[$index]["balance"]}\n";
            }
        }

        self::sendMessage($msg, $to, $location_id);
    }
    public static function deleteSaleMessage($receipt, string $to = '', $location_id = 'PT1001')
    {
        if (empty($receipt))
            return;

        $msg = "❌ <b>Deleted</b>\n\n";

        // Shop info
        $msg .= "<b>🏪 Shop:</b> {$receipt->display_name} ";
        if ($receipt->none_payment_account) {
            $msg .= "⚠️ <b>{$receipt->none_payment_account}</b>";
        }

        $msg .= "\n<b>📍 Address:</b> {$receipt->address}\n" .
            "<b>📱 Mobile:</b> {$receipt->contact}\n";
        if (isset($receipt->tax_info1)) {
            $msg .= "<b>🧾 VAT:</b> {$receipt->tax_info1}\n\n";
        }


        // Customer & Invoice
        $msg .= "<b>👤 Customer:</b> {$receipt->customer_name}\n" .
            "<b>📞 Mobile:</b> {$receipt->customer_mobile}\n\n" .
            "<b>🧾 Invoice No:</b> {$receipt->invoice_no}\n" .
            "<b>🕒 Date:</b> {$receipt->invoice_date}\n\n";

        // Products
        $product_lines = '';
        $total_base = 0;
        foreach ($receipt->lines as $p) {
            $total_base += $p['line_total_uf'];
            $product_lines .= "<b>• {$p['name']} {$p['product_variation']} {$p['variation']}, {$p['sub_sku']}</b>\n" .
                "Remain stock: " . number_format($p['remain_stock'], 2) . " Pc(s)\n" .
                "Qty: {$p['quantity']} {$p['units']}\n" .
                "Unit: \${$p['unit_price_before_discount']}\n" .
                "Discount: {$p['line_discount']}\n";

            if ($p['line_discount'] != '0.00') {
                $subtotal = $p['line_total_uf'] + $p['total_line_discount'];
                $product_lines .= "Subtotal: \${$subtotal} (-{$p['total_line_discount']})\n";
            }

            $product_lines .= "Total: \${$p['line_total']}\n\n";
        }

        $msg .= "<b>🛒 Products:</b>\n{$product_lines}";

        // Totals
        $msg .= "<b>🧾 Subtotal:</b> {$receipt->subtotal}\n" .
            "<b>🔻 Discount:</b> {$receipt->total_line_discount}\n" .
            "<b>Total:</b> {$receipt->total}\n";

        // Exchange currencies
        $business_id = auth()->user()->business_id;
        $currencies = ExchangeCurrency::where('business_id', $business_id)
            ->where('is_use', 1)
            ->get();

        foreach ($currencies as $c) {
            $converted = number_format($total_base * $c->exchange_rate, 2);
            $msg .= "<b>Total ({$c->symbol}):</b> {$c->symbol}{$converted}\n";
        }

        // Payments
        if (!empty($receipt->total_paid) && !empty($receipt->payments)) {
            foreach ($receipt->payments as $pay) {
                $msg .= "<b>💵 Payment:</b>\n" .
                    "Method: {$pay['method']}\n" .
                    "Amount: {$pay['amount']}\n" .
                    "Date: {$pay['date']}\n\n";
            }
        }

        // Final paid
        if (!empty($receipt->total_paid)) {
            $msg .= "<b>✅ Paid:</b> {$receipt->total_paid}\n";
        }


        $msg .= "🧾<b>PAYMENT ACCOUNT:</b>\n";

        if ($receipt->none_payment_account) {
            $msg .= "None : <b>{$receipt->none_payment_account}</b> \n";
        }

        if (filled($receipt->payment_account)) {

            foreach ($receipt->payment_account as $account) {
                $msg .= "{$account['name']}: {$account['balance']}\n";
            }
        }

        if (filled($receipt->all_account)) {
            $msg .= "🧾<b>LIST ACCOUNT:</b>\n";

            foreach ($receipt->all_account as $account) {
                $msg .= "{$account['name']}: {$account['balance']}\n";
            }
        }


        self::sendMessage($msg, $to, $location_id);
    }

    public static function returnSell($receipt, $transaction_id, string $to = '', $location_id = 'PT1001')
    {

        //get payment account
        $account = AccountTransaction::join('accounts', 'account_transactions.account_id', '=', 'accounts.id')
            ->where('transaction_id', $transaction_id)
            ->select('accounts.name', 'accounts.id', DB::raw("(SELECT SUM( IF(account_transactions.type='credit', amount, -1*amount) ) as balance from account_transactions where account_transactions.account_id = accounts.id AND deleted_at is NULL) as balance"))
            ->get();


        if (empty($receipt))
            return;
        if ($account->isNotEmpty()) {
            $receipt->payment_account = $account->map(function ($item) {
                return [
                    'name' => $item->name,
                    'id' => $item->id,
                    'balance' => $this->num_f($item->balance, true),
                ];
            })->toArray();
        } else {
            $receipt->payment_account = null;
        }

        // Shop Info
        $msg = "<b>♻️ RETURN RECEIPT</b>\n\n";
        $msg .= "<b>🏪 Shop:</b> {$receipt->display_name}\n" .
            "<b>📍 Address:</b> {$receipt->address}\n" .
            "<b>📱 Mobile:</b> {$receipt->contact}\n";

        if (isset($receipt->tax_info1)) {
            $msg .= "<b>🧾 VAT:</b> {$receipt->tax_info1}\n\n";
        }

        // Customer & Invoice
        $msg .= "<b>👤 Customer:</b> {$receipt->customer_name}\n" .
            "<b>📞 Mobile:</b> {$receipt->customer_mobile}\n\n" .
            "<b>🧾 Return No:</b> {$receipt->invoice_no}\n" .
            "<b>🕒 Date:</b> {$receipt->invoice_date}\n\n";

        // Returned Products
        $product_lines = '';
        // $total_base = 0;

        foreach ($receipt->lines as $p) {
            // $total_base += $p['line_total'];
            $product_lines .= "<b>• {$p['name']} {$p['product_variation']} {$p['variation']}, {$p['sub_sku']}</b>\n" .
                "Remain stock: " . number_format($p['remain_stock'], 2) . " Pc(s)\n" .
                "Qty: {$p['quantity']} {$p['units']}\n" .
                "Unit: \${$p['unit_price']}\n";

            if (!empty($p['line_discount']) && $p['line_discount'] != '0.00') {
                $subtotal = $p['line_total_uf'] + $p['total_line_discount'];
                $product_lines .= "Subtotal: \${$subtotal} (-{$p['total_line_discount']})\n";
            }

            $product_lines .= "Total: \${$p['line_total']}\n\n";
        }


        $msg .= "<b>🛒 Returned Products:</b>\n{$product_lines}";
        // Totals

        $msg .= "<b>🧾 Subtotal:</b> {$receipt->subtotal}\n";

        if ($receipt->discount != "0.00") {
            if ($receipt->discount_label == "Discount") {
                $msg .= "<b>🔻 Discount:</b> {$receipt->discount}\n";
            } else {
                $label = $receipt->discount_label;
                $cleanLabel = str_replace(['<small>', '</small>'], '', $label);

                $msg .= "<b>🔻 {$cleanLabel}</b> {$receipt->discount}\n";
            }
        }
        $msg .= "<b>💰 Total Refund:</b> {$receipt->total}\n";
        $numericTotal = (float) preg_replace('/[^\d.-]/', '', $receipt->total);

        // Currency conversion
        $business_id = auth()->user()->business_id;
        $currencies = ExchangeCurrency::where('business_id', $business_id)
            ->where('is_use', 1)
            ->get();

        foreach ($currencies as $c) {
            $converted = number_format($numericTotal * $c->exchange_rate, 2);
            $msg .= "<b>Total Refund In ({$c->symbol}):</b> {$c->symbol}{$converted}\n";
        }

        // Refund Payments (optional block)
        if (!empty($receipt->total_paid) && !empty($receipt->payments)) {
            foreach ($receipt->payments as $pay) {
                $msg .= "<b>💵 Refund Method:</b>\n" .
                    "Method: {$pay['method']}\n" .
                    "Amount: {$pay['amount']}\n" .
                    "Date: {$pay['date']}\n\n";
            }
        }

        if (!empty($receipt->total_paid)) {
            $msg .= "<b>✅ Refunded:</b> {$receipt->total_paid}\n";
        }

        if (!empty($receipt->payment_account)) {
            $msg .= "🧾<b>PAYMENT ACCOUNT:</b>\n";

            foreach ($receipt->payment_account as $account) {
                $msg .= "{$account['name']}: {$account['balance']}\n";
            }
        }


        self::sendMessage($msg, $to, $location_id);
    }
    public function num_f($input_number, $add_symbol = false, $business_details = null, $is_quantity = false)
    {
        $thousand_separator = ! empty($business_details) ? $business_details->thousand_separator : session('currency')['thousand_separator'];
        $decimal_separator = ! empty($business_details) ? $business_details->decimal_separator : session('currency')['decimal_separator'];

        $currency_precision = ! empty($business_details) ? $business_details->currency_precision : session('business.currency_precision', 2);

        if ($is_quantity) {
            $currency_precision = ! empty($business_details) ? $business_details->quantity_precision : session('business.quantity_precision', 2);
        }

        $formatted = number_format($input_number, $currency_precision, $decimal_separator, $thousand_separator);

        if ($add_symbol) {
            $currency_symbol_placement = ! empty($business_details) ? $business_details->currency_symbol_placement : session('business.currency_symbol_placement');
            $symbol = ! empty($business_details) ? $business_details->currency_symbol : session('currency')['symbol'];

            if ($currency_symbol_placement == 'after') {
                $formatted = $formatted . ' ' . $symbol;
            } else {
                $formatted = $symbol . ' ' . $formatted;
            }
        }

        return $formatted;
    }
    private static function diff($old, $new, $suffix = '')
    {
        if ((string)$old === (string)$new) {
            return $new . $suffix;
        }
        return "<s>{$old}</s> → {$new}{$suffix}";
    }

    private static function money($old, $new)
    {
        $o = number_format(self::num($old), 2);
        $n = number_format(self::num($new), 2);

        if ($o === $n) return "\${$n}";
        return "<s>\${$o}</s> → \${$n}";
    }
    private static function num($v)
    {
        if ($v === null || $v === '') return 0;
        return (float) str_replace(',', '', $v);
    }


    public static function addProductMessage($product, $combos, string $to = 'product', $location_id = 'PT1001')
    {
        if (empty($product)) return;

        $typeIcon = match ($product->type) {
            'single'   => '🔵',
            'variable' => '🟡',
            'combo'    => '🟢',
            default    => '📦',
        };

        $locations = $product->product_locations->pluck('name')->implode(', ') ?: 'N/A';

        // ── Base Info ──────────────────────────────────────────
        $msg  = "🆕 <b>NEW PRODUCT ADDED</b>\n\n";
        $msg .= "<b>📍 Business Locations:</b> {$locations}\n";
        $msg .= "<b>📦 Product Name:</b> {$product->name}\n";
        $msg .= "<b>🔢 SKU:</b> {$product->sku}\n";
        $msg .= "<b>📊 Barcode Type:</b> {$product->barcode_type}\n";
        $msg .= "<b>📐 Unit:</b> " . ($product->unit->actual_name ?? 'N/A') . "\n";
        $msg .= "<b>🏷 Brand:</b> " . ($product->brand->name ?? 'N/A') . "\n";
        $msg .= "<b>⚖️ Weight:</b> " . ($product->weight ?? 'N/A') . "\n";
        $msg .= "<b>📂 Category:</b> " . ($product->category->name ?? 'N/A') . "\n";
        $msg .= "<b>📁 Sub Category:</b> " . ($product->sub_category->name ?? 'N/A') . "\n";
        $msg .= "<b>⏱ Service Timer:</b> " . ($product->preparation_time_in_minutes ? $product->preparation_time_in_minutes . ' min' : 'N/A') . "\n";
        $msg .= "<b>🔲 Manage Stock:</b> " . ($product->enable_stock ? 'Yes' : 'No') . "\n";
        $msg .= "<b>🚫 Not for Selling:</b> " . ($product->not_for_selling ? 'Yes' : 'No') . "\n";
        $msg .= "<b>📝 Description:</b> " . ($product->product_description ?? 'N/A') . "\n\n";
        $msg .= "<b>🧾 Applicable Tax:</b> " . ($product->product_tax->name ?? 'N/A') . "\n";
        $msg .= "<b>💳 Tax Type:</b> " . ucfirst($product->tax_type) . "\n";
        $msg .= "{$typeIcon} <b>Product Type:</b> " . strtoupper($product->type) . "\n";

        // ── SINGLE ─────────────────────────────────────────────
        if ($product->type === 'single') {
            $v = $product->variations->first();
            $msg .= "\n<b>💰 PRICING</b>\n";
            $msg .= "🛒 <b>Purchase (Exc. Tax):</b> \${$v->default_purchase_price}\n";
            $msg .= "🛒 <b>Purchase (Inc. Tax):</b> \${$v->dpp_inc_tax}\n";
            $msg .= "📈 <b>Margin:</b> {$v->profit_percent}%\n";
            $msg .= "💵 <b>Selling (Exc. Tax):</b> \${$v->default_sell_price}\n";
            $msg .= "💵 <b>Selling (Inc. Tax):</b> \${$v->sell_price_inc_tax}\n";
        }

        // ── VARIABLE ───────────────────────────────────────────
        elseif ($product->type === 'variable') {
            $msg .= "\n<b>🎨 VARIATIONS</b>\n";

            $groups = $product->variations->groupBy(fn($v) => $v->product_variation->name);

            foreach ($groups as $groupName => $variations) {
                $msg .= "\n🔖 <b>" . ucfirst($groupName) . "</b>\n";
                foreach ($variations as $v) {
                    $msg .= "• <b>{$groupName} - {$v->name}</b> | SKU: {$v->sub_sku}\n";
                    $msg .= "  Purchase (Exc. Tax): \${$v->default_purchase_price}\n";
                    $msg .= "  Purchase (Inc. Tax): \${$v->dpp_inc_tax}\n";
                    $msg .= "  Margin: {$v->profit_percent}%\n";
                    $msg .= "  Selling (Exc. Tax): \${$v->default_sell_price}\n";
                    $msg .= "  Selling (Inc. Tax): \${$v->sell_price_inc_tax}\n";
                }
            }
        }

        // ── COMBO ──────────────────────────────────────────────
        elseif ($product->type === 'combo') {
            $v = $product->variations->first();

            // ✅ Use the $combos parameter passed in — NOT $v->combo_variations
            $msg .= "\n<b>🧩 COMBO ITEMS</b>\n";

            $netTotal = 0;
            foreach ($combos as $item) {
                $variation = $item['variation'] ?? null;
                $itemProd  = $variation['product'] ?? null;
                $itemName  = $itemProd['name'] ?? 'N/A';
                $varName   = (!empty($variation['name']) && $variation['name'] !== 'DUMMY') ? ' - ' . $variation['name'] : '';
                $subSku    = $variation['sub_sku'] ?? 'N/A';
                $qty       = $item['quantity'] ?? 1;
                $unitName  = $item['unit_name'] ?? 'N/A';
                $purchase  = $variation['default_purchase_price'] ?? 0;
                $selling   = $variation['default_sell_price'] ?? 0;
                $margin    = $variation['profit_percent'] ?? 0;
                $total     = $qty * $purchase;
                $netTotal += $total;

                $msg .= "\n• <b>{$itemName}{$varName}</b> | SKU: {$subSku}\n";
                $msg .= "  Qty: {$qty} {$unitName}\n";
                $msg .= "  Purchase (Exc. Tax): \${$purchase}\n";
                $msg .= "  Margin: {$margin}%\n";
                $msg .= "  Selling (Exc. Tax): \${$selling}\n";
                $msg .= "  Item Total: \$" . number_format($total, 2) . "\n";
            }

            $msg .= "\n💰 <b>Net Total Amount:</b> \$" . number_format($netTotal, 2) . "\n";
            $msg .= "📈 <b>Margin:</b> {$v->profit_percent}%\n";
            $msg .= "💵 <b>Default Selling Price:</b> \${$v->default_sell_price}\n";
        }

        $msg .= "\n⏰ <b>Date Added:</b> " . now()->format('d/m/Y H:i') . "\n";
        $msg .= "✅ <i>Saved via Shoper POS</i>";

        // ── Send with image if available ──
        $hasImage = !empty($product->image) && $product->image !== 'default.png';

        if ($hasImage && !empty($product->image_url)) {
            self::sendPhoto($product->image_url, $msg, $to, $location_id);
        } else {
            self::sendMessage($msg, $to, $location_id);
        }
    }
    public static function updateProductMessage($product, $old, $combos, string $to = 'product', $location_id = 'PT1001')
    {
        if (empty($product)) return;

        $typeIcon = match ($product->type) {
            'single'   => '🔵',
            'variable' => '🟡',
            'combo'    => '🟢',
            default    => '📦',
        };

        $newLocations = $product->product_locations->pluck('name')->implode(', ') ?: 'N/A';

        // ── Base Info with diff ────────────────────────────────
        $msg  = "✏️ <b>PRODUCT UPDATED</b>\n\n";
        $msg .= "<b>📍 Business Locations:</b> " . self::diff($old['locations'], $newLocations) . "\n";
        $msg .= "<b>📦 Product Name:</b> " . self::diff($old['name'], $product->name) . "\n";
        $msg .= "<b>🔢 SKU:</b> " . self::diff($old['sku'], $product->sku) . "\n";
        $msg .= "<b>📊 Barcode Type:</b> " . self::diff($old['barcode_type'], $product->barcode_type) . "\n";
        $msg .= "<b>📐 Unit:</b> " . self::diff($old['unit'], $product->unit->actual_name ?? 'N/A') . "\n";
        $msg .= "<b>🏷 Brand:</b> " . self::diff($old['brand'], $product->brand->name ?? 'N/A') . "\n";
        $msg .= "<b>⚖️ Weight:</b> " . self::diff($old['weight'], $product->weight ?? 'N/A') . "\n";
        $msg .= "<b>📂 Category:</b> " . self::diff($old['category'], $product->category->name ?? 'N/A') . "\n";
        $msg .= "<b>📁 Sub Category:</b> " . self::diff($old['sub_category'], $product->sub_category->name ?? 'N/A') . "\n";

        $oldTimer = $old['preparation_time_in_minutes'] ? $old['preparation_time_in_minutes'] . ' min' : 'N/A';
        $newTimer = $product->preparation_time_in_minutes ? $product->preparation_time_in_minutes . ' min' : 'N/A';
        $msg .= "<b>⏱ Service Timer:</b> " . self::diff($oldTimer, $newTimer) . "\n";

        $msg .= "<b>🔲 Manage Stock:</b> " . self::diff($old['enable_stock'] ? 'Yes' : 'No', $product->enable_stock ? 'Yes' : 'No') . "\n";
        $msg .= "<b>🚫 Not for Selling:</b> " . self::diff($old['not_for_selling'] ? 'Yes' : 'No', $product->not_for_selling ? 'Yes' : 'No') . "\n";
        $msg .= "<b>📝 Description:</b> " . self::diff($old['product_description'], $product->product_description ?? 'N/A') . "\n\n";
        $msg .= "<b>🧾 Applicable Tax:</b> " . self::diff($old['tax'], $product->product_tax->name ?? 'N/A') . "\n";
        $msg .= "<b>💳 Tax Type:</b> " . self::diff(ucfirst($old['tax_type']), ucfirst($product->tax_type)) . "\n";
        $msg .= "{$typeIcon} <b>Product Type:</b> " . strtoupper($product->type) . "\n";

        // ── SINGLE ─────────────────────────────────────────────
        if ($product->type === 'single') {
            $v    = $product->variations->first();
            $oldV = collect($old['variations'])->first() ?? [];

            $msg .= "\n<b>💰 PRICING</b>\n";
            $msg .= "🛒 <b>Purchase (Exc. Tax):</b> " . self::diff($oldV['default_purchase_price'] ?? 'N/A', $v->default_purchase_price) . "\n";
            $msg .= "🛒 <b>Purchase (Inc. Tax):</b> " . self::diff($oldV['dpp_inc_tax'] ?? 'N/A', $v->dpp_inc_tax) . "\n";
            $msg .= "📈 <b>Margin:</b> " . self::diff($oldV['profit_percent'] ?? 'N/A', $v->profit_percent, '%') . "\n";
            $msg .= "💵 <b>Selling (Exc. Tax):</b> " . self::diff($oldV['default_sell_price'] ?? 'N/A', $v->default_sell_price) . "\n";
            $msg .= "💵 <b>Selling (Inc. Tax):</b> " . self::diff($oldV['sell_price_inc_tax'] ?? 'N/A', $v->sell_price_inc_tax) . "\n";
        }

        // ── VARIABLE ───────────────────────────────────────────
        elseif ($product->type === 'variable') {
            $msg .= "\n<b>🎨 VARIATIONS</b>\n";

            $oldVariations = collect($old['variations'])->keyBy('sub_sku');
            $groups        = $product->variations->groupBy(fn($v) => $v->product_variation->name);

            foreach ($groups as $groupName => $variations) {
                $msg .= "\n🔖 <b>" . ucfirst($groupName) . "</b>\n";
                foreach ($variations as $v) {
                    $oldV = $oldVariations->get($v->sub_sku) ?? [];

                    $msg .= "• <b>{$groupName} - {$v->name}</b> | SKU: {$v->sub_sku}\n";
                    $msg .= "  Purchase (Exc. Tax): " . self::diff($oldV['default_purchase_price'] ?? 'N/A', $v->default_purchase_price) . "\n";
                    $msg .= "  Purchase (Inc. Tax): " . self::diff($oldV['dpp_inc_tax'] ?? 'N/A', $v->dpp_inc_tax) . "\n";
                    $msg .= "  Margin: " . self::diff($oldV['profit_percent'] ?? 'N/A', $v->profit_percent, '%') . "\n";
                    $msg .= "  Selling (Exc. Tax): " . self::diff($oldV['default_sell_price'] ?? 'N/A', $v->default_sell_price) . "\n";
                    $msg .= "  Selling (Inc. Tax): " . self::diff($oldV['sell_price_inc_tax'] ?? 'N/A', $v->sell_price_inc_tax) . "\n";
                }
            }
        }

        // ── COMBO ──────────────────────────────────────────────
        elseif ($product->type === 'combo') {
            $v         = $product->variations->first();
            $oldCombos = collect($old['combo_variations'] ?? [])->keyBy(
                fn($i) => $i['variation']['sub_sku'] ?? ''
            );

            $msg .= "\n<b>🧩 COMBO ITEMS</b>\n";

            $netTotal    = 0;
            $oldNetTotal = 0;

            // Calculate old net total
            foreach ($old['combo_variations'] ?? [] as $oldItem) {
                $oldV         = $oldItem['variation'] ?? [];
                $oldNetTotal += ($oldItem['quantity'] ?? 1) * ($oldV['default_purchase_price'] ?? 0);
            }

            // New items with diff
            foreach ($combos as $item) {
                $variation = $item['variation'] ?? null;
                $itemProd  = $variation['product'] ?? null;
                $itemName  = $itemProd['name'] ?? 'N/A';
                $varName   = (!empty($variation['name']) && $variation['name'] !== 'DUMMY') ? ' - ' . $variation['name'] : '';
                $subSku    = $variation['sub_sku'] ?? 'N/A';
                $qty       = $item['quantity'] ?? 1;
                $unitName  = $item['unit_name'] ?? 'N/A';
                $purchase  = $variation['default_purchase_price'] ?? 0;
                $selling   = $variation['default_sell_price'] ?? 0;
                $margin    = $variation['profit_percent'] ?? 0;
                $total     = $qty * $purchase;
                $netTotal += $total;

                $oldItem = $oldCombos->get($subSku);
                $oldV    = $oldItem['variation'] ?? null;

                $msg .= "\n• <b>{$itemName}{$varName}</b> | SKU: {$subSku}\n";

                if ($oldV) {
                    // Existing item — show diff
                    $msg .= "  Qty: " . self::diff($oldItem['quantity'] ?? 1, $qty) . " {$unitName}\n";
                    $msg .= "  Purchase (Exc. Tax): " . self::diff($oldV['default_purchase_price'] ?? 0, $purchase) . "\n";
                    $msg .= "  Margin: " . self::diff($oldV['profit_percent'] ?? 0, $margin, '%') . "\n";
                    $msg .= "  Selling (Exc. Tax): " . self::diff($oldV['default_sell_price'] ?? 0, $selling) . "\n";
                    $msg .= "  Item Total: " . self::diff(
                        '$' . number_format(($oldItem['quantity'] ?? 1) * ($oldV['default_purchase_price'] ?? 0), 2),
                        '$' . number_format($total, 2)
                    ) . "\n";
                } else {
                    // 🆕 Newly added item
                    $msg .= "  🆕 <i>New item added</i>\n";
                    $msg .= "  Qty: {$qty} {$unitName}\n";
                    $msg .= "  Purchase (Exc. Tax): \${$purchase}\n";
                    $msg .= "  Margin: {$margin}%\n";
                    $msg .= "  Selling (Exc. Tax): \${$selling}\n";
                    $msg .= "  Item Total: \$" . number_format($total, 2) . "\n";
                }
            }

            // ❌ Removed items
            $newSkus = collect($combos)->map(fn($i) => $i['variation']['sub_sku'] ?? '');
            foreach ($old['combo_variations'] ?? [] as $oldItem) {
                $oldV    = $oldItem['variation'] ?? [];
                $oldSku  = $oldV['sub_sku'] ?? '';
                $oldProd = $oldV['product'] ?? [];

                if (! $newSkus->contains($oldSku)) {
                    $oldName = $oldProd['name'] ?? 'N/A';
                    $msg .= "\n• <s><b>{$oldName}</b> | SKU: {$oldSku}</s> ❌ <i>Removed</i>\n";
                }
            }

            $oldV = collect($old['variations'])->first() ?? [];
            $msg .= "\n💰 <b>Net Total Amount:</b> " . self::diff(
                '$' . number_format($oldNetTotal, 2),
                '$' . number_format($netTotal, 2)
            ) . "\n";
            $msg .= "📈 <b>Margin:</b> " . self::diff($oldV['profit_percent'] ?? 'N/A', $v->profit_percent, '%') . "\n";
            $msg .= "💵 <b>Default Selling Price:</b> " . self::diff($oldV['default_sell_price'] ?? 'N/A', $v->default_sell_price) . "\n";
        }

        $msg .= "\n⏰ <b>Date Updated:</b> " . now()->format('d/m/Y H:i') . "\n";
        $msg .= "✅ <i>Updated via Shoper POS</i>";

        // ── Send with image if available ──
        $hasImage = !empty($product->image) && $product->image !== 'default.png';

        if ($hasImage && !empty($product->image_url)) {
            self::sendPhoto($product->image_url, $msg, $to, $location_id);
        } else {
            self::sendMessage($msg, $to, $location_id);
        }
    }
    public static function deleteProductMessage(
        $product,
        $combos = [],
        string $tg_unit = 'N/A',
        string $tg_brand = 'N/A',
        string $tg_category = 'N/A',
        string $tg_sub_cat = 'N/A',
        string $tg_tax = 'N/A',
        string $tg_locations = 'N/A',
        string $to = 'product',
        $location_id = 'PT1001',
        string $action_type = 'delete'
    ) {
        if (empty($product)) return;

        $typeIcon = match ($product->type) {
            'single'   => '🔵',
            'variable' => '🟡',
            'combo'    => '🟢',
            default    => '📦',
        };

        if ($action_type === 'remove') {
            $msg  = "🗑 <b>PRODUCT REMOVED FROM LOCATION</b>\n\n";
        } else {
            $msg  = "🗑 <b>PRODUCT DELETED</b>\n\n";
        }
        $msg .= "<b>📍 Business Locations:</b> {$tg_locations}\n";
        $msg .= "<b>📦 Product Name:</b> {$product->name}\n";
        $msg .= "<b>🔢 SKU:</b> {$product->sku}\n";
        $msg .= "<b>📊 Barcode Type:</b> {$product->barcode_type}\n";
        $msg .= "<b>📐 Unit:</b> {$tg_unit}\n";
        $msg .= "<b>🏷 Brand:</b> {$tg_brand}\n";
        $msg .= "<b>⚖️ Weight:</b> " . ($product->weight ?? 'N/A') . "\n";
        $msg .= "<b>📂 Category:</b> {$tg_category}\n";
        $msg .= "<b>📁 Sub Category:</b> {$tg_sub_cat}\n";
        $msg .= "<b>⏱ Service Timer:</b> " . ($product->preparation_time_in_minutes ? $product->preparation_time_in_minutes . ' min' : 'N/A') . "\n";
        $msg .= "<b>🔲 Manage Stock:</b> " . ($product->enable_stock ? 'Yes' : 'No') . "\n";
        $msg .= "<b>🚫 Not for Selling:</b> " . ($product->not_for_selling ? 'Yes' : 'No') . "\n";
        $msg .= "<b>📝 Description:</b> " . ($product->product_description ?? 'N/A') . "\n\n";
        $msg .= "<b>🧾 Applicable Tax:</b> {$tg_tax}\n";
        $msg .= "<b>💳 Tax Type:</b> " . ucfirst($product->tax_type) . "\n";
        $msg .= "{$typeIcon} <b>Product Type:</b> " . strtoupper($product->type) . "\n";

        // ── SINGLE ─────────────────────────────────────────────
        if ($product->type === 'single') {
            $v = $product->variations->first();
            if ($v) {
                $msg .= "\n<b>💰 PRICING</b>\n";
                $msg .= "🛒 <b>Purchase (Exc. Tax):</b> \${$v->default_purchase_price}\n";
                $msg .= "🛒 <b>Purchase (Inc. Tax):</b> \${$v->dpp_inc_tax}\n";
                $msg .= "📈 <b>Margin:</b> {$v->profit_percent}%\n";
                $msg .= "💵 <b>Selling (Exc. Tax):</b> \${$v->default_sell_price}\n";
                $msg .= "💵 <b>Selling (Inc. Tax):</b> \${$v->sell_price_inc_tax}\n";
            }
        }

        // ── VARIABLE ───────────────────────────────────────────
        elseif ($product->type === 'variable') {
            $msg .= "\n<b>🎨 VARIATIONS</b>\n";

            $groups = $product->variations->groupBy(fn($v) => $v->product_variation->name);

            foreach ($groups as $groupName => $variations) {
                $msg .= "\n🔖 <b>" . ucfirst($groupName) . "</b>\n";
                foreach ($variations as $v) {
                    $msg .= "• <b>{$groupName} - {$v->name}</b> | SKU: {$v->sub_sku}\n";
                    $msg .= "  Purchase (Exc. Tax): \${$v->default_purchase_price}\n";
                    $msg .= "  Purchase (Inc. Tax): \${$v->dpp_inc_tax}\n";
                    $msg .= "  Margin: {$v->profit_percent}%\n";
                    $msg .= "  Selling (Exc. Tax): \${$v->default_sell_price}\n";
                    $msg .= "  Selling (Inc. Tax): \${$v->sell_price_inc_tax}\n";
                }
            }
        }

        // ── COMBO ──────────────────────────────────────────────
        elseif ($product->type === 'combo') {
            $v = $product->variations->first();
            if ($v) {
                $msg .= "\n<b>🧩 COMBO ITEMS</b>\n";

                $netTotal = 0;
                foreach ($combos as $item) {
                    $variation = $item['variation'] ?? null;
                    $itemProd  = $variation['product'] ?? null;
                    $itemName  = $itemProd['name'] ?? 'N/A';
                    $varName   = (!empty($variation['name']) && $variation['name'] !== 'DUMMY') ? ' - ' . $variation['name'] : '';
                    $subSku    = $variation['sub_sku'] ?? 'N/A';
                    $qty       = $item['quantity'] ?? 1;
                    $unitName  = $item['unit_name'] ?? 'N/A';
                    $purchase  = $variation['default_purchase_price'] ?? 0;
                    $selling   = $variation['default_sell_price'] ?? 0;
                    $margin    = $variation['profit_percent'] ?? 0;
                    $total     = $qty * $purchase;
                    $netTotal += $total;

                    $msg .= "\n• <b>{$itemName}{$varName}</b> | SKU: {$subSku}\n";
                    $msg .= "  Qty: {$qty} {$unitName}\n";
                    $msg .= "  Purchase (Exc. Tax): \${$purchase}\n";
                    $msg .= "  Margin: {$margin}%\n";
                    $msg .= "  Selling (Exc. Tax): \${$selling}\n";
                    $msg .= "  Item Total: \$" . number_format($total, 2) . "\n";
                }

                $msg .= "\n💰 <b>Net Total Amount:</b> \$" . number_format($netTotal, 2) . "\n";
                $msg .= "📈 <b>Margin:</b> {$v->profit_percent}%\n";
                $msg .= "💵 <b>Default Selling Price:</b> \${$v->default_sell_price}\n";
            }
        }

        if ($action_type === 'remove') {
            $msg .= "\n⏰ <b>Date Removed:</b> " . now()->format('d/m/Y H:i') . "\n";
            $msg .= "🗑 <i>Removed via Shoper POS</i>";
        } else {
            $msg .= "\n⏰ <b>Date Deleted:</b> " . now()->format('d/m/Y H:i') . "\n";
            $msg .= "🗑 <i>Deleted via Shoper POS</i>";
        }

        // ── Send with image if available ──
        $hasImage = !empty($product->image) && $product->image !== 'default.png';

        if ($hasImage && !empty($product->image_url)) {
            self::sendPhoto($product->image_url, $msg, $to, $location_id);
        } else {
            self::sendMessage($msg, $to, $location_id);
        }
    }
    public static function sendPhoto(string $imageUrl, string $caption, string $to = '', $location_id = 'PT1001'): void
    {
        // $botToken = self::PRODUCTION_BOT;
        $botToken = self::LOCAL_BOT;
        $groups   = self::LOCAL_GROUP;
        // if (app()->environment("local")) {
        // }

        $group    = $groups[$location_id];
        $chat_id  = $group["chat_id"];
        $topic_id = $group["topic"][$to] ?? null;

        if (empty($topic_id)) {
            throw new \Exception("Topic ID not found for key: {$to}");
        }

        if (empty($chat_id)) {
            throw new \Exception("Chat ID not found for key: {$location_id}");
        }

        // Telegram caption max is 1024 chars
        $caption = mb_substr($caption, 0, 1024);

        $sent = false;

        // ── Try to read file from disk and upload as bytes ──
        try {
            // Convert URL to local file path
            $relativePath = parse_url($imageUrl, PHP_URL_PATH); // e.g. /uploads/img/xxx.png
            $localPath    = public_path($relativePath);          // e.g. /var/www/html/public/uploads/img/xxx.png

            if (file_exists($localPath)) {
                $fileContents = file_get_contents($localPath);
                $fileName     = basename($localPath);
                $mimeType     = mime_content_type($localPath);

                $response = Http::attach(
                    'photo',
                    $fileContents,
                    $fileName,
                    ['Content-Type' => $mimeType]
                )->post("https://api.telegram.org/bot{$botToken}/sendPhoto", [
                    'chat_id'           => $chat_id,
                    'message_thread_id' => $topic_id,
                    'caption'           => $caption,
                    'parse_mode'        => 'HTML',
                ]);

                $data = $response->json();
                $sent = $data['ok'] ?? false;

                if (!$sent) {
                    \Log::warning('Telegram sendPhoto (file upload) failed: ' . ($data['description'] ?? 'unknown'));
                }
            }
        } catch (\Exception $e) {
            \Log::warning('Telegram sendPhoto file read failed: ' . $e->getMessage());
        }

        // ── Fallback to text only if photo failed ──
        if (!$sent) {
            self::sendMessage($caption, $to, $location_id);
        }
    }

    // STOCK TRANSFER 
    public static function stockTransferMessage($sell_transfer, $location_details, $activities = [], string $to = 'transfer', $location_id = 'PT1001')
    {
        if (empty($sell_transfer)) return;

        $fromLocation = $location_details['sell'];
        $toLocation   = $location_details['purchase'];

        $fromName    = $fromLocation->name ?? 'N/A';
        $fromAddress = implode(', ', array_filter([
            $fromLocation->city ?? null,
            $fromLocation->state ?? null,
            $fromLocation->country ?? null,
        ]));
        $fromMobile = $fromLocation->mobile ?? null;

        $toName    = $toLocation->name ?? 'N/A';
        $toAddress = implode(', ', array_filter([
            $toLocation->city ?? null,
            $toLocation->state ?? null,
            $toLocation->country ?? null,
        ]));
        $toMobile = $toLocation->mobile ?? null;

        $status     = ucfirst($sell_transfer->status ?? 'N/A');
        $refNo      = $sell_transfer->ref_no ?? 'N/A';
        $date       = \Carbon\Carbon::parse($sell_transfer->transaction_date)->format('d/m/Y');
        $finalTotal = number_format($sell_transfer->final_total, 4);
        $shipping   = number_format($sell_transfer->shipping_charges, 4);
        $netTotal   = number_format($sell_transfer->total_before_tax, 4);
        $notes      = $sell_transfer->additional_notes ?? '--';

        // ── Header ─────────────────────────────────────────────
        $msg  = "🔄 <b>STOCK TRANSFER</b>\n\n";

        // ── From Location ──────────────────────────────────────
        $msg .= "📤 <b>Location (From):</b>\n";
        $msg .= "<b>{$fromName}</b>\n";
        if ($fromAddress) $msg .= "{$fromAddress}\n";
        if ($fromMobile)  $msg .= "📱 {$fromMobile}\n";
        $msg .= "\n";

        // ── To Location ────────────────────────────────────────
        $msg .= "📥 <b>Location (To):</b>\n";
        $msg .= "<b>{$toName}</b>\n";
        if ($toAddress) $msg .= "{$toAddress}\n";
        if ($toMobile)  $msg .= "📱 {$toMobile}\n";
        $msg .= "\n";

        // ── Transfer Info ──────────────────────────────────────
        $msg .= "🔖 <b>Reference No:</b> #{$refNo}\n";
        $msg .= "📅 <b>Date:</b> {$date}\n";
        $msg .= "📌 <b>Status:</b> {$status}\n\n";

        // ── Products ───────────────────────────────────────────
        $msg .= "<b>🛒 Products:</b>\n";

        $lineNo = 1;
        foreach ($sell_transfer->sell_lines as $line) {
            $productName = $line->product->name ?? 'N/A';
            $subSku      = $line->variations->sub_sku ?? $line->product->sku ?? 'N/A';
            $qty         = number_format($line->quantity, 2);
            $unitName    = $line->product->unit->short_name ?? 'Pc(s)';
            $unitPrice   = number_format($line->unit_price_before_discount, 4);
            $subtotal    = number_format($line->quantity * $line->unit_price_before_discount, 4);

            $msg .= "\n<b>{$lineNo}. {$productName} - {$subSku}</b>\n";
            $msg .= "   Qty: {$qty} {$unitName}\n";
            $msg .= "   Unit Price: \${$unitPrice}\n";
            $msg .= "   Subtotal: \${$subtotal}\n";

            $lineNo++;
        }

        // ── Totals ─────────────────────────────────────────────
        $msg .= "\n";
        $msg .= "💰 <b>Net Total Amount:</b> \${$netTotal}\n";
        $msg .= "🚚 <b>Additional Shipping Charges:</b> (+) \${$shipping}\n";
        $msg .= "🧾 <b>Purchase Total:</b> \${$finalTotal}\n";

        // ── Notes ──────────────────────────────────────────────
        $msg .= "\n📝 <b>Additional Notes:</b> {$notes}\n";

        // ── Activities ─────────────────────────────────────────
        if (!empty($activities) && count($activities) > 0) {
            $msg .= "\n<b>📋 Activities:</b>\n";
            foreach ($activities as $activity) {
                $actDate   = \Carbon\Carbon::parse($activity->created_at)->format('d/m/Y H:i');
                $action    = ucfirst($activity->description ?? 'N/A');
                $by        = trim(($activity->causer->surname ?? '') . ' ' . ($activity->causer->first_name ?? '') . ' ' . ($activity->causer->last_name ?? ''));
                $statusVal = $activity->properties['attributes']['status'] ?? null;

                $msg .= "\n🕒 <b>{$actDate}</b>\n";
                $msg .= "   Action: {$action}\n";
                $msg .= "   By: {$by}\n";
                if ($statusVal) {
                    $msg .= "   Status: " . ucfirst($statusVal) . "\n";
                }
            }
        }

        $msg .= "\n⏰ <b>Sent:</b> " . now()->format('d/m/Y H:i') . "\n";
        $msg .= "✅ <i>Saved via Shoper POS</i>";

        self::sendMessage($msg, $to, $location_id);
    }
    public static function stockTransferUpdatedMessage(
        $sell_transfer,
        $sell_transfer_before,
        $location_details,
        $activities = [],
        string $to = 'transfer',
        string $location_id = 'PT1001'
    ): void {
        if (empty($sell_transfer) || empty($sell_transfer_before)) return;

        $fromLocation = $location_details['sell'];
        $toLocation   = $location_details['purchase'];

        $fromName    = $fromLocation->name ?? 'N/A';
        $fromAddress = implode(', ', array_filter([
            $fromLocation->city ?? null,
            $fromLocation->state ?? null,
            $fromLocation->country ?? null,
        ]));
        $fromMobile = $fromLocation->mobile ?? null;

        $toName    = $toLocation->name ?? 'N/A';
        $toAddress = implode(', ', array_filter([
            $toLocation->city ?? null,
            $toLocation->state ?? null,
            $toLocation->country ?? null,
        ]));
        $toMobile = $toLocation->mobile ?? null;

        // ── NEW values ─────────────────────────────────────────
        $status     = ucfirst($sell_transfer->status ?? 'N/A');
        $refNo      = $sell_transfer->ref_no ?? 'N/A';
        $date       = \Carbon\Carbon::parse($sell_transfer->transaction_date)->format('d/m/Y');
        $finalTotal = number_format($sell_transfer->final_total, 4);
        $shipping   = number_format($sell_transfer->shipping_charges, 4);
        $netTotal   = number_format($sell_transfer->total_before_tax, 4);
        $notes      = $sell_transfer->additional_notes ?? '--';

        // ── OLD values ─────────────────────────────────────────
        $old_status     = ucfirst($sell_transfer_before->status ?? 'N/A');
        $old_refNo      = $sell_transfer_before->ref_no ?? 'N/A';
        $old_date       = \Carbon\Carbon::parse($sell_transfer_before->transaction_date)->format('d/m/Y');
        $old_finalTotal = number_format($sell_transfer_before->final_total, 4);
        $old_shipping   = number_format($sell_transfer_before->shipping_charges, 4);
        $old_netTotal   = number_format($sell_transfer_before->total_before_tax, 4);
        $old_notes      = $sell_transfer_before->additional_notes ?? '--';

        // ── Header ─────────────────────────────────────────────
        $msg  = "✏️ <b>STOCK TRANSFER UPDATED</b>\n\n";

        // ── From Location ──────────────────────────────────────
        $msg .= "📤 <b>Location (From):</b>\n";
        $msg .= "<b>{$fromName}</b>\n";
        if ($fromAddress) $msg .= "{$fromAddress}\n";
        if ($fromMobile)  $msg .= "📱 {$fromMobile}\n";
        $msg .= "\n";

        // ── To Location ────────────────────────────────────────
        $msg .= "📥 <b>Location (To):</b>\n";
        $msg .= "<b>{$toName}</b>\n";
        if ($toAddress) $msg .= "{$toAddress}\n";
        if ($toMobile)  $msg .= "📱 {$toMobile}\n";
        $msg .= "\n";

        // ── Transfer Info ──────────────────────────────────────
        $msg .= "🔖 <b>Reference No:</b> " . self::diff($old_refNo, $refNo) . "\n";
        $msg .= "📅 <b>Date:</b> "         . self::diff($old_date, $date) . "\n";
        $msg .= "📌 <b>Status:</b> "       . self::diff($old_status, $status) . "\n\n";

        // ── Products ───────────────────────────────────────────
        $msg .= "<b>🛒 Products:</b>\n";

        $lineNo = 1;
        foreach ($sell_transfer->sell_lines as $line) {
            $productName = $line->product->name ?? 'N/A';
            $subSku      = $line->variations->sub_sku ?? $line->product->sku ?? 'N/A';
            $qty         = number_format($line->quantity, 2);
            $unitName    = $line->product->unit->short_name ?? 'Pc(s)';
            $unitPrice   = number_format($line->unit_price_before_discount, 4);
            $subtotal    = number_format($line->quantity * $line->unit_price_before_discount, 4);

            $msg .= "\n<b>{$lineNo}. {$productName} - {$subSku}</b>\n";
            $msg .= "   Qty: {$qty} {$unitName}\n";
            $msg .= "   Unit Price: \${$unitPrice}\n";
            $msg .= "   Subtotal: \${$subtotal}\n";

            $lineNo++;
        }

        // ── Totals ─────────────────────────────────────────────
        $msg .= "\n";
        $msg .= "💰 <b>Net Total Amount:</b> "            . self::diff('$' . $old_netTotal, '$' . $netTotal) . "\n";
        $msg .= "🚚 <b>Additional Shipping Charges:</b> " . self::diff('$' . $old_shipping, '$' . $shipping) . "\n";
        $msg .= "🧾 <b>Purchase Total:</b> "              . self::diff('$' . $old_finalTotal, '$' . $finalTotal) . "\n";

        // ── Notes ──────────────────────────────────────────────
        if ($notes !== '--' || $old_notes !== '--') {
            $msg .= "\n📝 <b>Additional Notes:</b> " . self::diff($old_notes, $notes) . "\n";
        }

        // ── Activities ─────────────────────────────────────────
        if (!empty($activities) && count($activities) > 0) {
            $msg .= "\n<b>📋 Activities:</b>\n";
            foreach ($activities as $activity) {
                $actDate   = \Carbon\Carbon::parse($activity->created_at)->format('d/m/Y H:i');
                $action    = ucfirst($activity->description ?? 'N/A');
                $by        = trim(($activity->causer->surname ?? '') . ' ' . ($activity->causer->first_name ?? '') . ' ' . ($activity->causer->last_name ?? ''));
                $statusVal = $activity->properties['attributes']['status'] ?? null;

                $msg .= "\n🕒 <b>{$actDate}</b>\n";
                $msg .= "   Action: {$action}\n";
                $msg .= "   By: {$by}\n";
                if ($statusVal) {
                    $msg .= "   Status: " . ucfirst($statusVal) . "\n";
                }
            }
        }

        $msg .= "\n⏰ <b>Date Updated:</b> " . now()->format('d/m/Y H:i') . "\n";
        $msg .= "✏️ <i>Updated via Shoper POS</i>";

        self::sendMessage($msg, $to, $location_id);
    }
    public static function stockTransferStatusUpdatedMessage(
        $sell_transfer,
        $old_status,
        $location_details,
        $activities = [],
        string $to = 'transfer',
        string $location_id = 'PT1001'
    ): void {
        if (empty($sell_transfer)) return;

        $fromLocation = $location_details['sell'];
        $toLocation   = $location_details['purchase'];

        $fromName    = $fromLocation->name ?? 'N/A';
        $fromAddress = implode(', ', array_filter([
            $fromLocation->city ?? null,
            $fromLocation->state ?? null,
            $fromLocation->country ?? null,
        ]));
        $fromMobile = $fromLocation->mobile ?? null;

        $toName    = $toLocation->name ?? 'N/A';
        $toAddress = implode(', ', array_filter([
            $toLocation->city ?? null,
            $toLocation->state ?? null,
            $toLocation->country ?? null,
        ]));
        $toMobile = $toLocation->mobile ?? null;


        $newStatus  = ucfirst($sell_transfer->status ?? 'N/A');
        $prevStatus = ucfirst($old_status ?? 'N/A');
        $refNo      = $sell_transfer->ref_no ?? 'N/A';
        $date       = \Carbon\Carbon::parse($sell_transfer->transaction_date)->format('d/m/Y');
        $finalTotal = number_format($sell_transfer->final_total, 4);
        $shipping   = number_format($sell_transfer->shipping_charges, 4);
        $netTotal   = number_format($sell_transfer->total_before_tax, 4);
        $notes      = $sell_transfer->additional_notes ?? '--';

        // ── Header ─────────────────────────────────────────────
        $msg  = "🔁 <b>STOCK TRANSFER STATUS UPDATED</b>\n\n";

        // ── From Location ──────────────────────────────────────
        $msg .= "📤 <b>Location (From):</b>\n";
        $msg .= "<b>{$fromName}</b>\n";
        if ($fromAddress) $msg .= "{$fromAddress}\n";
        if ($fromMobile)  $msg .= "📱 {$fromMobile}\n";
        $msg .= "\n";

        // ── To Location ────────────────────────────────────────
        $msg .= "📥 <b>Location (To):</b>\n";
        $msg .= "<b>{$toName}</b>\n";
        if ($toAddress) $msg .= "{$toAddress}\n";
        if ($toMobile)  $msg .= "📱 {$toMobile}\n";
        $msg .= "\n";

        $prevStatus = [
            'Pending'   => "Pending",
            'Completed' => "Completed",
            "In_transit" => "In Transit",
            'Final'     => "Completed",
        ][$prevStatus] ?? $prevStatus;
        $newStatus = [
            'Pending'   => "Pending",
            'Completed' => "Completed",
            "In_transit" => "In Transit",
            'Final'     => "Completed",
        ][$newStatus] ?? $newStatus;
        // ── Transfer Info ──────────────────────────────────────
        $msg .= "🔖 <b>Reference No:</b> #{$refNo}\n";
        $msg .= "📅 <b>Date:</b> {$date}\n";
        $msg .= "📌 <b>Status:</b> " . self::diff($prevStatus, $newStatus) . "\n\n";

        // ── Products ───────────────────────────────────────────
        $msg .= "<b>🛒 Products:</b>\n";

        $lineNo = 1;
        foreach ($sell_transfer->sell_lines as $line) {
            $productName = $line->product->name ?? 'N/A';
            $subSku      = $line->variations->sub_sku ?? $line->product->sku ?? 'N/A';
            $qty         = number_format($line->quantity, 2);
            $unitName    = $line->product->unit->short_name ?? 'Pc(s)';
            $unitPrice   = number_format($line->unit_price_before_discount, 4);
            $subtotal    = number_format($line->quantity * $line->unit_price_before_discount, 4);

            $msg .= "\n<b>{$lineNo}. {$productName} - {$subSku}</b>\n";
            $msg .= "   Qty: {$qty} {$unitName}\n";
            $msg .= "   Unit Price: \${$unitPrice}\n";
            $msg .= "   Subtotal: \${$subtotal}\n";

            $lineNo++;
        }

        // ── Totals ─────────────────────────────────────────────
        $msg .= "\n";
        $msg .= "💰 <b>Net Total Amount:</b> \${$netTotal}\n";
        $msg .= "🚚 <b>Additional Shipping Charges:</b> (+) \${$shipping}\n";
        $msg .= "🧾 <b>Purchase Total:</b> \${$finalTotal}\n";

        // ── Notes ──────────────────────────────────────────────
        if ($notes !== '--') {
            $msg .= "\n📝 <b>Additional Notes:</b> {$notes}\n";
        }

        // ── Activities ─────────────────────────────────────────
        if (!empty($activities) && count($activities) > 0) {
            $msg .= "\n<b>📋 Activities:</b>\n";
            foreach ($activities as $activity) {
                $actDate   = \Carbon\Carbon::parse($activity->created_at)->format('d/m/Y H:i');
                $action    = ucfirst($activity->description ?? 'N/A');
                $by        = trim(($activity->causer->surname ?? '') . ' ' . ($activity->causer->first_name ?? '') . ' ' . ($activity->causer->last_name ?? ''));
                $statusVal = $activity->properties['attributes']['status'] ?? null;

                $msg .= "\n🕒 <b>{$actDate}</b>\n";
                $msg .= "   Action: {$action}\n";
                $msg .= "   By: {$by}\n";
                if ($statusVal) {
                    $msg .= "   Status: " . ucfirst($statusVal) . "\n";
                }
            }
        }

        $msg .= "\n⏰ <b>Date Updated:</b> " . now()->format('d/m/Y H:i') . "\n";
        $msg .= "🔁 <i>Status updated via Shoper POS</i>";

        self::sendMessage($msg, $to, $location_id);
    }
    public static function stockTransferDeletedMessage(
        $sell_transfer,
        $location_details,
        string $to = 'transfer',
        string $location_id = 'PT1001'
    ): void {
        if (empty($sell_transfer)) return;

        $fromLocation = $location_details['sell'];
        $toLocation   = $location_details['purchase'];

        $fromName    = $fromLocation->name ?? 'N/A';
        $fromAddress = implode(', ', array_filter([
            $fromLocation->city ?? null,
            $fromLocation->state ?? null,
            $fromLocation->country ?? null,
        ]));
        $fromMobile = $fromLocation->mobile ?? null;

        $toName    = $toLocation->name ?? 'N/A';
        $toAddress = implode(', ', array_filter([
            $toLocation->city ?? null,
            $toLocation->state ?? null,
            $toLocation->country ?? null,
        ]));
        $toMobile = $toLocation->mobile ?? null;

        $refNo      = $sell_transfer->ref_no ?? 'N/A';
        $date       = \Carbon\Carbon::parse($sell_transfer->transaction_date)->format('d/m/Y');
        $status     = ucfirst($sell_transfer->status ?? 'N/A');
        $finalTotal = number_format($sell_transfer->final_total, 4);
        $shipping   = number_format($sell_transfer->shipping_charges, 4);
        $netTotal   = number_format($sell_transfer->total_before_tax, 4);
        $notes      = $sell_transfer->additional_notes ?? '--';
        $deletedBy  = trim(
            (auth()->user()->surname ?? '') . ' ' .
                (auth()->user()->first_name ?? '') . ' ' .
                (auth()->user()->last_name ?? '')
        );

        // ── Header ─────────────────────────────────────────────
        $msg  = "🗑️ <b>STOCK TRANSFER DELETED</b>\n\n";

        // ── Deleted By ─────────────────────────────────────────
        $msg .= "👤 <b>Deleted By:</b> {$deletedBy}\n";
        $msg .= "⏰ <b>Deleted At:</b> " . now()->format('d/m/Y H:i') . "\n\n";

        // ── From Location ──────────────────────────────────────
        $msg .= "📤 <b>Location (From):</b>\n";
        $msg .= "<b>{$fromName}</b>\n";
        if ($fromAddress) $msg .= "{$fromAddress}\n";
        if ($fromMobile)  $msg .= "📱 {$fromMobile}\n";
        $msg .= "\n";

        // ── To Location ────────────────────────────────────────
        $msg .= "📥 <b>Location (To):</b>\n";
        $msg .= "<b>{$toName}</b>\n";
        if ($toAddress) $msg .= "{$toAddress}\n";
        if ($toMobile)  $msg .= "📱 {$toMobile}\n";
        $msg .= "\n";

        // ── Transfer Info ──────────────────────────────────────
        $msg .= "🔖 <b>Reference No:</b> #{$refNo}\n";
        $msg .= "📅 <b>Date:</b> {$date}\n";
        $msg .= "📌 <b>Status:</b> {$status}\n\n";

        // ── Products ───────────────────────────────────────────
        $msg .= "<b>🛒 Products:</b>\n";

        $lineNo = 1;
        foreach ($sell_transfer->sell_lines as $line) {
            $productName = $line->product->name ?? 'N/A';
            $subSku      = $line->variations->sub_sku ?? $line->product->sku ?? 'N/A';
            $qty         = number_format($line->quantity, 2);
            $unitName    = $line->product->unit->short_name ?? 'Pc(s)';
            $unitPrice   = number_format($line->unit_price_before_discount, 4);
            $subtotal    = number_format($line->quantity * $line->unit_price_before_discount, 4);

            $msg .= "\n<b>{$lineNo}. {$productName} - {$subSku}</b>\n";
            $msg .= "   Qty: {$qty} {$unitName}\n";
            $msg .= "   Unit Price: \${$unitPrice}\n";
            $msg .= "   Subtotal: \${$subtotal}\n";

            $lineNo++;
        }

        // ── Totals ─────────────────────────────────────────────
        $msg .= "\n";
        $msg .= "💰 <b>Net Total Amount:</b> \${$netTotal}\n";
        $msg .= "🚚 <b>Additional Shipping Charges:</b> (+) \${$shipping}\n";
        $msg .= "🧾 <b>Purchase Total:</b> \${$finalTotal}\n";

        // ── Notes ──────────────────────────────────────────────
        if ($notes !== '--') {
            $msg .= "\n📝 <b>Additional Notes:</b> {$notes}\n";
        }

        $msg .= "\n🗑️ <i>Deleted via Shoper POS</i>";

        self::sendMessage($msg, $to, $location_id);
    }
    // EXPENSE
    public static function addExpenseMessage(
        $transaction,
        $expense_categories,
        $business_locations,
        $users,
        $taxes,
        $contacts,
        $sub_categories,
        $payments,
        $payment_types,
        string $to = 'expense',
        string $location_id = 'PT1001'
    ): void {
        if (empty($transaction)) return;

        // ── Fetch accounts ─────────────────────────────────────
        $all_account = self::fetchAccounts($location_id);

        // ── Resolve labels ─────────────────────────────────────
        $businessName = $business_locations[$transaction->location_id] ?? 'N/A';
        $categoryName = $expense_categories[$transaction->expense_category_id] ?? 'N/A';
        $subCatName   = $sub_categories[$transaction->expense_sub_category_id] ?? 'N/A';
        $expenseFor   = $users[$transaction->expense_for] ?? 'None';
        $contactName  = $contacts[$transaction->contact_id] ?? 'N/A';

        // Tax label
        $taxName = 'None';
        if (!empty($transaction->tax_id) && !empty($taxes['tax_rates'])) {
            $taxName = $taxes['tax_rates']->firstWhere('id', $transaction->tax_id)?->name ?? 'None';
        }

        $date      = \Carbon\Carbon::parse($transaction->transaction_date)->format('d/m/Y H:i');
        $refNo     = $transaction->ref_no ?? 'N/A';
        $total     = number_format($transaction->final_total, 2);
        $taxAmount = number_format($transaction->tax_amount, 2);
        $notes     = $transaction->additional_notes ?? null;
        $status    = ucfirst($transaction->payment_status ?? 'N/A');
        $isRefund  = $transaction->adjustment_type === 'normal' ? 'No' : 'Yes';

        // ── Business info from relation ────────────────────────
        $business       = $transaction->business;
        $businessEmail  = $business->email ?? null;
        $businessMobile = $business->mobile ?? null;
        $businessVat    = $business->tax_number_1
            ? ($business->tax_label_1 ?? 'VAT') . ': ' . $business->tax_number_1
            : null;

        // ── Contact info from relation ─────────────────────────
        $contactMobile = $transaction->contact->mobile ?? null;

        // ── Expense for user from relation ─────────────────────
        $expenseForUser   = $transaction->transaction_for;
        $expenseForName   = trim(
            ($expenseForUser->surname ?? '') . ' ' .
                ($expenseForUser->first_name ?? '') . ' ' .
                ($expenseForUser->last_name ?? '')
        );
        $expenseForMobile = $expenseForUser->contact_no ?? null;

        // ── Message ────────────────────────────────────────────
        $msg = "💸 <b>NEW EXPENSE ADDED</b>\n\n";

        // Business block
        $msg .= "<b>🏪 Business:</b> {$businessName}\n";
        if ($businessEmail)  $msg .= "<b>📧 Email:</b> {$businessEmail}\n";
        if ($businessMobile) $msg .= "<b>📱 Mobile:</b> {$businessMobile}\n";
        if ($businessVat)    $msg .= "<b>🧾 VAT:</b> {$businessVat}\n";
        $msg .= "\n";

        // Expense for block
        $msg .= "<b>👤 Expense For:</b> {$expenseForName}\n";
        if ($expenseForMobile) $msg .= "<b>📱 Mobile:</b> {$expenseForMobile}\n";
        $msg .= "\n";

        // Contact block
        $msg .= "<b>🤝 Contact:</b> {$contactName}\n";
        if ($contactMobile) $msg .= "<b>📱 Mobile:</b> {$contactMobile}\n";
        $msg .= "\n";

        // Ref, date, status
        $msg .= "<b>🔖 Ref No:</b> #{$refNo}\n";
        $msg .= "<b>🕒 Date:</b> {$date}\n";
        $msg .= "<b>✅ Payment Status:</b> {$status}\n";
        $msg .= "<b>↩️ Is Refund:</b> {$isRefund}\n\n";

        // Category
        $msg .= "<b>📂 Category:</b> {$categoryName}\n";
        $msg .= "<b>📁 Sub Category:</b> {$subCatName}\n\n";

        // Tax & total
        $msg .= "<b>🧾 Applicable Tax:</b> {$taxName}\n";
        $msg .= "<b>💰 Tax Amount:</b> \${$taxAmount}\n";
        $msg .= "<b>💵 Total Amount:</b> \${$total}\n\n";

        // ── Payments ───────────────────────────────────────────
        if (!empty($payments) && $payments->isNotEmpty()) {
            $totalPaid = 0;
            $msg .= "<b>💳 Payments:</b>\n";

            foreach ($payments as $pay) {
                $method    = $payment_types[$pay->method] ?? ucfirst($pay->method);
                $amount    = number_format($pay->amount, 2);
                $paidOn    = \Carbon\Carbon::parse($pay->paid_on)->format('d/m/Y H:i');
                $payRefNo  = $pay->payment_ref_no ?? 'N/A';
                $payNote   = $pay->note ?? null;
                $totalPaid += $pay->amount;

                $msg .= "\n• <b>{$method}</b>\n";
                $msg .= "  Ref: {$payRefNo}\n";
                $msg .= "  Amount: \${$amount}\n";
                $msg .= "  Paid On: {$paidOn}\n";
                if ($payNote) $msg .= "  Note: {$payNote}\n";
            }

            $remaining = $transaction->final_total - $totalPaid;

            $msg .= "\n<b>✅ Total Paid:</b> \$" . number_format($totalPaid, 2) . "\n";
            if ($remaining > 0) {
                $msg .= "<b>⏳ Remaining:</b> \$" . number_format($remaining, 2) . "\n";
            }
            $msg .= "\n";
        }

        // ── Recurring ──────────────────────────────────────────
        if ($transaction->is_recurring) {
            $interval     = $transaction->recur_interval ?? 'N/A';
            $intervalType = ucfirst($transaction->recur_interval_type ?? '');
            $repetitions  = $transaction->recur_repetitions
                ? $transaction->recur_repetitions . 'x'
                : 'Infinite';

            $msg .= "🔁 <b>Recurring:</b> Yes\n";
            $msg .= "   Every: {$interval} {$intervalType}\n";
            $msg .= "   Repetitions: {$repetitions}\n\n";
        }

        // ── Notes ──────────────────────────────────────────────
        if (!empty($notes)) {
            $msg .= "<b>📝 Notes:</b> {$notes}\n\n";
        }

        // ── Accounts ───────────────────────────────────────────
        if (!empty($all_account)) {
            $msg .= "<b>🏦 Account Balances:</b>\n";
            foreach ($all_account as $account) {
                $msg .= "  • <b>{$account['name']}:</b> {$account['balance']}\n";
            }
            $msg .= "\n";
        }

        $msg .= "⏰ <b>Date Added:</b> " . now()->format('d/m/Y H:i') . "\n";
        $msg .= "✅ <i>Saved via Shoper POS</i>";

        self::sendMessage($msg, $to, $location_id);
    }

    public static function updateExpenseMessage(
        $transaction,
        $old_transaction,
        $expense_categories,
        $business_locations,
        $users,
        $taxes,
        $contacts,
        $sub_categories,
        $payments,
        $payment_types,
        string $to = 'expense',
        string $location_id = 'PT1001'
    ): void {
        if (empty($transaction) || empty($old_transaction)) return;

        // ── Fetch accounts ─────────────────────────────────────
        $all_account = self::fetchAccounts($location_id);

        // ── Resolve NEW labels ─────────────────────────────────
        $businessName = $business_locations[$transaction->location_id] ?? 'N/A';
        $categoryName = $expense_categories[$transaction->expense_category_id] ?? 'N/A';
        $subCatName   = $sub_categories[$transaction->expense_sub_category_id] ?? 'N/A';
        $contactName  = $contacts[$transaction->contact_id] ?? 'N/A';
        $expenseFor   = $users[$transaction->expense_for] ?? 'None';

        // ── Resolve OLD labels ─────────────────────────────────
        $old_categoryName = $expense_categories[$old_transaction->expense_category_id] ?? 'N/A';
        $old_subCatName   = $sub_categories[$old_transaction->expense_sub_category_id] ?? 'N/A';
        $old_contactName  = $contacts[$old_transaction->contact_id] ?? 'N/A';
        $old_expenseFor   = $users[$old_transaction->expense_for] ?? 'None';

        // Tax label NEW
        $taxName = 'None';
        if (!empty($transaction->tax_id) && !empty($taxes['tax_rates'])) {
            $taxName = $taxes['tax_rates']->firstWhere('id', $transaction->tax_id)?->name ?? 'None';
        }

        // Tax label OLD
        $old_taxName = 'None';
        if (!empty($old_transaction->tax_id) && !empty($taxes['tax_rates'])) {
            $old_taxName = $taxes['tax_rates']->firstWhere('id', $old_transaction->tax_id)?->name ?? 'None';
        }

        $date        = \Carbon\Carbon::parse($transaction->transaction_date)->format('d/m/Y H:i');
        $old_date    = \Carbon\Carbon::parse($old_transaction->transaction_date)->format('d/m/Y H:i');

        $refNo       = $transaction->ref_no ?? 'N/A';
        $old_refNo   = $old_transaction->ref_no ?? 'N/A';

        $total       = number_format($transaction->final_total, 2);
        $old_total   = number_format($old_transaction->final_total, 2);

        $taxAmount     = number_format($transaction->tax_amount, 2);
        $old_taxAmount = number_format($old_transaction->tax_amount, 2);

        $status      = ucfirst($transaction->payment_status ?? 'N/A');
        $old_status  = ucfirst($old_transaction->payment_status ?? 'N/A');

        $notes       = $transaction->additional_notes ?? null;
        $old_notes   = $old_transaction->additional_notes ?? null;

        $isRefund     = $transaction->adjustment_type === 'normal' ? 'No' : 'Yes';
        $old_isRefund = $old_transaction->adjustment_type === 'normal' ? 'No' : 'Yes';

        // ── Business info ──────────────────────────────────────
        $business       = $transaction->business;
        $businessEmail  = $business->email ?? null;
        $businessMobile = $business->mobile ?? null;
        $businessVat    = $business->tax_number_1
            ? ($business->tax_label_1 ?? 'VAT') . ': ' . $business->tax_number_1
            : null;

        // ── Contact & Expense For from relation ────────────────
        $contactMobile    = $transaction->contact->mobile ?? null;
        $expenseForUser   = $transaction->transaction_for;
        $expenseForName   = trim(
            ($expenseForUser->surname ?? '') . ' ' .
                ($expenseForUser->first_name ?? '') . ' ' .
                ($expenseForUser->last_name ?? '')
        );
        $expenseForMobile = $expenseForUser->contact_no ?? null;

        // ── Message ────────────────────────────────────────────
        $msg = "✏️ <b>EXPENSE UPDATED</b>\n\n";

        // Business block
        $msg .= "<b>🏪 Business:</b> {$businessName}\n";
        if ($businessEmail)  $msg .= "<b>📧 Email:</b> {$businessEmail}\n";
        if ($businessMobile) $msg .= "<b>📱 Mobile:</b> {$businessMobile}\n";
        if ($businessVat)    $msg .= "<b>🧾 VAT:</b> {$businessVat}\n";
        $msg .= "\n";

        // Expense for block
        $msg .= "<b>👤 Expense For:</b> " . self::diff($old_expenseFor, $expenseForName) . "\n";
        if ($expenseForMobile) $msg .= "<b>📱 Mobile:</b> {$expenseForMobile}\n";
        $msg .= "\n";

        // Contact block
        $msg .= "<b>🤝 Contact:</b> " . self::diff($old_contactName, $contactName) . "\n";
        if ($contactMobile) $msg .= "<b>📱 Mobile:</b> {$contactMobile}\n";
        $msg .= "\n";

        // Ref, date, status
        $msg .= "<b>🔖 Ref No:</b> "          . self::diff($old_refNo, $refNo) . "\n";
        $msg .= "<b>🕒 Date:</b> "            . self::diff($old_date, $date) . "\n";
        $msg .= "<b>✅ Payment Status:</b> "  . self::diff($old_status, $status) . "\n";
        $msg .= "<b>↩️ Is Refund:</b> "       . self::diff($old_isRefund, $isRefund) . "\n\n";

        // Category
        $msg .= "<b>📂 Category:</b> "     . self::diff($old_categoryName, $categoryName) . "\n";
        $msg .= "<b>📁 Sub Category:</b> " . self::diff($old_subCatName, $subCatName) . "\n\n";

        // Tax & total
        $msg .= "<b>🧾 Applicable Tax:</b> " . self::diff($old_taxName, $taxName) . "\n";
        $msg .= "<b>💰 Tax Amount:</b> "     . self::diff('$' . $old_taxAmount, '$' . $taxAmount) . "\n";
        $msg .= "<b>💵 Total Amount:</b> "   . self::diff('$' . $old_total, '$' . $total) . "\n\n";

        // ── Payments ───────────────────────────────────────────
        if (!empty($payments) && $payments->isNotEmpty()) {
            $totalPaid = 0;
            $msg .= "<b>💳 Payments:</b>\n";

            foreach ($payments as $pay) {
                $method    = $payment_types[$pay->method] ?? ucfirst($pay->method);
                $amount    = number_format($pay->amount, 2);
                $paidOn    = \Carbon\Carbon::parse($pay->paid_on)->format('d/m/Y H:i');
                $payRefNo  = $pay->payment_ref_no ?? 'N/A';
                $payNote   = $pay->note ?? null;
                $totalPaid += $pay->amount;

                $msg .= "\n• <b>{$method}</b>\n";
                $msg .= "  Ref: {$payRefNo}\n";
                $msg .= "  Amount: \${$amount}\n";
                $msg .= "  Paid On: {$paidOn}\n";
                if ($payNote) $msg .= "  Note: {$payNote}\n";
            }

            $remaining     = $transaction->final_total - $totalPaid;
            $old_remaining = $old_transaction->final_total - $totalPaid;

            $msg .= "\n<b>✅ Total Paid:</b> \$" . number_format($totalPaid, 2) . "\n";
            if ($remaining > 0) {
                $msg .= "<b>⏳ Remaining:</b> " . self::diff(
                    '$' . number_format($old_remaining, 2),
                    '$' . number_format($remaining, 2)
                ) . "\n";
            }
            $msg .= "\n";
        }

        // ── Recurring ──────────────────────────────────────────
        $new_is_recurring = $transaction->is_recurring;
        $old_is_recurring = $old_transaction->is_recurring;

        if ($new_is_recurring || $old_is_recurring) {
            $interval         = $transaction->recur_interval ?? 'N/A';
            $intervalType     = ucfirst($transaction->recur_interval_type ?? '');
            $repetitions      = $transaction->recur_repetitions ? $transaction->recur_repetitions . 'x' : 'Infinite';
            $old_interval     = $old_transaction->recur_interval ?? 'N/A';
            $old_intervalType = ucfirst($old_transaction->recur_interval_type ?? '');
            $old_repetitions  = $old_transaction->recur_repetitions ? $old_transaction->recur_repetitions . 'x' : 'Infinite';

            $msg .= "🔁 <b>Recurring:</b> " . self::diff($old_is_recurring ? 'Yes' : 'No', $new_is_recurring ? 'Yes' : 'No') . "\n";
            $msg .= "   Every: "            . self::diff("{$old_interval} {$old_intervalType}", "{$interval} {$intervalType}") . "\n";
            $msg .= "   Repetitions: "      . self::diff($old_repetitions, $repetitions) . "\n\n";
        }

        // ── Notes ──────────────────────────────────────────────
        if (!empty($notes) || !empty($old_notes)) {
            $msg .= "<b>📝 Notes:</b> " . self::diff($old_notes ?? '--', $notes ?? '--') . "\n\n";
        }

        // ── Accounts ───────────────────────────────────────────
        if (!empty($all_account)) {
            $msg .= "<b>🏦 Account Balances:</b>\n";
            foreach ($all_account as $account) {
                $msg .= "  • <b>{$account['name']}:</b> {$account['balance']}\n";
            }
            $msg .= "\n";
        }

        $msg .= "⏰ <b>Date Updated:</b> " . now()->format('d/m/Y H:i') . "\n";
        $msg .= "✏️ <i>Updated via Shoper POS</i>";

        self::sendMessage($msg, $to, $location_id);
    }

    public static function deleteExpenseMessage(
        $transaction,
        $expense_categories,
        $business_locations,
        $users,
        $taxes,
        $contacts,
        $sub_categories,
        $payments,
        $payment_types,
        string $to = 'expense',
        string $location_id = 'PT1001'
    ): void {
        if (empty($transaction)) return;

        // ── Fetch accounts ─────────────────────────────────────
        $all_account = self::fetchAccounts($location_id);

        // ── Resolve labels ─────────────────────────────────────
        $businessName = $business_locations[$transaction->location_id] ?? 'N/A';
        $categoryName = $expense_categories[$transaction->expense_category_id] ?? 'N/A';
        $subCatName   = $sub_categories[$transaction->expense_sub_category_id] ?? 'N/A';
        $contactName  = $contacts[$transaction->contact_id] ?? 'N/A';

        $expenseForUser   = $transaction->transaction_for;
        $expenseForName   = trim(
            ($expenseForUser->surname ?? '') . ' ' .
                ($expenseForUser->first_name ?? '') . ' ' .
                ($expenseForUser->last_name ?? '')
        );
        $expenseForMobile = $expenseForUser->contact_no ?? null;

        $taxName = 'None';
        if (!empty($transaction->tax_id) && !empty($taxes['tax_rates'])) {
            $taxName = $taxes['tax_rates']->firstWhere('id', $transaction->tax_id)?->name ?? 'None';
        }

        $date      = \Carbon\Carbon::parse($transaction->transaction_date)->format('d/m/Y H:i');
        $refNo     = $transaction->ref_no ?? 'N/A';
        $total     = number_format($transaction->final_total, 2);
        $taxAmount = number_format($transaction->tax_amount, 2);
        $notes     = $transaction->additional_notes ?? null;
        $status    = ucfirst($transaction->payment_status ?? 'N/A');
        $isRefund  = $transaction->adjustment_type === 'normal' ? 'No' : 'Yes';

        // ── Business info ──────────────────────────────────────
        $business       = $transaction->business;
        $businessEmail  = $business->email ?? null;
        $businessMobile = $business->mobile ?? null;
        $businessVat    = $business->tax_number_1
            ? ($business->tax_label_1 ?? 'VAT') . ': ' . $business->tax_number_1
            : null;

        $contactMobile = $transaction->contact->mobile ?? null;

        // ── Message ────────────────────────────────────────────
        $msg = "🗑 <b>EXPENSE DELETED</b>\n\n";

        // Business block
        $msg .= "<b>🏪 Business:</b> {$businessName}\n";
        if ($businessEmail)  $msg .= "<b>📧 Email:</b> {$businessEmail}\n";
        if ($businessMobile) $msg .= "<b>📱 Mobile:</b> {$businessMobile}\n";
        if ($businessVat)    $msg .= "<b>🧾 VAT:</b> {$businessVat}\n";
        $msg .= "\n";

        // Expense for block
        $msg .= "<b>👤 Expense For:</b> {$expenseForName}\n";
        if ($expenseForMobile) $msg .= "<b>📱 Mobile:</b> {$expenseForMobile}\n";
        $msg .= "\n";

        // Contact block
        $msg .= "<b>🤝 Contact:</b> {$contactName}\n";
        if ($contactMobile) $msg .= "<b>📱 Mobile:</b> {$contactMobile}\n";
        $msg .= "\n";

        // Ref, date, status
        $msg .= "<b>🔖 Ref No:</b> #{$refNo}\n";
        $msg .= "<b>🕒 Date:</b> {$date}\n";
        $msg .= "<b>✅ Payment Status:</b> {$status}\n";
        $msg .= "<b>↩️ Is Refund:</b> {$isRefund}\n\n";

        // Category
        $msg .= "<b>📂 Category:</b> {$categoryName}\n";
        $msg .= "<b>📁 Sub Category:</b> {$subCatName}\n\n";

        // Tax & total
        $msg .= "<b>🧾 Applicable Tax:</b> {$taxName}\n";
        $msg .= "<b>💰 Tax Amount:</b> \${$taxAmount}\n";
        $msg .= "<b>💵 Total Amount:</b> \${$total}\n\n";

        // ── Payments ───────────────────────────────────────────
        if (!empty($payments) && $payments->isNotEmpty()) {
            $totalPaid = 0;
            $msg .= "<b>💳 Payments:</b>\n";

            foreach ($payments as $pay) {
                $method    = $payment_types[$pay->method] ?? ucfirst($pay->method);
                $amount    = number_format($pay->amount, 2);
                $paidOn    = \Carbon\Carbon::parse($pay->paid_on)->format('d/m/Y H:i');
                $payRefNo  = $pay->payment_ref_no ?? 'N/A';
                $payNote   = $pay->note ?? null;
                $totalPaid += $pay->amount;

                $msg .= "\n• <b>{$method}</b>\n";
                $msg .= "  Ref: {$payRefNo}\n";
                $msg .= "  Amount: \${$amount}\n";
                $msg .= "  Paid On: {$paidOn}\n";
                if ($payNote) $msg .= "  Note: {$payNote}\n";
            }

            $remaining = $transaction->final_total - $totalPaid;

            $msg .= "\n<b>✅ Total Paid:</b> \$" . number_format($totalPaid, 2) . "\n";
            if ($remaining > 0) {
                $msg .= "<b>⏳ Remaining:</b> \$" . number_format($remaining, 2) . "\n";
            }
            $msg .= "\n";
        }

        // ── Recurring ──────────────────────────────────────────
        if ($transaction->is_recurring) {
            $interval     = $transaction->recur_interval ?? 'N/A';
            $intervalType = ucfirst($transaction->recur_interval_type ?? '');
            $repetitions  = $transaction->recur_repetitions
                ? $transaction->recur_repetitions . 'x'
                : 'Infinite';

            $msg .= "🔁 <b>Recurring:</b> Yes\n";
            $msg .= "   Every: {$interval} {$intervalType}\n";
            $msg .= "   Repetitions: {$repetitions}\n\n";
        }

        // ── Notes ──────────────────────────────────────────────
        if (!empty($notes)) {
            $msg .= "<b>📝 Notes:</b> {$notes}\n\n";
        }

        // ── Accounts ───────────────────────────────────────────
        if (!empty($all_account)) {
            $msg .= "<b>🏦 Account Balances:</b>\n";
            foreach ($all_account as $account) {
                $msg .= "  • <b>{$account['name']}:</b> {$account['balance']}\n";
            }
            $msg .= "\n";
        }

        $msg .= "⏰ <b>Date Deleted:</b> " . now()->format('d/m/Y H:i') . "\n";
        $msg .= "🗑 <i>Deleted via Shoper POS</i>";

        self::sendMessage($msg, $to, $location_id);
    }
    // ACCOUNT
    // PAYMENT ACCOUNT
    public static function addAccountMessage(
        $account,
        $opening_balance = null,
        // $all_account = [],
        string $to = 'payment_accoun',
        string $location_id = 'PT1001'
    ): void {
        if (empty($account)) return;
        $all_account = self::fetchAccounts($location_id);

        $name          = $account->name ?? 'N/A';
        $accountNumber = $account->account_number ?? 'N/A';
        $note          = $account->note ?? null;
        $accountType   = $account->account_type->name ?? 'N/A';
        $parentType    = $account->account_type->parent_account->name ?? null;
        $openingBal    = !empty($opening_balance)
            ? '$' . number_format((float) $opening_balance, 2)
            : '$0.00';

        $msg  = "🏦 <b>NEW ACCOUNT ADDED</b>\n\n";

        $msg .= "<b>📛 Name:</b> {$name}\n";
        $msg .= "<b>🔢 Account Number:</b> {$accountNumber}\n";

        $typeLabel = $parentType ? "{$parentType} → {$accountType}" : $accountType;
        $msg .= "<b>📂 Account Type:</b> {$typeLabel}\n";
        $msg .= "<b>💰 Opening Balance:</b> {$openingBal}\n\n";

        $details = collect($account->account_details ?? [])
            ->filter(fn($d) => !empty($d['label']) || !empty($d['value']));

        if ($details->isNotEmpty()) {
            $msg .= "<b>📋 Account Details:</b>\n";
            foreach ($details as $detail) {
                $label = $detail['label'] ?? 'N/A';
                $value = $detail['value'] ?? 'N/A';
                $msg .= "  • <b>{$label}:</b> {$value}\n";
            }
            $msg .= "\n";
        }

        if (!empty($note)) {
            $msg .= "<b>📝 Note:</b> {$note}\n\n";
        }

        // ── All Accounts ───────────────────────────────────────
        if (!empty($all_account)) {
            $msg .= "🧾 <b>LIST ACCOUNT:</b>\n";
            foreach ($all_account as $acc) {
                $msg .= "{$acc['name']}: {$acc['balance']}\n";
            }
            $msg .= "\n";
        }

        $msg .= "⏰ <b>Date Added:</b> " . now()->format('d/m/Y H:i') . "\n";
        $msg .= "✅ <i>Saved via Shoper POS</i>";

        self::sendMessage($msg, $to, $location_id);
    }

    public static function updateAccountMessage(
        $account,
        $old_account,
        // $all_account = [],
        string $to = 'payment_accoun',
        string $location_id = 'PT1001'
    ): void {
        if (empty($account) || empty($old_account)) return;
        $all_account = self::fetchAccounts($location_id);

        $name          = $account->name ?? 'N/A';
        $accountNumber = $account->account_number ?? 'N/A';
        $note          = $account->note ?? null;
        $accountType   = $account->account_type->name ?? 'N/A';
        $parentType    = $account->account_type->parent_account->name ?? null;
        $typeLabel     = $parentType ? "{$parentType} → {$accountType}" : $accountType;

        $old_name          = $old_account->name ?? 'N/A';
        $old_accountNumber = $old_account->account_number ?? 'N/A';
        $old_note          = $old_account->note ?? null;
        $old_accountType   = $old_account->account_type->name ?? 'N/A';
        $old_parentType    = $old_account->account_type->parent_account->name ?? null;
        $old_typeLabel     = $old_parentType ? "{$old_parentType} → {$old_accountType}" : $old_accountType;

        $msg  = "✏️ <b>ACCOUNT UPDATED</b>\n\n";

        $msg .= "<b>📛 Name:</b> "           . self::diff($old_name, $name) . "\n";
        $msg .= "<b>🔢 Account Number:</b> " . self::diff($old_accountNumber, $accountNumber) . "\n";
        $msg .= "<b>📂 Account Type:</b> "   . self::diff($old_typeLabel, $typeLabel) . "\n\n";

        $new_details = collect($account->account_details ?? [])
            ->filter(fn($d) => !empty($d['label']) || !empty($d['value']));
        $old_details = collect($old_account->account_details ?? [])
            ->filter(fn($d) => !empty($d['label']) || !empty($d['value']));

        if ($new_details->isNotEmpty() || $old_details->isNotEmpty()) {
            $msg .= "<b>📋 Account Details:</b>\n";

            $max         = max($new_details->count(), $old_details->count());
            $new_indexed = $new_details->values();
            $old_indexed = $old_details->values();

            for ($i = 0; $i < $max; $i++) {
                $newLabel = $new_indexed[$i]['label'] ?? null;
                $newValue = $new_indexed[$i]['value'] ?? null;
                $oldLabel = $old_indexed[$i]['label'] ?? null;
                $oldValue = $old_indexed[$i]['value'] ?? null;

                if ($newLabel || $oldLabel) {
                    $labelDiff = self::diff($oldLabel ?? 'N/A', $newLabel ?? 'N/A');
                    $valueDiff = self::diff($oldValue ?? 'N/A', $newValue ?? 'N/A');
                    $msg .= "  • <b>{$labelDiff}:</b> {$valueDiff}\n";
                }
            }
            $msg .= "\n";
        }

        if ($note !== $old_note) {
            $msg .= "<b>📝 Note:</b> " . self::diff($old_note ?? '--', $note ?? '--') . "\n\n";
        } elseif (!empty($note)) {
            $msg .= "<b>📝 Note:</b> {$note}\n\n";
        }

        // ── All Accounts ───────────────────────────────────────
        if (!empty($all_account)) {
            $msg .= "🧾 <b>LIST ACCOUNT:</b>\n";
            foreach ($all_account as $acc) {
                $msg .= "{$acc['name']}: {$acc['balance']}\n";
            }
            $msg .= "\n";
        }

        $msg .= "⏰ <b>Date Updated:</b> " . now()->format('d/m/Y H:i') . "\n";
        $msg .= "✏️ <i>Updated via Shoper POS</i>";

        self::sendMessage($msg, $to, $location_id);
    }

    public static function closeAccountMessage(
        $account,
        // $all_account = [],
        string $to = 'payment_accoun',
        string $location_id = 'PT1001'
    ): void {
        if (empty($account)) return;
        $all_account = self::fetchAccounts($location_id);

        $name          = $account->name ?? 'N/A';
        $accountNumber = $account->account_number ?? 'N/A';
        $note          = $account->note ?? null;
        $accountType   = $account->account_type->name ?? 'N/A';
        $parentType    = $account->account_type->parent_account->name ?? null;
        $typeLabel     = $parentType ? "{$parentType} → {$accountType}" : $accountType;

        $balance = AccountTransaction::where('account_id', $account->id)
            ->whereNull('deleted_at')
            ->selectRaw("SUM(IF(type='credit', amount, -1 * amount)) as balance")
            ->first()->balance ?? 0;

        $msg  = "🔒 <b>ACCOUNT CLOSED</b>\n\n";

        $msg .= "<b>📛 Name:</b> {$name}\n";
        $msg .= "<b>🔢 Account Number:</b> {$accountNumber}\n";
        $msg .= "<b>📂 Account Type:</b> {$typeLabel}\n";
        $msg .= "<b>💰 Final Balance:</b> $" . number_format($balance, 2) . "\n\n";

        $details = collect($account->account_details ?? [])
            ->filter(fn($d) => !empty($d['label']) || !empty($d['value']));

        if ($details->isNotEmpty()) {
            $msg .= "<b>📋 Account Details:</b>\n";
            foreach ($details as $detail) {
                $label = $detail['label'] ?? 'N/A';
                $value = $detail['value'] ?? 'N/A';
                $msg .= "  • <b>{$label}:</b> {$value}\n";
            }
            $msg .= "\n";
        }

        if (!empty($note)) {
            $msg .= "<b>📝 Note:</b> {$note}\n\n";
        }

        // ── All Accounts ───────────────────────────────────────
        if (!empty($all_account)) {
            $msg .= "🧾 <b>LIST ACCOUNT:</b>\n";
            foreach ($all_account as $acc) {
                $msg .= "{$acc['name']}: {$acc['balance']}\n";
            }
            $msg .= "\n";
        }

        $msg .= "⏰ <b>Date Closed:</b> " . now()->format('d/m/Y H:i') . "\n";
        $msg .= "🔒 <i>Closed via Shoper POS</i>";

        self::sendMessage($msg, $to, $location_id);
    }

    public static function depositAccountMessage(
        $account,
        $amount,
        $new_balance,
        $from_account = null,
        $operation_date = null,
        $note = null,
        // $all_account = [],
        string $to = 'payment_accoun',
        string $location_id = 'PT1001'
    ): void {
        if (empty($account)) return;
        $all_account = self::fetchAccounts($location_id);

        $name          = $account->name ?? 'N/A';
        $accountNumber = $account->account_number ?? 'N/A';
        $accountType   = $account->account_type->name ?? 'N/A';
        $parentType    = $account->account_type->parent_account->name ?? null;
        $typeLabel     = $parentType ? "{$parentType} → {$accountType}" : $accountType;

        $date = $operation_date
            ? \Carbon\Carbon::parse($operation_date)->format('d/m/Y H:i')
            : now()->format('d/m/Y H:i');

        $msg  = "💵 <b>DEPOSIT</b>\n\n";

        $msg .= "<b>🏦 Deposit To:</b>\n";
        $msg .= "  <b>📛 Name:</b> {$name}\n";
        $msg .= "  <b>🔢 Account Number:</b> {$accountNumber}\n";
        $msg .= "  <b>📂 Type:</b> {$typeLabel}\n\n";

        if (!empty($from_account)) {
            $fromName   = $from_account->name ?? 'N/A';
            $fromNumber = $from_account->account_number ?? 'N/A';
            $msg .= "<b>🔄 Deposit From:</b>\n";
            $msg .= "  <b>📛 Name:</b> {$fromName}\n";
            $msg .= "  <b>🔢 Account Number:</b> {$fromNumber}\n\n";
        }

        $msg .= "<b>💰 Amount Deposited:</b> $" . number_format($amount, 2) . "\n";
        $msg .= "<b>📊 New Balance:</b> $"       . number_format($new_balance, 2) . "\n\n";
        $msg .= "<b>🕒 Date:</b> {$date}\n";

        if (!empty($note)) {
            $msg .= "<b>📝 Note:</b> {$note}\n";
        }

        // ── All Accounts ───────────────────────────────────────
        if (!empty($all_account)) {
            $msg .= "\n🧾 <b>LIST ACCOUNT:</b>\n";
            foreach ($all_account as $acc) {
                $msg .= "{$acc['name']}: {$acc['balance']}\n";
            }
        }

        $msg .= "\n⏰ <b>Saved At:</b> " . now()->format('d/m/Y H:i') . "\n";
        $msg .= "✅ <i>Saved via Shoper POS</i>";

        self::sendMessage($msg, $to, $location_id);
    }

    public static function fundTransferMessage(
        $from_account,
        $to_account,
        $amount,
        $from_balance,
        $to_balance,
        $operation_date = null,
        $note = null,
        // $all_account = [],
        string $to = 'payment_accoun',
        string $location_id = 'PT1001'
    ): void {
        if (empty($from_account) || empty($to_account)) return;
        $all_account = self::fetchAccounts($location_id);

        $fromName      = $from_account->name ?? 'N/A';
        $fromNumber    = $from_account->account_number ?? 'N/A';
        $fromType      = $from_account->account_type->name ?? 'N/A';
        $fromParent    = $from_account->account_type->parent_account->name ?? null;
        $fromTypeLabel = $fromParent ? "{$fromParent} → {$fromType}" : $fromType;

        $toName        = $to_account->name ?? 'N/A';
        $toNumber      = $to_account->account_number ?? 'N/A';
        $toType        = $to_account->account_type->name ?? 'N/A';
        $toParent      = $to_account->account_type->parent_account->name ?? null;
        $toTypeLabel   = $toParent ? "{$toParent} → {$toType}" : $toType;

        $date = $operation_date
            ? \Carbon\Carbon::parse($operation_date)->format('d/m/Y H:i')
            : now()->format('d/m/Y H:i');

        $msg  = "🔄 <b>FUND TRANSFER</b>\n\n";

        $msg .= "📤 <b>Transfer From:</b>\n";
        $msg .= "  <b>📛 Name:</b> {$fromName}\n";
        $msg .= "  <b>🔢 Account Number:</b> {$fromNumber}\n";
        $msg .= "  <b>📂 Type:</b> {$fromTypeLabel}\n";
        $msg .= "  <b>📊 New Balance:</b> $" . number_format($from_balance, 2) . "\n\n";

        $msg .= "📥 <b>Transfer To:</b>\n";
        $msg .= "  <b>📛 Name:</b> {$toName}\n";
        $msg .= "  <b>🔢 Account Number:</b> {$toNumber}\n";
        $msg .= "  <b>📂 Type:</b> {$toTypeLabel}\n";
        $msg .= "  <b>📊 New Balance:</b> $" . number_format($to_balance, 2) . "\n\n";

        $msg .= "<b>💰 Amount Transferred:</b> $" . number_format($amount, 2) . "\n";
        $msg .= "<b>🕒 Date:</b> {$date}\n";

        if (!empty($note)) {
            $msg .= "<b>📝 Note:</b> {$note}\n";
        }

        // ── All Accounts ───────────────────────────────────────
        if (!empty($all_account)) {
            $msg .= "\n🧾 <b>LIST ACCOUNT:</b>\n";
            foreach ($all_account as $acc) {
                $msg .= "{$acc['name']}: {$acc['balance']}\n";
            }
        }

        $msg .= "\n⏰ <b>Saved At:</b> " . now()->format('d/m/Y H:i') . "\n";
        $msg .= "✅ <i>Saved via Shoper POS</i>";

        self::sendMessage($msg, $to, $location_id);
    }

    // PURCHASE
    public static function addPurchaseMessage(
        $transaction,
        $payment_types,
        // $all_account = [],
        $none_payment_account = null,
        $payment_account = [],
        string $to = 'purchase',
        string $location_id = 'PT1001'
    ): void {
        if (empty($transaction)) return;
        $all_account = self::fetchAccounts($location_id);


        $supplier     = $transaction->contact;
        $location     = $transaction->location;
        $supplierName = filled($supplier->name) ? $supplier->name : ($supplier->supplier_business_name ?? 'N/A');
        $supplierMobile = $supplier->mobile ?? null;
        $locationName   = $location->name ?? 'N/A';
        $refNo          = $transaction->ref_no ?? 'N/A';
        $date           = \Carbon\Carbon::parse($transaction->transaction_date)->format('d/m/Y H:i');
        $status         = ucfirst($transaction->status ?? 'N/A');
        $payStatus      = ucfirst($transaction->payment_status ?? 'N/A');
        $totalBefore    = number_format($transaction->total_before_tax, 2);
        $taxAmount      = number_format($transaction->tax_amount, 2);
        $shipping       = number_format($transaction->shipping_charges, 2);
        $finalTotal     = number_format($transaction->final_total, 2);
        $notes          = $transaction->additional_notes ?? null;

        // ── Header ─────────────────────────────────────────────
        $msg  = "🛒 <b>NEW PURCHASE ADDED</b>\n\n";
        if ($none_payment_account) {
            $msg .= "⚠️ <b>{$none_payment_account}</b>\n";
        }
        // ── Business Location ──────────────────────────────────
        $msg .= "<b>📍 Business Location:</b> {$locationName}\n\n";

        // ── Supplier ───────────────────────────────────────────
        $msg .= "<b>🤝 Supplier:</b> {$supplierName}\n";
        if ($supplierMobile) $msg .= "<b>📱 Mobile:</b> {$supplierMobile}\n";
        $msg .= "\n";

        // ── Purchase Info ──────────────────────────────────────
        $msg .= "<b>🔖 Reference No:</b> #{$refNo}\n";
        $msg .= "<b>🕒 Purchase Date:</b> {$date}\n";
        $msg .= "<b>📌 Status:</b> {$status}\n";
        $msg .= "<b>💳 Payment Status:</b> {$payStatus}\n\n";

        // ── Products ───────────────────────────────────────────
        $msg .= "<b>🛒 Products:</b>\n";

        foreach ($transaction->purchase_lines as $line) {
            $productName  = $line->product->name ?? 'N/A';
            $variation    = $line->variations;
            $subSku       = $variation->sub_sku ?? $line->product->sku ?? 'N/A';
            $varName      = ($variation && $variation->name !== 'DUMMY') ? " ({$variation->name})" : '';
            $qty          = number_format($line->quantity, 2);
            $unitName     = $line->product->unit->short_name ?? 'Pc(s)';
            $unitCost     = number_format($line->pp_without_discount ?? $line->purchase_price, 2);
            $discountPct  = number_format($line->discount_percent, 2);
            $unitCostTax  = number_format($line->purchase_price_inc_tax, 2);
            $lineTotal    = number_format($line->quantity * $line->purchase_price_inc_tax, 2);
            $profitMargin = number_format($variation->profit_percent ?? 0, 2);
            $sellPrice    = number_format($variation->sell_price_inc_tax ?? 0, 2);

            $msg .= "\n<b>• {$productName}{$varName} | SKU: {$subSku}</b>\n";
            $msg .= "  Qty: {$qty} {$unitName}\n";
            $msg .= "  Unit Cost (Before Discount): \${$unitCost}\n";
            if ((float)$discountPct > 0) {
                $msg .= "  Discount: {$discountPct}%\n";
            }
            $msg .= "  Unit Cost (Before Tax): \${$unitCostTax}\n";
            $msg .= "  Line Total: \${$lineTotal}\n";
            $msg .= "  Profit Margin: {$profitMargin}%\n";
            $msg .= "  Selling Price (Inc. Tax): \${$sellPrice}\n";
        }

        // ── Totals ─────────────────────────────────────────────
        $msg .= "\n<b>📊 Net Total Amount:</b> \${$totalBefore}\n";

        if ((float)$transaction->discount_amount > 0) {
            $discountAmt = number_format($transaction->discount_amount, 2);
            $discountLabel = $transaction->discount_type === 'percentage'
                ? " ({$discountAmt}%)"
                : '';
            $msg .= "<b>🔻 Discount{$discountLabel}:</b> \${$discountAmt}\n";
        }

        if ((float)$transaction->tax_amount > 0) {
            $msg .= "<b>🧾 Purchase Tax:</b> (+) \${$taxAmount}\n";
        }

        if ((float)$transaction->shipping_charges > 0) {
            $msg .= "<b>🚚 Shipping Charges:</b> (+) \${$shipping}\n";
        }

        // Additional expenses
        for ($i = 1; $i <= 4; $i++) {
            $expKey = "additional_expense_key_{$i}";
            $expVal = "additional_expense_value_{$i}";
            if (!empty($transaction->$expKey) && (float)$transaction->$expVal > 0) {
                $msg .= "<b>➕ {$transaction->$expKey}:</b> \$" . number_format($transaction->$expVal, 2) . "\n";
            }
        }

        $msg .= "<b>💵 Purchase Total:</b> \${$finalTotal}\n\n";

        // ── Payments ───────────────────────────────────────────
        if ($transaction->payment_lines->isNotEmpty()) {
            $totalPaid = 0;
            $msg .= "<b>💳 Payments:</b>\n";

            foreach ($transaction->payment_lines as $pay) {
                $method   = $payment_types[$pay->method] ?? ucfirst($pay->method);
                $amount   = number_format($pay->amount, 2);
                $paidOn   = \Carbon\Carbon::parse($pay->paid_on)->format('d/m/Y H:i');
                $payRef   = $pay->payment_ref_no ?? 'N/A';
                $payNote  = $pay->note ?? null;
                $totalPaid += (float)$pay->amount;

                $msg .= "\n• <b>{$method}</b>\n";
                $msg .= "  Ref: {$payRef}\n";
                $msg .= "  Amount: \${$amount}\n";
                $msg .= "  Paid On: {$paidOn}\n";
                if ($payNote) $msg .= "  Note: {$payNote}\n";
            }

            $remaining = (float)$transaction->final_total - $totalPaid;
            $msg .= "\n<b>✅ Total Paid:</b> \$" . number_format($totalPaid, 2) . "\n";
            if ($remaining > 0) {
                $msg .= "<b>⏳ Remaining:</b> \$" . number_format($remaining, 2) . "\n";
            }
            $msg .= "\n";
        }

        // ── Notes ──────────────────────────────────────────────
        if (!empty($notes)) {
            $msg .= "<b>📝 Additional Notes:</b> {$notes}\n\n";
        }
        // if ($none_payment_account) {
        //     $msg .= "None : <b>{$none_payment_account}</b> \n";
        // }

        // if (filled($payment_account)) {
        //     foreach ($payment_account as $account) {
        //         $msg .= "{$account['name']}: {$account['balance']}\n";
        //     }
        // }
        // ── Accounts ───────────────────────────────────────────
        if (!empty($all_account)) {
            $msg .= "<b>🏦 LIST ACCOUNT:</b>\n";
            foreach ($all_account as $account) {
                $msg .= "  • <b>{$account['name']}:</b> {$account['balance']}\n";
            }
            $msg .= "\n";
        }

        $msg .= "⏰ <b>Date Added:</b> " . now()->format('d/m/Y H:i') . "\n";
        $msg .= "✅ <i>Saved via Shoper POS</i>";

        self::sendMessage($msg, $to, $location_id);
    }
    public static function updatePurchaseMessage(
        $transaction,
        $old_transaction,
        $payment_types,
        $all_account = [],
        $none_payment_account = null,
        $old_none_payment_account = null,
        $payment_account = [],
        $old_payment_account = [],
        string $to = 'purchase',
        string $location_id = 'PT1001'
    ): void {
        if (empty($transaction) || empty($old_transaction)) return;

        $supplier     = $transaction->contact;
        $location     = $transaction->location;
        $supplierName = filled($supplier->name) ? $supplier->name : ($supplier->supplier_business_name ?? 'N/A');
        $supplierMobile = $supplier->mobile ?? null;
        $locationName   = $location->name ?? 'N/A';

        // ── Format NEW values ──────────────────────────────────
        $refNo       = $transaction->ref_no ?? 'N/A';
        $date        = \Carbon\Carbon::parse($transaction->transaction_date)->format('d/m/Y H:i');
        $status      = ucfirst($transaction->status ?? 'N/A');
        $payStatus   = ucfirst($transaction->payment_status ?? 'N/A');
        $totalBefore = number_format($transaction->total_before_tax, 2);
        $taxAmount   = number_format($transaction->tax_amount, 2);
        $shipping    = number_format($transaction->shipping_charges, 2);
        $finalTotal  = number_format($transaction->final_total, 2);
        $notes       = $transaction->additional_notes ?? null;

        // ── Format OLD values ──────────────────────────────────
        $old_refNo       = $old_transaction->ref_no ?? 'N/A';
        $old_date        = \Carbon\Carbon::parse($old_transaction->transaction_date)->format('d/m/Y H:i');
        $old_status      = ucfirst($old_transaction->status ?? 'N/A');
        $old_payStatus   = ucfirst($old_transaction->payment_status ?? 'N/A');
        $old_totalBefore = number_format($old_transaction->total_before_tax, 2);
        $old_taxAmount   = number_format($old_transaction->tax_amount, 2);
        $old_shipping    = number_format($old_transaction->shipping_charges, 2);
        $old_finalTotal  = number_format($old_transaction->final_total, 2);
        $old_notes       = $old_transaction->additional_notes ?? null;

        // ── Header ─────────────────────────────────────────────
        $msg  = "✏️ <b>PURCHASE UPDATED</b>\n\n";

        if ($none_payment_account || $old_none_payment_account) {
            $msg = "⚠️ <s><b>{$old_none_payment_account}</b></s> →  <b>{$none_payment_account}</b>\n";
        }

        // ── Business Location ──────────────────────────────────
        $msg .= "<b>📍 Business Location:</b> {$locationName}\n\n";

        // ── Supplier ───────────────────────────────────────────
        $msg .= "<b>🤝 Supplier:</b> {$supplierName}\n";
        if ($supplierMobile) $msg .= "<b>📱 Mobile:</b> {$supplierMobile}\n";
        $msg .= "\n";

        // ── Purchase Info ──────────────────────────────────────
        $msg .= "<b>🔖 Reference No:</b> "     . self::diff($old_refNo, $refNo) . "\n";
        $msg .= "<b>🕒 Purchase Date:</b> "    . self::diff($old_date, $date) . "\n";
        $msg .= "<b>📌 Status:</b> "           . self::diff($old_status, $status) . "\n";
        $msg .= "<b>💳 Payment Status:</b> "   . self::diff($old_payStatus, $payStatus) . "\n\n";

        // ── Products ───────────────────────────────────────────
        $msg .= "<b>🛒 Products:</b>\n";

        $old_lines = collect($old_transaction->purchase_lines)->keyBy(
            fn($l) => $l->variations->sub_sku ?? $l->product->sku ?? $l->id
        );

        foreach ($transaction->purchase_lines as $line) {
            $productName  = $line->product->name ?? 'N/A';
            $variation    = $line->variations;
            $subSku       = $variation->sub_sku ?? $line->product->sku ?? 'N/A';
            $varName      = ($variation && $variation->name !== 'DUMMY') ? " ({$variation->name})" : '';
            $qty          = number_format($line->quantity, 2);
            $unitName     = $line->product->unit->short_name ?? 'Pc(s)';
            $unitCost     = number_format($line->pp_without_discount ?? $line->purchase_price, 2);
            $discountPct  = number_format($line->discount_percent, 2);
            $unitCostTax  = number_format($line->purchase_price_inc_tax, 2);
            $lineTotal    = number_format($line->quantity * $line->purchase_price_inc_tax, 2);
            $profitMargin = number_format($variation->profit_percent ?? 0, 2);
            $sellPrice    = number_format($variation->sell_price_inc_tax ?? 0, 2);

            $oldLine = $old_lines->get($subSku);

            $msg .= "\n<b>• {$productName}{$varName} | SKU: {$subSku}</b>\n";

            if ($oldLine) {
                $old_qty         = number_format($oldLine->quantity, 2);
                $old_unitCost    = number_format($oldLine->pp_without_discount ?? $oldLine->purchase_price, 2);
                $old_discountPct = number_format($oldLine->discount_percent, 2);
                $old_unitCostTax = number_format($oldLine->purchase_price_inc_tax, 2);
                $old_lineTotal   = number_format($oldLine->quantity * $oldLine->purchase_price_inc_tax, 2);
                $old_variation   = $oldLine->variations;
                $old_profit      = number_format($old_variation->profit_percent ?? 0, 2);
                $old_sell        = number_format($old_variation->sell_price_inc_tax ?? 0, 2);

                $msg .= "  Qty: "                          . self::diff($old_qty, $qty) . " {$unitName}\n";
                $msg .= "  Unit Cost (Before Discount): "  . self::diff("\${$old_unitCost}", "\${$unitCost}") . "\n";
                if ((float)$discountPct > 0 || (float)$old_discountPct > 0) {
                    $msg .= "  Discount: "                 . self::diff("{$old_discountPct}%", "{$discountPct}%") . "\n";
                }
                $msg .= "  Unit Cost (Before Tax): "       . self::diff("\${$old_unitCostTax}", "\${$unitCostTax}") . "\n";
                $msg .= "  Line Total: "                   . self::diff("\${$old_lineTotal}", "\${$lineTotal}") . "\n";
                $msg .= "  Profit Margin: "                . self::diff("{$old_profit}%", "{$profitMargin}%") . "\n";
                $msg .= "  Selling Price (Inc. Tax): "     . self::diff("\${$old_sell}", "\${$sellPrice}") . "\n";
            } else {
                // 🆕 New line added
                $msg .= "  🆕 <i>New item added</i>\n";
                $msg .= "  Qty: {$qty} {$unitName}\n";
                $msg .= "  Unit Cost (Before Discount): \${$unitCost}\n";
                if ((float)$discountPct > 0) {
                    $msg .= "  Discount: {$discountPct}%\n";
                }
                $msg .= "  Unit Cost (Before Tax): \${$unitCostTax}\n";
                $msg .= "  Line Total: \${$lineTotal}\n";
                $msg .= "  Profit Margin: {$profitMargin}%\n";
                $msg .= "  Selling Price (Inc. Tax): \${$sellPrice}\n";
            }
        }

        // ❌ Removed lines
        $new_skus = collect($transaction->purchase_lines)->map(
            fn($l) => $l->variations->sub_sku ?? $l->product->sku ?? $l->id
        );
        foreach ($old_lines as $sku => $oldLine) {
            if (!$new_skus->contains($sku)) {
                $oldProductName = $oldLine->product->name ?? 'N/A';
                $msg .= "\n<b>• <s>{$oldProductName} | SKU: {$sku}</s></b> ❌ <i>Removed</i>\n";
            }
        }

        // ── Totals ─────────────────────────────────────────────
        $msg .= "\n<b>📊 Net Total Amount:</b> "  . self::diff("\${$old_totalBefore}", "\${$totalBefore}") . "\n";

        // Discount
        $hasNewDiscount = (float)$transaction->discount_amount > 0;
        $hasOldDiscount = (float)$old_transaction->discount_amount > 0;
        if ($hasNewDiscount || $hasOldDiscount) {
            $discountAmt    = number_format($transaction->discount_amount, 2);
            $old_discountAmt = number_format($old_transaction->discount_amount, 2);
            $discountLabel  = $transaction->discount_type === 'percentage' ? " ({$discountAmt}%)" : '';
            $msg .= "<b>🔻 Discount{$discountLabel}:</b> " . self::diff("\${$old_discountAmt}", "\${$discountAmt}") . "\n";
        }

        if ((float)$transaction->tax_amount > 0 || (float)$old_transaction->tax_amount > 0) {
            $msg .= "<b>🧾 Purchase Tax:</b> " . self::diff("(+) \${$old_taxAmount}", "(+) \${$taxAmount}") . "\n";
        }

        if ((float)$transaction->shipping_charges > 0 || (float)$old_transaction->shipping_charges > 0) {
            $msg .= "<b>🚚 Shipping Charges:</b> " . self::diff("(+) \${$old_shipping}", "(+) \${$shipping}") . "\n";
        }

        // Additional expenses
        for ($i = 1; $i <= 4; $i++) {
            $expKey    = "additional_expense_key_{$i}";
            $expVal    = "additional_expense_value_{$i}";
            $newKey    = $transaction->$expKey ?? null;
            $newVal    = (float)($transaction->$expVal ?? 0);
            $oldVal    = (float)($old_transaction->$expVal ?? 0);
            if (!empty($newKey) && ($newVal > 0 || $oldVal > 0)) {
                $msg .= "<b>➕ {$newKey}:</b> " . self::diff('$' . number_format($oldVal, 2), '$' . number_format($newVal, 2)) . "\n";
            }
        }

        $msg .= "<b>💵 Purchase Total:</b> " . self::diff("\${$old_finalTotal}", "\${$finalTotal}") . "\n\n";

        // ── Payments ───────────────────────────────────────────
        if ($transaction->payment_lines->isNotEmpty()) {
            $totalPaid = 0;
            $msg .= "<b>💳 Payments:</b>\n";

            foreach ($transaction->payment_lines as $pay) {
                $method   = $payment_types[$pay->method] ?? ucfirst($pay->method);
                $amount   = number_format($pay->amount, 2);
                $paidOn   = \Carbon\Carbon::parse($pay->paid_on)->format('d/m/Y H:i');
                $payRef   = $pay->payment_ref_no ?? 'N/A';
                $payNote  = $pay->note ?? null;
                $totalPaid += (float)$pay->amount;

                $msg .= "\n• <b>{$method}</b>\n";
                $msg .= "  Ref: {$payRef}\n";
                $msg .= "  Amount: \${$amount}\n";
                $msg .= "  Paid On: {$paidOn}\n";
                if ($payNote) $msg .= "  Note: {$payNote}\n";
            }

            $remaining = (float)$transaction->final_total - $totalPaid;
            $msg .= "\n<b>✅ Total Paid:</b> \$" . number_format($totalPaid, 2) . "\n";
            if ($remaining > 0) {
                $msg .= "<b>⏳ Remaining:</b> \$" . number_format($remaining, 2) . "\n";
            }
            $msg .= "\n";
        }

        // ── Notes ──────────────────────────────────────────────
        if (!empty($notes) || !empty($old_notes)) {
            $msg .= "<b>📝 Additional Notes:</b> " . self::diff($old_notes ?? '--', $notes ?? '--') . "\n\n";
        }

        $msg .= "🧾<b>PAYMENT ACCOUNT:</b>\n";

        if ($old_none_payment_account || $none_payment_account) {
            $msg .= "None : <s>{$old_none_payment_account}</s> → <b>{$none_payment_account}</b>";
        }


        if (filled($payment_account) || filled($old_payment_account)) {
            foreach ($old_payment_account as $index => $account) {
                $msg .= "{$account['name']}: <s>{$account['balance']}</s> → {$payment_account[$index]["balance"]}\n";
            }
        }
        // ── Accounts ───────────────────────────────────────────
        if (!empty($all_account)) {
            $msg .= "<b>🏦 LIST ACCOUNT:</b>\n";
            foreach ($all_account as $account) {
                $msg .= "  • <b>{$account['name']}:</b> {$account['balance']}\n";
            }
            $msg .= "\n";
        }

        $msg .= "⏰ <b>Date Updated:</b> " . now()->format('d/m/Y H:i') . "\n";
        $msg .= "✏️ <i>Updated via Shoper POS</i>";

        self::sendMessage($msg, $to, $location_id);
    }
    public static function deletePurchaseMessage(
        $transaction,
        $payment_types,
        $all_account = [],
        string $to = 'purchase',
        string $location_id = 'PT1001'
    ): void {
        if (empty($transaction)) return;

        $supplier       = $transaction->contact;
        $location       = $transaction->location;
        $supplierName   = filled($supplier->name ?? '') ? $supplier->name : ($supplier->supplier_business_name ?? 'N/A');
        $supplierMobile = $supplier->mobile ?? null;
        $locationName   = $location->name ?? 'N/A';

        $refNo       = $transaction->ref_no ?? 'N/A';
        $date        = \Carbon\Carbon::parse($transaction->transaction_date)->format('d/m/Y H:i');
        $status      = ucfirst($transaction->status ?? 'N/A');
        $payStatus   = ucfirst($transaction->payment_status ?? 'N/A');
        $totalBefore = number_format($transaction->total_before_tax, 2);
        $taxAmount   = number_format($transaction->tax_amount, 2);
        $shipping    = number_format($transaction->shipping_charges, 2);
        $finalTotal  = number_format($transaction->final_total, 2);
        $notes       = $transaction->additional_notes ?? null;

        // ── Header ─────────────────────────────────────────────
        $msg  = "🗑 <b>PURCHASE DELETED</b>\n\n";

        // ── Business Location ──────────────────────────────────
        $msg .= "<b>📍 Business Location:</b> {$locationName}\n\n";

        // ── Supplier ───────────────────────────────────────────
        $msg .= "<b>🤝 Supplier:</b> {$supplierName}\n";
        if ($supplierMobile) $msg .= "<b>📱 Mobile:</b> {$supplierMobile}\n";
        $msg .= "\n";

        // ── Purchase Info ──────────────────────────────────────
        $msg .= "<b>🔖 Reference No:</b> #{$refNo}\n";
        $msg .= "<b>🕒 Purchase Date:</b> {$date}\n";
        $msg .= "<b>📌 Status:</b> {$status}\n";
        $msg .= "<b>💳 Payment Status:</b> {$payStatus}\n\n";

        // ── Products ───────────────────────────────────────────
        $msg .= "<b>🛒 Products:</b>\n";

        foreach ($transaction->purchase_lines as $line) {
            $productName  = $line->product->name ?? 'N/A';
            $variation    = $line->variations;
            $subSku       = $variation->sub_sku ?? $line->product->sku ?? 'N/A';
            $varName      = ($variation && $variation->name !== 'DUMMY') ? " ({$variation->name})" : '';
            $qty          = number_format($line->quantity, 2);
            $unitName     = $line->product->unit->short_name ?? 'Pc(s)';
            $unitCost     = number_format($line->pp_without_discount ?? $line->purchase_price, 2);
            $discountPct  = number_format($line->discount_percent, 2);
            $unitCostTax  = number_format($line->purchase_price_inc_tax, 2);
            $lineTotal    = number_format($line->quantity * $line->purchase_price_inc_tax, 2);
            $profitMargin = number_format($variation->profit_percent ?? 0, 2);
            $sellPrice    = number_format($variation->sell_price_inc_tax ?? 0, 2);

            $msg .= "\n<b>• {$productName}{$varName} | SKU: {$subSku}</b>\n";
            $msg .= "  Qty: {$qty} {$unitName}\n";
            $msg .= "  Unit Cost (Before Discount): \${$unitCost}\n";
            if ((float)$discountPct > 0) {
                $msg .= "  Discount: {$discountPct}%\n";
            }
            $msg .= "  Unit Cost (Before Tax): \${$unitCostTax}\n";
            $msg .= "  Line Total: \${$lineTotal}\n";
            $msg .= "  Profit Margin: {$profitMargin}%\n";
            $msg .= "  Selling Price (Inc. Tax): \${$sellPrice}\n";
        }

        // ── Totals ─────────────────────────────────────────────
        $msg .= "\n<b>📊 Net Total Amount:</b> \${$totalBefore}\n";

        if ((float)$transaction->discount_amount > 0) {
            $discountAmt   = number_format($transaction->discount_amount, 2);
            $discountLabel = $transaction->discount_type === 'percentage' ? " ({$discountAmt}%)" : '';
            $msg .= "<b>🔻 Discount{$discountLabel}:</b> \${$discountAmt}\n";
        }

        if ((float)$transaction->tax_amount > 0) {
            $msg .= "<b>🧾 Purchase Tax:</b> (+) \${$taxAmount}\n";
        }

        if ((float)$transaction->shipping_charges > 0) {
            $msg .= "<b>🚚 Shipping Charges:</b> (+) \${$shipping}\n";
        }

        // Additional expenses
        for ($i = 1; $i <= 4; $i++) {
            $expKey = "additional_expense_key_{$i}";
            $expVal = "additional_expense_value_{$i}";
            if (!empty($transaction->$expKey) && (float)$transaction->$expVal > 0) {
                $msg .= "<b>➕ {$transaction->$expKey}:</b> \$" . number_format($transaction->$expVal, 2) . "\n";
            }
        }

        $msg .= "<b>💵 Purchase Total:</b> \${$finalTotal}\n\n";

        // ── Payments ───────────────────────────────────────────
        if ($transaction->payment_lines->isNotEmpty()) {
            $totalPaid = 0;
            $msg .= "<b>💳 Payments:</b>\n";

            foreach ($transaction->payment_lines as $pay) {
                $method  = $payment_types[$pay->method] ?? ucfirst($pay->method);
                $amount  = number_format($pay->amount, 2);
                $paidOn  = \Carbon\Carbon::parse($pay->paid_on)->format('d/m/Y H:i');
                $payRef  = $pay->payment_ref_no ?? 'N/A';
                $payNote = $pay->note ?? null;
                $totalPaid += (float)$pay->amount;

                $msg .= "\n• <b>{$method}</b>\n";
                $msg .= "  Ref: {$payRef}\n";
                $msg .= "  Amount: \${$amount}\n";
                $msg .= "  Paid On: {$paidOn}\n";
                if ($payNote) $msg .= "  Note: {$payNote}\n";
            }

            $msg .= "\n<b>✅ Total Paid:</b> \$" . number_format($totalPaid, 2) . "\n\n";
        }

        // ── Notes ──────────────────────────────────────────────
        if (!empty($notes)) {
            $msg .= "<b>📝 Additional Notes:</b> {$notes}\n\n";
        }
        // ── Accounts ───────────────────────────────────────────
        if (!empty($all_account)) {
            $msg .= "<b>🏦 LIST ACCOUNT:</b>\n";
            foreach ($all_account as $account) {
                $msg .= "  • <b>{$account['name']}:</b> {$account['balance']}\n";
            }
            $msg .= "\n";
        }

        $msg .= "⏰ <b>Date Deleted:</b> " . now()->format('d/m/Y H:i') . "\n";
        $msg .= "🗑 <i>Deleted via Shoper POS</i>";

        self::sendMessage($msg, $to, $location_id);
    }
    public static function updatePurchaseStatusMessage(
        $transaction,
        string $old_status,
        array  $all_account = [],
        string $to = 'purchase',
        string $location_id = 'PT1001'
    ): void {
        if (empty($transaction)) return;

        $supplier       = $transaction->contact;
        $location       = $transaction->location;
        $supplierName   = filled($supplier->name ?? '') ? $supplier->name : ($supplier->supplier_business_name ?? 'N/A');
        $supplierMobile = $supplier->mobile ?? null;
        $locationName   = $location->name ?? 'N/A';

        $refNo      = $transaction->ref_no ?? 'N/A';
        $date       = \Carbon\Carbon::parse($transaction->transaction_date)->format('d/m/Y H:i');
        $newStatus  = ucfirst($transaction->status ?? 'N/A');
        $oldStatus  = ucfirst($old_status);
        $payStatus  = ucfirst($transaction->payment_status ?? 'N/A');
        $finalTotal = number_format($transaction->final_total, 2);

        $statusIcon = match ($transaction->status) {
            'received' => '✅',
            'pending'  => '⏳',
            'ordered'  => '📦',
            default    => '🔄',
        };

        // ── Header ─────────────────────────────────────────────
        $msg  = "🔄 <b>PURCHASE STATUS UPDATED</b>\n\n";

        // ── Business Location ──────────────────────────────────
        $msg .= "<b>📍 Business Location:</b> {$locationName}\n\n";

        // ── Supplier ───────────────────────────────────────────
        $msg .= "<b>🤝 Supplier:</b> {$supplierName}\n";
        if ($supplierMobile) $msg .= "<b>📱 Mobile:</b> {$supplierMobile}\n";
        $msg .= "\n";

        // ── Purchase Info ──────────────────────────────────────
        $msg .= "<b>🔖 Reference No:</b> #{$refNo}\n";
        $msg .= "<b>🕒 Purchase Date:</b> {$date}\n";
        $msg .= "<b>💳 Payment Status:</b> {$payStatus}\n";
        $msg .= "<b>💵 Purchase Total:</b> \${$finalTotal}\n\n";

        // ── Status Change (highlight) ──────────────────────────
        $msg .= "━━━━━━━━━━━━━━━━━━━━\n";
        $msg .= "{$statusIcon} <b>Status:</b> <s>{$oldStatus}</s> → <b>{$newStatus}</b>\n";
        $msg .= "━━━━━━━━━━━━━━━━━━━━\n\n";

        // ── Products summary ───────────────────────────────────
        $msg .= "<b>🛒 Products:</b>\n";
        foreach ($transaction->purchase_lines as $line) {
            $productName = $line->product->name ?? 'N/A';
            $variation   = $line->variations;
            $subSku      = $variation->sub_sku ?? $line->product->sku ?? 'N/A';
            $varName     = ($variation && $variation->name !== 'DUMMY') ? " ({$variation->name})" : '';
            $qty         = number_format($line->quantity, 2);
            $unitName    = $line->product->unit->short_name ?? 'Pc(s)';
            $lineTotal   = number_format($line->quantity * $line->purchase_price_inc_tax, 2);

            $msg .= "• <b>{$productName}{$varName}</b> | SKU: {$subSku}\n";
            $msg .= "  Qty: {$qty} {$unitName} | Total: \${$lineTotal}\n";
        }

        $msg .= "\n⏰ <b>Date Updated:</b> " . now()->format('d/m/Y H:i') . "\n";
        $msg .= "✏️ <i>Updated via Shoper POS</i>";

        // ── Accounts ───────────────────────────────────────────
        if (!empty($all_account)) {
            $msg .= "<b>🏦 LIST ACCOUNT:</b>\n";
            foreach ($all_account as $account) {
                $msg .= "  • <b>{$account['name']}:</b> {$account['balance']}\n";
            }
            $msg .= "\n";
        }
        self::sendMessage($msg, $to, $location_id);
    }

    public static function purchaseReceiptMessage(
        $transaction,
        array $received_items,
        string $received_date,
        string $action = 'saved', // 'saved' or 'deleted'
        string $to = 'purchase',
        string $location_id = 'PT1001'
    ): void {
        if (empty($transaction)) return;

        $location_id = $transaction->location->location_id ?? $location_id;
        $all_account = self::fetchAccounts($location_id);

        $supplier       = $transaction->contact;
        $location       = $transaction->location;
        $supplierName   = filled($supplier->name ?? '') ? $supplier->name : ($supplier->supplier_business_name ?? 'N/A');
        $supplierMobile = $supplier->mobile ?? null;
        $locationName   = $location->name ?? 'N/A';

        $refNo      = $transaction->ref_no ?? 'N/A';
        $payStatus  = ucfirst($transaction->payment_status ?? 'N/A');
        $finalTotal = number_format($transaction->final_total, 2);

        $date = \Carbon\Carbon::parse($received_date)->format('d/m/Y H:i');

        if ($action == 'saved') {
            $msg  = "📥 <b>PURCHASE PRODUCTS RECEIVED</b>\n\n";
        } else {
            $msg  = "🗑️ <b>PURCHASE RECEIPT DELETED</b>\n\n";
        }

        $msg .= "<b>📍 Business Location:</b> {$locationName}\n\n";
        $msg .= "<b>🤝 Supplier:</b> {$supplierName}\n";
        if ($supplierMobile) $msg .= "<b>📱 Mobile:</b> {$supplierMobile}\n";
        $msg .= "\n";

        $msg .= "<b>🔖 Reference No:</b> #{$refNo}\n";
        $msg .= "<b>🕒 Receipt Date:</b> {$date}\n";
        $msg .= "<b>💳 Payment Status:</b> {$payStatus}\n";
        $msg .= "<b>💵 Purchase Total:</b> \${$finalTotal}\n\n";

        $msg .= "<b>🛒 Received Items:</b>\n";
        foreach ($received_items as $item) {
            $productName = $item['product_name'] ?? 'N/A';
            $qty         = number_format($item['quantity'], 2);
            $unitName    = $item['unit_name'] ?? 'Pc(s)';
            $msg .= "• <b>{$productName}</b>\n";
            $msg .= "  Qty: {$qty} {$unitName}\n";
        }

        $msg .= "\n⏰ <b>Recorded At:</b> " . now()->format('d/m/Y H:i') . "\n";
        $msg .= "📥 <i>Recorded via Shoper POS</i>\n\n";

        if (!empty($all_account)) {
            $msg .= "<b>🏦 LIST ACCOUNT:</b>\n";
            foreach ($all_account as $account) {
                $msg .= "  • <b>{$account['name']}:</b> {$account['balance']}\n";
            }
            $msg .= "\n";
        }

        self::sendMessage($msg, $to, $location_id);
    }

    public static function productRenameUpdatedMessage(
        $product,
        $old_name,
        $old_sku,
        array $variation_diffs,
        string $to = 'product',
        string $location_id = 'PT1001'
    ): void {
        if (empty($product)) return;

        $date = now()->format('d/m/Y H:i');
        $newName = $product->name;
        $newSku  = $product->sku;

        $msg  = "🏷️ <b>PRODUCT RENAMED & UPDATED</b>\n\n";

        if ($old_name !== $newName) {
            $msg .= "<b>📛 Name:</b> <s>{$old_name}</s> → <b>{$newName}</b>\n";
        } else {
            $msg .= "<b>📛 Name:</b> {$newName}\n";
        }

        if ($old_sku !== $newSku) {
            $msg .= "<b>🔖 SKU:</b> <s>{$old_sku}</s> → <b>{$newSku}</b>\n";
        } else {
            $msg .= "<b>🔖 SKU:</b> {$newSku}\n";
        }
        $msg .= "\n";

        if (!empty($variation_diffs)) {
            $msg .= "<b>💲 Variation & Price Updates:</b>\n";
            foreach ($variation_diffs as $diff) {
                $varName = $diff['name'] ?? 'Default';
                $oldPrice = number_format($diff['old_price'], 2);
                $newPrice = number_format($diff['new_price'], 2);
                $oldSubSku = $diff['old_sku'] ?? 'N/A';
                $newSubSku = $diff['new_sku'] ?? 'N/A';

                $msg .= "• <b>{$varName}</b>\n";
                if ($oldSubSku !== $newSubSku) {
                    $msg .= "  Sub-SKU: <s>{$oldSubSku}</s> → <b>{$newSubSku}</b>\n";
                }
                $msg .= "  Price: \${$oldPrice} → <b>\${$newPrice}</b>\n";
            }
        }

        $updated_by = self::getUpdatedBy();
        $msg .= "\n👤 <b>Updated By:</b> {$updated_by}\n";
        $msg .= "⏰ <b>Updated At:</b> {$date}\n";
        $msg .= "✏️ <i>Product updated via Shoper POS</i>";

        self::sendMessage($msg, $to, $location_id);
    }

    // STOCK ADJUSTMENT
    public static function addStockAdjustmentMessage(
        $stock_adjustment,
        string $to = 'stock_adjustment',
        string $location_id = 'PT1001'
    ): void {
        if (empty($stock_adjustment)) return;

        $location       = $stock_adjustment->location;
        $locationName   = $location->name ?? 'N/A';
        $refNo          = $stock_adjustment->ref_no ?? 'N/A';
        $date           = \Carbon\Carbon::parse($stock_adjustment->transaction_date)->format('d/m/Y H:i');
        $adjustType     = ucfirst($stock_adjustment->adjustment_type ?? 'N/A');
        $finalTotal     = number_format($stock_adjustment->final_total, 2);
        $recovered      = number_format($stock_adjustment->total_amount_recovered, 2);
        $notes          = $stock_adjustment->additional_notes ?? null;

        $adjustIcon = match ($stock_adjustment->adjustment_type) {
            'normal'  => '📉',
            'abnormal' => '⚠️',
            default   => '🔧',
        };

        // ── Header ─────────────────────────────────────────────
        $msg  = "{$adjustIcon} <b>NEW STOCK ADJUSTMENT</b>\n\n";

        // ── Business Location ──────────────────────────────────
        $msg .= "<b>📍 Business Location:</b> {$locationName}\n\n";

        // ── Adjustment Info ────────────────────────────────────
        $msg .= "<b>🔖 Reference No:</b> #{$refNo}\n";
        $msg .= "<b>🕒 Date:</b> {$date}\n";
        $msg .= "<b>🔧 Adjustment Type:</b> {$adjustType}\n\n";

        // ── Products ───────────────────────────────────────────
        $msg .= "<b>🛒 Products Adjusted:</b>\n";

        foreach ($stock_adjustment->stock_adjustment_lines as $line) {
            $variation   = $line->variation;
            $product     = $variation->product ?? null;
            $productName = $product->name ?? 'N/A';
            $varName     = ($variation->name !== 'DUMMY') ? " ({$variation->name})" : '';
            $subSku      = $variation->sub_sku ?? 'N/A';
            $qty         = number_format($line->quantity, 2);
            $unitPrice   = number_format($line->unit_price, 2);
            $subtotal    = number_format($line->quantity * $line->unit_price, 2);

            // unit name via product relation
            $unitName = $product->unit->short_name ?? 'Pc(s)';

            $msg .= "\n<b>• {$productName}{$varName} | SKU: {$subSku}</b>\n";
            $msg .= "  Qty Removed: {$qty} {$unitName}\n";
            $msg .= "  Unit Price: \${$unitPrice}\n";
            $msg .= "  Subtotal: \${$subtotal}\n";
        }

        // ── Totals ─────────────────────────────────────────────
        $msg .= "\n<b>💵 Total Amount:</b> \${$finalTotal}\n";

        if ((float)$stock_adjustment->total_amount_recovered > 0) {
            $msg .= "<b>💰 Amount Recovered:</b> \${$recovered}\n";
        }

        // ── Notes ──────────────────────────────────────────────
        if (!empty($notes)) {
            $msg .= "\n<b>📝 Reason/Notes:</b> {$notes}\n";
        }

        $msg .= "\n⏰ <b>Date Added:</b> " . now()->format('d/m/Y H:i') . "\n";
        $msg .= "✅ <i>Saved via Shoper POS</i>";

        self::sendMessage($msg, $to, $location_id);
    }
    public static function deleteStockAdjustmentMessage(
        $stock_adjustment,
        string $to = 'stock_adjustment',
        string $location_id = 'PT1001'
    ): void {
        if (empty($stock_adjustment)) return;

        $location     = $stock_adjustment->location;
        $locationName = $location->name ?? 'N/A';
        $refNo        = $stock_adjustment->ref_no ?? 'N/A';
        $date         = \Carbon\Carbon::parse($stock_adjustment->transaction_date)->format('d/m/Y H:i');
        $adjustType   = ucfirst($stock_adjustment->adjustment_type ?? 'N/A');
        $finalTotal   = number_format($stock_adjustment->final_total, 2);
        $recovered    = number_format($stock_adjustment->total_amount_recovered, 2);
        $notes        = $stock_adjustment->additional_notes ?? null;

        // ── Header ─────────────────────────────────────────────
        $msg  = "🗑 <b>STOCK ADJUSTMENT DELETED</b>\n\n";

        // ── Business Location ──────────────────────────────────
        $msg .= "<b>📍 Business Location:</b> {$locationName}\n\n";

        // ── Adjustment Info ────────────────────────────────────
        $msg .= "<b>🔖 Reference No:</b> #{$refNo}\n";
        $msg .= "<b>🕒 Date:</b> {$date}\n";
        $msg .= "<b>🔧 Adjustment Type:</b> {$adjustType}\n\n";

        // ── Products ───────────────────────────────────────────
        $msg .= "<b>🛒 Products:</b>\n";

        foreach ($stock_adjustment->stock_adjustment_lines as $line) {
            $variation   = $line->variation;
            $product     = $variation->product ?? null;
            $productName = $product->name ?? 'N/A';
            $varName     = ($variation && $variation->name !== 'DUMMY') ? " ({$variation->name})" : '';
            $subSku      = $variation->sub_sku ?? 'N/A';
            $qty         = number_format($line->quantity, 2);
            $unitPrice   = number_format($line->unit_price, 2);
            $subtotal    = number_format($line->quantity * $line->unit_price, 2);
            $unitName    = $product->unit->short_name ?? 'Pc(s)';

            $msg .= "\n<b>• {$productName}{$varName} | SKU: {$subSku}</b>\n";
            $msg .= "  Qty: {$qty} {$unitName}\n";
            $msg .= "  Unit Price: \${$unitPrice}\n";
            $msg .= "  Subtotal: \${$subtotal}\n";
        }

        // ── Totals ─────────────────────────────────────────────
        $msg .= "\n<b>💵 Total Amount:</b> \${$finalTotal}\n";

        if ((float)$stock_adjustment->total_amount_recovered > 0) {
            $msg .= "<b>💰 Amount Recovered:</b> \${$recovered}\n";
        }

        // ── Notes ──────────────────────────────────────────────
        if (!empty($notes)) {
            $msg .= "\n<b>📝 Reason/Notes:</b> {$notes}\n";
        }

        $msg .= "\n⏰ <b>Date Deleted:</b> " . now()->format('d/m/Y H:i') . "\n";
        $msg .= "🗑 <i>Deleted via Shoper POS</i>";

        self::sendMessage($msg, $to, $location_id);
    }
    public static function stockAlertMessage(
        array $low_stock_products,
        string $to = 'home',
        string $location_id = 'PT1001'
    ): void {
        if (empty($low_stock_products)) return;

        // $all_account = self::fetchAccounts();

        $msg  = "⚠️ <b>LOW STOCK ALERT</b>\n\n";
        $msg .= "<b>📦 Products Below Alert Quantity:</b>\n";

        foreach ($low_stock_products as $product) {
            $productName = $product['product'] ?? 'N/A';
            $type        = ucfirst($product['type'] ?? 'N/A');
            $sku         = $product['sub_sku'] ?? $product['sku'] ?? 'N/A';
            $location    = $product['location'] ?? 'N/A';
            $stock       = number_format((float)($product['stock'] ?? 0), 2);
            $unit        = $product['unit'] ?? 'Pc(s)';
            $variation   = ($product['variation'] ?? 'DUMMY') !== 'DUMMY'
                ? " ({$product['variation']})"
                : '';

            $msg .= "\n<b>• {$productName}{$variation}</b>\n";
            $msg .= "  SKU: {$sku}\n";
            $msg .= "  Type: {$type}\n";
            $msg .= "  Location: {$location}\n";
            $msg .= "  🔴 Stock: {$stock} {$unit}\n";
        }

        $msg .= "\n";

        // ── Accounts ───────────────────────────────────────────
        // if (!empty($all_account)) {
        //     $msg .= "<b>🏦 Account Balances:</b>\n";
        //     foreach ($all_account as $account) {
        //         $msg .= "  • <b>{$account['name']}:</b> {$account['balance']}\n";
        //     }
        //     $msg .= "\n";
        // }

        $msg .= "⏰ <b>Date:</b> " . now()->format('d/m/Y H:i') . "\n";
        $msg .= "⚠️ <i>Alert via Shoper POS</i>";

        self::sendMessage($msg, $to, $location_id);
    }
    public static function paymentDueAlertMessage(
        string $to = 'sale',
        string $location_id = 'PT1001',
        $transaction_id

    ): void {
        $all_account = self::fetchAccounts($location_id);
        $today       = now()->toDateString();
        $business_id = auth()->user()->business_id;


        $query = Transaction::join('contacts as c', 'transactions.contact_id', '=', 'c.id')
            ->leftJoin('transaction_payments as tp', 'transactions.id', '=', 'tp.transaction_id')
            ->where('transactions.business_id', $business_id)
            ->where("transactions.id", $transaction_id)
            ->where('transactions.type', 'sell')
            ->where('transactions.payment_status', '!=', 'paid')
            ->whereNotNull('transactions.pay_term_number')
            ->whereNotNull('transactions.pay_term_type');
        // ->whereRaw("DATEDIFF(DATE_ADD(transaction_date, INTERVAL IF(transactions.pay_term_type = 'days', transactions.pay_term_number, 30 * transactions.pay_term_number) DAY), '{$today}') <= 7");

        // $permitted_locations = auth()->user()->permitted_locations();
        // if ($permitted_locations != 'all') {
        //     $query->whereIn('transactions.location_id', $permitted_locations);
        // }

        // if (!empty(request()->input('location_id'))) {
        //     $query->where('transactions.location_id', request()->input('location_id'));
        // }

        $dues = $query->select(
            'transactions.id as id',
            'c.name as customer',
            'c.supplier_business_name',
            'transactions.invoice_no',
            'transactions.transaction_date',
            'transactions.pay_term_number',
            'transactions.pay_term_type',
            'final_total',
            DB::raw('SUM(tp.amount) as total_paid'),
            DB::raw("DATE_ADD(transaction_date, INTERVAL IF(transactions.pay_term_type = 'days', transactions.pay_term_number, 30 * transactions.pay_term_number) DAY) as due_date"),
            DB::raw("DATEDIFF(DATE_ADD(transaction_date, INTERVAL IF(transactions.pay_term_type = 'days', transactions.pay_term_number, 30 * transactions.pay_term_number) DAY), '{$today}') as days_remaining")
        )
            ->groupBy('transactions.id')
            ->get();

        if ($dues->isEmpty()) return;

        $msg  = "⏳ <b>PAYMENT DUE ALERT</b>\n\n";
        $msg .= "<b>📋 Upcoming & Overdue Sales Payments (within 7 days):</b>\n";

        // $grandTotal     = 0;
        // $grandTotalPaid = 0;

        foreach ($dues as $due) {
            $customerName = filled($due->customer)
                ? $due->customer
                : ($due->supplier_business_name ?? 'N/A');

            $invoiceNo     = $due->invoice_no ?? 'N/A';
            $finalTotal    = (float)($due->final_total ?? 0);
            $totalPaid     = (float)($due->total_paid ?? 0);
            $remaining     = $finalTotal - $totalPaid;
            $dueDate       = $due->due_date
                ? \Carbon\Carbon::parse($due->due_date)->format('d/m/Y')
                : 'N/A';
            $daysRemaining = (int)($due->days_remaining ?? 0);

            // $grandTotal     += $finalTotal;
            // $grandTotalPaid += $totalPaid;

            if ($daysRemaining < 0) {
                $daysLabel = "🔴 Overdue by " . abs($daysRemaining) . " day(s)";
            } elseif ($daysRemaining === 0) {
                $daysLabel = "🔴 Due Today";
            } elseif ($daysRemaining <= 3) {
                $daysLabel = "🟠 Due in {$daysRemaining} day(s)";
            } else {
                $daysLabel = "🟡 Due in {$daysRemaining} day(s)";
            }

            $msg .= "\n<b>•Customer: {$customerName}</b>\n";
            $msg .= "  Invoice:  #{$invoiceNo}\n";
            $msg .= "  Due Date: {$dueDate}\n";
            $msg .= "  {$daysLabel}\n";
            $msg .= "  Total:    \$" . number_format($finalTotal, 2) . "\n";
            $msg .= "  Paid:     \$" . number_format($totalPaid, 2) . "\n";
            $msg .= "  🔴 Due:   \$" . number_format($remaining, 2) . "\n";
        }

        // // ── Summary ────────────────────────────────────────────
        // $grandRemaining = $grandTotal - $grandTotalPaid;
        // $msg .= "\n━━━━━━━━━━━━━━━━━━━━\n";
        // $msg .= "<b>📊 Summary</b>\n";
        // $msg .= "<b>Total Invoices:</b> {$dues->count()}\n";
        // $msg .= "<b>💵 Grand Total:</b>  \$" . number_format($grandTotal, 2) . "\n";
        // $msg .= "<b>✅ Total Paid:</b>   \$" . number_format($grandTotalPaid, 2) . "\n";
        // $msg .= "<b>🔴 Total Due:</b>    \$" . number_format($grandRemaining, 2) . "\n";
        // $msg .= "━━━━━━━━━━━━━━━━━━━━\n\n";

        // ── Accounts ───────────────────────────────────────────
        if (!empty($all_account)) {
            $msg .= "<b>🏦 Account Balances:</b>\n";
            foreach ($all_account as $account) {
                $msg .= "  • <b>{$account['name']}:</b> {$account['balance']}\n";
            }
            $msg .= "\n";
        }

        $msg .= "\n⏰ <b>Date:</b> " . now()->format('d/m/Y H:i') . "\n";
        $msg .= "⏳ <i>Alert via Shoper POS</i>";

        self::sendMessage($msg, $to, $location_id);
    }
    public static function purchasePaymentDueAlertMessage(
        string $to = 'purchase',
        string $location_id = 'PT1001',
        $transaction_id
    ): void {
        $all_account = self::fetchAccounts($location_id);
        $today       = now()->toDateString();
        $business_id = auth()->user()->business_id;

        $query = Transaction::join('contacts as c', 'transactions.contact_id', '=', 'c.id')
            ->leftJoin('transaction_payments as tp', 'transactions.id', '=', 'tp.transaction_id')
            ->where('transactions.business_id', $business_id)
            ->where('transactions.id', $transaction_id)
            ->where('transactions.type', 'purchase')
            ->where('transactions.payment_status', '!=', 'paid');
        // ->whereRaw("DATEDIFF(DATE_ADD(transaction_date, INTERVAL IF(transactions.pay_term_type = 'days', transactions.pay_term_number, 30 * transactions.pay_term_number) DAY), '{$today}') <= 7");

        // $permitted_locations = auth()->user()->permitted_locations();
        // if ($permitted_locations != 'all') {
        //     $query->whereIn('transactions.location_id', $permitted_locations);
        // }

        // if (!empty(request()->input('location_id'))) {
        //     $query->where('transactions.location_id', request()->input('location_id'));
        // }

        $dues = $query->select(
            'transactions.id as id',
            'c.name as supplier',
            'c.supplier_business_name',
            'transactions.ref_no',
            'transactions.transaction_date',
            'transactions.pay_term_number',
            'transactions.pay_term_type',
            'final_total',
            DB::raw('SUM(tp.amount) as total_paid'),
            DB::raw("DATE_ADD(transaction_date, INTERVAL IF(transactions.pay_term_type = 'days', transactions.pay_term_number, 30 * transactions.pay_term_number) DAY) as due_date"),
            DB::raw("DATEDIFF(DATE_ADD(transaction_date, INTERVAL IF(transactions.pay_term_type = 'days', transactions.pay_term_number, 30 * transactions.pay_term_number) DAY), '{$today}') as days_remaining")
        )
            ->groupBy('transactions.id')
            ->get();

        if ($dues->isEmpty()) return;

        $msg  = "⏳ <b>PURCHASE PAYMENT DUE ALERT</b>\n\n";
        $msg .= "<b>📋 Upcoming & Overdue Purchase Payments (within 7 days):</b>\n";

        $grandTotal     = 0;
        $grandTotalPaid = 0;

        foreach ($dues as $due) {
            $supplierName = filled($due->supplier)
                ? $due->supplier
                : ($due->supplier_business_name ?? 'N/A');

            $refNo         = $due->ref_no ?? 'N/A';
            $finalTotal    = (float)($due->final_total ?? 0);
            $totalPaid     = (float)($due->total_paid ?? 0);
            $remaining     = $finalTotal - $totalPaid;
            $dueDate       = $due->due_date
                ? \Carbon\Carbon::parse($due->due_date)->format('d/m/Y')
                : 'N/A';
            $daysRemaining = (int)($due->days_remaining ?? 0);

            // $grandTotal     += $finalTotal;
            // $grandTotalPaid += $totalPaid;

            if ($daysRemaining < 0) {
                $daysLabel = "🔴 Overdue by " . abs($daysRemaining) . " day(s)";
            } elseif ($daysRemaining === 0) {
                $daysLabel = "🔴 Due Today";
            } elseif ($daysRemaining <= 3) {
                $daysLabel = "🟠 Due in {$daysRemaining} day(s)";
            } else {
                $daysLabel = "🟡 Due in {$daysRemaining} day(s)";
            }

            $msg .= "\n<b>•Supplier: {$supplierName}</b>\n";
            $msg .= "  Ref No:   #{$refNo}\n";
            $msg .= "  Due Date: {$dueDate}\n";
            $msg .= "  {$daysLabel}\n";
            $msg .= "  Total:    \$" . number_format($finalTotal, 2) . "\n";
            $msg .= "  Paid:     \$" . number_format($totalPaid, 2) . "\n";
            $msg .= "  🔴 Due:   \$" . number_format($remaining, 2) . "\n";
        }

        // ── Summary ────────────────────────────────────────────
        // $grandRemaining = $grandTotal - $grandTotalPaid;
        // $msg .= "\n━━━━━━━━━━━━━━━━━━━━\n";
        // $msg .= "<b>📊 Summary</b>\n";
        // $msg .= "<b>Total Purchases:</b> {$dues->count()}\n";
        // $msg .= "<b>💵 Grand Total:</b>  \$" . number_format($grandTotal, 2) . "\n";
        // $msg .= "<b>✅ Total Paid:</b>   \$" . number_format($grandTotalPaid, 2) . "\n";
        // $msg .= "<b>🔴 Total Due:</b>    \$" . number_format($grandRemaining, 2) . "\n";
        // $msg .= "━━━━━━━━━━━━━━━━━━━━\n\n";

        // ── Accounts ───────────────────────────────────────────
        if (!empty($all_account)) {
            $msg .= "\n<b>🏦 Account Balances:</b>\n";
            foreach ($all_account as $account) {
                $msg .= "  • <b>{$account['name']}:</b> {$account['balance']}\n";
            }
            $msg .= "\n";
        }

        $msg .= "⏰ <b>Date:</b> " . now()->format('d/m/Y H:i') . "\n";
        $msg .= "⏳ <i>Alert via Shoper POS</i>";

        self::sendMessage($msg, $to, $location_id);
    }
    public static function saleOrderMessage(
        $transaction,
        $payment_types,
        string $to = 'sale',
        string $location_id = 'PT1001'
    ): void {
        if (empty($transaction)) return;

        $all_account = self::fetchAccounts($location_id);

        $customerName  = filled($transaction->name)
            ? $transaction->name
            : ($transaction->supplier_business_name ?? 'N/A');
        $mobile        = $transaction->mobile ?? null;
        $invoiceNo     = $transaction->invoice_no ?? 'N/A';
        $date          = \Carbon\Carbon::parse($transaction->transaction_date)->format('d/m/Y H:i');
        $status        = ucfirst($transaction->status ?? 'N/A');
        $payStatus     = ucfirst($transaction->payment_status ?? 'N/A');
        $finalTotal    = number_format($transaction->final_total, 2);
        $totalBefore   = number_format($transaction->total_before_tax, 2);
        $taxAmount     = number_format($transaction->tax_amount, 2);
        $discount      = number_format($transaction->discount_amount, 2);
        $discountType  = $transaction->discount_type ?? null;
        $totalPaid     = number_format($transaction->total_paid ?? 0, 2);
        $remaining     = number_format(($transaction->final_total ?? 0) - ($transaction->total_paid ?? 0), 2);
        $location      = $transaction->business_location ?? 'N/A';
        $addedBy       = trim($transaction->added_by ?? '') ?: 'N/A';
        $notes         = $transaction->additional_notes ?? null;
        $staffNote     = $transaction->staff_note ?? null;
        $totalItems    = $transaction->total_items ?? 0;
        $soQtyRemaining = number_format($transaction->so_qty_remaining ?? 0, 2);

        // Pay term
        $payTerm = null;
        if ($transaction->pay_term_number && $transaction->pay_term_type) {
            $payTerm = $transaction->pay_term_number . ' ' . ucfirst($transaction->pay_term_type);
        }

        // Shipping
        $shippingStatus  = $transaction->shipping_status
            ? ucfirst(str_replace('_', ' ', $transaction->shipping_status))
            : null;

        // ── Header ─────────────────────────────────────────────
        $msg  = "📦 <b>SALE ORDER</b>\n\n";

        // ── Location ───────────────────────────────────────────
        $msg .= "<b>📍 Location:</b> {$location}\n\n";

        // ── Customer ───────────────────────────────────────────
        $msg .= "<b>👤 Customer:</b> {$customerName}\n";
        if ($mobile) $msg .= "<b>📱 Mobile:</b> {$mobile}\n";
        $msg .= "\n";

        // ── Order Info ─────────────────────────────────────────
        $msg .= "<b>🔖 Invoice No:</b> #{$invoiceNo}\n";
        $msg .= "<b>🕒 Date:</b> {$date}\n";
        $msg .= "<b>📌 Status:</b> {$status}\n";
        $msg .= "<b>💳 Payment Status:</b> {$payStatus}\n";
        if ($payTerm) $msg .= "<b>📅 Pay Term:</b> {$payTerm}\n";
        $msg .= "<b>🛍️ Total Items:</b> {$totalItems}\n";
        $msg .= "<b>📦 Qty Remaining:</b> {$soQtyRemaining}\n";
        $msg .= "\n";

        // ── Totals ─────────────────────────────────────────────
        $msg .= "<b>📊 Net Total:</b> \${$totalBefore}\n";

        if ((float)$transaction->discount_amount > 0) {
            $discountLabel = $discountType === 'percentage' ? " ({$discount}%)" : '';
            $msg .= "<b>🔻 Discount{$discountLabel}:</b> \${$discount}\n";
        }

        if ((float)$transaction->tax_amount > 0) {
            $msg .= "<b>🧾 Tax:</b> (+) \${$taxAmount}\n";
        }

        $msg .= "<b>💵 Grand Total:</b> \${$finalTotal}\n";
        $msg .= "<b>✅ Total Paid:</b> \${$totalPaid}\n";

        if ((float)$remaining > 0) {
            $msg .= "<b>⏳ Remaining:</b> \${$remaining}\n";
        }
        $msg .= "\n";

        // ── Shipping ───────────────────────────────────────────
        if ($shippingStatus) {
            $msg .= "<b>🚚 Shipping Status:</b> {$shippingStatus}\n";
            if ($transaction->shipping_details) {
                $msg .= "<b>📋 Shipping Details:</b> {$transaction->shipping_details}\n";
            }
            $msg .= "\n";
        }

        // ── Payments ───────────────────────────────────────────
        if (!empty($transaction->payment_lines) && $transaction->payment_lines->isNotEmpty()) {
            $msg .= "<b>💳 Payments:</b>\n";

            foreach ($transaction->payment_lines as $pay) {
                $method  = $payment_types[$pay->method] ?? ucfirst($pay->method);
                $amount  = number_format($pay->amount, 2);
                $paidOn  = \Carbon\Carbon::parse($pay->paid_on)->format('d/m/Y H:i');
                $payRef  = $pay->payment_ref_no ?? 'N/A';
                $payNote = $pay->note ?? null;

                $msg .= "\n• <b>{$method}</b>\n";
                $msg .= "  Ref: {$payRef}\n";
                $msg .= "  Amount: \${$amount}\n";
                $msg .= "  Paid On: {$paidOn}\n";
                if ($payNote) $msg .= "  Note: {$payNote}\n";
            }
            $msg .= "\n";
        }

        // ── Staff & Notes ──────────────────────────────────────
        $msg .= "<b>👨‍💼 Added By:</b> {$addedBy}\n";

        if (!empty($notes)) {
            $msg .= "<b>📝 Notes:</b> {$notes}\n";
        }

        if (!empty($staffNote)) {
            $msg .= "<b>🗒️ Staff Note:</b> {$staffNote}\n";
        }

        $msg .= "\n";

        // ── Accounts ───────────────────────────────────────────
        if (!empty($all_account)) {
            $msg .= "<b>🏦 Account Balances:</b>\n";
            foreach ($all_account as $account) {
                $msg .= "  • <b>{$account['name']}:</b> {$account['balance']}\n";
            }
            $msg .= "\n";
        }

        $msg .= "⏰ <b>Date Added:</b> " . now()->format('d/m/Y H:i') . "\n";
        $msg .= "📦 <i>Saved via Shoper POS</i>";

        self::sendMessage($msg, $to, $location_id);
    }
    public static function pendingShipmentsAlertMessage(
        string $to = 'sale',
        string $location_id = 'PT1001',
        $transaction_id = null
    ): void {
        $all_account = self::fetchAccounts($location_id);
        $business_id = auth()->user()->business_id;

        $sells = Transaction::leftJoin('contacts', 'transactions.contact_id', '=', 'contacts.id')
            ->leftJoin('transaction_sell_lines as tsl', function ($join) {
                $join->on('transactions.id', '=', 'tsl.transaction_id')
                    ->whereNull('tsl.parent_sell_line_id');
            })
            ->leftJoin('users as u', 'transactions.created_by', '=', 'u.id')
            ->leftJoin('users as dp', 'transactions.delivery_person', '=', 'dp.id')
            ->join('business_locations as bl', 'transactions.location_id', '=', 'bl.id')
            ->leftJoin('transactions as SR', 'transactions.id', '=', 'SR.return_parent_id')
            ->where("transactions.id", $transaction_id)
            ->where('transactions.business_id', $business_id)
            ->where('transactions.type', 'sell')
            ->where('transactions.status', 'final')
            ->whereNotNull('transactions.shipping_status')
            ->where('transactions.shipping_status', '!=', 'delivered')
            ->select(
                'transactions.id',
                'transactions.transaction_date',
                'transactions.invoice_no',
                'contacts.name',
                'contacts.mobile',
                'contacts.supplier_business_name',
                'transactions.status',
                'transactions.payment_status',
                'transactions.final_total',
                'transactions.shipping_status',
                'transactions.shipping_details',
                'transactions.shipping_custom_field_1',
                'transactions.shipping_custom_field_2',
                'transactions.shipping_custom_field_3',
                'transactions.shipping_custom_field_4',
                'transactions.shipping_custom_field_5',
                'transactions.additional_notes',
                'bl.name as business_location',
                DB::raw("CONCAT(COALESCE(u.surname, ''),' ',COALESCE(u.first_name, ''),' ',COALESCE(u.last_name,'')) as added_by"),
                DB::raw("CONCAT(COALESCE(dp.surname, ''),' ',COALESCE(dp.first_name, ''),' ',COALESCE(dp.last_name,'')) as delivery_person"),
                DB::raw('(SELECT SUM(IF(TP.is_return = 1,-1*TP.amount,TP.amount)) FROM transaction_payments AS TP WHERE TP.transaction_id=transactions.id) as total_paid'),
                DB::raw('COUNT(DISTINCT tsl.id) as total_items'),
                DB::raw('DATE_FORMAT(transactions.transaction_date, "%Y/%m/%d") as sale_date')
            )
            ->groupBy('transactions.id');

        // $permitted_locations = auth()->user()->permitted_locations();
        // if ($permitted_locations != 'all') {
        //     $sells->whereIn('transactions.location_id', $permitted_locations);
        // }

        // if (!empty(request()->input('location_id'))) {
        //     $sells->where('transactions.location_id', request()->input('location_id'));
        // }

        $shipments = $sells->get();

        if ($shipments->isEmpty()) return;

        // Group by shipping status
        $grouped = $shipments->groupBy('shipping_status');

        $msg  = "🚚 <b>PENDING SHIPMENTS ALERT</b>\n\n";
        $msg .= "<b>📦 Total Pending:</b> {$shipments->count()}\n\n";

        foreach ($grouped as $shippingStatus => $items) {
            $statusLabel = ucfirst(str_replace('_', ' ', $shippingStatus));
            $statusIcon  = match ($shippingStatus) {
                'ordered'         => '🟡',
                'packed'          => '📦',
                'ready_to_ship'   => '🔵',
                'out_for_delivery' => '🛵',
                'not_delivered'   => '🔴',
                default           => '🟠',
            };

            $msg .= "━━━━━━━━━━━━━━━━━━━━\n";
            $msg .= "{$statusIcon} <b>{$statusLabel}</b> ({$items->count()})\n";
            $msg .= "━━━━━━━━━━━━━━━━━━━━\n";

            foreach ($items as $item) {
                $customerName   = filled($item->name)
                    ? $item->name
                    : ($item->supplier_business_name ?? 'N/A');
                $mobile         = $item->mobile ?? null;
                $invoiceNo      = $item->invoice_no ?? 'N/A';
                $date           = \Carbon\Carbon::parse($item->transaction_date)->format('d/m/Y H:i');
                $finalTotal     = number_format($item->final_total, 2);
                $totalPaid      = number_format($item->total_paid ?? 0, 2);
                $remaining      = number_format(($item->final_total ?? 0) - ($item->total_paid ?? 0), 2);
                $payStatus      = ucfirst($item->payment_status ?? 'N/A');
                $location       = $item->business_location ?? 'N/A';
                $totalItems     = $item->total_items ?? 0;
                $deliveryPerson = trim($item->delivery_person ?? '') ?: null;
                $addedBy        = trim($item->added_by ?? '') ?: 'N/A';
                $notes          = $item->additional_notes ?? null;
                $shippingDetail = $item->shipping_details ?? null;

                // Shipping custom fields
                $shippingCustomFields = array_filter([
                    $item->shipping_custom_field_1 ?? null,
                    $item->shipping_custom_field_2 ?? null,
                    $item->shipping_custom_field_3 ?? null,
                    $item->shipping_custom_field_4 ?? null,
                    $item->shipping_custom_field_5 ?? null,
                ]);

                $msg .= "\n<b>• {$customerName}</b>\n";
                if ($mobile)         $msg .= "  📱 Mobile:    {$mobile}\n";
                $msg .= "  📍 Location:  {$location}\n";
                $msg .= "  🔖 Invoice:   #{$invoiceNo}\n";
                $msg .= "  🕒 Date:      {$date}\n";
                $msg .= "  🛍️ Items:     {$totalItems}\n";
                $msg .= "  💳 Pay Status: {$payStatus}\n";
                $msg .= "  💵 Total:     \${$finalTotal}\n";
                $msg .= "  ✅ Paid:      \${$totalPaid}\n";

                if ((float)$remaining > 0) {
                    $msg .= "  🔴 Due:       \${$remaining}\n";
                }

                if ($deliveryPerson) $msg .= "  🚴 Delivery:  {$deliveryPerson}\n";
                if ($shippingDetail) $msg .= "  📋 Shipping:  {$shippingDetail}\n";

                if (!empty($shippingCustomFields)) {
                    foreach ($shippingCustomFields as $field) {
                        $msg .= "  ➕ {$field}\n";
                    }
                }

                if ($notes) $msg .= "  📝 Notes:     {$notes}\n";
                $msg .= "  👨‍💼 Added By: {$addedBy}\n";
            }

            $msg .= "\n";
        }

        // ── Summary ────────────────────────────────────────────
        $grandTotal     = $shipments->sum('final_total');
        $grandTotalPaid = $shipments->sum('total_paid');
        $grandRemaining = $grandTotal - $grandTotalPaid;

        $msg .= "━━━━━━━━━━━━━━━━━━━━\n";
        $msg .= "<b>📊 Summary</b>\n";
        $msg .= "<b>Total Shipments:</b> {$shipments->count()}\n";
        $msg .= "<b>💵 Grand Total:</b>  \$" . number_format($grandTotal, 2) . "\n";
        $msg .= "<b>✅ Total Paid:</b>   \$" . number_format($grandTotalPaid, 2) . "\n";
        $msg .= "<b>🔴 Total Due:</b>    \$" . number_format($grandRemaining, 2) . "\n";
        $msg .= "━━━━━━━━━━━━━━━━━━━━\n\n";

        // ── Accounts ───────────────────────────────────────────
        if (!empty($all_account)) {
            $msg .= "<b>🏦 Account Balances:</b>\n";
            foreach ($all_account as $account) {
                $msg .= "  • <b>{$account['name']}:</b> {$account['balance']}\n";
            }
            $msg .= "\n";
        }

        $msg .= "⏰ <b>Date:</b> " . now()->format('d/m/Y H:i') . "\n";
        $msg .= "🚚 <i>Alert via Shoper POS</i>";

        self::sendMessage($msg, $to, $location_id);
    }
    public static function addJobSheetMessage(
        $job_sheet,
        $contact,
        $location,
        $brand,
        $device,
        $deviceModel,
        $status,
        $serviceStaff,
        string $to = 'repair',
        string $location_id = 'PT1001'
    ): void {
        if (empty($job_sheet)) return;

        $all_account = self::fetchAccounts($location_id);

        // ── Resolve fields safely ──────────────────────────────
        $customerName    = filled($contact->name ?? '') ? $contact->name : ($contact->supplier_business_name ?? 'N/A');
        $customerMobile  = $contact->mobile ?? null;
        $locationName    = $location->name ?? 'N/A';
        $brandName       = $brand->name ?? 'N/A';
        $deviceName      = $device->name ?? 'N/A';
        $modelName       = $deviceModel->name ?? 'N/A';
        $statusName      = ($status && ($job_sheet->status_id ?? 0) != 0) ? ($status->name ?? 'N/A') : 'N/A';
        $staffName       = $serviceStaff
            ? trim(
                ($serviceStaff->surname    ?? '') . ' ' .
                    ($serviceStaff->first_name ?? '') . ' ' .
                    ($serviceStaff->last_name  ?? '')
            )
            : null;

        $jobSheetNo      = $job_sheet->job_sheet_no ?? 'N/A';
        $date            = \Carbon\Carbon::parse($job_sheet->created_at)->format('d/m/Y H:i');
        $serviceType     = ucfirst(str_replace('_', ' ', $job_sheet->service_type ?? 'N/A'));
        $serialNo        = $job_sheet->serial_no ?? 'N/A';
        $securityPwd     = $job_sheet->security_pwd ?? null;
        $securityPattern = $job_sheet->security_pattern ?? null;
        $estimatedCost   = $job_sheet->estimated_cost
            ? number_format($job_sheet->estimated_cost, 2)
            : '0.00';
        $deliveryDate    = $job_sheet->delivery_date
            ? \Carbon\Carbon::parse($job_sheet->delivery_date)->format('d/m/Y H:i')
            : 'N/A';
        $productConfig   = self::decodeRepairField($job_sheet->product_configuration);
        $defects         = self::decodeRepairField($job_sheet->defects);
        $condition       = self::decodeRepairField($job_sheet->product_condition);
        $commentBySS     = $job_sheet->comment_by_ss ?? null;
        $pickUpAddr      = $job_sheet->pick_up_on_site_addr ?? null;

        $customFields = array_filter([
            '1' => $job_sheet->custom_field_1 ?? null,
            '2' => $job_sheet->custom_field_2 ?? null,
            '3' => $job_sheet->custom_field_3 ?? null,
            '4' => $job_sheet->custom_field_4 ?? null,
            '5' => $job_sheet->custom_field_5 ?? null,
        ]);

        // ── Header ─────────────────────────────────────────────
        $msg  = "🔧 <b>NEW JOB SHEET CREATED</b>\n\n";

        // ── Business / Location ────────────────────────────────
        $msg .= "<b>📍 Location:</b> {$locationName}\n\n";

        // ── Customer ───────────────────────────────────────────
        $msg .= "<b>👤 Customer:</b> {$customerName}\n";
        if ($customerMobile) $msg .= "<b>📱 Mobile:</b> {$customerMobile}\n";
        $msg .= "\n";

        // ── Job Sheet Info ─────────────────────────────────────
        $msg .= "<b>🔖 Job Sheet No:</b> #{$jobSheetNo}\n";
        $msg .= "<b>🕒 Date:</b> {$date}\n";
        $msg .= "<b>🛠️ Service Type:</b> {$serviceType}\n";
        $msg .= "<b>📌 Status:</b> {$statusName}\n";
        $msg .= "<b>📅 Due Date:</b> {$deliveryDate}\n";
        $msg .= "<b>💵 Estimated Cost:</b> \${$estimatedCost}\n";
        $msg .= "\n";

        // ── Device Info ────────────────────────────────────────
        $msg .= "<b>📱 Brand:</b> {$brandName}\n";
        $msg .= "<b>📟 Device:</b> {$deviceName}\n";
        $msg .= "<b>🔩 Device Model:</b> {$modelName}\n";
        $msg .= "<b>🔢 Serial Number:</b> {$serialNo}\n";
        if ($securityPwd)     $msg .= "<b>🔑 Password:</b> {$securityPwd}\n";
        if ($securityPattern) $msg .= "<b>🔐 Security Pattern Code:</b> {$securityPattern}\n";
        $msg .= "\n";

        // ── Technician ─────────────────────────────────────────
        $msg .= "<b>👨‍🔧 Technician:</b> " . ($staffName ?: 'N/A') . "\n";
        if ($commentBySS) $msg .= "<b>💬 Comment by Technician:</b> {$commentBySS}\n";
        $msg .= "\n";

        // ── Repair Details ─────────────────────────────────────
        $msg .= "<b>📍 Pick up/On site address:</b> "        . ($pickUpAddr    ?: 'N/A') . "\n";
        $msg .= "<b>⚙️ Product Configuration:</b> "          . ($productConfig ?: 'N/A') . "\n";
        $msg .= "<b>📋 Condition Of The Product:</b> "        . ($condition     ?: 'N/A') . "\n";
        $msg .= "<b>🐛 Problem Reported By Customer:</b> "    . ($defects       ?: 'N/A') . "\n";
        $msg .= "\n";

        // ── Pre Repair Checklist ───────────────────────────────
        if (!empty($job_sheet->checklist)) {
            $checklist = is_array($job_sheet->checklist)
                ? $job_sheet->checklist
                : json_decode($job_sheet->checklist, true);

            if (!empty($checklist)) {
                $msg .= "<b>✅ Pre Repair Checklist:</b>\n";
                foreach ($checklist as $item => $value) {
                    $icon = $value == 1 ? '✅' : ($value == 0 ? '❌' : '➖');
                    $msg .= "  {$icon} {$item}\n";
                }
                $msg .= "\n";
            } else {
                $msg .= "<b>✅ Pre Repair Checklist:</b> N/A\n\n";
            }
        } else {
            $msg .= "<b>✅ Pre Repair Checklist:</b> N/A\n\n";
        }

        // ── Parts Used ─────────────────────────────────────────
        try {
            $parts = $job_sheet->getPartsUsed();


            if (!empty($parts)) {
                $msg .= "<b>🔩 Parts Used:</b>\n";
                foreach ($parts as $part) {
                    $partName   = $part['variation_name'] ?? 'N/A';
                    $partQty    = $part['quantity']        ?? '0';
                    $partUnit   = $part['unit']            ?? 'Pc(s)';
                    $partStatus = isset($part['status'])
                        ? ' | ' . ucfirst($part['status'])
                        : '';
                    $msg .= "  • <b>{$partName}</b> | Qty: {$partQty} {$partUnit}{$partStatus}\n";
                }
                $msg .= "\n";
            } else {
                $msg .= "<b>🔩 Parts Used:</b> N/A\n\n";
            }
        } catch (\Exception $e) {
            $msg .= "<b>🔩 Parts Used:</b> N/A\n\n";
        }

        // ── Custom Fields ──────────────────────────────────────
        if (!empty($customFields)) {
            $msg .= "<b>📝 Custom Fields:</b>\n";
            foreach ($customFields as $i => $value) {
                $msg .= "  • Field {$i}: {$value}\n";
            }
            $msg .= "\n";
        }

        // ── Accounts ───────────────────────────────────────────
        if (!empty($all_account)) {
            $msg .= "<b>🏦 Account Balances:</b>\n";
            foreach ($all_account as $account) {
                $msg .= "  • <b>{$account['name']}:</b> {$account['balance']}\n";
            }
            $msg .= "\n";
        }

        $msg .= "⏰ <b>Date Added:</b> " . now()->format('d/m/Y H:i') . "\n";
        $msg .= "🔧 <i>Saved via Shoper POS</i>";

        self::sendMessage($msg, $to, $location_id);
    }

    public static function updateStatusMessage(
        $job_sheet,
        $contact,
        $location,
        $brand,
        $device,
        $deviceModel,
        $status,
        $serviceStaff,
        $old_status,
        string $to = 'repair',
        string $location_id = 'PT1001'
    ): void {
        if (empty($job_sheet)) return;

        $all_account = self::fetchAccounts($location_id);

        // ── Resolve fields safely ──────────────────────────────
        $customerName    = filled($contact->name ?? '') ? $contact->name : ($contact->supplier_business_name ?? 'N/A');
        $customerMobile  = $contact->mobile ?? null;
        $locationName    = $location->name ?? 'N/A';
        $brandName       = $brand->name ?? 'N/A';
        $deviceName      = $device->name ?? 'N/A';
        $modelName       = $deviceModel->name ?? 'N/A';
        $statusName      = ($status && ($job_sheet->status_id ?? 0) != 0) ? self::diff($old_status->name, $status->name) : 'N/A';
        $staffName       = $serviceStaff
            ? trim(
                ($serviceStaff->surname    ?? '') . ' ' .
                    ($serviceStaff->first_name ?? '') . ' ' .
                    ($serviceStaff->last_name  ?? '')
            )
            : null;

        $jobSheetNo      = $job_sheet->job_sheet_no ?? 'N/A';
        $date            = \Carbon\Carbon::parse($job_sheet->created_at)->format('d/m/Y H:i');
        $serviceType     = ucfirst(str_replace('_', ' ', $job_sheet->service_type ?? 'N/A'));
        $serialNo        = $job_sheet->serial_no ?? 'N/A';
        $securityPwd     = $job_sheet->security_pwd ?? null;
        $securityPattern = $job_sheet->security_pattern ?? null;
        $estimatedCost   = $job_sheet->estimated_cost
            ? number_format($job_sheet->estimated_cost, 2)
            : '0.00';
        $deliveryDate    = $job_sheet->delivery_date
            ? \Carbon\Carbon::parse($job_sheet->delivery_date)->format('d/m/Y H:i')
            : 'N/A';
        $productConfig   = self::decodeRepairField($job_sheet->product_configuration);
        $defects         = self::decodeRepairField($job_sheet->defects);
        $condition       = self::decodeRepairField($job_sheet->product_condition);
        $commentBySS     = $job_sheet->comment_by_ss ?? null;
        $pickUpAddr      = $job_sheet->pick_up_on_site_addr ?? null;

        $customFields = array_filter([
            '1' => $job_sheet->custom_field_1 ?? null,
            '2' => $job_sheet->custom_field_2 ?? null,
            '3' => $job_sheet->custom_field_3 ?? null,
            '4' => $job_sheet->custom_field_4 ?? null,
            '5' => $job_sheet->custom_field_5 ?? null,
        ]);

        // ── Header ─────────────────────────────────────────────
        $msg  = "🔧 <b>UPDATE JOB SHEET STATUS</b>\n\n";

        // ── Business / Location ────────────────────────────────
        $msg .= "<b>📍 Location:</b> {$locationName}\n\n";

        // ── Customer ───────────────────────────────────────────
        $msg .= "<b>👤 Customer:</b> {$customerName}\n";
        if ($customerMobile) $msg .= "<b>📱 Mobile:</b> {$customerMobile}\n";
        $msg .= "\n";

        // ── Job Sheet Info ─────────────────────────────────────
        $msg .= "<b>🔖 Job Sheet No:</b> #{$jobSheetNo}\n";
        $msg .= "<b>🕒 Date:</b> {$date}\n";
        $msg .= "<b>🛠️ Service Type:</b> {$serviceType}\n";
        $msg .= "<b>📌 Status:</b> {$statusName}\n";
        $msg .= "<b>📅 Due Date:</b> {$deliveryDate}\n";
        $msg .= "<b>💵 Estimated Cost:</b> \${$estimatedCost}\n";
        $msg .= "\n";

        // ── Device Info ────────────────────────────────────────
        $msg .= "<b>📱 Brand:</b> {$brandName}\n";
        $msg .= "<b>📟 Device:</b> {$deviceName}\n";
        $msg .= "<b>🔩 Device Model:</b> {$modelName}\n";
        $msg .= "<b>🔢 Serial Number:</b> {$serialNo}\n";
        if ($securityPwd)     $msg .= "<b>🔑 Password:</b> {$securityPwd}\n";
        if ($securityPattern) $msg .= "<b>🔐 Security Pattern Code:</b> {$securityPattern}\n";
        $msg .= "\n";

        // ── Technician ─────────────────────────────────────────
        $msg .= "<b>👨‍🔧 Technician:</b> " . ($staffName ?: 'N/A') . "\n";
        if ($commentBySS) $msg .= "<b>💬 Comment by Technician:</b> {$commentBySS}\n";
        $msg .= "\n";

        // ── Repair Details ─────────────────────────────────────
        $msg .= "<b>📍 Pick up/On site address:</b> "        . ($pickUpAddr    ?: 'N/A') . "\n";
        $msg .= "<b>⚙️ Product Configuration:</b> "          . ($productConfig ?: 'N/A') . "\n";
        $msg .= "<b>📋 Condition Of The Product:</b> "        . ($condition     ?: 'N/A') . "\n";
        $msg .= "<b>🐛 Problem Reported By Customer:</b> "    . ($defects       ?: 'N/A') . "\n";
        $msg .= "\n";

        // ── Pre Repair Checklist ───────────────────────────────
        if (!empty($job_sheet->checklist)) {
            $checklist = is_array($job_sheet->checklist)
                ? $job_sheet->checklist
                : json_decode($job_sheet->checklist, true);

            if (!empty($checklist)) {
                $msg .= "<b>✅ Pre Repair Checklist:</b>\n";
                foreach ($checklist as $item => $value) {
                    $icon = $value == 1 ? '✅' : ($value == 0 ? '❌' : '➖');
                    $msg .= "  {$icon} {$item}\n";
                }
                $msg .= "\n";
            } else {
                $msg .= "<b>✅ Pre Repair Checklist:</b> N/A\n\n";
            }
        } else {
            $msg .= "<b>✅ Pre Repair Checklist:</b> N/A\n\n";
        }

        // ── Parts Used ─────────────────────────────────────────
        try {
            $parts = $job_sheet->getPartsUsed();
            if (!empty($parts)) {
                $msg .= "<b>🔩 Parts Used:</b>\n";
                foreach ($parts as $part) {
                    $partName   = $part['variation_name'] ?? 'N/A';
                    $partQty    = $part['quantity']        ?? '0';
                    $partUnit   = $part['unit']            ?? 'Pc(s)';
                    $partStatus = isset($part['status'])
                        ? ' | ' . ucfirst($part['status'])
                        : '';
                    $msg .= "  • <b>{$partName}</b> | Qty: {$partQty} {$partUnit}{$partStatus}\n";
                }
                $msg .= "\n";
            } else {
                $msg .= "<b>🔩 Parts Used:</b> N/A\n\n";
            }
        } catch (\Exception $e) {
            $msg .= "<b>🔩 Parts Used:</b> N/A\n\n";
        }

        // ── Custom Fields ──────────────────────────────────────
        if (!empty($customFields)) {
            $msg .= "<b>📝 Custom Fields:</b>\n";
            foreach ($customFields as $i => $value) {
                $msg .= "  • Field {$i}: {$value}\n";
            }
            $msg .= "\n";
        }

        // ── Accounts ───────────────────────────────────────────
        if (!empty($all_account)) {
            $msg .= "<b>🏦 Account Balances:</b>\n";
            foreach ($all_account as $account) {
                $msg .= "  • <b>{$account['name']}:</b> {$account['balance']}\n";
            }
            $msg .= "\n";
        }

        $msg .= "⏰ <b>Date Added:</b> " . now()->format('d/m/Y H:i') . "\n";
        $msg .= "🔧 <i>Saved via Shoper POS</i>";

        self::sendMessage($msg, $to, $location_id);
    }

    private static function decodeRepairField($value): ?string
    {
        if (empty($value)) return null;

        $decoded = json_decode($value, true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            $items = array_filter(array_map(fn($item) => trim($item['value'] ?? ''), $decoded));
            return !empty($items) ? implode(', ', $items) : null;
        }

        return $value;
    }
    public static function addPartsJobSheetMessage(
        $job_sheet,
        array $parts,
        array $old_parts = [],
        string $to = 'repair',
        string $location_id = 'PT1001'
    ): void {
        if (empty($job_sheet)) return;

        $all_account = self::fetchAccounts($location_id);

        // ── Resolve only what's needed ─────────────────────────
        $contact  = \App\Contact::find($job_sheet->contact_id);
        $location = \App\BusinessLocation::find($job_sheet->location_id);

        $customerName   = filled($contact->name ?? '') ? $contact->name : ($contact->supplier_business_name ?? 'N/A');
        $customerMobile = $contact->mobile ?? null;
        $locationName   = $location->name ?? 'N/A';
        $jobSheetNo     = $job_sheet->job_sheet_no ?? 'N/A';
        $date           = \Carbon\Carbon::parse($job_sheet->created_at)->format('d/m/Y H:i');

        // ── Build old/new parts map keyed by variation_name + status ────
        $oldPartsMap = [];
        foreach ($old_parts as $index => $part) {
            $name   = $part['variation_name'] ?? null;
            $status = $part['status']         ?? 'no_status';
            $key    = $name . '||' . $status;
            if ($name) $oldPartsMap[$key] = $part;
        }
        $newPartsMap = [];
        foreach ($parts as $index => $part) {
            $name   = $part['variation_name'] ?? null;
            $status = $part['status']         ?? 'no_status';
            $key    = $name . '||' . $status;
            if ($name) $newPartsMap[$key] = $part;
        }

        // ── Detect removed parts ───────────────────────────────
        $removedParts = array_diff_key($oldPartsMap, $newPartsMap);

        // ── Detect added parts ─────────────────────────────────
        $addedParts = array_diff_key($newPartsMap, $oldPartsMap);

        $changedParts = [];
        foreach ($newPartsMap as $key => $newPart) {
            if (!isset($oldPartsMap[$key])) continue;

            $oldPart = $oldPartsMap[$key];
            $oldQty  = $oldPart['quantity'] ?? '0';
            $newQty  = $newPart['quantity'] ?? '0';
            $oldUnit = $oldPart['unit']     ?? 'Pc(s)';
            $newUnit = $newPart['unit']     ?? 'Pc(s)';

            if ((string)$oldQty !== (string)$newQty || $oldUnit !== $newUnit) {
                $changedParts[$key] = ['old' => $oldPart, 'new' => $newPart];
            }
        }

        // ── Header ─────────────────────────────────────────────
        $msg  = "🔩 <b>JOB SHEET PARTS UPDATED</b>\n\n";

        // ── Info ───────────────────────────────────────────────
        $msg .= "<b>🔖 Job Sheet No:</b> #{$jobSheetNo}\n";
        $msg .= "<b>🕒 Date:</b> {$date}\n";
        $msg .= "<b>👤 Customer:</b> {$customerName}\n";
        if ($customerMobile) $msg .= "<b>📱 Mobile:</b> {$customerMobile}\n";
        $msg .= "<b>📍 Location:</b> {$locationName}\n\n";

        // ── Added Parts ────────────────────────────────────────
        if (!empty($addedParts)) {
            $msg .= "<b>🆕 Added Parts:</b>\n";
            foreach ($addedParts as $part) {
                $partName   = $part['variation_name'] ?? 'N/A';
                $partQty    = $part['quantity']        ?? '0';
                $partUnit   = $part['unit']            ?? 'Pc(s)';
                $partStatus = isset($part['status'])
                    ? ' | ' . ucfirst($part['status'])
                    : '';
                $msg .= "  • <b>{$partName}</b> | Qty: {$partQty} {$partUnit}{$partStatus}\n";
            }
            $msg .= "\n";
        }

        // ── Changed Parts ──────────────────────────────────────
        if (!empty($changedParts)) {
            $msg .= "<b>✏️ Changed Parts:</b>\n";
            foreach ($changedParts as $key => $data) {
                $oldPart  = $data['old'];
                $newPart  = $data['new'];
                $partName = $newPart['variation_name'] ?? 'N/A';
                $oldUnit  = $oldPart['unit']           ?? 'Pc(s)';
                $newUnit  = $newPart['unit']           ?? 'Pc(s)';
                $oldQty   = $oldPart['quantity']       ?? '0';
                $newQty   = $newPart['quantity']       ?? '0';
                $status   = isset($newPart['status'])
                    ? ' | ' . ucfirst($newPart['status'])
                    : '';

                $qtyDisplay = self::diff("{$oldQty} {$oldUnit}", "{$newQty} {$newUnit}");

                $msg .= "\n  • <b>{$partName}</b>{$status}\n";
                $msg .= "    Qty: {$qtyDisplay}\n";
            }
            $msg .= "\n";
        }

        // ── Removed Parts ──────────────────────────────────────
        if (!empty($removedParts)) {
            $msg .= "<b>🗑️ Removed Parts:</b>\n";
            foreach ($removedParts as $part) {
                $partName   = $part['variation_name'] ?? 'N/A';
                $partQty    = $part['quantity']        ?? '0';
                $partUnit   = $part['unit']            ?? 'Pc(s)';
                $partStatus = isset($part['status'])
                    ? ' | ' . ucfirst($part['status'])
                    : '';
                $msg .= "  • <s><b>{$partName}</b> | Qty: {$partQty} {$partUnit}{$partStatus}</s>\n";
            }
            $msg .= "\n";
        }

        // ── No changes detected ────────────────────────────────
        if (empty($addedParts) && empty($changedParts) && empty($removedParts)) {
            $msg .= "<b>ℹ️ No changes detected.</b>\n\n";
        }

        // ── Remaining Parts Summary ────────────────────────────
        if (!empty($parts)) {
            $msg .= "<b>📋 All Parts:</b>\n";
            $totalQty = 0;
            foreach ($parts as $part) {
                $partName   = $part['variation_name'] ?? 'N/A';
                $partQty    = $part['quantity']        ?? '0';
                $partUnit   = $part['unit']            ?? 'Pc(s)';
                $partStatus = isset($part['status'])
                    ? ' | ' . ucfirst($part['status'])
                    : '';
                $totalQty += (float) $partQty;
                $msg .= "  • <b>{$partName}</b> | Qty: {$partQty} {$partUnit}{$partStatus}\n";
            }
            $msg .= "\n<b>📦 Total Parts:</b> " . count($parts) . " item(s) | Total Qty: " . number_format($totalQty, 2) . "\n\n";
        } else {
            $msg .= "<b>🔩 Parts Used:</b> N/A\n\n";
        }

        // ── Accounts ───────────────────────────────────────────
        if (!empty($all_account)) {
            $msg .= "<b>🏦 Account Balances:</b>\n";
            foreach ($all_account as $account) {
                $msg .= "  • <b>{$account['name']}:</b> {$account['balance']}\n";
            }
            $msg .= "\n";
        }

        $msg .= "⏰ <b>Date Updated:</b> " . now()->format('d/m/Y H:i') . "\n";
        $msg .= "🔩 <i>Updated via Shoper POS</i>";

        self::sendMessage($msg, $to, $location_id);
    }
    public static function updatePartsStatusJobSheetMessage(
        $job_sheet,
        array $old_parts,
        array $new_parts,
        string $to = 'repair',
        string $location_id = 'PT1001',
        string $label = '🔄 <b>JOB SHEET PARTS STATUS UPDATED</b>'
    ): void {
        if (empty($job_sheet)) return;

        $all_account = self::fetchAccounts($location_id);

        // ── Resolve only what's needed ─────────────────────────
        $contact  = \App\Contact::find($job_sheet->contact_id);
        $location = \App\BusinessLocation::find($job_sheet->location_id);

        $customerName   = filled($contact->name ?? '') ? $contact->name : ($contact->supplier_business_name ?? 'N/A');
        $customerMobile = $contact->mobile ?? null;
        $locationName   = $location->name ?? 'N/A';
        $jobSheetNo     = $job_sheet->job_sheet_no ?? 'N/A';
        $date           = \Carbon\Carbon::parse($job_sheet->created_at)->format('d/m/Y H:i');

        // ── Parts are keyed by part_key (row_16_0, row_16_1, …) ──
        // Old and new are already associative arrays keyed by part_key.

        // ── Detect removed rows (part_key in old but not in new) ──
        $removed_parts = [];
        foreach ($old_parts as $part_key => $old_part) {
            if (!isset($new_parts[$part_key])) {
                $removed_parts[$part_key] = $old_part;
            }
        }

        // ── Detect changed rows (part_key exists in both, but something differs) ──
        $changed_parts = [];
        foreach ($new_parts as $part_key => $new_part) {
            $old_part = $old_parts[$part_key] ?? null;

            $old_status = $old_part['status']   ?? null;
            $new_status = $new_part['status']   ?? null;
            $old_note   = $old_part['note']     ?? null;
            $new_note   = $new_part['note']     ?? null;
            $old_qty    = $old_part['quantity'] ?? null;
            $new_qty    = $new_part['quantity'] ?? null;

            if ($old_status != $new_status || $old_note != $new_note || (string)$old_qty !== (string)$new_qty) {
                $changed_parts[$part_key] = [
                    'new' => $new_part,
                    'old' => $old_part,
                ];
            }
        }

        // ── Header ─────────────────────────────────────────────
        $msg  = "🔄 <b>JOB SHEET PARTS STATUS UPDATED</b>\n\n";

        // ── Info ───────────────────────────────────────────────
        $msg .= "<b>🔖 Job Sheet No:</b> #{$jobSheetNo}\n";
        $msg .= "<b>🕒 Date:</b> {$date}\n";
        $msg .= "<b>👤 Customer:</b> {$customerName}\n";
        if ($customerMobile) $msg .= "<b>📱 Mobile:</b> {$customerMobile}\n";
        $msg .= "<b>📍 Location:</b> {$locationName}\n\n";

        // ── Removed Rows (shown with strikethrough) ────────────
        if (!empty($removed_parts)) {
            $msg .= "<b>🗑️ Removed Rows (merged/deleted):</b>\n";
            foreach ($removed_parts as $part_key => $part) {
                $pName   = $part['variation_name'] ?? 'N/A';
                $pQty    = $part['quantity']        ?? '0';
                $pUnit   = $part['unit']            ?? 'Pc(s)';
                $pStatus = ucfirst($part['status']  ?? 'N/A');
                $pNote   = $part['note']            ?? null;

                // Entire row shown as struck-through to indicate it was removed/merged
                $msg .= "\n  <s>❌ {$pName}</s>\n";
                $msg .= "  <s>  Qty: {$pQty} {$pUnit}</s>\n";
                $msg .= "  <s>  Status: {$pStatus}</s>\n";
                if ($pNote) $msg .= "  <s>  Note: {$pNote}</s>\n";
            }
            $msg .= "\n";
        }

        // ── Changed Rows ───────────────────────────────────────
        if (!empty($changed_parts)) {
            $msg .= "<b>🔩 Changed Parts:</b>\n";
            foreach ($changed_parts as $part_key => $data) {
                $new_part = $data['new'];
                $old_part = $data['old'];

                $partName  = $new_part['variation_name'] ?? 'N/A';
                $partUnit  = $new_part['unit']            ?? 'Pc(s)';
                $newQty    = $new_part['quantity']        ?? '0';
                $oldQty    = $old_part['quantity']        ?? '0';
                $newStatus = ucfirst($new_part['status']  ?? 'N/A');
                $oldStatus = ucfirst($old_part['status']  ?? 'N/A');
                $newNote   = $new_part['note']            ?? null;
                $oldNote   = $old_part['note']            ?? null;

                $msg .= "\n  • <b>{$partName}</b>\n";
                $msg .= "    Qty: "    . self::diff($oldQty, $newQty, " {$partUnit}") . "\n";
                $msg .= "    Status: " . self::diff($oldStatus, $newStatus) . "\n";

                if ($newNote !== $oldNote) {
                    $msg .= "    Note: " . self::diff($oldNote ?? '--', $newNote ?? '--') . "\n";
                } elseif ($newNote) {
                    $msg .= "    Note: {$newNote}\n";
                }
            }
            $msg .= "\n";
        }

        // ── No changes detected ────────────────────────────────
        if (empty($removed_parts) && empty($changed_parts)) {
            $msg .= "<b>ℹ️ No changes detected.</b>\n\n";
        }

        // ── Remaining Parts Summary ────────────────────────────
        if (!empty($new_parts)) {
            $msg .= "<b>📋 Remaining Parts:</b>\n";
            $totalQty = 0;
            foreach ($new_parts as $part) {
                $pName   = $part['variation_name'] ?? 'N/A';
                $pQty    = $part['quantity']        ?? '0';
                $pUnit   = $part['unit']            ?? 'Pc(s)';
                $pStatus = ucfirst($part['status']  ?? 'N/A');
                $totalQty += (float) $pQty;

                $msg .= "  • <b>{$pName}</b> | Qty: {$pQty} {$pUnit} | {$pStatus}\n";
            }
            $msg .= "\n<b>📦 Total Remaining:</b> " . count($new_parts) . " item(s) | Qty: " . number_format($totalQty, 2) . "\n\n";
        } else {
            $msg .= "<b>📋 Remaining Parts:</b> None\n\n";
        }

        // ── Account Balances ───────────────────────────────────
        if (!empty($all_account)) {
            $msg .= "<b>🏦 Account Balances:</b>\n";
            foreach ($all_account as $account) {
                $msg .= "  • <b>{$account['name']}:</b> {$account['balance']}\n";
            }
            $msg .= "\n";
        }

        $msg .= "⏰ <b>Date Updated:</b> " . now()->format('d/m/Y H:i') . "\n";
        $msg .= "🔄 <i>Updated via Shoper POS</i>";

        self::sendMessage($msg, $to, $location_id);
    }
    public static function deletePartJobSheetMessage(
        $job_sheet,
        array $deleted_part,  // ← changed from string to array
        string $to = 'repair',
        string $location_id = 'PT1001'
    ): void {
        if (empty($job_sheet)) return;

        $all_account = self::fetchAccounts($location_id);

        // ── Resolve info ───────────────────────────────────────
        $contact  = \App\Contact::find($job_sheet->contact_id);
        $location = \App\BusinessLocation::find($job_sheet->location_id);

        $customerName   = filled($contact->name ?? '') ? $contact->name : ($contact->supplier_business_name ?? 'N/A');
        $customerMobile = $contact->mobile ?? null;
        $locationName   = $location->name ?? 'N/A';
        $jobSheetNo     = $job_sheet->job_sheet_no ?? 'N/A';
        $date           = \Carbon\Carbon::parse($job_sheet->created_at)->format('d/m/Y H:i');

        // ── Deleted part details ───────────────────────────────
        $deletedName   = $deleted_part['variation_name'] ?? 'N/A';
        $deletedQty    = $deleted_part['quantity']        ?? '0';
        $deletedUnit   = $deleted_part['unit']            ?? 'Pc(s)';
        $deletedStatus = ucfirst($deleted_part['status']  ?? 'N/A');
        $deletedNote   = $deleted_part['note']            ?? null;

        // ── Message ────────────────────────────────────────────
        $msg  = "🗑️ <b>PART DELETED FROM JOB SHEET</b>\n\n";

        $msg .= "<b>🔖 Job Sheet No:</b> #{$jobSheetNo}\n";
        $msg .= "<b>🕒 Date:</b> {$date}\n";
        $msg .= "<b>👤 Customer:</b> {$customerName}\n";
        if ($customerMobile) $msg .= "<b>📱 Mobile:</b> {$customerMobile}\n";
        $msg .= "<b>📍 Location:</b> {$locationName}\n\n";

        // ── Deleted part ───────────────────────────────────────
        $msg .= "<b>🗑️ Deleted Part:</b>\n";
        $msg .= "  • <b>{$deletedName}</b>\n";
        $msg .= "    Qty: {$deletedQty} {$deletedUnit}\n";
        $msg .= "    Status: {$deletedStatus}\n";
        if ($deletedNote) {
            $msg .= "    Note: {$deletedNote}\n";
        }
        $msg .= "\n";

        // ── Remaining parts ────────────────────────────────────
        $remaining_parts = $job_sheet->getPartsUsed();
        if (!empty($remaining_parts)) {
            $msg .= "<b>📋 Remaining Parts:</b>\n";
            $totalQty = 0;
            foreach ($remaining_parts as $part) {
                $pName   = $part['variation_name'] ?? 'N/A';
                $pQty    = $part['quantity']        ?? '0';
                $pUnit   = $part['unit']            ?? 'Pc(s)';
                $pStatus = ucfirst($part['status']  ?? 'N/A');
                $totalQty += (float) $pQty;

                $msg .= "  • <b>{$pName}</b>\n";
                $msg .= "    Qty: {$pQty} {$pUnit} | Status: {$pStatus}\n";
            }
            $msg .= "\n<b>📦 Total Remaining:</b> " . count($remaining_parts) . " item(s) | Qty: " . number_format($totalQty, 2) . "\n\n";
        } else {
            $msg .= "<b>📋 Remaining Parts:</b> None\n\n";
        }

        // ── Account Balances ───────────────────────────────────
        if (!empty($all_account)) {
            $msg .= "<b>🏦 Account Balances:</b>\n";
            foreach ($all_account as $account) {
                $msg .= "  • <b>{$account['name']}:</b> {$account['balance']}\n";
            }
            $msg .= "\n";
        }

        $msg .= "⏰ <b>Date Deleted:</b> " . now()->format('d/m/Y H:i') . "\n";
        $msg .= "🗑️ <i>Deleted via Shoper POS</i>";

        self::sendMessage($msg, $to, $location_id);
    }
    public static function addPaymentMessage(
        $transaction,
        $payment,
        string $to = 'repair',
        string $location_id = 'PT1001',
        string $previous_payment_status = ''
    ): void {
        if (empty($transaction) || empty($payment)) return;

        $all_account = self::fetchAccounts($location_id);

        // ── Resolve contact & location ─────────────────────────
        $contact  = $transaction->contact ?? \App\Contact::find($transaction->contact_id);
        $location = \App\BusinessLocation::find($transaction->location_id);

        $customerName   = filled($contact->name ?? '') ? $contact->name : ($contact->supplier_business_name ?? 'N/A');
        $customerMobile = $contact->mobile ?? null;
        $locationName   = $location->name ?? 'N/A';
        $date           = \Carbon\Carbon::parse($payment->paid_on)->format('d/m/Y H:i');

        // ── Payment method label ───────────────────────────────
        $methodMap = [
            'cash'          => '💵 Cash',
            'card'          => '💳 Card',
            'cheque'        => '🧾 Cheque',
            'bank_transfer' => '🏦 Bank Transfer',
            'advance'       => '⏩ Advance',
            'other'         => '🔄 Other',
        ];
        $methodLabel = $methodMap[$payment->method] ?? ucfirst($payment->method);

        // ── Transaction type label ─────────────────────────────
        $typeMap = [
            'sell'         => '🛒 Sale',
            'sell_return'  => '↩️ Sale Return',
            'purchase'     => '📦 Purchase',
            'expense'      => '💸 Expense',
        ];
        $typeLabel = $typeMap[$transaction->type] ?? ucfirst($transaction->type);

        // ── Payment status label map ───────────────────────────
        $statusMap = [
            'paid'         => '✅ Paid',
            'partial'      => '🔄 Partial',
            'due'          => '⏳ Due',
            'overdue'      => '🔴 Overdue',
        ];
        $fromStatusLabel = $statusMap[$previous_payment_status] ?? ucfirst($previous_payment_status ?: 'due');
        $toStatusLabel   = $statusMap[$transaction->payment_status] ?? ucfirst($transaction->payment_status);
        $statusDisplay   = self::diff($fromStatusLabel, $toStatusLabel);

        // ── Header ─────────────────────────────────────────────
        $msg  = "💰 <b>NEW PAYMENT RECEIVED</b>\n\n";

        // ── Transaction info ───────────────────────────────────
        $msg .= "<b>🧾 Invoice No:</b> #{$transaction->invoice_no}\n";
        $msg .= "<b>📋 Type:</b> {$typeLabel}\n";
        $msg .= "<b>🔖 Payment Ref:</b> {$payment->payment_ref_no}\n";
        $msg .= "<b>🕒 Paid On:</b> {$date}\n\n";

        // ── Customer info ──────────────────────────────────────
        $msg .= "<b>👤 Customer:</b> {$customerName}\n";
        if ($customerMobile) $msg .= "<b>📱 Mobile:</b> {$customerMobile}\n";
        $msg .= "<b>📍 Location:</b> {$locationName}\n\n";

        // ── Payment details ────────────────────────────────────
        $msg .= "<b>💳 Payment Details:</b>\n";
        $msg .= "  • <b>Method:</b> {$methodLabel}\n";
        $msg .= "  • <b>Amount Paid:</b> " . number_format((float)$payment->amount, 2) . "\n";
        $msg .= "  • <b>Invoice Total:</b> " . number_format((float)$transaction->final_total, 2) . "\n";
        $msg .= "  • <b>Status:</b> {$statusDisplay}\n";

        if (!empty($payment->note)) {
            $msg .= "  • <b>Note:</b> {$payment->note}\n";
        }
        if (!empty($payment->transaction_no)) {
            $msg .= "  • <b>Transaction No:</b> {$payment->transaction_no}\n";
        }
        $msg .= "\n";

        // ── Account balances ───────────────────────────────────
        if (!empty($all_account)) {
            $msg .= "<b>🏦 Account Balances:</b>\n";
            foreach ($all_account as $account) {
                $msg .= "  • <b>{$account['name']}:</b> {$account['balance']}\n";
            }
            $msg .= "\n";
        }

        $msg .= "⏰ <b>Date:</b> " . now()->format('d/m/Y H:i') . "\n";
        $msg .= "💰 <i>Payment recorded via Shoper POS</i>";

        self::sendMessage($msg, $to, $location_id);
    }
    public static function addPurchaseOrderStatusMessage(
        $transaction,
        string $from_status,
        string $to_status,
        string $to = 'repair',
        string $location_id = 'PT1001'
    ): void {
        if (empty($transaction)) return;

        $all_account = self::fetchAccounts($location_id);

        // ── Resolve contact & location ─────────────────────────
        $contact  = \App\Contact::find($transaction->contact_id);
        $location = \App\BusinessLocation::find($transaction->location_id);

        $supplierName   = filled($contact->supplier_business_name ?? '') ? $contact->supplier_business_name : ($contact->name ?? 'N/A');
        $supplierMobile = $contact->mobile ?? null;
        $locationName   = $location->name ?? 'N/A';
        $date           = \Carbon\Carbon::parse($transaction->transaction_date)->format('d/m/Y H:i');

        // ── Status label map ───────────────────────────────────
        $statusMap = [
            'ordered'    => '📋 Ordered',
            'pending'    => '⏳ Pending',
            'partial'    => '🔄 Partial',
            'completed'  => '✅ Completed',
            'cancelled'  => '❌ Cancelled',
            'received'   => '📦 Received',
        ];
        $fromLabel = $statusMap[$from_status] ?? ucfirst($from_status);
        $toLabel   = $statusMap[$to_status]   ?? ucfirst($to_status);

        // ── Shipping status ────────────────────────────────────
        $shippingMap = [
            'ordered'   => '📋 Ordered',
            'packed'    => '📦 Packed',
            'shipped'   => '🚚 Shipped',
            'delivered' => '✅ Delivered',
            'cancelled' => '❌ Cancelled',
        ];
        $shippingLabel = $shippingMap[$transaction->shipping_status ?? ''] ?? ucfirst($transaction->shipping_status ?? 'N/A');

        // ── Header ─────────────────────────────────────────────
        $msg  = "📋 <b>PURCHASE ORDER STATUS UPDATED</b>\n\n";

        // ── Order info ─────────────────────────────────────────
        $msg .= "<b>🔖 Ref No:</b> #{$transaction->ref_no}\n";
        $msg .= "<b>🕒 Order Date:</b> {$date}\n";
        $msg .= "<b>📍 Location:</b> {$locationName}\n\n";

        // ── Supplier info ──────────────────────────────────────
        $msg .= "<b>🏢 Supplier:</b> {$supplierName}\n";
        if ($supplierMobile) $msg .= "<b>📱 Mobile:</b> {$supplierMobile}\n";
        $msg .= "\n";

        // ── Status change ──────────────────────────────────────
        $msg .= "<b>🔄 Status Change:</b>\n";
        $msg .= "  • <b>From:</b> {$fromLabel}\n";
        $msg .= "  • <b>To:</b> {$toLabel}\n\n";

        // ── Order details ──────────────────────────────────────
        $msg .= "<b>📦 Order Details:</b>\n";
        $msg .= "  • <b>Total:</b> " . number_format((float)$transaction->final_total, 2) . "\n";
        $msg .= "  • <b>Shipping Status:</b> {$shippingLabel}\n";

        if (!empty($transaction->shipping_address)) {
            $msg .= "  • <b>Shipping Address:</b> {$transaction->shipping_address}\n";
        }
        if (!empty($transaction->delivered_to)) {
            $msg .= "  • <b>Delivered To:</b> {$transaction->delivered_to}\n";
        }
        if (!empty($transaction->additional_notes)) {
            $msg .= "  • <b>Notes:</b> {$transaction->additional_notes}\n";
        }
        $msg .= "\n";

        // ── Account balances ───────────────────────────────────
        if (!empty($all_account)) {
            $msg .= "<b>🏦 Account Balances:</b>\n";
            foreach ($all_account as $account) {
                $msg .= "  • <b>{$account['name']}:</b> {$account['balance']}\n";
            }
            $msg .= "\n";
        }

        $msg .= "⏰ <b>Updated At:</b> " . now()->format('d/m/Y H:i') . "\n";
        $msg .= "📋 <i>Updated via Shoper POS</i>";

        self::sendMessage($msg, $to, $location_id);
    }
    public static function productPriceUpdatedMessage(
        $variation,
        $old_price,
        string $to = 'transfer',
        string $location_id = 'PT1001'
    ): void {
        if (empty($variation)) return;

        $productName = $variation->product->name ?? 'N/A';
        $subSku      = $variation->sub_sku ?? $variation->product->sku ?? 'N/A';
        $oldPrice    = number_format($old_price, 4);
        $newPrice    = number_format($variation->sell_price_inc_tax, 4);
        $date        = now()->format('d/m/Y H:i');

        $msg  = "💲 <b>PRODUCT PRICE UPDATED</b>\n\n";
        $msg .= "<b>{$productName}</b>\n";
        $msg .= "🔖 <b>SKU:</b> {$subSku}\n\n";
        $msg .= "📉 <b>Old Price:</b> \${$oldPrice}\n";
        $msg .= "📈 <b>New Price:</b> \${$newPrice}\n";
        $updated_by = self::getUpdatedBy();
        $msg .= "\n👤 <b>Updated By:</b> {$updated_by}\n";
        $msg .= "⏰ <b>Updated At:</b> {$date}\n";
        $msg .= "🔁 <i>Price updated via Shoper POS</i>";

        self::sendMessage($msg, $to, $location_id);
    }
    public static function productSkuUpdatedMessage(
        $product,
        $old_sku,
        array $variation_diffs = [],
        string $to = 'product',
        string $location_id = 'PT1001'
    ): void {
        if (empty($product)) return;

        $productName = $product->name ?? 'N/A';
        $newSku      = $product->sku ?? 'N/A';
        $date        = now()->format('d/m/Y H:i');

        $msg  = "🏷️ <b>PRODUCT SKU UPDATED</b>\n\n";
        $msg .= "<b>Product:</b> {$productName}\n";

        if ($product->type == 'single' || $product->type == 'combo') {
            $msg .= "📉 <b>Old SKU:</b> {$old_sku}\n";
            $msg .= "📈 <b>New SKU:</b> {$newSku}\n";
        }

        if (!empty($variation_diffs)) {
            $msg .= "\n<b>📋 Variation SKU Updates:</b>\n";
            foreach ($variation_diffs as $diff) {
                $varName = $diff['name'] ?? 'Default';
                $oldSubSku = $diff['old_sku'] ?? 'N/A';
                $newSubSku = $diff['new_sku'] ?? 'N/A';
                $msg .= "• <b>{$varName}:</b> <s>{$oldSubSku}</s> → <b>{$newSubSku}</b>\n";
            }
        }

        $updated_by = self::getUpdatedBy();
        $msg .= "\n👤 <b>Updated By:</b> {$updated_by}\n";
        $msg .= "⏰ <b>Updated At:</b> {$date}\n";
        $msg .= "🔁 <i>SKU updated via Shoper POS</i>";

        self::sendMessage($msg, $to, $location_id);
    }
    public static function deleteJobSheetMessage(
        $job_sheet,
        string $to = 'repair',
        string $location_id = 'PT1001'
    ): void {
        if (empty($job_sheet)) return;

        $all_account = self::fetchAccounts($location_id);

        $customerName   = filled($job_sheet->customer->name ?? '')
            ? $job_sheet->customer->name
            : ($job_sheet->customer->supplier_business_name ?? 'N/A');
        $customerMobile = $job_sheet->customer->mobile ?? null;
        $locationName   = $job_sheet->businessLocation->name ?? 'N/A';
        $brandName      = $job_sheet->Brand->name ?? 'N/A';
        $deviceName     = $job_sheet->Device->name ?? 'N/A';
        $modelName      = $job_sheet->deviceModel->name ?? 'N/A';
        $statusName     = $job_sheet->status->name ?? 'N/A';
        $staffName      = $job_sheet->technician
            ? trim(
                ($job_sheet->technician->surname    ?? '') . ' ' .
                    ($job_sheet->technician->first_name ?? '') . ' ' .
                    ($job_sheet->technician->last_name  ?? '')
            )
            : 'N/A';

        $jobSheetNo    = $job_sheet->job_sheet_no ?? 'N/A';
        $date          = \Carbon\Carbon::parse($job_sheet->created_at)->format('d/m/Y H:i');
        $serviceType   = ucfirst(str_replace('_', ' ', $job_sheet->service_type ?? 'N/A'));
        $serialNo      = $job_sheet->serial_no ?? 'N/A';
        $estimatedCost = $job_sheet->estimated_cost
            ? number_format($job_sheet->estimated_cost, 2)
            : '0.00';
        $deliveryDate  = $job_sheet->delivery_date
            ? \Carbon\Carbon::parse($job_sheet->delivery_date)->format('d/m/Y H:i')
            : 'N/A';

        $msg  = "🗑️ <b>JOB SHEET DELETED</b>\n\n";

        $msg .= "<b>📍 Location:</b> {$locationName}\n\n";

        $msg .= "<b>👤 Customer:</b> {$customerName}\n";
        if ($customerMobile) $msg .= "<b>📱 Mobile:</b> {$customerMobile}\n";
        $msg .= "\n";

        $msg .= "<b>🔖 Job Sheet No:</b> #{$jobSheetNo}\n";
        $msg .= "<b>🕒 Created Date:</b> {$date}\n";
        $msg .= "<b>🛠️ Service Type:</b> {$serviceType}\n";
        $msg .= "<b>📌 Status:</b> {$statusName}\n";
        $msg .= "<b>📅 Due Date:</b> {$deliveryDate}\n";
        $msg .= "<b>💵 Estimated Cost:</b> \${$estimatedCost}\n\n";

        $msg .= "<b>📱 Brand:</b> {$brandName}\n";
        $msg .= "<b>📟 Device:</b> {$deviceName}\n";
        $msg .= "<b>🔩 Device Model:</b> {$modelName}\n";
        $msg .= "<b>🔢 Serial Number:</b> {$serialNo}\n\n";

        $msg .= "<b>👨‍🔧 Technician:</b> {$staffName}\n\n";

        // ── Accounts ───────────────────────────────────────────
        if (!empty($all_account)) {
            $msg .= "<b>🏦 Account Balances:</b>\n";
            foreach ($all_account as $account) {
                $msg .= "  • <b>{$account['name']}:</b> {$account['balance']}\n";
            }
            $msg .= "\n";
        }

        $msg .= "⏰ <b>Deleted At:</b> " . now()->format('d/m/Y H:i') . "\n";
        $msg .= "🗑️ <i>Deleted via Shoper POS</i>";

        self::sendMessage($msg, $to, $location_id);
    }
    public static function updateJobSheetMessage(
        $job_sheet,
        $old_job_sheet,
        $contact,
        $location,
        $brand,
        $device,
        $deviceModel,
        $status,
        $old_status,
        $serviceStaff,
        $old_serviceStaff,
        $old_brand,
        $old_device,
        $old_deviceModel,
        string $to = 'repair',
        string $location_id = 'PT1001'
    ): void {
        if (empty($job_sheet)) return;

        $all_account = self::fetchAccounts($location_id);

        // ── Resolve fields safely ──────────────────────────────
        $customerName    = filled($contact->name ?? '') ? $contact->name : ($contact->supplier_business_name ?? 'N/A');
        $customerMobile  = $contact->mobile ?? null;
        $locationName    = $location->name ?? 'N/A';

        // ── Diff fields ────────────────────────────────────────
        $brandName  = self::diff($old_brand->name       ?? 'N/A', $brand->name       ?? 'N/A');
        $deviceName = self::diff($old_device->name      ?? 'N/A', $device->name      ?? 'N/A');
        $modelName  = self::diff($old_deviceModel->name ?? 'N/A', $deviceModel->name ?? 'N/A');
        $statusName = self::diff($old_status->name      ?? 'N/A', $status->name      ?? 'N/A');

        $oldStaffName = $old_serviceStaff
            ? trim(($old_serviceStaff->surname ?? '') . ' ' . ($old_serviceStaff->first_name ?? '') . ' ' . ($old_serviceStaff->last_name ?? ''))
            : 'N/A';
        $newStaffName = $serviceStaff
            ? trim(($serviceStaff->surname ?? '') . ' ' . ($serviceStaff->first_name ?? '') . ' ' . ($serviceStaff->last_name ?? ''))
            : 'N/A';
        $staffName = self::diff($oldStaffName, $newStaffName);

        $jobSheetNo  = $job_sheet->job_sheet_no ?? 'N/A';
        $date        = \Carbon\Carbon::parse($job_sheet->created_at)->format('d/m/Y H:i');
        $serviceType = self::diff(
            ucfirst(str_replace('_', ' ', $old_job_sheet->service_type ?? 'N/A')),
            ucfirst(str_replace('_', ' ', $job_sheet->service_type     ?? 'N/A'))
        );
        $serialNo = self::diff(
            $old_job_sheet->serial_no ?? 'N/A',
            $job_sheet->serial_no     ?? 'N/A'
        );
        $securityPwd     = $job_sheet->security_pwd     ?? null;
        $securityPattern = $job_sheet->security_pattern ?? null;
        $estimatedCost   = self::diff(
            $old_job_sheet->estimated_cost ? number_format($old_job_sheet->estimated_cost, 2) : '0.00',
            $job_sheet->estimated_cost     ? number_format($job_sheet->estimated_cost,     2) : '0.00'
        );
        $deliveryDate = self::diff(
            $old_job_sheet->delivery_date ? \Carbon\Carbon::parse($old_job_sheet->delivery_date)->format('d/m/Y H:i') : 'N/A',
            $job_sheet->delivery_date     ? \Carbon\Carbon::parse($job_sheet->delivery_date)->format('d/m/Y H:i')     : 'N/A'
        );
        $productConfig = self::diff(
            self::decodeRepairField($old_job_sheet->product_configuration) ?: 'N/A',
            self::decodeRepairField($job_sheet->product_configuration)     ?: 'N/A'
        );
        $defects = self::diff(
            self::decodeRepairField($old_job_sheet->defects) ?: 'N/A',
            self::decodeRepairField($job_sheet->defects)     ?: 'N/A'
        );
        $condition = self::diff(
            self::decodeRepairField($old_job_sheet->product_condition) ?: 'N/A',
            self::decodeRepairField($job_sheet->product_condition)     ?: 'N/A'
        );
        $commentBySS = $job_sheet->comment_by_ss        ?? null;
        $pickUpAddr  = $job_sheet->pick_up_on_site_addr ?? null;

        $customFields = array_filter([
            '1' => $job_sheet->custom_field_1 ?? null,
            '2' => $job_sheet->custom_field_2 ?? null,
            '3' => $job_sheet->custom_field_3 ?? null,
            '4' => $job_sheet->custom_field_4 ?? null,
            '5' => $job_sheet->custom_field_5 ?? null,
        ]);

        // ── Header ─────────────────────────────────────────────
        $msg  = "✏️ <b>JOB SHEET UPDATED</b>\n\n";

        // ── Business / Location ────────────────────────────────
        $msg .= "<b>📍 Location:</b> {$locationName}\n\n";

        // ── Customer ───────────────────────────────────────────
        $msg .= "<b>👤 Customer:</b> {$customerName}\n";
        if ($customerMobile) $msg .= "<b>📱 Mobile:</b> {$customerMobile}\n";
        $msg .= "\n";

        // ── Job Sheet Info ─────────────────────────────────────
        $msg .= "<b>🔖 Job Sheet No:</b> #{$jobSheetNo}\n";
        $msg .= "<b>🕒 Date:</b> {$date}\n";
        $msg .= "<b>🛠️ Service Type:</b> {$serviceType}\n";
        $msg .= "<b>📌 Status:</b> {$statusName}\n";
        $msg .= "<b>📅 Due Date:</b> {$deliveryDate}\n";
        $msg .= "<b>💵 Estimated Cost:</b> \${$estimatedCost}\n";
        $msg .= "\n";

        // ── Device Info ────────────────────────────────────────
        $msg .= "<b>📱 Brand:</b> {$brandName}\n";
        $msg .= "<b>📟 Device:</b> {$deviceName}\n";
        $msg .= "<b>🔩 Device Model:</b> {$modelName}\n";
        $msg .= "<b>🔢 Serial Number:</b> {$serialNo}\n";
        if ($securityPwd)     $msg .= "<b>🔑 Password:</b> {$securityPwd}\n";
        if ($securityPattern) $msg .= "<b>🔐 Security Pattern Code:</b> {$securityPattern}\n";
        $msg .= "\n";

        // ── Technician ─────────────────────────────────────────
        $msg .= "<b>👨‍🔧 Technician:</b> {$staffName}\n";
        if ($commentBySS) $msg .= "<b>💬 Comment by Technician:</b> {$commentBySS}\n";
        $msg .= "\n";

        // ── Repair Details ─────────────────────────────────────
        $msg .= "<b>📍 Pick up/On site address:</b> "     . ($pickUpAddr    ?: 'N/A') . "\n";
        $msg .= "<b>⚙️ Product Configuration:</b> "       . ($productConfig ?: 'N/A') . "\n";
        $msg .= "<b>📋 Condition Of The Product:</b> "     . ($condition     ?: 'N/A') . "\n";
        $msg .= "<b>🐛 Problem Reported By Customer:</b> " . ($defects       ?: 'N/A') . "\n";
        $msg .= "\n";

        // ── Pre Repair Checklist ───────────────────────────────
        if (!empty($job_sheet->checklist)) {
            $checklist = is_array($job_sheet->checklist)
                ? $job_sheet->checklist
                : json_decode($job_sheet->checklist, true);

            if (!empty($checklist)) {
                $msg .= "<b>✅ Pre Repair Checklist:</b>\n";
                foreach ($checklist as $item => $value) {
                    $icon = $value == 1 ? '✅' : ($value == 0 ? '❌' : '➖');
                    $msg .= "  {$icon} {$item}\n";
                }
                $msg .= "\n";
            } else {
                $msg .= "<b>✅ Pre Repair Checklist:</b> N/A\n\n";
            }
        } else {
            $msg .= "<b>✅ Pre Repair Checklist:</b> N/A\n\n";
        }

        // ── Parts Used ─────────────────────────────────────────
        try {
            $parts = $job_sheet->getPartsUsed();
            if (!empty($parts)) {
                $msg .= "<b>🔩 Parts Used:</b>\n";
                foreach ($parts as $part) {
                    $partName   = $part['variation_name'] ?? 'N/A';
                    $partQty    = $part['quantity']        ?? '0';
                    $partUnit   = $part['unit']            ?? 'Pc(s)';
                    $partStatus = isset($part['status'])
                        ? ' | ' . ucfirst($part['status'])
                        : '';
                    $msg .= "  • <b>{$partName}</b> | Qty: {$partQty} {$partUnit}{$partStatus}\n";
                }
                $msg .= "\n";
            } else {
                $msg .= "<b>🔩 Parts Used:</b> N/A\n\n";
            }
        } catch (\Exception $e) {
            $msg .= "<b>🔩 Parts Used:</b> N/A\n\n";
        }

        // ── Custom Fields ──────────────────────────────────────
        if (!empty($customFields)) {
            $msg .= "<b>📝 Custom Fields:</b>\n";
            foreach ($customFields as $i => $value) {
                $msg .= "  • Field {$i}: {$value}\n";
            }
            $msg .= "\n";
        }

        // ── Accounts ───────────────────────────────────────────
        if (!empty($all_account)) {
            $msg .= "<b>🏦 Account Balances:</b>\n";
            foreach ($all_account as $account) {
                $msg .= "  • <b>{$account['name']}:</b> {$account['balance']}\n";
            }
            $msg .= "\n";
        }

        $msg .= "⏰ <b>Updated At:</b> " . now()->format('d/m/Y H:i') . "\n";
        $msg .= "✏️ <i>Updated via Shoper POS</i>";

        self::sendMessage($msg, $to, $location_id);
    }
}
