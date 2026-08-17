@php 
    $colspan = 16;
    $custom_labels = json_decode(session('business.custom_labels'), true);
@endphp
<div id="product_table_top_actions" class="tw-flex tw-items-center tw-gap-2 tw-overflow-x-auto tw-flex-nowrap tw-my-2 tw-pb-1" style="display: flex; gap: 6px; flex-wrap: nowrap; overflow-x: auto; align-items: center; white-space: nowrap; -webkit-overflow-scrolling: touch; width: 100%;">
    @can('product.delete')
        {!! Form::open(['url' => action([\App\Http\Controllers\ProductController::class, 'massDestroy']), 'method' => 'post', 'id' => 'mass_delete_form_top', 'style' => 'display:inline-block; margin:0; flex-shrink:0;' ]) !!}
        {!! Form::hidden('selected_rows', null, ['id' => 'selected_rows_top']); !!}
        {!! Form::submit(__('lang_v1.delete_selected'), array('class' => 'tw-dw-btn tw-dw-btn-outline tw-dw-btn-xs tw-dw-btn-error delete-selected-btn tw-whitespace-nowrap', 'id' => 'delete-selected-top')) !!}
        {!! Form::close() !!}
    @endcan

    @can('product.update')
        @if(config('constants.enable_product_bulk_edit'))
            {!! Form::open(['url' => action([\App\Http\Controllers\ProductController::class, 'bulkEdit']), 'method' => 'post', 'id' => 'bulk_edit_form_top', 'style' => 'display:inline-block; margin:0; flex-shrink:0;' ]) !!}
            {!! Form::hidden('selected_products', null, ['id' => 'selected_products_for_edit_top']); !!}
            <button type="submit" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline tw-dw-btn-primary edit-selected-btn tw-whitespace-nowrap tw-flex-shrink-0" id="edit-selected-top"> <i class="fa fa-edit"></i>{{__('lang_v1.bulk_edit')}}</button>
            {!! Form::close() !!}
        @endif
        <button type="button" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline tw-dw-btn-accent update_product_location tw-whitespace-nowrap tw-flex-shrink-0" data-type="add">@lang('lang_v1.add_to_location')</button>
        <button type="button" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline tw-dw-btn-neutral update_product_location tw-whitespace-nowrap tw-flex-shrink-0" data-type="remove">@lang('lang_v1.remove_from_location')</button>
    @endcan

    {!! Form::open(['url' => action([\App\Http\Controllers\ProductController::class, 'massDeactivate']), 'method' => 'post', 'id' => 'mass_deactivate_form_top', 'style' => 'display:inline-block; margin:0; flex-shrink:0;' ]) !!}
    {!! Form::hidden('selected_products', null, ['id' => 'selected_products_top']); !!}
    {!! Form::submit(__('lang_v1.deactivate_selected'), array('class' => 'tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline tw-dw-btn-warning deactivate-selected-btn tw-whitespace-nowrap', 'id' => 'deactivate-selected-top')) !!}
    {!! Form::close() !!}
    <span class="tw-flex-shrink-0" style="display:inline-flex; align-items:center;">@show_tooltip(__('lang_v1.deactive_product_tooltip'))</span>

    @if($is_woocommerce)
        <button type="button" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline tw-dw-btn-warning toggle_woocomerce_sync tw-whitespace-nowrap tw-flex-shrink-0">
            @lang('lang_v1.woocommerce_sync')
        </button>
    @endif
</div>
<table class="table table-bordered table-striped ajax_view hide-footer" id="product_table">
    <thead>
        <tr>
            <th><input type="checkbox" id="select-all-row" data-table-id="product_table"></th>
            <th class="tw-w-full">{{__('lang_v1.product_image')}} </th>
            <th>@lang('messages.action')</th>
            <th>@lang('sale.product')</th>
            <th>@lang('product.sku')</th>
            <th>@lang('lang_v1.product_description')</th>
            <th>@lang('purchase.business_location') @show_tooltip(__('lang_v1.product_business_location_tooltip'))</th>
            @can('view_purchase_price')
                @php 
                    $colspan++;
                @endphp
                <th>@lang('lang_v1.unit_perchase_price')</th>
            @endcan
            @php 
                $colspan++;
            @endphp
            <th>@lang('lang_v1.selling_price')</th>
            <th>@lang('report.current_stock')</th>
            <th>@lang('product.product_type')</th>
            <th>@lang('product.category')</th>
            <th>@lang('product.brand')</th>
            <th>@lang('product.tax')</th>
            <th id="cf_1">{{ $custom_labels['product']['custom_field_1'] ?? '' }}</th>
            <th id="cf_2">{{ $custom_labels['product']['custom_field_2'] ?? '' }}</th>
            <th id="cf_3">{{ $custom_labels['product']['custom_field_3'] ?? '' }}</th>
            <th id="cf_4">{{ $custom_labels['product']['custom_field_4'] ?? '' }}</th>
            <th id="cf_5">{{ $custom_labels['product']['custom_field_5'] ?? '' }}</th>
            <th id="cf_6">{{ $custom_labels['product']['custom_field_6'] ?? '' }}</th>
            <th id="cf_7">{{ $custom_labels['product']['custom_field_7'] ?? '' }}</th>
        </tr>
    </thead>
    <tfoot>
        <tr>
            <td colspan="{{$colspan}}">
            <div class="tw-flex tw-items-center tw-gap-2 tw-overflow-x-auto tw-flex-nowrap tw-pb-1" style="display: flex; gap: 6px; flex-wrap: nowrap; overflow-x: auto; align-items: center; white-space: nowrap; -webkit-overflow-scrolling: touch; width: 100%;">
                @can('product.delete')
                    {!! Form::open(['url' => action([\App\Http\Controllers\ProductController::class, 'massDestroy']), 'method' => 'post', 'id' => 'mass_delete_form', 'style' => 'display:inline-block; margin:0; flex-shrink:0;' ]) !!}
                    {!! Form::hidden('selected_rows', null, ['id' => 'selected_rows']); !!}
                    {!! Form::submit(__('lang_v1.delete_selected'), array('class' => 'tw-dw-btn tw-dw-btn-outline tw-dw-btn-xs tw-dw-btn-error delete-selected-btn tw-whitespace-nowrap', 'id' => 'delete-selected')) !!}
                    {!! Form::close() !!}
                @endcan

                @can('product.update')
                    @if(config('constants.enable_product_bulk_edit'))
                        {!! Form::open(['url' => action([\App\Http\Controllers\ProductController::class, 'bulkEdit']), 'method' => 'post', 'id' => 'bulk_edit_form', 'style' => 'display:inline-block; margin:0; flex-shrink:0;' ]) !!}
                        {!! Form::hidden('selected_products', null, ['id' => 'selected_products_for_edit']); !!}
                        <button type="submit" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline tw-dw-btn-primary edit-selected-btn tw-whitespace-nowrap tw-flex-shrink-0" id="edit-selected"> <i class="fa fa-edit"></i>{{__('lang_v1.bulk_edit')}}</button>
                        {!! Form::close() !!}
                    @endif
                    <button type="button" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline tw-dw-btn-accent update_product_location tw-whitespace-nowrap tw-flex-shrink-0" data-type="add">@lang('lang_v1.add_to_location')</button>
                    <button type="button" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline tw-dw-btn-neutral update_product_location tw-whitespace-nowrap tw-flex-shrink-0" data-type="remove">@lang('lang_v1.remove_from_location')</button>
                @endcan

                {!! Form::open(['url' => action([\App\Http\Controllers\ProductController::class, 'massDeactivate']), 'method' => 'post', 'id' => 'mass_deactivate_form', 'style' => 'display:inline-block; margin:0; flex-shrink:0;' ]) !!}
                {!! Form::hidden('selected_products', null, ['id' => 'selected_products']); !!}
                {!! Form::submit(__('lang_v1.deactivate_selected'), array('class' => 'tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline tw-dw-btn-warning deactivate-selected-btn tw-whitespace-nowrap', 'id' => 'deactivate-selected')) !!}
                {!! Form::close() !!}
                <span class="tw-flex-shrink-0" style="display:inline-flex; align-items:center;">@show_tooltip(__('lang_v1.deactive_product_tooltip'))</span>

                @if($is_woocommerce)
                    <button type="button" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline tw-dw-btn-warning toggle_woocomerce_sync tw-whitespace-nowrap tw-flex-shrink-0">
                        @lang('lang_v1.woocommerce_sync')
                    </button>
                @endif
            </div>
            </td>
        </tr>
    </tfoot>
</table>
