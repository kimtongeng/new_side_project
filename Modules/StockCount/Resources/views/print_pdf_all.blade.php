<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock Count (All Sessions Report)</title>
    <style>
        #stock_count_print_template {
            font-family: Arial, Helvetica, sans-serif !important;
            color: #000;
            background: #fff;
            font-size: 12px;
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
            margin: 15px 0 20px 0;
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

        #stock_count_print_template .print-divider {
            display: flex;
            align-items: center;
            text-align: center;
            margin: 15px 0 25px 0;
        }

        #stock_count_print_template .print-divider::before,
        #stock_count_print_template .print-divider::after {
            content: '';
            flex: 1;
            border-bottom: 5px double #2b82c9;
        }

        #stock_count_print_template .print-divider::before {
            margin-right: 15px;
        }

        #stock_count_print_template .print-divider::after {
            margin-left: 15px;
        }

        #stock_count_print_template .divider-text {
            font-size: 16px;
            font-weight: bold;
            color: #000;
            white-space: nowrap;
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
            text-align: center;
        }

        #stock_count_print_template .print-table td {
            padding: 8px 6px;
            border: 1px solid #1a202c;
            color: #000;
        }

        #stock_count_print_template .signature-container {
            display: flex;
            justify-content: space-between;
            margin-top: 80px;
            font-size: 11px;
            font-weight: bold;
            text-align: center;
            page-break-inside: avoid;
        }

        #stock_count_print_template .signature-block {
            width: 28%;
        }

        #stock_count_print_template .signature-line {
            border-top: 1.5px solid #000;
            margin-top: 50px;
        }

        @media print {
            @page {
                size: A4 landscape;
                margin: 6mm;
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
        }
    </style>
</head>
<body>
    <div id="stock_count_print_template">
        <!-- Floating print button for screen view -->
        <div style="text-align: right; margin-bottom: 20px;" class="no-print">
            <button onclick="window.print();" style="padding: 8px 18px; font-weight: bold; cursor: pointer; background: #2b82c9; color: #fff; border: none; border-radius: 4px; box-shadow: 0 2px 4px rgba(0,0,0,0.15);">
                Print Report
            </button>
        </div>

        <!-- Company Header -->
        <div class="print-header" style="justify-content: center;">
            <div class="company-details" style="text-align: center;">
                <h1>{{ $business->name ?? 'Ultimate POS' }}</h1>
                <p>#101(2nd floor), St.598, Phnom Penh Tmey, Sensok Phnom Penh</p>
                <p>Tel : +855 69 64 00 00 / +855 99 46 72 72</p>
                <p style="margin-top: 10px; font-size: 13px; font-weight: bold; font-family: Arial, Helvetica, sans-serif; color: #000;">
                    Reference No: All Sessions Report
                </p>
            </div>
        </div>

        <!-- Session Info Metadata -->
        <div class="print-metadata-block">
            <table class="metadata-table">
                <tr>
                    <td class="meta-label">Location:</td>
                    <td class="meta-value">{{ $location_name }}</td>
                    <td class="meta-label">Printed By:</td>
                    <td class="meta-value">{{ auth()->user()->user_full_name ?? auth()->user()->username }}</td>
                </tr>
                <tr>
                    <td class="meta-label">Total Sessions:</td>
                    <td class="meta-value">{{ count($sessions) }}</td>
                    <td class="meta-label">Created At:</td>
                    <td class="meta-value">{{ date('m/d/Y H:i') }}</td>
                </tr>
            </table>
        </div>

        <!-- Double Blue Line Divider -->
        <div class="print-divider">
            <span class="divider-text">Stock Count (All Sessions Report)</span>
        </div>

        <!-- Main Data Table -->
        <table class="print-table">
            <thead>
                <tr>
                    <th style="width: 4%; text-align: center;">No</th>
                    <th style="width: 12%; text-align: center;">Reference No</th>
                    <th style="width: 22%; text-align: left;">Session Name</th>
                    <th style="width: 14%; text-align: left;">Location</th>
                    <th style="width: 10%; text-align: center;">Status</th>
                    <th style="width: 10%; text-align: center;">Completion</th>
                    <th style="width: 8%; text-align: right;">Total Items</th>
                    <th style="width: 8%; text-align: right;">Counted</th>
                    <th style="width: 12%; text-align: left;">Added By</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $sum_total_items = 0;
                    $sum_total_counted = 0;
                @endphp
                @forelse($sessions as $index => $row)
                    @php
                        $total = $row->lines->count();
                        $counted = $row->lines->whereNotNull('counted_by')->count();
                        $percent = $total > 0 ? round(($counted / $total) * 100) : 0;
                        $sum_total_items += $total;
                        $sum_total_counted += $counted;

                        $status_label = 'Pending';
                        if ($row->status === 'completed' || $row->status === 'approved') {
                            $status_label = 'Completed';
                        } elseif ($row->status === 'reconciled' || $row->status === 'reconcile') {
                            $status_label = 'Reconciled';
                        } elseif ($row->status === 'in_progress') {
                            $status_label = 'In Progress';
                        } else {
                            $status_label = ucfirst(str_replace('_', ' ', $row->status));
                        }
                    @endphp
                    <tr>
                        <td style="text-align: center;">{{ $index + 1 }}</td>
                        <td style="text-align: center; font-weight: bold;">{{ $row->reference_no }}</td>
                        <td style="font-weight: bold;">{{ $row->name }}</td>
                        <td>{{ $row->location->name ?? 'N/A' }}</td>
                        <td style="text-align: center; font-weight: bold;">{{ $status_label }}</td>
                        <td style="text-align: center; font-weight: bold;">{{ $percent }}%</td>
                        <td style="text-align: right;">{{ number_format($total) }}</td>
                        <td style="text-align: right;">{{ number_format($counted) }}</td>
                        <td>{{ $row->creator->user_full_name ?? ($row->creator->username ?? '') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" style="text-align: center; padding: 20px; font-weight: bold;">
                            No stock count sessions found matching the selected criteria.
                        </td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr style="background-color: #f7fafc; font-weight: bold; border-top: 2px solid #2b82c9;">
                    <td colspan="6" style="text-align: right; padding-right: 15px;">Total Summary:</td>
                    <td style="text-align: right;">{{ number_format($sum_total_items) }}</td>
                    <td style="text-align: right;">{{ number_format($sum_total_counted) }}</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>

        <!-- Signatures Footer -->
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
                <p>Preparer Signature</p>
                <div class="signature-line"></div>
            </div>
        </div>
    </div>

    @if(!request()->ajax())
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
