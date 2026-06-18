<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">×</span>
            </button>
            <h4 class="modal-title">@lang('Loan::lang.weighing_scale')</h4>
        </div>
        <div class="modal-body">
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        {!! Form::label('weighing_scale_barcode', __('lang_v1.weighing_scale_barcode') . ':') !!}
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa fa-barcode"></i>
                            </span>
                            {!! Form::text('weighing_scale_barcode', null, [
                                'class' => 'form-control',
                                'id' => 'weighing_scale_barcode',
                                'placeholder' => __('lang_v1.enter_barcode'),
                                'autofocus',
                            ]) !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-primary" id="add_weighing_scale_product">@lang('messages.add')</button>
            <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
        </div>
    </div>
</div>

@stop

@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {
            // Handle add weighing scale product
            $('#add_weighing_scale_product').click(function() {
                var barcode = $('#weighing_scale_barcode').val();

                if (!barcode) {
                    toastr.error('@lang("lang_v1.barcode_required")');
                    return;
                }

                $.ajax({
                    url: '{{ action([\Modules\Loan\Http\Controllers\LoanPosUtilityController::class, 'getProductRow']) }}',
                    method: 'GET',
                    data: {
                        variation_id: 'null',
                        location_id: $('#location_id').val(),
                        weighing_scale_barcode: barcode,
                        quantity: 1,
                        product_row: $('.product_row').length
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#pos_table_body').append(response.html_content);
                            if (response.html_modifier) {
                                $('#pos_table_body').append(response.html_modifier);
                            }
                            $('.weighing_scale_modal').modal('hide');
                            $('#weighing_scale_barcode').val('');
                            toastr.success('@lang("Loan::lang.product_added")');
                            // Update totals (function assumed to be in pos.js)
                            if (typeof update_pos_totals === 'function') {
                                update_pos_totals();
                            }
                        } else {
                            toastr.error(response.msg);
                        }
                    },
                    error: function() {
                        toastr.error('@lang("messages.something_went_wrong")');
                    }
                });
            });

            // Auto-focus barcode input when modal opens
            $('#weighing_scale_modal').on('shown.bs.modal', function() {
                $('#weighing_scale_barcode').focus();
            });

            // Handle enter key to add product
            $('#weighing_scale_barcode').on('keypress', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    $('#add_weighing_scale_product').click();
                }
            });
        });
    </script>
@endsection