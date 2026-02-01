<?php

namespace App\Notifications;

use App\AccountTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Modules\ExchangeCurrency\Entities\ExchangeCurrency;

class TelegramNotification
{

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
    private const BOT = [
        'token' => '8152281759:AAFEN2PObxW-S8Jck251--mxQEEuNJYCanQ',
        "group_pt1001" => [
            "sell" => [
                "id" => '-1002658900346',
                "title" => 'sell',
                "key" => 'sell',
                "topic" => []
            ],
            "draft" => [
                "id" => '-1002731590375',
                "title" => 'draft',
                "key" => 'draft',
                "topic" => []
            ],
            "quotation" => [
                "id" => '-1002548125523',
                "title" => 'quotation',
                "key" => 'quotation',
                "topic" => []
            ]
        ],
        "group_pt1002" => [
            "sell" => [
                "id" => '-1002884292516',
                "title" => 'sell',
                "key" => 'sell',
                "topic" => []
            ],
            "draft" => [
                "id" => '-1003002593739',
                "title" => 'draft',
                "key" => 'draft',
                "topic" => []
            ],
            "quotation" => [
                "id" => '-1003067353221',
                "title" => 'quotation',
                "key" => 'quotation',
                "topic" => []
            ]
        ],
        "group_pt1003" => [
            "sell" => [
                "id" => '-1003146868305',
                "title" => 'sell',
                "key" => 'sell',
                "topic" => []
            ],
            "draft" => [
                "id" => '-1002996581417',
                "title" => 'draft',
                "key" => 'draft',
                "topic" => []
            ],
            "quotation" => [
                "id" => '-1002982963687',
                "title" => 'quotation',
                "key" => 'quotation',
                "topic" => []
            ]
        ]
    ];

    public static function sendMessage(string $message, string $to = self::BOT["group_bl01"]["sell"]["key"], $location_id = "PT1001"): void
    {
        $botToken = self::BOT["token"];
        $group = "group_pt1001";
        if ($location_id == 'PT1001') {
            $group = "group_pt1001";
        } else if ($location_id == 'PT1002') {
            $group = "group_pt1002";
        } else if ($location_id == 'PT1003') {
            $group = "group_pt1003";
        }

        if ($to == self::BOT[$group]["sell"]["key"]) {
            $chatId = self::BOT[$group]["sell"]["id"];
        } elseif ($to == self::BOT[$group]["draft"]["key"]) {
            $chatId = self::BOT[$group]["draft"]["id"];
        } elseif ($to == self::BOT[$group]["quotation"]["key"]) {
            $chatId = self::BOT[$group]["quotation"]["id"];
        }
        if (empty($chatId)) {
            throw new \Exception("Chat ID not found for key: {$to}");
        }

        // Try to send the main message
        $response = Http::get("https://api.telegram.org/bot{$botToken}/sendMessage", [
            'chat_id' => $chatId,
            'text' => $message,
            'parse_mode' => 'HTML',
        ]);

        $data = $response->json();

        // If sending failed, send error details to 1928945924
        if (empty($data['ok'])) {
            $errorDescription = $data['description'] ?? 'Unknown error';
            self::sendErrorMessage($errorDescription, $chatId, $to, $message);
        }
    }
    public static function sendErrorMessage(string $errorDescription, string $chatIdTried, string $targetKey, string $originalMessage): void
    {
        $botToken = self::BOT["token"];
        $adminChatId = 1928945924; // Where to send error reports

        $text = "❌ <b>Failed to send message</b>\n"
            . "Chat ID: <code>{$chatIdTried}</code>\n"
            . "Target Key: <code>{$targetKey}</code>\n"
            . "Error: <i>{$errorDescription}</i>\n"
            . "Message Tried: \n<pre>{$originalMessage}</pre>\n"
            . "Time: " . date('Y-m-d H:i:s');

        Http::get("https://api.telegram.org/bot{$botToken}/sendMessage", [
            'chat_id' => $adminChatId,
            'text' => $text,
            'parse_mode' => 'HTML',
        ]);
    }


    public static function addSaleMessage($receipt, string $to = self::BOT["group_bl01"]["sell"]["key"], $location_id = 'BL01')
    {
        // dd($receipt);
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
    public static function updateSaleMessage($receipt, $old_receipt, string $to = self::BOT["group_bl01"]["sell"]["key"], $location_id = 'BL01')
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

        // 💵 Totals`
        $msg .= "<b>🧾 Subtotal:</b> ".self::diff($old_receipt->subtotal, $receipt->subtotal)."\n";
        $msg .= "<b>🔻 Discount:</b> ".self::diff($old_receipt->total_line_discount,$receipt->total_line_discount)."\n";
        $msg .= "<b>Total:</b> ".self::diff($old_receipt->total,$receipt->total)."\n";

        // 🌍 Exchange Currency Comparison
        $business_id = auth()->user()->business_id;
        $currencies = ExchangeCurrency::where('business_id', $business_id)->where('is_use', 1)->get();

        $old_base = collect($old_receipt->lines)->sum('line_total_uf');
        $new_base = collect($receipt->lines)->sum('line_total_uf');

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
                    $msg .= "Method: ".self::diff($old['method'], $pay['method'])."\n";
                    $msg .= "Amount: ".self::diff($old['amount'], $pay['amount'])."\n";
                    $msg .= "Date: ".self::diff($old['date'], $pay['date'])."\n\n"; 
                } else {
                    $msg .= "🆕 {$pay['method']} - {$pay['amount']} on {$pay['date']}\n\n";
                }
            }
        }

        // ✅ Paid
        if (isset($old_receipt->total_paid) || isset($receipt->total_paid)) {
            $old_paid = $old_receipt->total_paid ?? 0;
            $new_paid = $receipt->total_paid ?? 0;
            $msg .= "<b>✅ Paid:</b> ".self::diff($old_paid, $new_paid)."\n";
        }

        $msg .= "🧾<b>PAYMENT ACCOUNT:</b>\n";

        if ($old_receipt->none_payment_account || $receipt->none_payment_account) {
            $msg .= "None : <s>{$old_receipt->none_payment_account}</s> → <b>{$receipt->none_payment_account}</b>";
            $msg .= "None : <s>{$old_receipt->none_payment_account}</s> → <b>{$receipt->none_payment_account}</b>";
        }


        if (filled($receipt->payment_account) || filled($old_receipt->payment_account)) {
            foreach ($old_receipt->payment_account as $index => $account) {
                $msg .= "{$account['name']}: <s>{$account['balance']}</s> → {$receipt->payment_account[$index]["balance"]}\n";
            }
        }

        // dd($receipt->all_account);

        if (filled($receipt->all_account) || filled($old_receipt->all_account)) {
            $msg .= "\n🧾<b>LIST ACCOUNT:</b>\n";

            foreach ($old_receipt->all_account as $index => $account) {
                $msg .= "{$account['name']}: <s>{$account['balance']}</s> → {$receipt->all_account[$index]["balance"]}\n";
            }
        }

        self::sendMessage($msg, $to, $location_id);
    }
    public static function deleteSaleMessage($receipt, string $to = self::BOT["group_bl01"]["sell"]["key"], $location_id = 'BL01')
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

    public static function returnSell($receipt, $transaction_id, string $to = self::BOT["group_bl01"]["sell"]["key"], $location_id = 'BL01')
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
}
