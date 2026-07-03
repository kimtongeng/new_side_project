@php
    $total_remaining = 0;
    foreach($transaction->purchase_lines as $line) {
        $total_remaining += ($line->quantity - $line->receipts->sum('quantity'));
    }
@endphp
<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title">Receive Products (Ref No: {{ $transaction->ref_no }})</h4>
        </div>

        <div class="modal-body">
            <div class="row">
                @if(!empty($transaction->contact))
                    <div class="col-md-4">
                        <div class="well">
                            <strong>@lang('purchase.supplier'):</strong> {{ $transaction->contact->full_name_with_business }}<br>
                            <strong>@lang('business.business'): </strong>{{ $transaction->contact->supplier_business_name }}
                        </div>
                    </div>
                @endif
                <div class="col-md-4">
                    <div class="well">
                        <strong>@lang('purchase.ref_no'): </strong>{{ $transaction->ref_no }}<br>
                        @if(!empty($transaction->location))
                            <strong>@lang('purchase.location'): </strong>{{ $transaction->location->name }}
                        @endif
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="well">
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
                    {!! Form::open(['url' => action([\App\Http\Controllers\PurchaseController::class, 'saveReceiveRecord']), 'method' => 'post', 'id' => 'save_receive_record_form' ]) !!}
                        <input type="hidden" name="purchase_id" value="{{ $transaction->id }}">

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    {!! Form::label('received_date', 'Receive Date & Time:*') !!}
                                    <input type="datetime-local" class="form-control" name="received_date" id="received_date" required value="{{ \Carbon\Carbon::now()->format('Y-m-d\TH:i') }}" @if($total_remaining <= 0) disabled @endif>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    {!! Form::label('receive_type', 'Receive Mode:*') !!}
                                    @php
                                        $receive_type_attrs = ['class' => 'form-control', 'id' => 'receive_type', 'required'];
                                        if ($total_remaining <= 0) {
                                            $receive_type_attrs['disabled'] = 'disabled';
                                        }
                                    @endphp
                                    {!! Form::select('receive_type', ['all' => 'Receive All Items', 'custom' => 'Enter Amount and Date'], 'all', $receive_type_attrs) !!}
                                </div>
                            </div>
                        </div>

                        <div class="row" id="custom_qty_container">
                            <div class="col-md-12">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Product Name</th>
                                            <th>Ordered Qty</th>
                                            <th>Already Received</th>
                                            <th>Remaining</th>
                                            <th style="width: 150px;">Receive Qty</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($transaction->purchase_lines as $line)
                                            @php
                                                $already_received = $line->receipts->sum('quantity');
                                                $remaining = $line->quantity - $already_received;
                                            @endphp
                                            <tr>
                                                <td>
                                                    {{ $line->product->name }}
                                                    @if($line->product->type == 'variable')
                                                        - {{ $line->variations->name }}
                                                    @endif
                                                </td>
                                                <td>{{ number_format($line->quantity, 2) }}</td>
                                                <td>{{ number_format($already_received, 2) }}</td>
                                                <td>{{ number_format($remaining, 2) }}</td>
                                                <td>
                                                    <input type="number" step="any" min="0" max="{{ $remaining }}" class="form-control receive-qty-input" name="qty[{{ $line->id }}]" value="{{ $remaining > 0 ? $remaining : 0 }}" disabled>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>


                    {!! Form::close() !!}
                </div>

                <div role="tabpanel" class="tab-pane" id="history_tab">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Product Name</th>
                                <th>Quantity Received</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $has_history = false; @endphp
                            @foreach($transaction->purchase_lines as $line)
                                @foreach($line->receipts as $receipt)
                                    @php $has_history = true; @endphp
                                    <tr>
                                        <td>{{ @format_datetime($receipt->received_date) }}</td>
                                        <td>
                                            {{ $line->product->name }}
                                            @if($line->product->type == 'variable')
                                                - {{ $line->variations->name }}
                                            @endif
                                        </td>
                                        <td>{{ number_format($receipt->quantity, 2) }}</td>
                                        <td>
                                            <button type="button" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline tw-dw-btn-error btn-delete-receipt" data-href="{{ action([\App\Http\Controllers\PurchaseController::class, 'deleteReceiveRecord'], [$receipt->id]) }}">
                                                <i class="fa fa-trash" aria-hidden="true"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            @endforeach
                            @if(!$has_history)
                                <tr>
                                    <td colspan="4" class="text-center">No receipts recorded yet.</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="modal-footer">
            @if($total_remaining > 0)
                <button type="submit" form="save_receive_record_form" id="save_receive_record_btn" class="tw-dw-btn tw-dw-btn-primary tw-text-white">Save Receipt</button>
            @endif
            <button type="button" class="tw-dw-btn tw-dw-btn-neutral tw-text-white" data-dismiss="modal">Close</button>
        </div>
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
    });
</script>
