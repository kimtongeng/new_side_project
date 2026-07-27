<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock Count Worksheet - {{ $session->name }}</title>
    <style>
        #stock_count_print_template {
            font-family: Arial, Helvetica, sans-serif !important;
            color: #000;
            background: #fff;
            font-size: 13px;
            line-height: 1.4;
            max-width: 1100px;
            margin: 0 auto;
            padding: 20px;
        }
        #stock_count_print_template .print-header {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 20px;
        }
        #stock_count_print_template .company-details {
            text-align: center;
            flex-grow: 1;
        }
        #stock_count_print_template .company-details h1 {
            margin: 0;
            font-size: 26px;
            font-weight: 800;
            color: #000;
            letter-spacing: 0.5px;
        }
        #stock_count_print_template .company-details p {
            margin: 5px 0 2px 0;
            font-size: 10px;
            font-weight: bold;
            color: #000;
        }
        #stock_count_print_template .print-metadata-block {
            margin: 10px 0 20px 0;
            font-family: Arial, Helvetica, sans-serif !important;
        }
        #stock_count_print_template .metadata-table {
            width: 100%;
            border-collapse: collapse;
            border: none !important;
        }
        #stock_count_print_template .metadata-table td {
            padding: 5px 0;
            border: none !important;
            font-size: 11px;
            color: #000;
        }
        #stock_count_print_template .metadata-table td.meta-label {
            font-weight: bold;
            width: 18%;
        }
        #stock_count_print_template .metadata-table td.meta-value {
            width: 32%;
            text-align: right;
            padding-right: 20px;
        }
        #stock_count_print_template .divider-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0 25px 0;
        }
        #stock_count_print_template .divider-line {
            border-bottom: 4px double #2b82c9;
            height: 1px;
        }
        #stock_count_print_template .divider-text {
            font-size: 16px;
            font-weight: bold;
            color: #000;
            text-align: center;
            white-space: nowrap;
            padding: 0 15px;
        }
        #stock_count_print_template .print-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            font-size: 11px;
        }
        #stock_count_print_template .print-table th {
            background-color: #2b82c9 !important;
            color: #ffffff !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            font-weight: bold;
            padding: 10px 8px;
            border: 1px solid #1a202c;
            text-align: left;
        }
        #stock_count_print_template .print-table td {
            padding: 8px;
            border: 1px solid #1a202c;
            color: #000;
        }
        #stock_count_print_template .print-table tfoot {
            display: table-row-group !important;
        }
        #stock_count_print_template .signature-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 60px;
            font-size: 11px;
            font-weight: bold;
            page-break-inside: avoid;
        }
        #stock_count_print_template .signature-table td {
            text-align: center;
            vertical-align: bottom;
            border: none !important;
        }
        #stock_count_print_template .signature-line {
            border-top: 1.5px solid #000;
            width: 80%;
            margin: 40px auto 0 auto;
        }
        @if(empty($for_pdf))
        @media print {
            @page {
                size: portrait;
                margin: 5mm;
            }
            body * {
                visibility: hidden;
            }
            #receipt_section, #receipt_section *,
            #stock_count_print_template, #stock_count_print_template * {
                visibility: visible !important;
            }
            #receipt_section, #stock_count_print_template {
                position: absolute !important;
                left: 0 !important;
                top: 0 !important;
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                display: block !important;
                background: #fff !important;
            }
            #stock_count_print_template {
                max-width: 100% !important;
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            .no-print {
                display: none !important;
            }
            #stock_count_print_template .print-table th {
                background-color: #2b82c9 !important;
                color: #ffffff !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            #stock_count_print_template .print-table tfoot {
                display: table-row-group !important;
            }
        }
        @endif
    </style>
</head>
<body>
    <div id="stock_count_print_template">
        @if(empty($for_pdf))
        <!-- Floating print button for screen view -->
        <div style="text-align: right; margin-bottom: 20px;" class="no-print">
            <button onclick="window.print();" style="padding: 8px 18px; font-weight: bold; cursor: pointer; background: #2b82c9; color: #fff; border: none; border-radius: 4px; box-shadow: 0 2px 4px rgba(0,0,0,0.15);">
                Print Worksheet
            </button>
        </div>
        @endif

        <!-- Company Header -->
        <div class="print-header" style="justify-content: center;">
            <div class="company-details" style="text-align: center;">
                <h1>Ultimate POS</h1>
                <p>#101(2nd floor), St.598, Phnom Penh Tmey, Sensok Phnom Penh</p>
                <p>Tel : +855 69 64 00 00 / +855 99 46 72 72</p>
                <p style="margin-top: 10px; font-size: 13px; font-weight: bold; font-family: Arial, Helvetica, sans-serif; color: #000;">
                    Reference No: {{ $session->reference_no }}
                </p>
            </div>
        </div>

        @php
            $is_admin = auth()->user()->hasRole('Admin#' . session()->get('user.business_id')) || auth()->user()->can('superadmin');
            $can_print_all = $is_admin || auth()->user()->can('stock_count.print_all');
            $is_blind = $session->blind_count || auth()->user()->can('stock_count.print_blind');
            if ($can_print_all) {
                $is_blind = false;
            }
        @endphp

        <!-- Session Info Metadata -->
        <div class="print-metadata-block">
            <table class="metadata-table">
                <tr>
                    <td class="meta-label">Location:</td>
                    <td class="meta-value">{{ $session->location->name ?? '' }}</td>
                    <td class="meta-label">Added By:</td>
                    <td class="meta-value">{{ $session->creator->user_full_name ?? '' }}</td>
                </tr>
                <tr>
                    <td class="meta-label">Blind Count Mode:</td>
                    <td class="meta-value">{{ $is_blind ? 'Yes' : 'No' }}</td>
                    <td class="meta-label">Created At:</td>
                    <td class="meta-value">{{ @format_datetime($session->created_at) }}</td>
                </tr>
            </table>
        </div>

        <!-- Double Blue Line Divider -->
        <table class="divider-table">
            <tr>
                <td class="divider-line" style="width: 25%;"></td>
                <td class="divider-text" style="width: 50%;">Stock Count {{ $is_blind ? '(Blind Worksheet)' : '(Full Worksheet)' }}</td>
                <td class="divider-line" style="width: 25%;"></td>
            </tr>
        </table>

        <!-- Print Table -->
        <table class="print-table">
            <thead>
                <tr>
                    <th style="width: 4%; text-align: center;">No</th>
                    <th style="width: {{ $is_blind ? '70%' : '32%' }};">Description</th>
                    @if(!$is_blind)
                        <th style="width: 11%; text-align: right;">Cost Price</th>
                        <th style="width: 9%; text-align: right;">Expected</th>
                    @endif
                    <th style="width: {{ $is_blind ? '25%' : '9%' }}; text-align: right;">Counted</th>
                    @if(!$is_blind)
                        <th style="width: 10%; text-align: right;">Difference</th>
                        <th style="width: 13%; text-align: right;">Financial Impact</th>
                        <th style="width: 12%; text-align: right;">Total Cost</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @php
                    $sum_expected = 0;
                    $sum_counted = 0;
                    $sum_diff = 0;
                    $sum_financial_impact = 0;
                    $sum_total_cost = 0;
                @endphp
                @foreach($lines as $line)
                    @php
                        $expected = (float)$line->book_quantity;
                        $has_counted = $line->counted_by !== null;
                        $counted = $has_counted ? (float)$line->counted_quantity : 0;
                        $diff = $has_counted ? ($counted - $expected) : 0;
                        $unit_price = (float)($line->unit_price ?? $line->variation->default_purchase_price ?? 0);
                        $financial_impact = $diff * $unit_price;
                        $total_cost = ($has_counted ? $counted : $expected) * $unit_price;

                        $sum_expected += $expected;
                        if ($has_counted) {
                            $sum_counted += $counted;
                            $sum_diff += $diff;
                            $sum_financial_impact += $financial_impact;
                        }
                        $sum_total_cost += $total_cost;
                    @endphp
                    <tr>
                        <td style="text-align: center;">{{ $loop->iteration }}</td>
                        <td>
                            @if(!empty($line->variation->sub_sku))
                                {{ $line->variation->sub_sku }} - 
                            @endif
                            <strong>{{ $line->product->name ?? '' }}</strong>
                            @if(!empty($line->variation->name) && $line->variation->name !== 'DUMMY')
                                <small style="color: #555;">({{ $line->variation->name }})</small>
                            @endif
                        </td>
                        @if(!$is_blind)
                            <td style="text-align: right;">{{ @num_format($unit_price) }}</td>
                            <td style="text-align: right;">{{ number_format($expected, 2) }}</td>
                        @endif
                        <td style="text-align: right;">{{ $has_counted ? number_format($counted, 2) : '' }}</td>
                        @if(!$is_blind)
                            <td style="text-align: right;">
                                @if($has_counted)
                                    <span style="{{ $diff < 0 ? 'color: #c53030; font-weight: bold;' : ($diff > 0 ? 'color: #2f855a; font-weight: bold;' : '') }}">
                                        {{ $diff > 0 ? '+' : '' }}{{ number_format($diff, 2) }}
                                    </span>
                                @endif
                            </td>
                            <td style="text-align: right;">
                                @if($has_counted)
                                    <span style="{{ $financial_impact < 0 ? 'color: #c53030; font-weight: bold;' : ($financial_impact > 0 ? 'color: #2f855a; font-weight: bold;' : '') }}">
                                        {{ $financial_impact > 0 ? '+' : '' }}{{ @num_format($financial_impact) }}
                                    </span>
                                @endif
                            </td>
                            <td style="text-align: right; font-weight: 600;">
                                {{ @num_format($total_cost) }}
                            </td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="background-color: #f7fafc; font-weight: bold; border-top: 2px solid #2b6cb0;">
                    <td colspan="2" style="text-align: right;">Total Summary:</td>
                    @if(!$is_blind)
                        <td style="text-align: right;">-</td>
                        <td style="text-align: right;">{{ number_format($sum_expected, 2) }}</td>
                    @endif
                    <td style="text-align: right;">{{ number_format($sum_counted, 2) }}</td>
                    @if(!$is_blind)
                        <td style="text-align: right;">
                            <span style="{{ $sum_diff < 0 ? 'color: #c53030;' : ($sum_diff > 0 ? 'color: #2f855a;' : '') }}">
                                {{ $sum_diff > 0 ? '+' : '' }}{{ number_format($sum_diff, 2) }}
                            </span>
                        </td>
                        <td style="text-align: right;">
                            <span style="{{ $sum_financial_impact < 0 ? 'color: #c53030;' : ($sum_financial_impact > 0 ? 'color: #2f855a;' : '') }}">
                                {{ $sum_financial_impact > 0 ? '+' : '' }}{{ @num_format($sum_financial_impact) }}
                            </span>
                        </td>
                        <td style="text-align: right; color: #2b6cb0;">
                            {{ @num_format($sum_total_cost) }}
                        </td>
                    @endif
                </tr>
            </tfoot>
        </table>

        <!-- Signatures Footer -->
        <table style="width: 100%; margin-top: 50px; border-collapse: collapse; page-break-inside: avoid;">
            <tr>
                <td style="width: 30%; text-align: center; vertical-align: top;">
                    <div style="font-weight: bold; font-size: 11px;">Stock Keeper Signature</div>
                    <div style="height: 55px; line-height: 55px;">&nbsp;</div>
                    <hr style="border: none; border-top: 1.5px solid #000; width: 85%; margin: 0 auto;" />
                </td>
                <td style="width: 5%;"></td>
                <td style="width: 30%; text-align: center; vertical-align: top;">
                    <div style="font-weight: bold; font-size: 11px;">Counter Signature</div>
                    <div style="height: 55px; line-height: 55px;">&nbsp;</div>
                    <hr style="border: none; border-top: 1.5px solid #000; width: 85%; margin: 0 auto;" />
                </td>
                <td style="width: 5%;"></td>
                <td style="width: 30%; text-align: center; vertical-align: top;">
                    <div style="font-weight: bold; font-size: 11px;">Preparer Signature</div>
                    <div style="height: 55px; line-height: 55px;">&nbsp;</div>
                    <hr style="border: none; border-top: 1.5px solid #000; width: 85%; margin: 0 auto;" />
                </td>
            </tr>
        </table>
    </div>

    @if(empty($for_pdf) && !request()->ajax())
    <!-- Auto-trigger print dialog for direct page loads -->
    <script>
        window.addEventListener('DOMContentLoaded', () => {
            setTimeout(() => {
                window.print();
            }, 500);
        });
    </script>
    @endif
</body>
</html>
