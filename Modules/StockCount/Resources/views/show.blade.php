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
        box-shadow: 0 2px 5px rgba(0,0,0,0.12);
    }
    .action-btn-group .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 14px rgba(0,0,0,0.18);
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
    .btn-worksheet-custom:hover { color: #fff; }
    .btn-export-custom {
        background: linear-gradient(135deg, #1d9e6f, #158a5e);
        color: #fff;
        border: none;
    }
    .btn-export-custom:hover { color: #fff; }
    .btn-reconcile-custom {
        background: linear-gradient(135deg, #e74c3c, #c0392b);
        color: #fff;
        border: none;
    }
    .btn-reconcile-custom:hover { color: #fff; }
</style>
@endsection

@section('content')
    <section class="content-header no-print">
        <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">
            @lang('stockcount::lang.stock_count_session'): {{ $session->name }}
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
                    <div class="col-sm-3">
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
                    <div class="col-sm-3">
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
@endsection

@section('javascript')
    <script>
        $(document).ready(function () {
            $('#variance_table').DataTable({
                pageLength: 25,
                dom: 'frtip'
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