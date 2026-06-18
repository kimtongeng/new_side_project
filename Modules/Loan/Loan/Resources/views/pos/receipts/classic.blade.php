<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>@lang('Loan::lang.receipt') - {{ $receipt_details->invoice_no }}</title>
    <style type="text/css">
        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
            line-height: 1.4;
            margin: 0;
            padding: 10px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
        }
        .header, .footer {
            text-align: center;
            margin-bottom: 20px;
        }
        .header img {
            max-width: 150px;
        }
        .header h2, .header h3 {
            margin: 5px 0;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .table th, .table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .table th {
            background-color: #f2f2f2;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .no-border {
            border: none;
        }
        .total-row {
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            @if (!empty($receipt_details->logo))
                <img src="{{ $receipt_details->logo }}" alt="Business Logo">
            @endif
            <h2>{{ $receipt_details->business_name }}</h2>
            <h3>@lang('Loan::lang.receipt')</h3>
            <p>{{ $receipt_details->location_address }}</p>
            <p>@lang('lang_v1.contact'): {{ $receipt_details->contact }}</p>
            @if (!empty($receipt_details->website))
                <p>@lang('lang_v1.website'): {{ $receipt_details->website }}</p>
            @endif
            @if (!empty($receipt_details->tax_info1))
                <p>{{ $receipt_details->tax_label1 }}: {{ $receipt_details->tax_info1 }}</p>
            @endif
            @if (!empty($receipt_details->tax_info2))
                <p>{{ $receipt_details->tax_label2 }}: {{ $receipt_details->tax_info2 }}</p>
            @endif
        </div>

        <div class="row">
            <div class="col-md-6">
                <p><strong>@lang('lang_v1.invoice_no'):</strong> {{ $receipt_details->invoice_no }}</p>
                <p><strong>@lang('lang_v1.date'):</strong> {{ \Carbon::createFromFormat('Y-m-d H:i:s', $receipt_details->transaction_date)->format('d/m/Y H:i') }}</p>
                <p><strong>@lang('lang_v1.customer'):</strong> {{ $receipt_details->customer_name }}</p>
                @if (!empty($receipt_details->customer_info))
                    <p>{{ $receipt_details->customer_info }}</p>
                @endif
            </div>
            <div class="col-md-6 text-right">
                @if (!empty($receipt_details->sales_person_label))
                    <p><strong>{{ $receipt_details->sales_person_label }}:</strong> {{ $receipt_details->sales_person }}</p>
                @endif
                @if (!empty($receipt_details->commission_agent_label))
                    <p><strong>{{ $receipt_details->commission_agent_label }}:</strong> {{ $receipt_details->commission_agent }}</p>
                @endif
            </div>
        </div>

        <table class="table">
            <thead>
                <tr>
                    <th>@lang('lang_v1.item')</th>
                    <th>@lang('lang_v1.quantity')</th>
                    <th>@lang('lang_v1.unit_price')</th>
                    <th>@lang('lang_v1.discount')</th>
                    <th>@lang('lang_v1.tax')</th>
                    <th>@lang('lang_v1.sub_total')</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($receipt_details->lines as $line)
                    <tr>
                        <td>{{ $line['name'] }} {{ $line['variation'] }}
                            @if (!empty($line['sub_sku']))
                                <br><small>@lang('lang_v1.sku'): {{ $line['sub_sku'] }}</small>
                            @endif
                            @if (!empty($line['sell_line_note']))
                                <br><small>{{ $line['sell_line_note'] }}</small>
                            @endif
                        </td>
                        <td>{{ $line['quantity'] }}</td>
                        <td>{{ number_format($line['unit_price_before_discount'], 2, $receipt_details->currency['decimal_separator'], $receipt_details->currency['thousand_separator']) }}</td>
                        <td>
                            @if ($line['line_discount'] > 0)
                                {{ number_format($line['line_discount'], 2, $receipt_details->currency['decimal_separator'], $receipt_details->currency['thousand_separator']) }}
                                ({{ $line['line_discount_type'] == 'percentage' ? $line['line_discount'] . '%' : 'fixed' }})
                            @else
                                0
                            @endif
                        </td>
                        <td>{{ number_format($line['item_tax'], 2, $receipt_details->currency['decimal_separator'], $receipt_details->currency['thousand_separator']) }}</td>
                        <td>{{ number_format($line['quantity'] * $line['unit_price'], 2, $receipt_details->currency['decimal_separator'], $receipt_details->currency['thousand_separator']) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <table class="table no-border">
            <tr>
                <td class="text-right total-row">@lang('lang_v1.total'):</td>
                <td class="text-right total-row">
                    {{ $receipt_details->currency['symbol'] }} {{ number_format($receipt_details->total_before_tax, 2, $receipt_details->currency['decimal_separator'], $receipt_details->currency['thousand_separator']) }}
                </td>
            </tr>
            @if ($receipt_details->discount > 0)
                <tr>
                    <td class="text-right">@lang('lang_v1.discount'):</td>
                    <td class="text-right">
                        -{{ $receipt_details->currency['symbol'] }} {{ number_format($receipt_details->discount, 2, $receipt_details->currency['decimal_separator'], $receipt_details->currency['thousand_separator']) }}
                        ({{ $receipt_details->discount_type == 'percentage' ? $receipt_details->discount . '%' : 'fixed' }})
                    </td>
                </tr>
            @endif
            @if ($receipt_details->tax > 0)
                <tr>
                    <td class="text-right">@lang('lang_v1.tax'):</td>
                    <td class="text-right">
                        {{ $receipt_details->currency['symbol'] }} {{ number_format($receipt_details->tax, 2, $receipt_details->currency['decimal_separator'], $receipt_details->currency['thousand_separator']) }}
                    </td>
                </tr>
            @endif
            @if ($receipt_details->shipping_charges > 0)
                <tr>
                    <td class="text-right">@lang('lang_v1.shipping_charges'):</td>
                    <td class="text-right">
                        {{ $receipt_details->currency['symbol'] }} {{ number_format($receipt_details->shipping_charges, 2, $receipt_details->currency['decimal_separator'], $receipt_details->currency['thousand_separator']) }}
                    </td>
                </tr>
            @endif
            <tr>
                <td class="text-right total-row">@lang('lang_v1.grand_total'):</td>
                <td class="text-right total-row">
                    {{ $receipt_details->currency['symbol'] }} {{ number_format($receipt_details->final_total, 2, $receipt_details->currency['decimal_separator'], $receipt_details->currency['thousand_separator']) }}
                </td>
            </tr>
        </table>

        <div class="row">
            <div class="col-md-12">
                <h4>@lang('Loan::lang.payment_details')</h4>
                <table class="table">
                    <thead>
                        <tr>
                            <th>@lang('lang_v1.payment_method')</th>
                            <th>@lang('lang_v1.amount')</th>
                            <th>@lang('lang_v1.note')</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($receipt_details->payment_lines as $payment)
                            @if ($payment['amount'] > 0 || $payment['is_return'] == 1)
                                <tr>
                                    <td>
                                        {{ $payment['method'] == 'cash' ? __('lang_v1.cash') : 
                                           ($payment['method'] == 'card' ? __('lang_v1.card') : 
                                           ($payment['method'] == 'cheque' ? __('lang_v1.cheque') : $payment['method'])) }}
                                    </td>
                                    <td>
                                        @if ($payment['is_return'] == 1)
                                            -{{ $receipt_details->currency['symbol'] }} {{ number_format($payment['amount'], 2, $receipt_details->currency['decimal_separator'], $receipt_details->currency['thousand_separator']) }}
                                        @else
                                            {{ $receipt_details->currency['symbol'] }} {{ number_format($payment['amount'], 2, $receipt_details->currency['decimal_separator'], $receipt_details->currency['thousand_separator']) }}
                                        @endif
                                    </td>
                                    <td>{{ $payment['note'] ?? '-' }}</td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="footer">
            @if (!empty($receipt_details->additional_notes))
                <p><strong>@lang('lang_v1.notes'):</strong> {{ $receipt_details->additional_notes }}</p>
            @endif
            @if (!empty($receipt_details->footer_text))
                <p>{{ $receipt_details->footer_text }}</p>
            @endif
            <p>@lang('lang_v1.thank_you')!</p>
            <p>{{ $receipt_details->business_name }}</p>
        </div>
    </div>
</body>
</html>
@stop