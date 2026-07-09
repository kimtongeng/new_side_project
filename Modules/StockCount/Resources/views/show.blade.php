@extends('layouts.app')

@section('title', __('stockcount::lang.stock_count_session') . ' - ' . $session->name)

@section('css')
    <style>
        .action-btn-group {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            justify-content: flex-end;
            align-items: center;
        }

        .action-btn-group .btn {
            border-radius: 20px;
            font-weight: 600;
            font-size: 13px;
            padding: 6px 16px;
            letter-spacing: 0.3px;
            transition: all 0.2s ease;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.12);
        }

        .action-btn-group .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 14px rgba(0, 0, 0, 0.18);
        }

        .action-btn-group .btn i {
            margin-right: 5px;
        }

        .btn-back-custom {
            background: #f0f0f0;
            color: #555;
            border: 1px solid #ddd;
        }

        .btn-back-custom:hover {
            background: #e2e2e2;
            color: #333;
        }

        .btn-worksheet-custom {
            background: linear-gradient(135deg, #3a7bd5, #2563b0);
            color: #fff;
            border: none;
        }

        .btn-worksheet-custom:hover {
            color: #fff;
        }

        .btn-export-custom {
            background: linear-gradient(135deg, #1d9e6f, #158a5e);
            color: #fff;
            border: none;
        }

        .btn-export-custom:hover {
            color: #fff;
        }

        .btn-reconcile-custom {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            color: #fff;
            border: none;
        }

        .btn-reconcile-custom:hover {
            color: #fff;
        }

        .dt-buttons {
            display: none !important;
        }

        /* Hide print template in screen view */
        #stock_count_print_template {
            display: none;
        }

        @media print {
            body.printing-active>*:not(#stock_count_print_template) {
                display: none !important;
            }

            body.printing-active #stock_count_print_template {
                display: block !important;
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                font-family: Arial, Helvetica, sans-serif !important;
                color: #000;
                padding: 10px 0;
                background: #fff;
            }

            .print-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 20px;
            }

            .company-details {
                text-align: center;
                flex-grow: 1;
            }

            .company-details h1 {
                margin: 0;
                font-size: 26px;
                font-weight: 800;
                color: #000;
                letter-spacing: 0.5px;
            }

            .company-details p {
                margin: 5px 0 2px 0;
                font-size: 10px;
                font-weight: bold;
                color: #000;
            }

            .print-divider {
                display: flex;
                align-items: center;
                text-align: center;
                margin: 15px 0 25px 0;
            }

            .print-divider::before,
            .print-divider::after {
                content: '';
                flex: 1;
                border-bottom: 5px double #2b82c9;
            }

            .print-divider::before {
                margin-right: 15px;
            }

            .print-divider::after {
                margin-left: 15px;
            }

            .divider-text {
                font-size: 16px;
                font-weight: bold;
                color: #000;
                white-space: nowrap;
            }

            .print-metadata-block {
                margin: 10px 0 20px 0;
                font-family: Arial, Helvetica, sans-serif !important;
            }

            .metadata-table {
                width: 100%;
                border-collapse: collapse;
                border: none !important;
            }

            .metadata-table td {
                padding: 5px 0;
                border: none !important;
                font-size: 11px;
                color: #000;
            }

            .metadata-table td.meta-label {
                font-weight: bold;
                width: 18%;
            }

            .metadata-table td.meta-value {
                width: 32%;
                text-align: right;
                padding-right: 20px;
            }

            .print-table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 15px;
                font-size: 11px;
            }

            .print-table th {
                background-color: #2b82c9 !important;
                color: #ffffff !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                font-weight: bold;
                padding: 10px 8px;
                border: 1px solid #1a202c;
                text-align: left;
            }

            .print-table td {
                padding: 8px;
                border: 1px solid #1a202c;
                color: #000;
            }

            .signature-container {
                display: flex;
                justify-content: space-between;
                margin-top: 80px;
                font-size: 11px;
                font-weight: bold;
                text-align: center;
                page-break-inside: avoid;
            }

            .signature-block {
                width: 28%;
            }

            .signature-line {
                border-top: 1.5px solid #000;
                margin-top: 50px;
            }
        }
    </style>
@endsection

@section('content')
    <section class="content-header no-print">
        <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">
            @lang('stockcount::lang.stock_count_session'): {{ $session->name }}
            <small class="text-muted" style="font-size: 16px; margin-left: 10px;">Ref No:
                {{ $session->reference_no }}</small>
            <span
                class="label @if($session->status == 'completed') bg-green @elseif($session->status == 'active') bg-blue @else bg-gray @endif font-size-17">
                {{ __('stockcount::lang.' . $session->status) }}
            </span>
        </h1>
    </section>

    <section class="content">
        <div class="row no-print">
            <div class="col-md-12">
                @component('components.widget', ['class' => 'box-solid'])
                <div class="row">
                    <div class="col-sm-2">
                        <strong>@lang('stockcount::lang.location'): </strong> {{ $session->location->name ?? '' }}<br>
                        <strong>@lang('stockcount::lang.blind_count'): </strong>
                        {{ $session->blind_count ? __('messages.yes') : __('messages.no') }}
                    </div>
                    <div class="col-sm-3">
                        <strong>Added By: </strong> {{ $session->creator->user_full_name ?? '' }}<br>
                        <strong>Created At: </strong> {{ @format_datetime($session->created_at) }}
                    </div>
                    <div class="col-sm-3">
                        @if($session->status === 'completed')
                            <strong>Reconciled By: </strong> {{ $session->completer->user_full_name ?? '' }}<br>
                            <strong>Reconciled At: </strong> {{ @format_datetime($session->completed_at) }}
                        @endif
                    </div>
                    <div class="col-sm-4">
                        <div class="action-btn-group" style="margin-top: 6px;">

                            <a href="{{ action([\Modules\StockCount\Http\Controllers\StockCountController::class, 'index']) }}"
                                class="btn btn-back-custom">
                                <i class="fa fa-arrow-left"></i> Back
                            </a>

                            @if($session->status === 'active' && auth()->user()->can('stock_count.count'))
                                <a href="{{ action([\Modules\StockCount\Http\Controllers\StockCountController::class, 'worksheet'], [$session->id]) }}"
                                    class="btn btn-worksheet-custom">
                                    <i class="fa fa-edit"></i> @lang('stockcount::lang.worksheet')
                                </a>
                            @endif

                            @if(auth()->user()->can('stock_count.export'))
                                <a href="{{ action([\Modules\StockCount\Http\Controllers\StockCountController::class, 'export'], [$session->id]) }}"
                                    class="btn btn-export-custom">
                                    <i class="fa fa-download"></i> Export Excel
                                </a>
                                <button type="button" id="pdf_variance_report" class="btn btn-export-custom">
                                    <i class="fa fa-file-pdf"></i> Export PDF
                                </button>
                                <button type="button" id="print_variance_report" class="btn btn-back-custom">
                                    <i class="fa fa-print"></i> Print
                                </button>
                            @endif

                            @if($session->status === 'active' && auth()->user()->can('stock_count.reconcile'))
                                {!! Form::open(['url' => action([\Modules\StockCount\Http\Controllers\StockCountController::class, 'reconcile'], [$session->id]), 'method' => 'post', 'class' => 'inline-form', 'id' => 'reconcile_form']) !!}
                                <button type="submit" class="btn btn-reconcile-custom btn-reconcile">
                                    <i class="fa fa-check-circle"></i> @lang('stockcount::lang.reconcile')
                                </button>
                                {!! Form::close() !!}
                            @endif

                        </div>
                    </div>
                </div>
                @endcomponent
            </div>
        </div>

        <!-- Filters -->
        {!! Form::open(['url' => action([\Modules\StockCount\Http\Controllers\StockCountController::class, 'show'], [$session->id]), 'method' => 'get', 'id' => 'variance_filter_form']) !!}
        <div class="row no-print">
            <div class="col-md-12">
                @component('components.filters', ['title' => __('report.filters')])
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('category_id', __('product.category') . ':') !!}
                        {!! Form::select('category_id', $categories, request()->get('category_id'), ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')]) !!}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('brand_id', __('product.brand') . ':') !!}
                        {!! Form::select('brand_id', $brands, request()->get('brand_id'), ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')]) !!}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('variance_type', __('stockcount::lang.variance_type') . ':') !!}
                        {!! Form::select('variance_type', [
        'all' => __('stockcount::lang.all'),
        'variance' => __('stockcount::lang.only_variance'),
        'shortage' => __('stockcount::lang.shortage'),
        'surplus' => __('stockcount::lang.surplus'),
        'no_variance' => __('stockcount::lang.no_variance')
    ], request()->get('variance_type') ?? 'all', ['class' => 'form-control select2', 'style' => 'width:100%']) !!}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <div style="display: flex; gap: 8px;">
                            <button type="submit" class="btn btn-primary form-control" style="border-radius: 20px;">
                                <i class="fa fa-filter"></i> @lang('stockcount::lang.filter')
                            </button>
                            <a href="{{ action([\Modules\StockCount\Http\Controllers\StockCountController::class, 'show'], [$session->id]) }}"
                                class="btn btn-default form-control"
                                style="border-radius: 20px; line-height: 20px; text-align: center;">
                                Reset
                            </a>
                        </div>
                    </div>
                </div>
                @endcomponent
            </div>
        </div>
        {!! Form::close() !!}

        <!-- Summary cards for Variance & Financial Impact -->
        <div class="row">
            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box bg-aqua text-white">
                    <span class="info-box-icon"><i class="fa fa-list"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Items Counted</span>
                        <span class="info-box-number">{{ $summary['total_items'] }}</span>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box bg-red text-white">
                    <span class="info-box-icon"><i class="fa fa-minus-circle"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Shortage Quantity</span>
                        <span class="info-box-number">{{ number_format($summary['shortage_qty'], 2) }}</span>
                        <span class="progress-description text-white">
                            Loss: <span class="display_currency"
                                data-currency_symbol="true">{{ $summary['shortage_value'] }}</span>
                        </span>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box bg-green text-white">
                    <span class="info-box-icon"><i class="fa fa-plus-circle"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Surplus Quantity</span>
                        <span class="info-box-number">{{ number_format($summary['surplus_qty'], 2) }}</span>
                        <span class="progress-description text-white">
                            Gain: <span class="display_currency"
                                data-currency_symbol="true">{{ $summary['surplus_value'] }}</span>
                        </span>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box bg-yellow text-white">
                    <span class="info-box-icon"><i class="fa fa-balance-scale"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Net Financial Impact</span>
                        @php
                            $net_impact = $summary['surplus_value'] - $summary['shortage_value'];
                        @endphp
                        <span class="info-box-number">
                            <span class="display_currency" data-currency_symbol="true">{{ $net_impact }}</span>
                        </span>
                        <span class="progress-description text-white">
                            {{ $net_impact >= 0 ? 'Surplus/Gain' : 'Shortage/Loss' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Count Lines Table -->
        <div class="row">
            <div class="col-md-12">
                @component('components.widget', ['class' => 'box-primary', 'title' => __('stockcount::lang.variance_report')])
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="variance_table" style="width: 100%;">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Product Name</th>
                                <th>SKU</th>
                                <th>@lang('stockcount::lang.book_qty')</th>
                                <th>@lang('stockcount::lang.counted_qty')</th>
                                <th>@lang('stockcount::lang.variance')</th>
                                <th>Cost Price</th>
                                <th>Financial Impact</th>
                                <th>Counted By</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($lines as $line)
                                @php
                                    $variance = $line->counted_quantity - $line->book_quantity;
                                    $financial_diff = $variance * $line->unit_price;
                                @endphp
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>
                                        {{ $line->product->name ?? '' }}
                                        @if(!empty($line->variation->name) && $line->variation->name !== 'DUMMY')
                                            <span class="text-muted">({{ $line->variation->name }})</span>
                                        @endif
                                    </td>
                                    <td>{{ $line->variation->sub_sku ?? '' }}</td>
                                    <td>{{ number_format($line->book_quantity, 2) }}</td>
                                    <td>{{ number_format($line->counted_quantity, 2) }}</td>
                                    <td
                                        class="@if($variance < 0) text-danger @elseif($variance > 0) text-success @endif font-weight-bold">
                                        {{ $variance > 0 ? '+' : '' }}{{ number_format($variance, 2) }}
                                    </td>
                                    <td>
                                        <span class="display_currency"
                                            data-currency_symbol="true">{{ $line->unit_price }}</span>
                                    </td>
                                    <td
                                        class="@if($financial_diff < 0) text-danger @elseif($financial_diff > 0) text-success @endif font-weight-bold">
                                        <span class="display_currency" data-currency_symbol="true">{{ $financial_diff }}</span>
                                    </td>
                                    <td>
                                        {{ $line->counter->user_full_name ?? '' }}<br>
                                        <small class="text-muted">{{ $line->counted_at ? @format_datetime($line->counted_at) :
                                            '' }}</small>
                                    </td>
                                    <td>{{ $line->note }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endcomponent
            </div>
        </div>
    </section>

    <!-- Beautiful Print Template (Hidden on screen, shown only on print) -->
    <div id="stock_count_print_template" class="print-only">
        <!-- Company Header -->
        <div class="print-header" style="justify-content: center;">
            <div class="company-details" style="text-align: center;">
                <h1>Ultimate POS</h1>
                <p>#101(2nd floor), St.598, Phnom Penh Tmey, Sensok Phnom Penh</p>
                <p>Tel : +855 69 64 00 00 / +855 99 46 72 72</p>
                {{-- <p>Email : info@codentech.biz</p> --}}
                <p
                    style="margin-top: 10px; font-size: 13px; font-weight: bold; font-family: Arial, Helvetica, sans-serif; color: #000;">
                    Reference No: {{ $session->reference_no }}
                </p>
            </div>
        </div>
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
                    <td class="meta-value">{{ $session->blind_count ? 'Yes' : 'No' }}</td>
                    <td class="meta-label">Created At:</td>
                    <td class="meta-value">{{ @format_datetime($session->created_at) }}</td>
                </tr>
            </table>
        </div>
        <div class="print-divider">
            <span class="divider-text">Stock Count</span>
        </div>



        <!-- Print Table -->
        <table class="print-table">
            <thead>
                <tr>
                    <th style="width: 5%;">No</th>
                    <th style="width: 65%;">Description</th>
                    <th style="width: 10%; text-align: right;">Expected</th>
                    <th style="width: 10%; text-align: right;">Counted</th>
                    <th style="width: 10%; text-align: right;">Difference</th>
                </tr>
            </thead>
            <tbody>
                <!-- Populated dynamically via JS -->
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
                <p>Preparer Signature</p>
                <div class="signature-line"></div>
            </div>
        </div>
    </div>
@endsection

@section('javascript')
    <script>
        $(document).ready(function () {
            var table = $('#variance_table').DataTable({
                pageLength: 25,
                dom: 'Bfrtip',
                buttons: [
                    {
                        extend: 'pdf',
                        className: 'buttons-pdf hidden',
                        orientation: 'landscape',
                        pageSize: 'A4',
                        exportOptions: {
                            columns: ':visible'
                        },
                        title: 'Stock Count Variance Report - ' + "{{ $session->name }}",
                        customize: function (doc) {
                            // Page margins
                            doc.pageMargins = [30, 30, 30, 30];

                            // Style title
                            if (doc.content[0]) {
                                doc.content[0].fontSize = 18;
                                doc.content[0].bold = true;
                                doc.content[0].alignment = 'center';
                                doc.content[0].margin = [0, 0, 0, 15];
                            }

                            // Subtitle grid with metadata
                            var metadata = {
                                table: {
                                    widths: ['*', '*'],
                                    body: [
                                        [
                                            { text: 'Location: ' + "{{ $session->location->name ?? '' }}", fontSize: 10, bold: true },
                                            { text: 'Added By: ' + "{{ $session->creator->user_full_name ?? '' }}", fontSize: 10 }
                                        ],
                                        [
                                            { text: 'Blind Count Mode: ' + "{{ $session->blind_count ? 'Yes' : 'No' }}", fontSize: 10, bold: true },
                                            { text: 'Created At: ' + "{{ @format_datetime($session->created_at) }}", fontSize: 10 }
                                        ]
                                    ]
                                },
                                layout: 'noBorders',
                                margin: [0, 0, 0, 15]
                            };

                            doc.content.splice(1, 0, metadata);

                            // The main table is now at index 2
                            var tableNode = doc.content[2];
                            if (tableNode && tableNode.table) {
                                // 10 columns: #, Product Name, SKU, Book Qty, Counted Qty, Variance, Cost Price, Financial Impact, Counted By, Notes
                                tableNode.table.widths = ['4%', '20%', '10%', '8%', '8%', '8%', '8%', '10%', '12%', '12%'];

                                // Clean layout with border styling
                                tableNode.layout = {
                                    hLineWidth: function (i, node) { return (i === 0 || i === node.table.body.length) ? 1.5 : 0.5; },
                                    vLineWidth: function (i, node) { return 0.5; },
                                    hLineColor: function (i, node) { return '#cbd5e0'; },
                                    vLineColor: function (i, node) { return '#e2e8f0'; },
                                    paddingLeft: function (i, node) { return 6; },
                                    paddingRight: function (i, node) { return 6; },
                                    paddingTop: function (i, node) { return 6; },
                                    paddingBottom: function (i, node) { return 6; }
                                };

                                // Header row styling
                                var headerRow = tableNode.table.body[0];
                                for (var i = 0; i < headerRow.length; i++) {
                                    headerRow[i].fillColor = '#1a365d'; // Deep blue
                                    headerRow[i].color = '#ffffff';
                                    headerRow[i].bold = true;
                                    headerRow[i].fontSize = 9;
                                    headerRow[i].alignment = 'left';
                                }

                                // Body rows styling
                                for (var r = 1; r < tableNode.table.body.length; r++) {
                                    var row = tableNode.table.body[r];
                                    for (var c = 0; c < row.length; c++) {
                                        row[c].fontSize = 8;
                                        if (r % 2 === 0) {
                                            row[c].fillColor = '#f7fafc'; // Zebra crossing
                                        }

                                        // Align numbers to right
                                        if (c === 3 || c === 4 || c === 5 || c === 6 || c === 7) {
                                            row[c].alignment = 'right';
                                        }

                                        // Variance text highlighting (col 5)
                                        if (c === 5) {
                                            var text = row[c].text.trim();
                                            if (text.indexOf('+') === 0) {
                                                row[c].color = '#2f855a'; // dark green
                                                row[c].bold = true;
                                            } else if (text.indexOf('-') === 0) {
                                                row[c].color = '#c53030'; // dark red
                                                row[c].bold = true;
                                            }
                                        }

                                        // Financial impact highlighting (col 7)
                                        if (c === 7) {
                                            var fText = row[c].text.trim();
                                            if (fText.indexOf('-') === 0) {
                                                row[c].color = '#c53030';
                                                row[c].bold = true;
                                            } else if (fText.indexOf('+') === 0 || parseFloat(fText.replace(/[^0-9.-]+/g, "")) > 0) {
                                                row[c].color = '#2f855a';
                                                row[c].bold = true;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    },
                    {
                        extend: 'print',
                        className: 'buttons-print hidden',
                        exportOptions: {
                            columns: ':visible'
                        },
                        title: 'Stock Count Variance Report - ' + "{{ $session->name }}",
                        customize: function (win) {
                            $(win.document.body).css({
                                'font-family': '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif',
                                'color': '#2d3748',
                                'padding': '20px'
                            });

                            $(win.document.body).find('h1').css({
                                'text-align': 'center',
                                'font-size': '22px',
                                'font-weight': 'bold',
                                'color': '#1a365d',
                                'margin-bottom': '20px'
                            });

                            // Session Info Header Block
                            var metadataHtml = `
                                         <div style="display: flex; justify-content: space-between; border-bottom: 2px solid #cbd5e0; padding-bottom: 12px; margin-bottom: 20px; font-size: 13px; font-family: sans-serif;">
                                             <div>
                                                 <strong>Location:</strong> {{ $session->location->name ?? '' }}<br>
                                                 <strong>Blind Count Mode:</strong> {{ $session->blind_count ? 'Yes' : 'No' }}
                                             </div>
                                             <div>
                                                 <strong>Added By:</strong> {{ $session->creator->user_full_name ?? '' }}<br>
                                                 <strong>Created At:</strong> {{ @format_datetime($session->created_at) }}
                                             </div>
                                         </div>
                                     `;
                            $(metadataHtml).insertBefore($(win.document.body).find('table'));

                            // Table Styling
                            var $table = $(win.document.body).find('table');
                            $table.removeClass('table-bordered table-striped');
                            $table.css({
                                'width': '100%',
                                'border-collapse': 'collapse',
                                'font-size': '11px',
                                'margin-top': '10px'
                            });

                            // Table Header styling
                            $table.find('thead th').css({
                                'background-color': '#1a365d',
                                'color': '#ffffff',
                                'font-weight': 'bold',
                                'padding': '8px 6px',
                                'border-bottom': '2px solid #cbd5e0',
                                'border-top': 'none',
                                'border-left': 'none',
                                'border-right': 'none',
                                'text-align': 'left'
                            });

                            $table.find('tbody tr').each(function (idx) {
                                // zebra striping
                                if (idx % 2 === 1) {
                                    $(this).css('background-color', '#f7fafc');
                                }

                                $(this).find('td').css({
                                    'padding': '6px 6px',
                                    'border-bottom': '1px solid #e2e8f0',
                                    'border-top': 'none',
                                    'border-left': 'none',
                                    'border-right': 'none'
                                });

                                // Right align numeric columns
                                $(this).find('td').eq(3).css('text-align', 'right'); // book qty
                                $(this).find('td').eq(4).css('text-align', 'right'); // counted qty
                                $(this).find('td').eq(5).css('text-align', 'right'); // variance
                                $(this).find('td').eq(6).css('text-align', 'right'); // cost price
                                $(this).find('td').eq(7).css('text-align', 'right'); // financial impact

                                // Variance Coloring
                                var $varCell = $(this).find('td').eq(5);
                                var varVal = $varCell.text().trim();
                                if (varVal.indexOf('+') === 0) {
                                    $varCell.css({ 'color': '#2f855a', 'font-weight': 'bold' });
                                } else if (varVal.indexOf('-') === 0) {
                                    $varCell.css({ 'color': '#c53030', 'font-weight': 'bold' });
                                }

                                // Financial Impact Coloring
                                var $impactCell = $(this).find('td').eq(7);
                                var impactVal = $impactCell.text().trim();
                                if (impactVal.indexOf('-') === 0) {
                                    $impactCell.css({ 'color': '#c53030', 'font-weight': 'bold' });
                                } else if (impactVal.indexOf('+') === 0 || parseFloat(impactVal.replace(/[^0-9.-]+/g, "")) > 0) {
                                    $impactCell.css({ 'color': '#2f855a', 'font-weight': 'bold' });
                                }
                            });
                        }
                    }
                ]
            });

            function populatePrintTable() {
                var printTbody = $('#stock_count_print_template .print-table tbody');
                printTbody.empty();

                var rows = table.rows({ filter: 'applied' }).nodes();

                $(rows).each(function (index, el) {
                    var $row = $(el);

                    var no = index + 1;
                    var nameText = $row.find('td').eq(1).text().trim().replace(/\s+/g, ' ');
                    var sku = $row.find('td').eq(2).text().trim();
                    var bookQty = $row.find('td').eq(3).text().trim();
                    var countedQty = $row.find('td').eq(4).text().trim();
                    var variance = $row.find('td').eq(5).text().trim();

                    var description = sku + ' - ' + nameText;

                    var tr = $('<tr></tr>');
                    tr.append('<td style="text-align: center;">' + no + '</td>');
                    tr.append('<td>' + description + '</td>');
                    tr.append('<td style="text-align: right;">' + bookQty + '</td>');
                    tr.append('<td style="text-align: right;">' + countedQty + '</td>');
                    tr.append('<td style="text-align: right;">' + variance + '</td>');

                    printTbody.append(tr);
                });
            }

            $(document).on('click', '#print_variance_report, #pdf_variance_report', function (e) {
                e.preventDefault();
                populatePrintTable();

                // Move template directly under body so it survives display: none on wrapper
                if (!$('body > #stock_count_print_template').length) {
                    $('body').append($('#stock_count_print_template'));
                }

                $('body').addClass('printing-active');

                window.print();

                // Restore screen display after printing dialog closes
                setTimeout(function () {
                    $('body').removeClass('printing-active');
                }, 1000);
            });

            $(document).on('click', '.btn-reconcile', function (e) {
                e.preventDefault();
                swal({
                    title: "Are you sure you want to reconcile?",
                    text: "This will finalize counts and adjust live inventory in the system! You cannot revert this action.",
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                }).then((willReconcile) => {
                    if (willReconcile) {
                        $('#reconcile_form').submit();
                    }
                });
            });
        });
    </script>
@endsection