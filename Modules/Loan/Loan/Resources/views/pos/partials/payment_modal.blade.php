<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">×</span>
            </button>
            <h4 class="modal-title">@lang('Loan::lang.add_payment')</h4>
        </div>
        <div class="modal-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('amount', __('lang_v1.amount') . ':') !!}
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa fa-money"></i>
                            </span>
                            {!! Form::text('amount', 0, [
                                'class' => 'form-control input_number',
                                'id' => 'payment_modal_amount',
                                'required',
                            ]) !!}
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('method', __('lang_v1.payment_method') . ':') !!}
                        <div class="input-group">
                            {!! Form::select('method', $payment_types, 'cash', [
                                'class' => 'form-control select2',
                                'id' => 'payment_modal_method',
                                'required',
                            ]) !!}
                        </div>
                    </div>
                </div>
            </div>

            <div class="row payment_details" style="display: none;">
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('card_number', __('lang_v1.card_number') . ':') !!}
                        {!! Form::text('card_number', null, [
                            'class' => 'form-control',
                            'id' => 'payment_modal_card_number',
                        ]) !!}
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('card_holder_name', __('lang_v1.card_holder_name') . ':') !!}
                        {!! Form::text('card_holder_name', null, [
                            'class' => 'form-control',
                            'id' => 'payment_modal_card_holder_name',
                        ]) !!}
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label('card_transaction_number', __('lang_v1.card_transaction_number') . ':') !!}
                        {!! Form::text('card_transaction_number', null, [
                            'class' => 'form-control',
                            'id' => 'payment_modal_card_transaction_number',
                        ]) !!}
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label('card_type', __('lang_v1.card_type') . ':') !!}
                        {!! Form::select('card_type', [
                            'visa' => 'Visa',
                            'master' => 'MasterCard',
                            'amex' => 'American Express',
                            'discover' => 'Discover',
                        ], null, [
                            'class' => 'form-control select2',
                            'id' => 'payment_modal_card_type',
                            'placeholder' => __('lang_v1.select_card_type'),
                        ]) !!}
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label('card_month', __('lang_v1.month') . ':') !!}
                        {!! Form::text('card_month', null, [
                            'class' => 'form-control',
                            'id' => 'payment_modal_card_month',
                            'placeholder' => 'MM',
                        ]) !!}
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label('card_year', __('lang_v1.year') . ':') !!}
                        {!! Form::text('card_year', null, [
                            'class' => 'form-control',
                            'id' => 'payment_modal_card_year',
                            'placeholder' => 'YYYY',
                        ]) !!}
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label('card_security', __('lang_v1.security_code') . ':') !!}
                        {!! Form::text('card_security', null, [
                            'class' => 'form-control',
                            'id' => 'payment_modal_card_security',
                            'placeholder' => 'CVV',
                        ]) !!}
                    </div>
                </div>
            </div>

            <div class="row cheque_details" style="display: none;">
                <div class="col-md-12">
                    <div class="form-group">
                        {!! Form::label('cheque_number', __('lang_v1.cheque_number') . ':') !!}
                        {!! Form::text('cheque_number', null, [
                            'class' => 'form-control',
                            'id' => 'payment_modal_cheque_number',
                        ]) !!}
                    </div>
                </div>
            </div>

            <div class="row bank_details" style="display: none;">
                <div class="col-md-12">
                    <div class="form-group">
                        {!! Form::label('bank_account_number', __('lang_v1.bank_account_number') . ':') !!}
                        {!! Form::text('bank_account_number', null, [
                            'class' => 'form-control',
                            'id' => 'payment_modal_bank_account_number',
                        ]) !!}
                    </div>
                </div>
            </div>

            @if (!empty($accounts))
                <div class="row account_details" style="display: none;">
                    <div class="col-md-12">
                        <div class="form-group">
                            {!! Form::label('account_id', __('lang_v1.payment_account') . ':') !!}
                            {!! Form::select('account_id', $accounts, null, [
                                'class' => 'form-control select2',
                                'id' => 'payment_modal_account_id',
                                'placeholder' => __('lang_v1.select_account'),
                            ]) !!}
                        </div>
                    </div>
                </div>
            @endif

            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        {!! Form::label('note', __('lang_v1.note') . ':') !!}
                        {!! Form::textarea('note', null, [
                            'class' => 'form-control',
                            'id' => 'payment_modal_note',
                            'rows' => 3,
                            'placeholder' => __('lang_v1.payment_note'),
                        ]) !!}
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-primary" id="save_payment">@lang('messages.save')</button>
            <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
        </div>
    </div>
</div>

@stop

@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {
            // Initialize Select2
            $('#payment_modal_method, #payment_modal_card_type, #payment_modal_account_id').select2();

            // Toggle payment details based on method
            $('#payment_modal_method').change(function() {
                var method = $(this).val();
                $('.payment_details, .cheque_details, .bank_details, .account_details').hide();
                if (method === 'card') {
                    $('.payment_details').show();
                } else if (method === 'cheque') {
                    $('.cheque_details').show();
                } else if (method === 'bank_transfer') {
                    $('.bank_details').show();
                } else if (method === 'advance') {
                    $('.account_details').show();
                }
            });

            // Save payment
            $('#save_payment').click(function() {
                var payment_data = {
                    amount: $('#payment_modal_amount').val(),
                    method: $('#payment_modal_method').val(),
                    card_number: $('#payment_modal_card_number').val(),
                    card_holder_name: $('#payment_modal_card_holder_name').val(),
                    card_transaction_number: $('#payment_modal_card_transaction_number').val(),
                    card_type: $('#payment_modal_card_type').val(),
                    card_month: $('#payment_modal_card_month').val(),
                    card_year: $('#payment_modal_card_year').val(),
                    card_security: $('#payment_modal_card_security').val(),
                    cheque_number: $('#payment_modal_cheque_number').val(),
                    bank_account_number: $('#payment_modal_bank_account_number').val(),
                    account_id: $('#payment_modal_account_id').val(),
                    note: $('#payment_modal_note').val(),
                };

                // Validate amount
                if (!payment_data.amount || parseFloat(payment_data.amount) <= 0) {
                    toastr.error('@lang("lang_v1.amount_required")');
                    return;
                }

                // Add payment row via AJAX
                $.ajax({
                    url: '{{ action([\Modules\Loan\Http\Controllers\LoanPosUtilityController::class, 'getPaymentRow']) }}',
                    method: 'GET',
                    data: {
                        row_index: $('.payment_row').length,
                        location_id: $('#location_id').val(),
                    },
                    success: function(response) {
                        $('#payment_rows_div').append(response);
                        var new_row = $('#payment_rows_div .payment_row:last');
                        new_row.find('.payment_amount').val(payment_data.amount);
                        new_row.find('.payment_method').val(payment_data.method);
                        new_row.find('.card_number').val(payment_data.card_number);
                        new_row.find('.card_holder_name').val(payment_data.card_holder_name);
                        new_row.find('.card_transaction_number').val(payment_data.card_transaction_number);
                        new_row.find('.card_type').val(payment_data.card_type);
                        new_row.find('.card_month').val(payment_data.card_month);
                        new_row.find('.card_year').val(payment_data.card_year);
                        new_row.find('.card_security').val(payment_data.card_security);
                        new_row.find('.cheque_number').val(payment_data.cheque_number);
                        new_row.find('.bank_account_number').val(payment_data.bank_account_number);
                        new_row.find('.account_id').val(payment_data.account_id);
                        new_row.find('.note').val(payment_data.note);
                        $('.payment_modal').modal('hide');
                        toastr.success('@lang("Loan::lang.payment_added")');
                        update_payment_totals();
                    },
                    error: function() {
                        toastr.error('@lang("messages.something_went_wrong")');
                    }
                });
            });

            function update_payment_totals() {
                // This function should be defined in pos.js to update totals
                // Placeholder to ensure totals are recalculated after adding a payment
                if (typeof calculate_payment_totals === 'function') {
                    calculate_payment_totals();
                }
            }
        });
    </script>
@endsection