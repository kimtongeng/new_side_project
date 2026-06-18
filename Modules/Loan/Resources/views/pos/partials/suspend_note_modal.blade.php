<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">×</span>
            </button>
            <h4 class="modal-title">@lang('Loan::lang.suspend_sale')</h4>
        </div>
        <div class="modal-body">
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        {!! Form::label('suspend_note', __('lang_v1.suspend_note') . ':') !!}
                        {!! Form::textarea('suspend_note', null, [
                            'class' => 'form-control',
                            'id' => 'suspend_note',
                            'rows' => 4,
                            'placeholder' => __('lang_v1.suspend_note_placeholder'),
                        ]) !!}
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-primary" id="save_suspend">@lang('messages.save')</button>
            <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
        </div>
    </div>
</div>

@stop

@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {
            $('#save_suspend').click(function() {
                var note = $('#suspend_note').val();
                
                // Update the sale note in the main form
                $('#additional_notes').val(note);
                
                // Set is_suspend flag
                $('<input>').attr({
                    type: 'hidden',
                    name: 'is_suspend',
                    value: '1'
                }).appendTo('#add_pos_sell_form, #edit_pos_sell_form');
                
                // Trigger form submission
                var form = $('#add_pos_sell_form').length ? $('#add_pos_sell_form') : $('#edit_pos_sell_form');
                form.submit();
                
                $('.suspend_note_modal').modal('hide');
                toastr.success('@lang("Loan::lang.sale_suspended")');
            });
        });
    </script>
@endsection