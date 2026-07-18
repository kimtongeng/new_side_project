@extends('layouts.app')
@section('title', 'Worksheet Comparison Report')

@section('css')
    <style>
        .compare-header-box {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            border: 1px solid #eaeaea;
            padding: 24px;
            margin-bottom: 30px;
        }

        .compare-info-card {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px 20px;
            border-left: 4px solid #1a73e8;
            height: 100%;
        }

        .compare-info-card.card-second {
            border-left-color: #27ae60;
        }

        .compare-title {
            font-size: 16px;
            font-weight: 700;
            color: #2c3e50;
            margin-top: 0;
            margin-bottom: 10px;
        }

        .table-compare th {
            background-color: #f1f2f6 !important;
            color: #2c3e50 !important;
            font-weight: bold !important;
            text-align: center;
        }

        .badge-diff {
            font-size: 13px;
            padding: 5px 10px;
            border-radius: 4px;
            font-weight: bold;
        }

        .badge-match {
            background-color: #d4edda;
            color: #155724;
        }

        .badge-mismatch {
            background-color: #f8d7da;
            color: #721c24;
        }

        .btn-export-custom {
            background: #1a73e8;
            color: #fff;
            font-weight: bold;
            border-radius: 30px;
            padding: 8px 20px;
            border: none;
            box-shadow: 0 4px 10px rgba(26, 115, 232, 0.2);
            transition: all 0.2s;
        }

        .btn-export-custom:hover {
            background: #1557b0;
            color: #fff;
            transform: translateY(-1px);
        }

        .btn-back-custom {
            background: #fff;
            color: #555;
            font-weight: bold;
            border-radius: 30px;
            padding: 8px 20px;
            border: 1px solid #ccc;
            transition: all 0.2s;
        }

        .btn-back-custom:hover {
            background: #f1f2f6;
            color: #333;
        }

        @media print {
            .no-print {
                display: none !important;
            }
            .compare-header-box {
                box-shadow: none !important;
                border: none !important;
                padding: 0 !important;
            }
            .compare-info-card {
                border: 1px solid #ccc !important;
                background: #fff !important;
            }
            body {
                background: #fff !important;
            }
        }
    </style>
@endsection

@section('content')
    <section class="content-header no-print">
        <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">Worksheet Comparison Report</h1>
    </section>

    <section class="content">
        
        <!-- Header Info & Comparison Controls -->
        <div class="compare-header-box">
            <div class="row no-print" style="margin-bottom: 20px;">
                <div class="col-md-12" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                    <div>
                        <a href="{{ action([\Modules\StockCount\Http\Controllers\StockCountController::class, 'index']) }}" class="btn btn-back-custom">
                            <i class="fa fa-arrow-left"></i> Back to Sessions
                        </a>
                    </div>
                    <div>
                        <button type="button" onclick="window.print()" class="btn btn-export-custom">
                            <i class="fa fa-print"></i> Print Comparison
                        </button>
                    </div>
                </div>
            </div>

            <!-- Side by Side Sessions summary -->
            <div class="row">
                <div class="col-md-6" style="margin-bottom: 15px;">
                    <div class="compare-info-card">
                        <h3 class="compare-title"><i class="fa fa-clipboard-list text-primary"></i> Count Session A</h3>
                        <table class="table table-condensed" style="margin-bottom: 0; background: transparent;">
                            <tr>
                                <th style="width: 35%; border: none; text-align: left; background: transparent; padding: 4px 0;">Session Name:</th>
                                <td style="border: none; padding: 4px 0;">{{ $session_1->name }}</td>
                            </tr>
                            <tr>
                                <th style="border: none; text-align: left; background: transparent; padding: 4px 0;">Reference No:</th>
                                <td style="border: none; padding: 4px 0;">{{ $session_1->reference_no ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th style="border: none; text-align: left; background: transparent; padding: 4px 0;">Location:</th>
                                <td style="border: none; padding: 4px 0;">{{ $session_1->location->name ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th style="border: none; text-align: left; background: transparent; padding: 4px 0;">Created By:</th>
                                <td style="border: none; padding: 4px 0;">{{ $session_1->creator->first_name ?? '' }} {{ $session_1->creator->last_name ?? '' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="col-md-6" style="margin-bottom: 15px;">
                    <div class="compare-info-card card-second">
                        <h3 class="compare-title"><i class="fa fa-clipboard-list text-success"></i> Count Session B</h3>
                        <table class="table table-condensed" style="margin-bottom: 0; background: transparent;">
                            <tr>
                                <th style="width: 35%; border: none; text-align: left; background: transparent; padding: 4px 0;">Session Name:</th>
                                <td style="border: none; padding: 4px 0;">{{ $session_2->name }}</td>
                            </tr>
                            <tr>
                                <th style="border: none; text-align: left; background: transparent; padding: 4px 0;">Reference No:</th>
                                <td style="border: none; padding: 4px 0;">{{ $session_2->reference_no ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th style="border: none; text-align: left; background: transparent; padding: 4px 0;">Location:</th>
                                <td style="border: none; padding: 4px 0;">{{ $session_2->location->name ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th style="border: none; text-align: left; background: transparent; padding: 4px 0;">Created By:</th>
                                <td style="border: none; padding: 4px 0;">{{ $session_2->creator->first_name ?? '' }} {{ $session_2->creator->last_name ?? '' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Comparison Table Widget -->
        @component('components.widget', ['class' => 'box-primary', 'title' => 'Discrepancy Details'])
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-compare" style="width: 100%;">
                    <thead>
                        <tr>
                            <th style="width: 5%;">#</th>
                            <th style="text-align: left;">Product Name</th>
                            <th style="width: 15%;">SKU</th>
                            <th style="width: 15%;">Count Qty (A)</th>
                            <th style="width: 15%;">Count Qty (B)</th>
                            <th style="width: 15%;">Difference</th>
                            <th style="width: 10%;">Unit</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($comparison as $index => $row)
                            <tr>
                                <td style="text-align: center;">{{ $index + 1 }}</td>
                                <td>{{ $row['product_name'] }}</td>
                                <td>{{ $row['sub_sku'] }}</td>
                                <td style="text-align: right;">{{ number_format($row['qty_1'], 4) }}</td>
                                <td style="text-align: right;">{{ number_format($row['qty_2'], 4) }}</td>
                                <td style="text-align: center;">
                                    @if($row['diff'] == 0)
                                        <span class="badge-diff badge-match">0.0000</span>
                                    @else
                                        <span class="badge-diff badge-mismatch">
                                            {{ $row['diff'] > 0 ? '+' : '' }}{{ number_format($row['diff'], 4) }}
                                        </span>
                                    @endif
                                </td>
                                <td>{{ $row['unit'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">No counted items found in either session.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endcomponent

    </section>
@endsection
