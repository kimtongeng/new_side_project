<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">×</span>
            </button>
            <h4 class="modal-title">@lang('Loan::lang.recurring_invoice')</h4>
        </div>
        <div class="modal-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('recurring_interval', __('lang_v1.recurring_interval') . ':') !!}
                        <div class="input-group">
                            {!! Form::number('recurring_interval', 1, [
                                'class' => 'form-control',
                                'id' => 'recurring_interval',
                                'min' => 1,
                                'required',
                            ]) !!}
                            <span class="input-group-addon">
                                {!! Form::select('recurring_interval_type', [
                                    'days' => __('lang_v1.days'),
                                    'weeks' => __('lang_v1.weeks'),
                                    'months' => __('lang_v1.months'),
                                    'years' => __('lang_v1.years'),
                                ], 'months', [
                                    'class' => 'form-control',
                                    'id' => 'recurring_interval_type',
                                ]) !!}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('recurring_repetitions', __('lang_v1.repetitions') . ':') !!}
                        {!! Form::number('recurring_repetitions', 0, [
                            'class' => 'form-control',
                            'id' => 'recurring_repetitions',
                            'min' => 0,
                            'placeholder' => __('lang_v1.infinite'),
                        ]) !!}
                        <small class="help-block">@lang('lang_v1.zero_for_infinite')</small>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        {!! Form::label('recurring_start_date', __('lang_v1.start_date') . ':') !!}
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa fa-calendar"></i>
                            </span>
                            {!! Form::text('recurring_start_date', \Carbon::today()->format('Y-m-d'), [
                                'class' => 'form-control start_date',
                                'id' => 'recurring_start_date',
                                'readonly',
                            ]) !!}
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        {!! Form::label('recurring_note', __('lang_v1.note') . ':') !!}
                        {!! Form::textarea('recurring_note', null, [
                            'class' => 'form-control',
                            'id' => 'recurring_note',
                            'rows' => 3,
                            'placeholder' => __('lang_v1.recurring_note_placeholder'),
                        ]) !!}
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-primary" id="save_recurring_invoice">@lang('messages.save')</button>
            <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
        </div>
    </div>
</div>

@stop

@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {
            // Initialize datepicker
            $('#recurring_start_date').datepicker({
                format: '{{ session("business.date_format") }}',
                autoclose: true
            });

            // Save recurring invoice settings
            $('#save_recurring_invoice').click(function() {
                var recurring_data = {
                    is_recurring: 1,
                    recurring_interval: $('#recurring_interval').val(),
                    recurring_interval_type: $('#recurring_interval_type').val(),
                    recurring_repetitions: $('#recurring_repetitions').val() || 0,
                    recurring_start_date: $('#recurring_start_date').val(),
                    recurring_note: $('#recurring_note').val()
                };

                // Validate inputs
                if (!recurring_data.recurring_interval || parseInt(recurring_data.recurring_interval) <= 0) {
                    toastr.error('@lang("lang_v1.recurring_interval_required")');
                    return;
                }

                // Add recurring data to the main form
                $('<input>').attr({
                    type: 'hidden',
                    name: 'is_recurring',
                    value: recurring_data.is_recurring
                }).appendTo('#add_pos_sell_form, #edit_pos_sell_form');
                $('<input>').attr({
                    type: 'hidden',
                    name: 'recurring_interval',
                    value: recurring_data.recurring_interval
                }).appendTo('#add_pos_sell_form, #edit_pos_sell_form');
                $('<input>').attr({
                    type: 'hidden',
                    name: 'recurring_interval_type',
                    value: recurring_data.recurring_interval_type
                }).appendTo('#add_pos_sell_form, #edit_pos_sell_form');
                $('<input>').attr({
                    type: 'hidden',
                    name: 'recurring_repetitions',
                    value: recurring_data.recurring_repetitions
                }).appendTo('#add_pos_sell_form, #edit_pos_sell_form');
                $('<input>').attr({
                    type: 'hidden',
                    name: 'recurring_start_date',
                    value: recurring_data.recurring_start_date
                }).appendTo('#add_pos_sell_form, #edit_pos_sell_form');
                $('<input>').attr({
                    type: 'hidden',
                    name: 'recurring_note',
                    value: recurring_data.recurring_note
                }).appendTo('#add_pos_sell_form, #edit_pos_sell_form');

                // Close modal and notify
                $('.recurring_invoice_modal').modal('hide');
                toastr.success('@lang("Loan::lang.recurring_invoice_added")');
            });
        });
    </script>
@endsection