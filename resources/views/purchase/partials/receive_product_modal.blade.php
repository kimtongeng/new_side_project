@php
    $total_remaining = 0;
    foreach($transaction->purchase_lines as $line) {
        $total_remaining += ($line->quantity - $line->receipts->sum('quantity'));
    }
@endphp
<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        {!! Form::open(['url' => action([\App\Http\Controllers\PurchaseController::class, 'saveReceiveRecord']), 'method' => 'post', 'id' => 'save_receive_record_form' ]) !!}
        <input type="hidden" name="purchase_id" value="{{ $transaction->id }}">

        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title">Receive Products (Ref No: {{ $transaction->ref_no }})</h4>
        </div>

        <div class="modal-body">
            <div class="row">
                @if(!empty($transaction->contact))
                    <div class="col-md-4 col-sm-6 col-xs-12">
                        <div class="well well-sm">
                            <strong>@lang('purchase.supplier'):</strong> {{ $transaction->contact->full_name_with_business }}<br>
                            <strong>@lang('business.business'): </strong>{{ $transaction->contact->supplier_business_name }}
                        </div>
                    </div>
                @endif
                <div class="col-md-4 col-sm-6 col-xs-12">
                    <div class="well well-sm">
                        <strong>@lang('purchase.ref_no'): </strong>{{ $transaction->ref_no }}<br>
                        @if(!empty($transaction->location))
                            <strong>@lang('purchase.location'): </strong>{{ $transaction->location->name }}
                        @endif
                    </div>
                </div>
                <div class="col-md-4 col-sm-12 col-xs-12">
                    <div class="well well-sm">
                        <strong>@lang('sale.total_amount'): </strong><span class="display_currency" data-currency_symbol="true">{{ $transaction->final_total }}</span><br>
                        <strong>@lang('purchase.payment_status'): </strong>{{ __('lang_v1.' . $transaction->payment_status) }}
                    </div>
                </div>
            </div>

            <!-- Nav tabs -->
            <ul class="nav nav-tabs" role="tablist">
                <li role="presentation" class="active">
                    <a href="#add_receipt_tab" aria-controls="add_receipt_tab" role="tab" data-toggle="tab">Receive Items</a>
                </li>
                <li role="presentation">
                    <a href="#history_tab" aria-controls="history_tab" role="tab" data-toggle="tab">Received History</a>
                </li>
            </ul>

            <!-- Tab panes -->
            <div class="tab-content" style="padding-top: 15px;">
                <div role="tabpanel" class="tab-pane active" id="add_receipt_tab">
                    @if($total_remaining <= 0)
                        <div class="alert alert-warning text-center">
                            <i class="fa fa-info-circle"></i> All products have been fully received for this purchase.
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-md-6 col-sm-6 col-xs-12">
                            <div class="form-group">
                                {!! Form::label('received_date', 'Receive Date & Time:*') !!}
                                <input type="datetime-local" class="form-control" name="received_date" id="received_date" required value="{{ \Carbon\Carbon::now()->format('Y-m-d\TH:i') }}">
                            </div>
                        </div>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                            <div class="form-group">
                                {!! Form::label('receive_type', 'Receive Mode:*') !!}
                                @php
                                    $receive_type_attrs = ['class' => 'form-control', 'id' => 'receive_type', 'required'];
                                @endphp
                                {!! Form::select('receive_type', ['all' => 'Receive All Items', 'custom' => 'Enter Amount and Date'], 'all', $receive_type_attrs) !!}
                            </div>
                        </div>
                    </div>

                    <div class="row" id="custom_qty_container">
                        <div class="col-md-12">
                            <div class="table-responsive" style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
                                <table class="table table-bordered table-striped" style="min-width: 520px; margin-bottom: 0;">
                                    <thead>
                                        <tr class="bg-gray">
                                            <th>Product Name</th>
                                            <th class="text-center" style="width: 100px;">Ordered Qty</th>
                                            <th class="text-center" style="width: 110px;">Already Received</th>
                                            <th class="text-center" style="width: 95px;">Remaining</th>
                                            <th class="text-center" style="width: 120px;">Receive Qty</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($transaction->purchase_lines as $line)
                                            @php
                                                $already_received = $line->receipts->sum('quantity');
                                                $remaining = $line->quantity - $already_received;
                                            @endphp
                                            <tr>
                                                <td style="vertical-align: middle;">
                                                    <strong>{!! \App\Utils\ProductUtil::getFormattedProductName($line->product->name, $line->product->secondary_name, true) !!}</strong>
                                                    @if($line->product->type == 'variable')
                                                        <br><small class="text-muted">{{ $line->variations->name }}</small>
                                                    @endif
                                                </td>
                                                <td class="text-center" style="vertical-align: middle;">{{ number_format($line->quantity, 2) }}</td>
                                                <td class="text-center" style="vertical-align: middle;">{{ number_format($already_received, 2) }}</td>
                                                <td class="text-center" style="vertical-align: middle;">
                                                    <span class="badge {{ $remaining > 0 ? 'bg-yellow' : 'bg-green' }}">
                                                        {{ number_format($remaining, 2) }}
                                                    </span>
                                                </td>
                                                <td style="vertical-align: middle;">
                                                    <input type="number" step="any" min="0" class="form-control receive-qty-input text-center input-sm" name="qty[{{ $line->id }}]" value="{{ $remaining > 0 ? $remaining : 0 }}" disabled style="min-width: 90px; font-weight: bold;">
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div role="tabpanel" class="tab-pane" id="history_tab">
                    <div class="table-responsive" style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
                        <table class="table table-bordered table-striped" style="min-width: 480px; margin-bottom: 0;">
                            <thead>
                                <tr class="bg-gray">
                                    <th>Date</th>
                                    <th>Product Name</th>
                                    <th class="text-center">Quantity Received</th>
                                    <th class="text-center" style="width: 80px;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $has_history = false; @endphp
                                @foreach($transaction->purchase_lines as $line)
                                    @foreach($line->receipts as $receipt)
                                        @php $has_history = true; @endphp
                                        <tr>
                                            <td style="vertical-align: middle;">{{ @format_datetime($receipt->received_date) }}</td>
                                            <td style="vertical-align: middle;">
                                                {!! \App\Utils\ProductUtil::getFormattedProductName($line->product->name, $line->product->secondary_name, true) !!}
                                                @if($line->product->type == 'variable')
                                                    <br><small class="text-muted">{{ $line->variations->name }}</small>
                                                @endif
                                            </td>
                                            <td class="text-center" style="vertical-align: middle;">
                                                <strong>{{ number_format($receipt->quantity, 2) }}</strong>
                                            </td>
                                            <td class="text-center" style="vertical-align: middle;">
                                                <button type="button" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline tw-dw-btn-error btn-delete-receipt" data-href="{{ action([\App\Http\Controllers\PurchaseController::class, 'deleteReceiveRecord'], [$receipt->id]) }}">
                                                    <i class="fa fa-trash" aria-hidden="true"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endforeach
                                @if(!$has_history)
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">No receipts recorded yet.</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <button type="submit" id="save_receive_record_btn" class="tw-dw-btn tw-dw-btn-primary tw-text-white">Save Receipt</button>
            <button type="button" class="tw-dw-btn tw-dw-btn-neutral tw-text-white" data-dismiss="modal">Close</button>
        </div>
        {!! Form::close() !!}
    </div>
</div>

<script>
    $(document).ready(function() {
        $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
            var target = $(e.target).attr("href");
            if (target === '#history_tab') {
                $('#save_receive_record_btn').hide();
            } else {
                $('#save_receive_record_btn').show();
            }
        });

        $('#receive_type').on('change', function() {
            if ($(this).val() == 'all') {
                $('.receive-qty-input').prop('disabled', true);
            } else {
                $('.receive-qty-input').prop('disabled', false);
            }
        });
    });
</script>
