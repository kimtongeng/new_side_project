<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Worksheet - {{ $session->name }}</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif !important;
            color: #333;
            background: #fff;
            padding: 20px;
            font-size: 13px;
            line-height: 1.4;
        }
        .container {
            max-width: 1100px;
            margin: 0 auto;
        }
        .header {
            text-align: center;
            margin-bottom: 25px;
            border-bottom: 3px double #333;
            padding-bottom: 15px;
        }
        .header h1 {
            margin: 0 0 10px 0;
            font-size: 24px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .header p {
            margin: 5px 0;
            font-size: 14px;
            color: #666;
        }
        .meta-table {
            width: 100%;
            margin-bottom: 30px;
            border-collapse: collapse;
        }
        .meta-table td {
            padding: 6px 10px;
            border: none;
            width: 25%;
        }
        .meta-label {
            font-weight: bold;
            color: #111;
        }
        .meta-value {
            border-bottom: 1px dashed #ccc;
        }
        .worksheet-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 40px;
        }
        .worksheet-table th {
            background-color: #f2f2f2 !important;
            color: #000;
            font-weight: bold;
            text-align: left;
            border: 1px solid #999;
            padding: 8px 10px;
            font-size: 12px;
        }
        .worksheet-table td {
            border: 1px solid #999;
            padding: 8px 10px;
            vertical-align: middle;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .signature-container {
            display: flex;
            justify-content: space-between;
            margin-top: 80px;
            page-break-inside: avoid;
        }
        .signature-block {
            width: 28%;
            text-align: center;
            font-weight: bold;
        }
        .signature-line {
            border-top: 1.5px solid #000;
            margin-top: 50px;
        }
        @media print {
            body {
                padding: 0;
            }
            .no-print {
                display: none !important;
            }
            .worksheet-table th {
                background-color: #e5e5e5 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Floating print button for screen view -->
        <div style="text-align: right; margin-bottom: 20px;" class="no-print">
            <button onclick="window.print();" style="padding: 8px 18px; font-weight: bold; cursor: pointer; background: #3a7bd5; color: #fff; border: none; border-radius: 4px; box-shadow: 0 2px 4px rgba(0,0,0,0.15);">
                Print Worksheet
            </button>
        </div>

        <div class="header">
            <h1>Stock Count Worksheet</h1>
            <p>Please perform the physical count and note the quantities in the space provided below.</p>
        </div>

        <table class="meta-table">
            <tr>
                <td class="meta-label">Session Name:</td>
                <td class="meta-value">{{ $session->name }}</td>
                <td class="meta-label">Reference No:</td>
                <td class="meta-value">{{ $session->reference_no }}</td>
            </tr>
            <tr>
                <td class="meta-label">Location:</td>
                <td class="meta-value">{{ $session->location->name ?? '' }}</td>
                <td class="meta-label">Created By:</td>
                <td class="meta-value">{{ $session->creator->user_full_name ?? '' }}</td>
            </tr>
            <tr>
                <td class="meta-label">Date:</td>
                <td class="meta-value">{{ @format_datetime($session->created_at) }}</td>
                <td class="meta-label">Blind Count Mode:</td>
                <td class="meta-value">{{ $session->blind_count ? 'Yes' : 'No' }}</td>
            </tr>
        </table>

        <table class="worksheet-table">
            <thead>
                <tr>
                    <th>Product Name (Product Code)</th>
                    @if(!$session->blind_count)
                        <th style="min-width: 60px; text-align: center;">QOH</th>
                        <th style="min-width: 60px; text-align: center;">Type</th>
                        <th style="min-width: 90px; text-align: center;">Quantity</th>
                        <th style="min-width: 60px; text-align: center;">Unit</th>
                    @endif
                    <th style="min-width: 90px; text-align: center;">{{ $session->blind_count ? 'Counted Qty' : 'New QOH' }}</th>
                    <th style="min-width: 60px; text-align: center;">Unit</th>
                    <th style="min-width: 150px;">Note</th>
                </tr>
            </thead>
            <tbody>
                @foreach($lines as $line)
                    @php
                        $unit_name = $line->product->unit->short_name ?? '';
                        if ($line->counted_by !== null) {
                            $diff = (float)$line->counted_quantity - (float)$line->book_quantity;
                            $type = $diff >= 0 ? '+' : '-';
                            $qty = abs($diff);
                            $new_qoh = (float)$line->counted_quantity;
                        } else {
                            $type = '+';
                            $qty = 0;
                            $new_qoh = (float)$line->book_quantity;
                        }
                    @endphp
                    <tr>
                        <td>
                            <strong>{{ $line->product->name ?? '' }}</strong> ({{ $line->variation->sub_sku ?? '' }})
                            @if(!empty($line->variation->name) && $line->variation->name !== 'DUMMY')
                                <br><small style="color: #555;">{{ $line->variation->name }}</small>
                            @endif
                        </td>
                        @if(!$session->blind_count)
                            <td class="text-center">{{ (float)$line->book_quantity }}</td>
                            <td class="text-center">{{ $type }}</td>
                            <td class="text-center">{{ (float)$qty }}</td>
                            <td class="text-center">{{ $unit_name }}</td>
                        @endif
                        <td class="text-center">{{ (float)$new_qoh }}</td>
                        <td class="text-center">{{ $unit_name }}</td>
                        <td>{{ $line->note ?? '' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Signatures -->
        <div class="signature-container">
            <div class="signature-block">
                <p>Stock Keeper Signature</p>
                <div class="signature-line"></div>
            </div>
            <div class="signature-block">
                <p>Counter Signature</p>
                <div class="signature-line"></div>
            </div>
            <div class="signature-block">
                <p>Verifier Signature</p>
                <div class="signature-line"></div>
            </div>
        </div>
    </div>

    <!-- Auto-trigger print dialog -->
    <script>
        window.addEventListener('DOMContentLoaded', () => {
            setTimeout(() => {
                window.print();
            }, 500);
        });
    </script>
</body>
</html>
