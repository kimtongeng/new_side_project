<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>@lang('Loan::lang.delivery_note') - {{ $receipt_details->invoice_no }}</title>
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
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            @if (!empty($receipt_details->logo))
                <img src="{{ $receipt_details->logo }}" alt="Business Logo">
            @endif
            <h2>{{ $receipt_details->business_name }}</h2>
            <h3>@lang('Loan::lang.delivery_note')</h3>
            <p>{{ $receipt_details->location_address }}</p>
            <p>@lang('lang_v1.contact'): {{ $receipt_details->contact }}</p>
            @if (!empty($receipt_details->website))
                <p>@lang('lang_v1.website'): {{ $receipt_details->website }}</p>
            @endif
        </div>

        <div class="row">
            <div class="col-md-6">
                <p><strong>@lang('lang_v1.invoice_no'):</strong> {{ $receipt_details->invoice_no }}</p>
                <p><strong>@lang('lang_v1.date'):</strong> {{ \Carbon::createFromFormat('Y-m-d H:i:s', $receipt_details->transaction_date)->format('d/m/Y') }}</p>
                <p><strong>@lang('lang_v1.customer'):</strong> {{ $receipt_details->customer_name }}</p>
                @if (!empty($receipt_details->customer_info))
                    <p>{{ $receipt_details->customer_info }}</p>
                @endif
            </div>
            <div class="col-md-6 text-right">
                <p><strong>@lang('lang_v1.shipping_address'):</strong></p>
                @if (!empty($receipt_details->shipping_address))
                    <p>{{ $receipt_details->shipping_address }}</p>
                @else
                    <p>@lang('lang_v1.same_as_billing')</p>
                @endif
            </div>
        </div>

        <table class="table">
            <thead>
                <tr>
                    <th>@lang('lang_v1.item')</th>
                    <th>@lang('lang_v1.quantity')</th>
                    <th>@lang('lang_v1.unit')</th>
                    <th>@lang('lang_v1.notes')</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($receipt_details->lines as $line)
                    <tr>
                        <td>{{ $line['name'] }} {{ $line['variation'] }}
                            @if (!empty($line['sub_sku']))
                                <br><small>@lang('lang_v1.sku'): {{ $line['sub_sku'] }}</small>
                            @endif
                        </td>
                        <td>{{ $line['quantity'] }}</td>
                        <td>{{ $line['unit'] }}</td>
                        <td>
                            @if (!empty($line['sell_line_note']))
                                {{ $line['sell_line_note'] }}
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="footer">
            @if (!empty($receipt_details->additional_notes))
                <p><strong>@lang('lang_v1.notes'):</strong> {{ $receipt_details->additional_notes }}</p>
            @endif
            <p>@lang('lang_v1.thank_you')!</p>
            <p>{{ $receipt_details->business_name }}</p>
        </div>
    </div>
</body>
</html>
@stop