<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title">@lang('Loan::lang.sale_details')</h4>
        </div>

        <div class="modal-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>@lang('lang_v1.invoice_no'):</strong> {{ $transaction->invoice_no }}</p>
                    <p><strong>@lang('lang_v1.date'):</strong> {{ \Carbon::createFromFormat('Y-m-d H:i:s', $transaction->transaction_date)->format('d/m/Y H:i') }}</p>
                    <p><strong>@lang('lang_v1.customer'):</strong> {{ $transaction->contact->name }}</p>
                    @if (!empty($transaction->contact->mobile))
                        <p><strong>@lang('lang_v1.mobile'):</strong> {{ $transaction->contact->mobile }}</p>
                    @endif
                    @if (!empty($transaction->location))
                        <p><strong>@lang('lang_v1.location'):</strong> {{ $transaction->location->name }}</p>
                    @endif
                </div>
                <div class="col-md-6">
                    @if (!empty($transaction->sales_person))
                        <p><strong>@lang('lang_v1.sales_person'):</strong> {{ $transaction->sales_person->user_full_name }}</p>
                    @endif
                    @if (!empty($transaction->commission_agent))
                        <p><strong>@lang('lang_v1.commission_agent'):</strong> {{ $transaction->commission_agent->user_full_name }}</p>
                    @endif
                    <p><strong>@lang('lang_v1.status'):</strong> 
                        <span class="label @if ($transaction->status == 'final') label-success @elseif ($transaction->status == 'draft') label-warning @else label-info @endif">
                            {{ __('lang_v1.' . $transaction->status) }}
                        </span>
                    </p>
                    @if ($transaction->is_quotation)
                        <p><strong>@lang('lang_v1.sub_status'):</strong> @lang('lang_v1.quotation')</p>
                    @endif
                    @if (!empty($transaction->payment_status))
                        <p><strong>@lang('lang_v1.payment_status'):</strong> 
                            <span class="label @if ($transaction->payment_status == 'paid') label-success @elseif ($transaction->payment_status == 'partial') label-info @else label-danger @endif">
                                {{ __('lang_v1.' . $transaction->payment_status) }}
                            </span>
                        </p>
                    @endif
                </div>
            </div>

            <hr>

            <div class="row">
                <div class="col-md-12">
                    <h4>@lang('Loan::lang.sale_lines')</h4>
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>@lang('lang_v1.product')</th>
                                <th>@lang('lang_v1.quantity')</th>
                                <th>@lang('lang_v1.unit_price')</th>
                                <th>@lang('lang_v1.discount')</th>
                                <th>@lang('lang_v1.tax')</th>
                                <th>@lang('lang_v1.sub_total')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($sell_details as $line)
                                <tr>
                                    <td>
                                        {{ $line->product_name }}
                                        @if ($line->product_type == 'combo')
                                            <i class="fa fa-info-circle text-info" data-toggle="tooltip" title="@lang('Loan::lang.combo_product')"></i>
                                        @endif
                                        @if (!empty($line->sell_line_note))
                                            <br><small>{{ $line->sell_line_note }}</small>
                                        @endif
                                    </td>
                                    <td>{{ $line->quantity_ordered }} {{ $line->unit }}</td>
                                    <td>{{ number_format($line->unit_price_before_discount, 2) }}</td>
                                    <td>
                                        @if ($line->line_discount_amount > 0)
                                            {{ number_format($line->line_discount_amount, 2) }} 
                                            ({{ $line->line_discount_type == 'percentage' ? $line->line_discount_amount . '%' : 'fixed' }})
                                        @else
                                            0
                                        @endif
                                    </td>
                                    <td>{{ number_format($line->item_tax, 2) }}</td>
                                    <td>{{ number_format($line->quantity_ordered * $line->unit_price, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <h4>@lang('Loan::lang.payment_details')</h4>
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>@lang('lang_v1.payment_method')</th>
                                <th>@lang('lang_v1.amount')</th>
                                <th>@lang('lang_v1.note')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($payment_lines as $payment)
                                @if ($payment['amount'] > 0 || $payment['is_return'] == 1)
                                    <tr>
                                        <td>{{ $payment['method'] == 'cash' ? __('lang_v1.cash') : ($payment['method'] == 'card' ? __('lang_v1.card') : ($payment['method'] == 'cheque' ? __('lang_v1.cheque') : $payment['method'])) }}</td>
                                        <td>
                                            @if ($payment['is_return'] == 1)
                                                -{{ number_format($payment['amount'], 2) }}
                                            @else
                                                {{ number_format($payment['amount'], 2) }}
                                            @endif
                                        </td>
                                        <td>{{ $payment['note'] ?? '' }}</td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    @if (!empty($transaction->sale_note))
                        <p><strong>@lang('lang_v1.sale_note'):</strong> {{ $transaction->sale_note }}</p>
                    @endif
                    @if (!empty($transaction->shipping_details))
                        <p><strong>@lang('lang_v1.shipping_details'):</strong> {{ $transaction->shipping_details }}</p>
                    @endif
                </div>
                <div class="col-md-6 text-right">
                    <p><strong>@lang('lang_v1.total'):</strong> {{ number_format($transaction->final_total, 2) }}</p>
                    @if ($transaction->discount_amount > 0)
                        <p><strong>@lang('lang_v1.discount'):</strong> 
                            {{ number_format($transaction->discount_amount, 2) }} 
                            ({{ $transaction->discount_type == 'percentage' ? $transaction->discount_amount . '%' : 'fixed' }})
                        </p>
                    @endif
                    @if ($transaction->tax_amount > 0)
                        <p><strong>@lang('lang_v1.tax'):</strong> {{ number_format($transaction->tax_amount, 2) }}</p>
                    @endif
                    @if ($transaction->shipping_charges > 0)
                        <p><strong>@lang('lang_v1.shipping_charges'):</strong> {{ number_format($transaction->shipping_charges, 2) }}</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="modal-footer">
            @can('sell.update')
                <a href="{{ action([\Modules\Loan\Http\Controllers\LoanPosEditController::class, 'edit'], [$transaction->id]) }}" class="btn btn-primary">@lang('messages.edit')</a>
            @endcan
            @can('print_invoice')
                <button type="button" class="btn btn-success print_invoice" data-href="{{ action([\Modules\Loan\Http\Controllers\LoanPosController::class, 'store']) }}?print_invoice={{ $transaction->id }}">@lang('lang_v1.print_invoice')</button>
            @endcan
            <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
        </div>
    </div>
</div>

@stop

@section('css')
    <style type="text/css">
        .modal-lg {
            max-width: 90%;
        }
        .table {
            font-size: 14px;
        }
        .label {
            font-size: 12px;
            padding: 5px 10px;
        }
    </style>
@endsection

@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {
            $('[data-toggle="tooltip"]').tooltip();

            $('.print_invoice').click(function() {
                window.location.href = $(this).data('href');
            });
        });
    </script>
@endsection