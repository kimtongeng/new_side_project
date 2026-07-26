<div class="modal-dialog" role="document">
  <div class="modal-content">

    {!! Form::open(['url' => action([\App\Http\Controllers\AccountController::class, 'postFundTransfer']), 'method' => 'post', 'id' => 'fund_transfer_form', 'files' => true ]) !!}

    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      <h4 class="modal-title">@lang( 'account.fund_transfer' )</h4>
    </div>

    <div class="modal-body">

            <div class="form-group">
                {!! Form::label('from_account', __( 'lang_v1.transfer_from' ) .":*") !!}
                {!! Form::select('from_account', $all_accounts_dropdown, $from_account->id, ['class' => 'form-control select2', 'required', 'id' => 'from_account', 'style' => 'width:100%' ]); !!}
            </div>

            <div class="form-group">
                {!! Form::label('to_account', __( 'account.transfer_to' ) .":*") !!}
                {!! Form::select('to_account', $to_accounts, null, ['class' => 'form-control select2', 'required', 'id' => 'to_account', 'style' => 'width:100%', 'placeholder' => __('messages.please_select') ]); !!}
            </div>

            <div class="form-group">
                {!! Form::label('amount', __( 'sale.amount' ) .":*") !!}
                {!! Form::text('amount', 0, ['class' => 'form-control input_number', 'required','placeholder' => __( 'sale.amount' ) ]); !!}
            </div>

            <div class="form-group">
                {!! Form::label('od_datetimepicker', __( 'messages.date' ) .":*") !!}
                <div class="input-group">
                  {!! Form::text('operation_date', null, ['class' => 'form-control', 'required','placeholder' => __( 'messages.date' ), 'id' => 'od_datetimepicker' ]); !!}
                  <span class="input-group-addon">
                    <span class="glyphicon glyphicon-calendar"></span>
                  </span>
                </div>
            </div>

            <div class="form-group">
                {!! Form::label('note', __( 'brand.note' )) !!}
                {!! Form::textarea('note', null, ['class' => 'form-control', 'placeholder' => __( 'brand.note' ), 'rows' => 4]); !!}
            </div>

            <div class="form-group">
                {!! Form::label('document', __('purchase.attach_document') . ':') !!}
                {!! Form::file('document', ['id' => 'upload_document', 'accept' => implode(',', array_keys(config('constants.document_upload_mimes_types')))]); !!}
                <p class="help-block">
                  @lang('purchase.max_file_size', ['size' => (config('constants.document_size_limit') / 1000000)])
                  @includeIf('components.document_help_text')
                </p>
            </div>
    </div>

    <div class="modal-footer">
      <button type="submit" class="tw-dw-btn tw-dw-btn-primary tw-text-white">@lang( 'messages.submit' )</button>
      <button type="button" class="tw-dw-btn tw-dw-btn-neutral tw-text-white" data-dismiss="modal">@lang( 'messages.close' )</button>
    </div>

    {!! Form::close() !!}

  </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->

<script type="text/javascript">
  $(document).ready( function(){
    $('#od_datetimepicker').datetimepicker({
      format: moment_date_format + ' ' + moment_time_format
    });

    var accountsData = @json($accounts_data);

    function filterToAccounts() {
      var fromId = $('#from_account').val();
      var fromLocs = (accountsData[fromId] && accountsData[fromId].location_ids) ? accountsData[fromId].location_ids : null;

      var selectedTo = $('#to_account').val();
      $('#to_account').empty();
      $('#to_account').append(new Option("@lang('messages.please_select')", "", false, false));

      $.each(accountsData, function(id, acc) {
        var accLocs = acc.location_ids;
        var isCompatible = false;

        if (!fromLocs || !accLocs) {
          isCompatible = true;
        } else {
          $.each(fromLocs, function(i, fLoc) {
            if (accLocs.indexOf(fLoc) !== -1 || accLocs.indexOf(parseInt(fLoc)) !== -1 || accLocs.indexOf(String(fLoc)) !== -1) {
              isCompatible = true;
              return false;
            }
          });
        }

        if (isCompatible) {
          var isSelected = (id == selectedTo);
          $('#to_account').append(new Option(acc.name, id, false, isSelected));
        }
      });

      if ($('#to_account').hasClass('select2-hidden-accessible')) {
        $('#to_account').select2('destroy');
      }
      $('#to_account').select2({ width: '100%' });
    }

    if ($('#from_account').hasClass('select2-hidden-accessible')) {
      $('#from_account').select2('destroy');
    }
    $('#from_account').select2({ width: '100%' });

    filterToAccounts();

    $(document).on('change', '#from_account', function(){
      filterToAccounts();
    });
  });
</script>