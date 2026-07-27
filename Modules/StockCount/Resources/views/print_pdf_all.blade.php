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
            font-size: 11px;
            line-height: 1.4;
            max-width: 1000px;
            margin: 0 auto;
            padding: 15px;
        }

        .company-details {
            text-align: center;
            margin-bottom: 15px;
        }

        .company-details h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 800;
            color: #000;
            letter-spacing: 0.5px;
        }

        .company-details p {
            margin: 3px 0 1px 0;
            font-size: 10px;
            font-weight: bold;
            color: #000;
        }

        .metadata-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 11px;
        }

        .metadata-table td {
            padding: 4px 0;
            font-size: 11px;
            color: #000;
            border: none !important;
        }

        .divider-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }

        .divider-line {
            border-bottom: 3px double #2b82c9;
            height: 1px;
        }

        .divider-text {
            font-size: 15px;
            font-weight: bold;
            color: #000;
            text-align: center;
            white-space: nowrap;
            padding: 0 15px;
        }

        .print-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 11px;
        }

        .print-table th {
            background-color: #2b82c9 !important;
            color: #ffffff !important;
            font-weight: bold;
            padding: 8px 6px;
            border: 1px solid #1a202c;
            text-align: center;
        }

        .print-table td {
            padding: 7px 6px;
            border: 1px solid #1a202c;
            color: #000;
        }

        .signature-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 50px;
            font-size: 11px;
            font-weight: bold;
            page-break-inside: avoid;
        }

        .signature-table td {
            text-align: center;
            vertical-align: bottom;
            border: none !important;
        }

        .signature-line {
            border-top: 1.5px solid #000;
            width: 80%;
            margin: 40px auto 0 auto;
        }

        @if(empty($for_pdf))
        @media print {
            @page {
                size: portrait;
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
            .no-print {
                display: none !important;
            }
            .print-table th {
                background-color: #2b82c9 !important;
                color: #ffffff !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
        @endif
    </style>
</head>
<body>
    <div id="stock_count_print_template">
        @if(empty($for_pdf))
        <!-- Floating print button for screen view -->
        <div style="text-align: right; margin-bottom: 15px;" class="no-print">
            <button onclick="window.print();" style="padding: 8px 18px; font-weight: bold; cursor: pointer; background: #2b82c9; color: #fff; border: none; border-radius: 4px; box-shadow: 0 2px 4px rgba(0,0,0,0.15);">
                Print Report
            </button>
        </div>
        @endif

        <!-- Company Header -->
        <div class="company-details">
            <h1>{{ $business->name ?? 'Ultimate POS' }}</h1>
            <p>#101(2nd floor), St.598, Phnom Penh Tmey, Sensok Phnom Penh</p>
            <p>Tel : +855 69 64 00 00 / +855 99 46 72 72</p>
            <p style="margin-top: 8px; font-size: 12px; font-weight: bold; color: #000;">
                Reference No: All Sessions Report
            </p>
        </div>

        <!-- Session Info Metadata -->
        <table class="metadata-table">
            <tr>
                <td style="font-weight: bold; width: 15%;">Location:</td>
                <td style="width: 35%; text-align: right; padding-right: 20px;">{{ $location_name }}</td>
                <td style="font-weight: bold; width: 15%;">Printed By:</td>
                <td style="width: 35%; text-align: right;">{{ auth()->user()->user_full_name ?? auth()->user()->username }}</td>
            </tr>
            <tr>
                <td style="font-weight: bold; width: 15%;">Total Sessions:</td>
                <td style="width: 35%; text-align: right; padding-right: 20px;">{{ count($sessions) }}</td>
                <td style="font-weight: bold; width: 15%;">Created At:</td>
                <td style="width: 35%; text-align: right;">{{ date('m/d/Y H:i') }}</td>
            </tr>
        </table>

        <!-- Double Blue Line Divider -->
        <table class="divider-table">
            <tr>
                <td class="divider-line" style="width: 25%;"></td>
                <td class="divider-text" style="width: 50%;">Stock Count (All Sessions Report)</td>
                <td class="divider-line" style="width: 25%;"></td>
            </tr>
        </table>

        <!-- Main Data Table -->
        <table class="print-table">
            <thead>
                <tr>
                    <th style="width: 5%; text-align: center;">No</th>
                    <th style="width: 15%; text-align: center;">Reference No</th>
                    <th style="width: 22%; text-align: left;">Session Name</th>
                    <th style="width: 15%; text-align: left;">Location</th>
                    <th style="width: 11%; text-align: center;">Status</th>
                    <th style="width: 10%; text-align: center;">Completion</th>
                    <th style="width: 7%; text-align: right;">Total Items</th>
                    <th style="width: 7%; text-align: right;">Counted</th>
                    <th style="width: 8%; text-align: left;">Added By</th>
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
