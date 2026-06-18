<div class="row">
    <div class="col-md-12">
        <div class="box box-solid">
            <div class="box-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            {!! Form::label('discount_type', __('lang_v1.discount_type') . ':') !!}
                            <div class="input-group">
                                {!! Form::select('discount_type', [
                                    'fixed' => __('lang_v1.fixed'),
                                    'percentage' => __('lang_v1.percentage'),
                                ], 'fixed', [
                                    'class' => 'form-control',
                                    'id' => 'discount_type',
                                    'data-default' => 'fixed',
                                ]) !!}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            {!! Form::label('discount_amount', __('lang_v1.discount_amount') . ':') !!}
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <i class="fa fa-money"></i>
                                </span>
                                {!! Form::text('discount_amount', 0, [
                                    'class' => 'form-control input_number',
                                    'id' => 'discount_amount',
                                    'data-default' => 0,
                                    @if (!$edit_discount) 'readonly' @endif,
                                ]) !!}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            {!! Form::label('tax_rate_id', __('lang_v1.tax') . ':') !!}
                            <div class="input-group">
                                {!! Form::select('tax_rate_id', $taxes['tax_rates'], null, [
                                    'class' => 'form-control',
                                    'id' => 'tax_rate_id',
                                    'placeholder' => __('lang_v1.no_tax'),
                                ]) !!}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            {!! Form::label('shipping_charges', __('lang_v1.shipping_charges') . ':') !!}
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <i class="fa fa-money"></i>
                                </span>
                                {!! Form::text('shipping_charges', 0, [
                                    'class' => 'form-control input_number',
                                    'id' => 'shipping_charges',
                                    'data-default' => 0,
                                ]) !!}
                            </div>
                        </div>
                    </div>
                    @if (!empty($pos_settings['enable_packing_charge']))
                        <div class="col-md-4">
                            <div class="form-group">
                                {!! Form::label('packing_charge', __('lang_v1.packing_charge') . ':') !!}
                                <div class="input-group">
                                    <span class="input-group-addon">
                                        <i class="fa fa-money"></i>
                                    </span>
                                    {!! Form::text('packing_charge', 0, [
                                        'class' => 'form-control input_number',
                                        'id' => 'packing_charge',
                                        'data-default' => 0,
                                    ]) !!}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                {!! Form::label('packing_charge_type', __('lang_v1.packing_charge_type') . ':') !!}
                                {!! Form::select('packing_charge_type', [
                                    'fixed' => __('lang_v1.fixed'),
                                    'percentage' => __('lang_v1.percentage'),
                                ], 'fixed', [
                                    'class' => 'form-control',
                                    'id' => 'packing_charge_type',
                                ]) !!}
                            </div>
                        </div>
                    @endif
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <table class="table table-bordered table-striped">
                            <tr>
                                <th>@lang('lang_v1.total_items'):</th>
                                <td>
                                    <span id="total_items">0</span>
                                    <input type="hidden" id="total_quantity" name="total_quantity" value="0">
                                </td>
                            </tr>
                            <tr>
                                <th>@lang('lang_v1.total'):</th>
                                <td>
                                    <span id="total_amount">0.00</span>
                                    <input type="hidden" id="total_amount_input" name="total_amount" value="0">
                                </td>
                            </tr>
                            <tr>
                                <th>@lang('lang_v1.discount'):</th>
                                <td>
                                    <span id="total_discount">0.00</span>
                                    <input type="hidden" id="total_discount_input" name="total_discount" value="0">
                                </td>
                            </tr>
                            <tr>
                                <th>@lang('lang_v1.tax'):</th>
                                <td>
                                    <span id="total_tax">0.00</span>
                                    <input type="hidden" id="total_tax_input" name="total_tax" value="0">
                                </td>
                            </tr>
                            <tr>
                                <th>@lang('lang_v1.shipping_charges'):</th>
                                <td>
                                    <span id="total_shipping_charges">0.00</span>
                                    <input type="hidden" id="total_shipping_charges_input" name="total_shipping_charges" value="0">
                                </td>
                            </tr>
                            @if (!empty($pos_settings['enable_packing_charge']))
                                <tr>
                                    <th>@lang('lang_v1.packing_charge'):</th>
                                    <td>
                                        <span id="total_packing_charge">0.00</span>
                                        <input type="hidden" id="total_packing_charge_input" name="total_packing_charge" value="0">
                                    </td>
                                </tr>
                            @endif
                            <tr>
                                <th>@lang('lang_v1.grand_total'):</th>
                                <td>
                                    <span id="grand_total">0.00</span>
                                    <input type="hidden" id="grand_total_input" name="final_total" value="0">
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                @if ($is_rp_enabled)
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                {!! Form::label('rp_redeemed', __('lang_v1.redeem_points') . ':') !!}
                                <div class="input-group">
                                    <span class="input-group-addon">
                                        <i class="fa fa-gift"></i>
                                    </span>
                                    {!! Form::text('rp_redeemed', 0, [
                                        'class' => 'form-control input_number',
                                        'id' => 'rp_redeemed',
                                        'data-default' => 0,
                                    ]) !!}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                {!! Form::label('rp_redeemed_amount', __('lang_v1.redeemed_amount') . ':') !!}
                                <div class="input-group">
                                    <span class="input-group-addon">
                                        <i class="fa fa-money"></i>
                                    </span>
                                    {!! Form::text('rp_redeemed_amount', 0, [
                                        'class' => 'form-control input_number',
                                        'id' => 'rp_redeemed_amount',
                                        'readonly',
                                        'data-default' => 0,
                                    ]) !!}
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            {!! Form::label('payment_method', __('lang_v1.payment_method') . ':') !!}
                            <div class="input-group">
                                @foreach ($payment_types as $payment_type => $label)
                                    <label class="radio-inline">
                                        <input type="radio" name="payment[0][method]" value="{{ $payment_type }}"
                                            class="payment_method"
                                            @if ($loop->first) checked @endif>
                                        {{ $label }}
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            {!! Form::label('payment[0][amount]', __('lang_v1.amount') . ':') !!}
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <i class="fa fa-money"></i>
                                </span>
                                {!! Form::text('payment[0][amount]', 0, [
                                    'class' => 'form-control input_number payment_amount',
                                    'id' => 'payment_0_amount',
                                    'data-default' => 0,
                                ]) !!}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            {!! Form::label('payment[0][note]', __('lang_v1.note') . ':') !!}
                            {!! Form::text('payment[0][note]', null, [
                                'class' => 'form-control',
                                'id' => 'payment_0_note',
                                'placeholder' => __('lang_v1.payment_note'),
                            ]) !!}
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <div class="input-group">
                                <button type="button" class="btn btn-primary add_payment_row">
                                    <i class="fa fa-plus"></i> @lang('Loan::lang.add_payment')
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="payment_rows_div">
                    @foreach ($payment_lines as $payment_line)
                        @if ($loop->first)
                            @continue
                        @endif
                        @include('Loan::pos.partials.payment_row', [
                            'payment_line' => $payment_line,
                            'row_index' => $loop->index,
                            'removable' => true,
                            'payment_types' => $payment_types,
                            'accounts' => $accounts,
                        ])
                    @endforeach
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <strong>@lang('lang_v1.total_payable'):</strong>
                            <span id="total_payable">0.00</span>
                            <input type="hidden" id="total_payable_input" name="total_payable" value="0">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <strong>@lang('lang_v1.change_return'):</strong>
                            <span id="change_return">0.00</span>
                            <input type="hidden" id="change_return_input" name="change_return" value="0">
                            {!! Form::hidden('change_return_id', $change_return['payment_id'] ?? null, ['id' => 'change_return_id']) !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop