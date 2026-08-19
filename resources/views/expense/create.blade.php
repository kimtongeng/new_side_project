@extends('layouts.app')
@section('title', __('expense.add_expense'))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">@lang('expense.add_expense')</h1>
</section>

<!-- Main content -->
<section class="content">
	{!! Form::open(['url' => action([\App\Http\Controllers\ExpenseController::class, 'store']), 'method' => 'post', 'id' => 'add_expense_form', 'files' => true ]) !!}
	<div class="box box-solid">
		<div class="box-body">
			<div class="row">

				@if(count($business_locations) == 1)
					@php 
						$default_location = current(array_keys($business_locations->toArray())) 
					@endphp
				@else
					@php $default_location = null; @endphp
				@endif
				<div class="col-sm-4">
					<div class="form-group">
						{!! Form::label('location_id', __('purchase.business_location').':*') !!}
						{!! Form::select('location_id', $business_locations, $default_location, ['class' => 'form-control select2', 'placeholder' => __('messages.please_select'), 'required'], $bl_attributes); !!}
					</div>
				</div>

				<div class="col-sm-4">
					<div class="form-group">
						{!! Form::label('expense_category_id', __('expense.expense_category').':') !!}
						{!! Form::select('expense_category_id', $expense_categories, null, ['class' => 'form-control select2', 'placeholder' => __('messages.please_select')]); !!}
					</div>
				</div>
				<div class="col-md-4">
					<div class="form-group">
			            {!! Form::label('expense_sub_category_id', __('product.sub_category') . ':') !!}
			              {!! Form::select('expense_sub_category_id', [],  null, ['placeholder' => __('messages.please_select'), 'class' => 'form-control select2']); !!}
			          </div>
				</div>
				<div class="col-sm-4">
					<div class="form-group">
						{!! Form::label('ref_no', __('purchase.ref_no').':') !!}
						{!! Form::text('ref_no', null, ['class' => 'form-control']); !!}
						<p class="help-block">
			                @lang('lang_v1.leave_empty_to_autogenerate')
			            </p>
					</div>
				</div>
				<div class="clearfix"></div>
				<div class="col-sm-4">
					<div class="form-group">
						{!! Form::label('transaction_date', __('messages.date') . ':*') !!}
						<div class="input-group">
							<span class="input-group-addon">
								<i class="fa fa-calendar"></i>
							</span>
							{!! Form::text('transaction_date', @format_datetime('now'), ['class' => 'form-control', 'readonly', 'required', 'id' => 'expense_transaction_date']); !!}
						</div>
					</div>
				</div>
				<div class="col-sm-4">
					<div class="form-group">
						{!! Form::label('expense_for', __('expense.expense_for').':') !!} @show_tooltip(__('tooltip.expense_for'))
						{!! Form::select('expense_for', $users, null, ['class' => 'form-control select2', 'placeholder' => __('messages.please_select')]); !!}
					</div>
				</div>
				<div class="col-sm-4">
					<div class="form-group">
						{!! Form::label('contact_id', __('lang_v1.expense_for_contact').':') !!} 
						{!! Form::select('contact_id', $contacts, null, ['class' => 'form-control select2', 'placeholder' => __('messages.please_select')]); !!}
					</div>
				</div>
				<div class="clearfix"></div>
				<div class="col-sm-4">
                    <div class="form-group">
                        {!! Form::label('document', __('purchase.attach_document') . ':') !!}
                        {!! Form::file('document', ['id' => 'upload_document', 'accept' => implode(',', array_keys(config('constants.document_upload_mimes_types')))]); !!}
                        <small><p class="help-block">@lang('purchase.max_file_size', ['size' => (config('constants.document_size_limit') / 1000000)])
                        @includeIf('components.document_help_text')</p></small>
                    </div>
                </div>
				<div class="col-md-4">
			    	<div class="form-group">
			            {!! Form::label('tax_id', __('product.applicable_tax') . ':' ) !!}
			            <div class="input-group">
			                <span class="input-group-addon">
			                    <i class="fa fa-info"></i>
			                </span>
			                {!! Form::select('tax_id', $taxes['tax_rates'], null, ['class' => 'form-control'], $taxes['attributes']); !!}

							<input type="hidden" name="tax_calculation_amount" id="tax_calculation_amount" 
							value="0">
			            </div>
			        </div>
			    </div>
			    <div class="col-sm-4">
					<div class="form-group">
						{!! Form::label('final_total', __('sale.total_amount') . ':*') !!}
						{!! Form::text('final_total', null, ['class' => 'form-control input_number', 'placeholder' => __('sale.total_amount'), 'required']); !!}
					</div>
				</div>
				<div class="clearfix"></div>
				<div class="col-sm-4">
					<div class="form-group">
						{!! Form::label('additional_notes', __('expense.expense_note') . ':') !!}
								{!! Form::textarea('additional_notes', null, ['class' => 'form-control', 'rows' => 3]); !!}
					</div>
				</div>
				<div class="col-md-4 col-sm-6">
					<br>
					<label>
		              {!! Form::checkbox('is_refund', 1, false, ['class' => 'input-icheck', 'id' => 'is_refund']); !!} @lang('lang_v1.is_refund')?
		            </label>@show_tooltip(__('lang_v1.is_refund_help'))
				</div>
			</div>
		</div>
	</div> <!--box end-->
	@include('expense.recur_expense_form_part')
	@component('components.widget', ['class' => 'box-solid', 'id' => "payment_rows_div", 'title' => __('purchase.add_payment')])
	<div class="payment_row">
		@include('sale_pos.partials.payment_row_form', ['row_index' => 0, 'show_date' => true])
		<hr>
		<div class="row">
			<div class="col-sm-12">
				<div class="pull-right">
					<strong>@lang('purchase.payment_due'):</strong>
					<span id="payment_due">{{@num_format(0)}}</span>
				</div>
			</div>
		</div>
	</div>
	@endcomponent
	<div class="col-sm-12 text-center">
		<button type="submit" class="tw-dw-btn tw-dw-btn-primary tw-dw-btn-lg tw-text-white">@lang('messages.save')</button>
	</div>
{!! Form::close() !!}
</section>
@endsection
@section('javascript')
<script type="text/javascript">
	$(document).ready( function(){
		$('.paid_on').datetimepicker({
            format: moment_date_format + ' ' + moment_time_format,
            ignoreReadonly: true,
        });

		if ($('select#location_id').length && $('select#location_id').val()) {
			$('select#location_id').trigger('change');
		} else if ($('.payment_types_dropdown').length) {
			$('.payment_types_dropdown').change();
		}
		set_payment_type_dropdown();
	});
	
	__page_leave_confirmation('#add_expense_form');
	$(document).on('change', 'input#final_total, input.payment-amount', function() {
		calculateExpensePaymentDue();
	});

	function calculateExpensePaymentDue() {
		var final_total = __read_number($('input#final_total'));
		var payment_amount = __read_number($('input.payment-amount'));
		var payment_due = final_total - payment_amount;
		$('#payment_due').text(__currency_trans_from_en(payment_due, true, false));
	}

	$(document).on('change', '#recur_interval_type', function() {
	    if ($(this).val() == 'months') {
	        $('.recur_repeat_on_div').removeClass('hide');
	    } else {
	        $('.recur_repeat_on_div').addClass('hide');
	    }
	});

	$('#is_refund').on('ifChecked', function(event){
		$('#recur_expense_div').addClass('hide');
	});
	$('#is_refund').on('ifUnchecked', function(event){
		$('#recur_expense_div').removeClass('hide');
	});

	$(document).on('change', 'select#location_id', function() {
		set_payment_type_dropdown();
		var location_id = $(this).val();
		var default_payment_accounts = $(this).find(':selected').data('default_payment_accounts');
		if (typeof default_payment_accounts === 'string') {
			try { default_payment_accounts = JSON.parse(default_payment_accounts); } catch(e) { default_payment_accounts = {}; }
		}

		if ($('.account-dropdown').length) {
			$.ajax({
				url: '/get-location-accounts/' + (location_id ? location_id : ''),
				dataType: 'json',
				success: function(accounts) {
					$('.account-dropdown').each(function() {
						var $acc_dropdown = $(this);
						if ($acc_dropdown.hasClass('select2-hidden-accessible')) {
							$acc_dropdown.select2('destroy');
						}
						$acc_dropdown.empty();
						var items = [];
						$.each(accounts, function(key, value) {
							items.push({ id: key, name: value });
						});
						items.sort(function(a, b) {
							if (a.id === '' || a.id === null) return -1;
							if (b.id === '' || b.id === null) return 1;
							return (a.name || '').localeCompare(b.name || '', undefined, { sensitivity: 'base', numeric: true });
						});
						$.each(items, function(i, item) {
							$acc_dropdown.append($('<option>', {
								value: item.id,
								text: item.name
							}));
						});
						$acc_dropdown.select2();
						var payment_row = $acc_dropdown.closest('.payment_row');
						var payment_type = payment_row.find('.payment_types_dropdown').val();
						if (payment_type && default_payment_accounts && default_payment_accounts[payment_type]) {
							var default_account = default_payment_accounts[payment_type]['account'] ? default_payment_accounts[payment_type]['account'] : '';
							$acc_dropdown.val(default_account).trigger('change');
						} else {
							$acc_dropdown.val('').trigger('change');
						}
					});
				}
			});
		}
	});

	$(document).on('change', '.payment_types_dropdown', function(e) {
	    var default_accounts = $('select#location_id').length ? 
	                $('select#location_id')
	                .find(':selected')
	                .data('default_payment_accounts') : [];
	    if (typeof default_accounts === 'string') {
	        try { default_accounts = JSON.parse(default_accounts); } catch(e) { default_accounts = {}; }
	    }
	    var payment_types_dropdown = $(this);
	    var payment_type = payment_types_dropdown.val();
	    var payment_row = payment_types_dropdown.closest('.payment_row');
	    var row_index = payment_row.find('.payment_row_index').val();

	    var account_dropdown = payment_row.find('select#account_' + row_index);
	    if (!account_dropdown.length) {
	        account_dropdown = payment_row.find('.account-dropdown');
	    }
	    if (payment_type && payment_type != 'advance') {
	        var default_account = default_accounts && default_accounts[payment_type] && default_accounts[payment_type]['account'] ? 
	            default_accounts[payment_type]['account'] : '';
	        if (account_dropdown.length && default_accounts) {
	            account_dropdown.val(default_account).trigger('change');
	        }
	    }

	    if (payment_type == 'advance') {
	        if (account_dropdown.length) {
	            account_dropdown.prop('disabled', true);
	            account_dropdown.closest('.form-group').addClass('hide');
	        }
	    } else {
	        if (account_dropdown.length) {
	            account_dropdown.prop('disabled', false); 
	            account_dropdown.closest('.form-group').removeClass('hide');
	        }    
	    }
	});

	function set_payment_type_dropdown() {
		var payment_settings = $('#location_id').find(':selected').data('default_payment_accounts');
		if (typeof payment_settings === 'string') {
		    try { payment_settings = JSON.parse(payment_settings); } catch(e) { payment_settings = {}; }
		}
		payment_settings = payment_settings ? payment_settings : {};
		enabled_payment_types = [];
		for (var key in payment_settings) {
			if (payment_settings[key] && payment_settings[key]['is_enabled']) {
				enabled_payment_types.push(key);
			}
		}
		if (enabled_payment_types.length) {
			$(".payment_types_dropdown > option").each(function() {
				//skip if advance
				if ($(this).val() && $(this).val() != 'advance') {
					if (enabled_payment_types.indexOf($(this).val()) != -1) {
						$(this).removeClass('hide');
					} else {
						$(this).addClass('hide');
					}
				}
			});
		}
	}
</script>
@endsection