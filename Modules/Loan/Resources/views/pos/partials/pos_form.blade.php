<div class="row">
    <div class="col-md-12">
        <div class="form-group">
            <div class="input-group">
                <span class="input-group-addon">
                    <i class="fa fa-user"></i>
                </span>
                {!! Form::select('contact_id', [], $walk_in_customer['id'], [
                    'class' => 'form-control select2',
                    'id' => 'customer_id',
                    'required',
                    'data-default' => $walk_in_customer['id'],
                ]) !!}
                <span class="input-group-btn">
                    <button type="button" class="btn btn-primary btn-modal" 
                        data-href="{{ action([\App\Http\Controllers\ContactController::class, 'create'], ['quick_add' => true]) }}"
                        data-container=".contact_modal">
                        <i class="fa fa-plus"></i> @lang('Loan::lang.add_new_customer')
                    </button>
                </span>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            {!! Form::label('transaction_date', __('lang_v1.date') . ':') !!}
            <div class="input-group">
                <span class="input-group-addon">
                    <i class="fa fa-calendar"></i>
                </span>
                {!! Form::text('transaction_date', $default_datetime, [
                    'class' => 'form-control',
                    'readonly',
                    'id' => 'transaction_date',
                ]) !!}
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            {!! Form::label('invoice_no', __('lang_v1.invoice_no') . ':') !!}
            {!! Form::text('invoice_no', null, ['class' => 'form-control', 'readonly']) !!}
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            {!! Form::label('status', __('lang_v1.status') . ':') !!}
            {!! Form::select('status', [
                'final' => __('lang_v1.final'),
                'draft' => __('lang_v1.draft'),
                'quotation' => __('lang_v1.quotation'),
            ], 'final', ['class' => 'form-control', 'id' => 'status']) !!}
        </div>
    </div>
</div>

<div class="row">
    @if (!empty($commission_agent))
        <div class="col-md-4">
            <div class="form-group">
                {!! Form::label('commission_agent', __('lang_v1.commission_agent') . ':') !!}
                {!! Form::select('commission_agent', $commission_agent, null, [
                    'class' => 'form-control select2',
                    'placeholder' => __('lang_v1.select_commission_agent'),
                ]) !!}
            </div>
        </div>
    @endif
    <div class="col-md-4">
        <div class="form-group">
            {!! Form::label('price_group', __('lang_v1.price_group') . ':') !!}
            {!! Form::select('price_group', $price_groups, $default_price_group_id, [
                'class' => 'form-control select2',
                'placeholder' => __('lang_v1.select_price_group'),
                'id' => 'price_group',
            ]) !!}
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            {!! Form::label('invoice_scheme_id', __('lang_v1.invoice_scheme') . ':') !!}
            {!! Form::select('invoice_scheme_id', $invoice_schemes, $default_invoice_schemes->id, [
                'class' => 'form-control select2',
                'id' => 'invoice_scheme_id',
            ]) !!}
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="form-group">
            {!! Form::label('search_product', __('lang_v1.search_product') . ':') !!}
            <div class="input-group">
                <span class="input-group-addon">
                    <i class="fa fa-search"></i>
                </span>
                {!! Form::text('search_product', null, [
                    'class' => 'form-control',
                    'id' => 'search_product',
                    'placeholder' => __('lang_v1.search_product_placeholder'),
                ]) !!}
                <span class="input-group-btn">
                    <button type="button" class="btn btn-primary" id="add_product_modal">
                        <i class="fa fa-plus"></i> @lang('lang_v1.add_product')
                    </button>
                </span>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="table-responsive">
            <table class="table table-bordered table-striped" id="pos_table">
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
                <tbody id="pos_table_body">
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        @if (!empty($types_of_service))
            <div class="form-group">
                {!! Form::label('types_of_service_id', __('lang_v1.types_of_service') . ':') !!}
                {!! Form::select('types_of_service_id', $types_of_service, null, [
                    'class' => 'form-control select2',
                    'id' => 'types_of_service_id',
                    'placeholder' => __('lang_v1.select_service_type'),
                ]) !!}
            </div>
        @endif
        @if ($is_tables_enabled)
            <div class="form-group">
                {!! Form::label('res_table_id', __('lang_v1.table') . ':') !!}
                {!! Form::select('res_table_id', $tables, null, [
                    'class' => 'form-control select2',
                    'placeholder' => __('lang_v1.select_table'),
                ]) !!}
            </div>
        @endif
        @if ($is_service_staff_enabled)
            <div class="form-group">
                {!! Form::label('res_waiter_id', __('lang_v1.service_staff') . ':') !!}
                {!! Form::select('res_waiter_id', $service_staffs, null, [
                    'class' => 'form-control select2',
                    'placeholder' => __('lang_v1.select_service_staff'),
                ]) !!}
            </div>
        @endif
        <div class="form-group">
            {!! Form::label('sale_note', __('lang_v1.sale_note') . ':') !!}
            {!! Form::textarea('sale_note', null, [
                'class' => 'form-control',
                'rows' => 3,
                'placeholder' => __('lang_v1.sale_note_placeholder'),
            ]) !!}
        </div>
    </div>
    <div class="col-md-6">
        @if (!empty($accounts))
            <div class="form-group">
                {!! Form::label('account_id', __('lang_v1.account') . ':') !!}
                {!! Form::select('account_id', $accounts, null, [
                    'class' => 'form-control select2',
                    'placeholder' => __('lang_v1.select_account'),
                ]) !!}
            </div>
        @endif
        @if (!empty($shipping_statuses))
            <div class="form-group">
                {!! Form::label('shipping_status', __('lang_v1.shipping_status') . ':') !!}
                {!! Form::select('shipping_status', $shipping_statuses, null, [
                    'class' => 'form-control select2',
                    'placeholder' => __('lang_v1.select_shipping_status'),
                ]) !!}
            </div>
        @endif
        <div class="form-group">
            {!! Form::label('shipping_details', __('lang_v1.shipping_details') . ':') !!}
            {!! Form::textarea('shipping_details', null, [
                'class' => 'form-control',
                'rows' => 3,
                'placeholder' => __('lang_v1.shipping_details_placeholder'),
            ]) !!}
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="form-group">
            {!! Form::label('additional_notes', __('lang_v1.additional_notes') . ':') !!}
            {!! Form::textarea('additional_notes', null, [
                'class' => 'form-control',
                'rows' => 3,
                'placeholder' => __('lang_v1.additional_notes_placeholder'),
            ]) !!}
        </div>
    </div>
</div>

<input type="hidden" name="is_direct_sale" value="0">
<input type="hidden" name="is_save_and_print" id="is_save_and_print" value="0">

@stop