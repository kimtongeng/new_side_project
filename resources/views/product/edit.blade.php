@extends('layouts.app')
@section('title', __('product.edit_product'))

@section('css')
<style>
    .gallery-upload-zone {
        border: 2px dashed #3c8dbc;
        background-color: #fcfdfe;
        border-radius: 8px;
        padding: 24px 15px;
        text-align: center;
        position: relative;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: inset 0 1px 2px rgba(0,0,0,0.05);
    }
    .gallery-upload-zone:hover {
        border-color: #357ca5;
        background-color: #f2f7fa;
    }
    .gallery-upload-zone input[type="file"] {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        opacity: 0;
        cursor: pointer;
        z-index: 10;
    }
    .upload-zone-content i {
        color: #3c8dbc;
        margin-bottom: 8px;
        transition: transform 0.2s ease;
    }
    .gallery-upload-zone:hover .upload-zone-content i {
        transform: translateY(-3px);
    }
    .upload-zone-content p {
        margin: 0;
        font-size: 13px;
        font-weight: 600;
        color: #444;
    }
    .upload-zone-content span {
        font-size: 11px;
        color: #777;
        display: block;
        margin-top: 3px;
    }
    .gallery-preview-item {
        position: relative;
        display: inline-block;
        width: 80px;
        height: 80px;
        border-radius: 6px;
        border: 1px solid #d2d6de;
        overflow: hidden;
        box-shadow: 0 2px 4px rgba(0,0,0,0.08);
        transition: transform 0.2s ease;
    }
    .gallery-preview-item:hover {
        transform: scale(1.05);
    }
    .gallery-preview-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .gallery-preview-item .remove-btn {
        position: absolute;
        top: 4px;
        right: 4px;
        background: rgba(217, 83, 79, 0.95);
        color: white;
        border: none;
        border-radius: 50%;
        width: 20px;
        height: 20px;
        font-size: 9px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: background 0.2s;
        z-index: 15;
    }
    .gallery-preview-item .remove-btn:hover {
        background: rgba(201, 48, 44, 1);
    }
    .gallery-preview-item .img-name {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        background: rgba(0, 0, 0, 0.7);
        color: #fff;
        font-size: 8px;
        padding: 1px 4px;
        text-align: center;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .variation_row_handle, .variation_value_handle {
        cursor: grab;
        color: #aaa;
        transition: color 0.2s;
    }
    .variation_row_handle:hover, .variation_value_handle:hover {
        color: #333;
    }
    .variation_row_handle:active, .variation_value_handle:active {
        cursor: grabbing;
    }
    /* Style for the element being dragged when forceFallback is true */
    .sortable-drag {
        background: #e8f4fd !important;
        opacity: 0.8;
        box-shadow: 0 5px 15px rgba(0,0,0,0.15);
    }
    /* Row being dragged placeholder */
    tr.pos-dragging {
        opacity: 0.4;
        background: #e8f4fd !important;
    }
    .ui-state-highlight {
        background-color: #fcf8e3;
        border: 1px dashed #fbeed5;
        height: 45px;
    }
</style>
@endsection

@section('content')

@php
  $is_image_required = !empty($common_settings['is_product_image_required']) && empty($product->image);
@endphp

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">@lang('product.edit_product')</h1>
    <!-- <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
        <li class="active">Here</li>
    </ol> -->
</section>

<!-- Main content -->
<section class="content">
{!! Form::open(['url' => action([\App\Http\Controllers\ProductController::class, 'update'] , [$product->id] ), 'method' => 'PUT', 'id' => 'product_add_form',
        'class' => 'product_form', 'files' => true ]) !!}
    <input type="hidden" id="product_id" value="{{ $product->id }}">

    @component('components.widget', ['class' => 'box-primary'])
        <div class="row">
            <div class="col-sm-4">
              <div class="form-group">
                {!! Form::label('name', __('product.product_name') . ':*') !!}
                  {!! Form::text('name', $product->name, ['class' => 'form-control', 'required',
                  'placeholder' => __('product.product_name')]); !!}
              </div>
            </div>

            <div class="col-sm-4">
              <div class="form-group">
                {!! Form::label('secondary_name', 'Second Language Name:') !!}
                {!! Form::text('secondary_name', $product->secondary_name, ['class' => 'form-control',
                'placeholder' => 'Second Language Name (e.g. Khmer Name)']); !!}
              </div>
            </div>

            <div class="col-sm-4">
              <div class="form-group">
                {!! Form::label('sku', __('product.sku')  . ':*') !!} @show_tooltip(__('tooltip.sku'))
                 {!! Form::text('sku', $product->sku, ['class' => 'form-control',
                 'placeholder' => __('product.sku'), 'required', 'readonly' => !auth()->user()->can('product.rename_sku')]); !!}
              </div>
            </div>

            <div class="col-sm-4">
              <div class="form-group">
                {!! Form::label('barcode_type', __('product.barcode_type') . ':*') !!}
                  {!! Form::select('barcode_type', $barcode_types, $product->barcode_type, ['placeholder' => __('messages.please_select'), 'class' => 'form-control select2', 'required']); !!}
              </div>
            </div>

            <div class="clearfix"></div>
            
            <div class="col-sm-4">
              <div class="form-group">
                {!! Form::label('unit_id', __('product.unit') . ':*') !!}
                <div class="input-group">
                  {!! Form::select('unit_id', $units, $product->unit_id, ['placeholder' => __('messages.please_select'), 'class' => 'form-control select2', 'required']); !!}
                  <span class="input-group-btn">
                    <button type="button" @if(!auth()->user()->can('unit.create')) disabled @endif class="btn btn-default bg-white btn-flat quick_add_unit btn-modal" data-href="{{action([\App\Http\Controllers\UnitController::class, 'create'], ['quick_add' => true])}}" title="@lang('unit.add_unit')" data-container=".view_modal"><i class="fa fa-plus-circle text-primary fa-lg"></i></button>
                  </span>
                </div>
              </div>
            </div>

            <div class="col-sm-4 @if(!session('business.enable_sub_units')) hide @endif">
              <div class="form-group">
                {!! Form::label('sub_unit_ids', __('lang_v1.related_sub_units') . ':') !!} @show_tooltip(__('lang_v1.sub_units_tooltip'))

                <select name="sub_unit_ids[]" class="form-control select2" multiple id="sub_unit_ids">
                  @foreach($sub_units as $sub_unit_id => $sub_unit_value)
                    <option value="{{$sub_unit_id}}" 
                      @if(is_array($product->sub_unit_ids) &&in_array($sub_unit_id, $product->sub_unit_ids))   selected 
                      @endif>{{$sub_unit_value['name']}}</option>
                  @endforeach
                </select>
              </div>
            </div>

            @if(!empty($common_settings['enable_secondary_unit']))
                <div class="col-sm-4">
                    <div class="form-group">
                        {!! Form::label('secondary_unit_id', __('lang_v1.secondary_unit') . ':') !!} @show_tooltip(__('lang_v1.secondary_unit_help'))
                        {!! Form::select('secondary_unit_id', $units, $product->secondary_unit_id, ['class' => 'form-control select2']); !!}
                    </div>
                </div>
            @endif

            <div class="col-sm-4 @if(!session('business.enable_brand')) hide @endif">
              <div class="form-group">
                {!! Form::label('brand_id', __('product.brand') . ':') !!}
                <div class="input-group">
                  {!! Form::select('brand_id', $brands, $product->brand_id, ['placeholder' => __('messages.please_select'), 'class' => 'form-control select2']); !!}
                  <span class="input-group-btn">
                    <button type="button" @if(!auth()->user()->can('brand.create')) disabled @endif class="btn btn-default bg-white btn-flat btn-modal" data-href="{{action([\App\Http\Controllers\BrandController::class, 'create'], ['quick_add' => true])}}" title="@lang('brand.add_brand')" data-container=".view_modal"><i class="fa fa-plus-circle text-primary fa-lg"></i></button>
                  </span>
                </div>
              </div>
            </div>
            <div class="col-sm-4 @if(!session('business.enable_category')) hide @endif">
              <div class="form-group">
                {!! Form::label('category_id', __('product.category') . ':') !!}
                  {!! Form::select('category_id', $categories, $product->category_id, ['placeholder' => __('messages.please_select'), 'class' => 'form-control select2']); !!}
              </div>
            </div>

            <div class="col-sm-4 @if(!(session('business.enable_category') && session('business.enable_sub_category'))) hide @endif">
              <div class="form-group">
                {!! Form::label('sub_category_id', __('product.sub_category')  . ':') !!}
                  {!! Form::select('sub_category_id', $sub_categories, $product->sub_category_id, ['placeholder' => __('messages.please_select'), 'class' => 'form-control select2']); !!}
              </div>
            </div>

            <div class="col-sm-4">
              <div class="form-group">
                {!! Form::label('product_locations', __('business.business_locations') . ':') !!} @show_tooltip(__('lang_v1.product_location_help'))
                  {!! Form::select('product_locations[]', $business_locations, $product->product_locations->pluck('id'), ['class' => 'form-control select2', 'multiple', 'id' => 'product_locations']); !!}
              </div>
            </div>

            <div class="clearfix"></div>
            
            <div class="col-sm-4">
              <div class="form-group">
              <br>
                <label>
                  {!! Form::checkbox('enable_stock', 1, $product->enable_stock, ['class' => 'input-icheck', 'id' => 'enable_stock']); !!} <strong>@lang('product.manage_stock')</strong>
                </label>@show_tooltip(__('tooltip.enable_stock')) <p class="help-block"><i>@lang('product.enable_stock_help')</i></p>
              </div>
            </div>
            <div class="col-sm-4" id="alert_quantity_div" @if(!$product->enable_stock) style="display:none" @endif>
              <div class="form-group">
                {!! Form::label('alert_quantity', __('product.alert_quantity') . ':') !!} @show_tooltip(__('tooltip.alert_quantity'))
                {!! Form::text('alert_quantity', $alert_quantity, ['class' => 'form-control input_number',
                'placeholder' => __('product.alert_quantity') , 'min' => '0']); !!}
              </div>
            </div>
            @if(!empty($common_settings['enable_product_warranty']))
            <div class="col-sm-4">
              <div class="form-group">
                {!! Form::label('warranty_id', __('lang_v1.warranty') . ':') !!}
                {!! Form::select('warranty_id', $warranties, $product->warranty_id, ['class' => 'form-control select2', 'placeholder' => __('messages.please_select')]); !!}
              </div>
            </div>
            @endif
            <!-- include module fields -->
            @if(!empty($pos_module_data))
                @foreach($pos_module_data as $key => $value)
                    @if(!empty($value['view_path']))
                        @includeIf($value['view_path'], ['view_data' => $value['view_data']])
                    @endif
                @endforeach
            @endif
            <div class="clearfix"></div>
            <div class="col-sm-8">
              <div class="form-group">
                {!! Form::label('product_description', __('lang_v1.product_description') . ':') !!}
                  {!! Form::textarea('product_description', $product->product_description, ['class' => 'form-control']); !!}
              </div>
            </div>
            <div class="col-sm-4">
              <div class="form-group">
                {!! Form::label('image', __('lang_v1.product_image') . ':') !!}
                {!! Form::file('image', ['id' => 'upload_image', 'accept' => 'image/*', 'required' => $is_image_required]); !!}
                <small><p class="help-block">@lang('purchase.max_file_size', ['size' => (config('constants.document_size_limit') / 1000000)]). @lang('lang_v1.aspect_ratio_should_be_1_1') @if(!empty($product->image)) <br> @lang('lang_v1.previous_image_will_be_replaced') @endif</p></small>
              </div>
            </div>
            <div class="col-sm-4">
              <div class="form-group">
                {!! Form::label('product_images', __('lang_v1.product_gallery') . ':') !!}
                <div class="gallery-upload-container">
                    <div class="gallery-upload-zone" id="gallery-dropzone">
                        {!! Form::file('product_images[]', ['id' => 'product_images', 'accept' => 'image/*', 'multiple']) !!}
                        <div class="upload-zone-content">
                            <i class="fas fa-cloud-upload-alt fa-3x"></i>
                            <p>Drag & drop gallery images here</p>
                            <span>or click to browse from device</span>
                            <small class="tw-block tw-mt-2 tw-text-gray-400">
                                @lang('purchase.max_file_size', ['size' => (config('constants.document_size_limit') / 1000000)]) MB
                            </small>
                        </div>
                    </div>
                    
                    <div id="gallery-preview-container" class="tw-mt-4 tw-hidden">
                        <h5 class="tw-text-xs tw-font-bold tw-text-gray-700 tw-mb-2">Selected images for upload:</h5>
                        <div id="gallery-preview-grid" class="tw-flex tw-flex-wrap tw-gap-2"></div>
                    </div>
                </div>
                @php
                    $gallery_images = $product->media->where('model_media_type', 'product_gallery');
                @endphp
                @if($gallery_images->count() > 0)
                    <div class="row" style="margin-top: 10px;">
                        @foreach($gallery_images as $media)
                            <div class="col-xs-4 col-sm-3 img-thumbnail text-center" style="margin: 5px; position: relative;">
                                <img src="{{$media->display_url}}" class="img-responsive" style="max-height: 80px; display: inline-block;">
                                <br>
                                <span class="badge bg-red delete-media" data-href="{{ action([\App\Http\Controllers\ProductController::class, 'deleteMedia'], ['media_id' => $media->id])}}" style="cursor: pointer; position: absolute; top: -5px; right: -5px; z-index: 10;">
                                    <i class="fas fa-times"></i>
                                </span>
                            </div>
                        @endforeach
                    </div>
                @endif
              </div>
            </div>
            </div>
            <div class="col-sm-4">
              <div class="form-group">
                {!! Form::label('product_brochure', __('lang_v1.product_brochure') . ':') !!}
                {!! Form::file('product_brochure', ['id' => 'product_brochure', 'accept' => implode(',', array_keys(config('constants.document_upload_mimes_types')))]); !!}
                <small>
                    <p class="help-block">
                        @lang('lang_v1.previous_file_will_be_replaced')<br>
                        @lang('purchase.max_file_size', ['size' => (config('constants.document_size_limit') / 1000000)])
                        @includeIf('components.document_help_text')
                    </p>
                </small>
              </div>
            </div>
    @endcomponent

    @component('components.widget', ['class' => 'box-primary'])
        <div class="row">
        @if(session('business.enable_product_expiry'))

          @if(session('business.expiry_type') == 'add_expiry')
            @php
              $expiry_period = 12;
              $hide = true;
            @endphp
          @else
            @php
              $expiry_period = null;
              $hide = false;
            @endphp
          @endif
          <div class="col-sm-4 @if($hide) hide @endif">
            <div class="form-group">
              <div class="multi-input">
                @php
                  $disabled = false;
                  $disabled_period = false;
                  if( empty($product->expiry_period_type) || empty($product->enable_stock) ){
                    $disabled = true;
                  }
                  if( empty($product->enable_stock) ){
                    $disabled_period = true;
                  }
                @endphp
                  {!! Form::label('expiry_period', __('product.expires_in') . ':') !!}<br>
                  {!! Form::text('expiry_period', @num_format($product->expiry_period), ['class' => 'form-control pull-left input_number',
                    'placeholder' => __('product.expiry_period'), 'style' => 'width:60%;', 'disabled' => $disabled]); !!}
                  {!! Form::select('expiry_period_type', ['months'=>__('product.months'), 'days'=>__('product.days'), '' =>__('product.not_applicable') ], $product->expiry_period_type, ['class' => 'form-control select2 pull-left', 'style' => 'width:40%;', 'id' => 'expiry_period_type', 'disabled' => $disabled_period]); !!}
              </div>
            </div>
          </div>
          @endif
          <div class="col-sm-4">
            <div class="checkbox">
              <label>
                {!! Form::checkbox('enable_sr_no', 1, $product->enable_sr_no, ['class' => 'input-icheck']); !!} <strong>@lang('lang_v1.enable_imei_or_sr_no')</strong>
              </label>
              @show_tooltip(__('lang_v1.tooltip_sr_no'))
            </div>
          </div>

          <div class="col-sm-4">
          <div class="form-group">
            <br>
            <label>
              {!! Form::checkbox('not_for_selling', 1, $product->not_for_selling, ['class' => 'input-icheck']); !!} <strong>@lang('lang_v1.not_for_selling')</strong>
            </label> @show_tooltip(__('lang_v1.tooltip_not_for_selling'))
          </div>
        </div>

        <div class="clearfix"></div>

        <!-- Rack, Row & position number -->
        @if(session('business.enable_racks') || session('business.enable_row') || session('business.enable_position'))
          <div class="col-md-12">
            <h4>@lang('lang_v1.rack_details'):
              @show_tooltip(__('lang_v1.tooltip_rack_details'))
            </h4>
          </div>
          @foreach($business_locations as $id => $location)
            <div class="col-sm-3">
              <div class="form-group">
                {!! Form::label('rack_' . $id,  $location . ':') !!}

                
                  @if(!empty($rack_details[$id]))
                    @if(session('business.enable_racks'))
                      {!! Form::text('product_racks_update[' . $id . '][rack]', $rack_details[$id]['rack'], ['class' => 'form-control', 'id' => 'rack_' . $id]); !!}
                    @endif

                    @if(session('business.enable_row'))
                      {!! Form::text('product_racks_update[' . $id . '][row]', $rack_details[$id]['row'], ['class' => 'form-control']); !!}
                    @endif

                    @if(session('business.enable_position'))
                      {!! Form::text('product_racks_update[' . $id . '][position]', $rack_details[$id]['position'], ['class' => 'form-control']); !!}
                    @endif
                  @else
                    {!! Form::text('product_racks[' . $id . '][rack]', null, ['class' => 'form-control', 'id' => 'rack_' . $id, 'placeholder' => __('lang_v1.rack')]); !!}

                    {!! Form::text('product_racks[' . $id . '][row]', null, ['class' => 'form-control', 'placeholder' => __('lang_v1.row')]); !!}

                    {!! Form::text('product_racks[' . $id . '][position]', null, ['class' => 'form-control', 'placeholder' => __('lang_v1.position')]); !!}
                  @endif

              </div>
            </div>
          @endforeach
        @endif


        <div class="col-sm-4">
          <div class="form-group">
            {!! Form::label('weight',  __('lang_v1.weight') . ':') !!}
            {!! Form::text('weight', $product->weight, ['class' => 'form-control', 'placeholder' => __('lang_v1.weight')]); !!}
          </div>
        </div>
        <div class="clearfix"></div>
        
        @php
            $custom_labels = json_decode(session('business.custom_labels'), true);
            $product_custom_fields = !empty($custom_labels['product']) ? $custom_labels['product'] : [];
            $product_cf_details = !empty($custom_labels['product_cf_details']) ? $custom_labels['product_cf_details'] : [];
        @endphp
        <!--custom fields-->

        @foreach($product_custom_fields as $index => $cf)
            @if(!empty($cf))
                @php
                    $db_field_name = 'product_custom_field' . $loop->iteration;
                    $cf_type = !empty($product_cf_details[$loop->iteration]['type']) ? $product_cf_details[$loop->iteration]['type'] : 'text';
                    $dropdown = !empty($product_cf_details[$loop->iteration]['dropdown_options']) ? explode(PHP_EOL, $product_cf_details[$loop->iteration]['dropdown_options']) : [];
                @endphp

                <div class="col-sm-3">
                    <div class="form-group">
                        {!! Form::label($db_field_name, $cf . ':') !!}
                        @if(in_array($cf_type, ['text', 'date']))
                            <input type="{{$cf_type}}" name="{{$db_field_name}}" id="{{$db_field_name}}" 
                            value="{{$product->$db_field_name}}" class="form-control" placeholder="{{$cf}}">
                        @elseif($cf_type == 'dropdown')
                            {!! Form::select($db_field_name, $dropdown, $product->$db_field_name, ['placeholder' => $cf, 'class' => 'form-control select2']); !!}
                        @endif
                    </div>
                </div>
            @endif
        @endforeach

        <div class="col-sm-3">
          <div class="form-group">
            {!! Form::label('preparation_time_in_minutes',  __('lang_v1.preparation_time_in_minutes') . ':') !!}
            {!! Form::number('preparation_time_in_minutes', $product->preparation_time_in_minutes, ['class' => 'form-control', 'placeholder' => __('lang_v1.preparation_time_in_minutes')]); !!}
          </div>
        </div>
        <!--custom fields-->
        @include('layouts.partials.module_form_part')
        </div>
    @endcomponent

    @component('components.widget', ['class' => 'box-primary'])
        <div class="row">
            <div class="col-sm-4 @if(!session('business.enable_price_tax')) hide @endif">
              <div class="form-group">
                {!! Form::label('tax', __('product.applicable_tax') . ':') !!}
                  {!! Form::select('tax', $taxes, $product->tax, ['placeholder' => __('messages.please_select'), 'class' => 'form-control select2'], $tax_attributes); !!}
              </div>
            </div>

            <div class="col-sm-4 @if(!session('business.enable_price_tax')) hide @endif">
              <div class="form-group">
                {!! Form::label('tax_type', __('product.selling_price_tax_type') . ':*') !!}
                  {!! Form::select('tax_type',['inclusive' => __('product.inclusive'), 'exclusive' => __('product.exclusive')], $product->tax_type,
                  ['class' => 'form-control select2', 'required']); !!}
              </div>
            </div>

            <div class="clearfix"></div>
            <div class="col-sm-4">
              <div class="form-group">
                {!! Form::label('type', __('product.product_type') . ':*') !!} @show_tooltip(__('tooltip.product_type'))
                {!! Form::select('type', $product_types, $product->type, ['class' => 'form-control select2',
                  'required','disabled', 'data-action' => 'edit', 'data-product_id' => $product->id ]); !!}
              </div>
            </div>

            <div class="form-group col-sm-12" id="product_form_part"></div>
            <input type="hidden" id="variation_counter" value="0">
            <input type="hidden" id="default_profit_percent" value="{{ $default_profit_percent }}">
            </div>
    @endcomponent

  <div class="row">
    <input type="hidden" name="submit_type" id="submit_type">
        <div class="col-sm-12">
          <div class="text-center">
            <div class="btn-group">
              @if($selling_price_group_count)
                <button type="submit" value="submit_n_add_selling_prices" class="tw-dw-btn tw-dw-btn-warning tw-text-white tw-dw-btn-lg submit_product_form">@lang('lang_v1.save_n_add_selling_price_group_prices')</button>
              @endif

              @can('product.opening_stock')
              <button type="submit" @if(empty($product->enable_stock)) disabled="true" @endif id="opening_stock_button"  value="update_n_edit_opening_stock" class="tw-dw-btn tw-text-white tw-dw-btn-lg bg-purple submit_product_form">@lang('lang_v1.update_n_edit_opening_stock')</button>
              @endif

              <button type="submit" value="save_n_add_another" class="tw-dw-btn tw-text-white tw-dw-btn-lg bg-maroon submit_product_form">@lang('lang_v1.update_n_add_another')</button>

              <button type="submit" value="submit" class="tw-dw-btn tw-dw-btn-primary tw-text-white tw-dw-btn-lg submit_product_form">@lang('messages.update')</button>
            </div>
          </div>
        </div>
  </div>
{!! Form::close() !!}
</section>
<!-- /.content -->

@endsection

@section('javascript')
  <!-- SortableJS for drag-and-drop (local copy, conflict-free) -->
  <script src="{{ asset('js/sortable.min.js?v=' . $asset_v) }}"></script>
  <!-- CDN fallback in case local file is missing -->
  <script>
    if (typeof Sortable === 'undefined') {
        document.write('<scr' + 'ipt src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"><\/scr' + 'ipt>');
    }
  </script>
  <script src="{{ asset('js/product.js?v=' . $asset_v) }}"></script>
  <script type="text/javascript">
    $(document).ready( function(){
      __page_leave_confirmation('#product_add_form');

      // Product Gallery Drag and Drop styling and preview logic
      $(document).on('change', '#product_images', function(e) {
          var galleryInput = this;
          var previewGrid = $('#gallery-preview-grid');
          var previewContainer = $('#gallery-preview-container');
          
          var dt = $(galleryInput).data('dataTransfer');
          if (!dt) {
              dt = new DataTransfer();
          }
          
          for (var i = 0; i < this.files.length; i++) {
              dt.items.add(this.files[i]);
          }
          
          $(galleryInput).data('dataTransfer', dt);
          this.files = dt.files;
          
          updateGalleryPreviews(galleryInput, previewGrid, previewContainer, dt);
      });

      function updateGalleryPreviews(input, grid, container, dt) {
          grid.empty();
          
          if (dt.files.length === 0) {
              container.addClass('tw-hidden');
              return;
          }

          container.removeClass('tw-hidden');

          Array.from(dt.files).forEach(function(file, index) {
              var reader = new FileReader();
              reader.onload = function(e) {
                  var previewItem = $('<div class="gallery-preview-item"></div>');
                  var img = $('<img src="' + e.target.result + '" alt="preview">');
                  var removeBtn = $('<button type="button" class="remove-btn"><i class="fa fa-times"></i></button>');
                  var nameSpan = $('<span class="img-name">' + file.name + '</span>');
                  
                  removeBtn.on('click', function(evt) {
                      evt.stopPropagation();
                      evt.preventDefault();
                      
                      dt.items.remove(index);
                      input.files = dt.files;
                      updateGalleryPreviews(input, grid, container, dt);
                  });
                  
                  previewItem.append(img).append(removeBtn).append(nameSpan);
                  grid.append(previewItem);
              };
              reader.readAsDataURL(file);
          });
      }

      // Initialize sortable and index-handling for variations
      function initialize_variation_sortable() {
          // Main product variations
          var mainTbody = document.querySelector('#product_variation_form_part > tbody');
          if (mainTbody) {
              if (mainTbody.__sortable) {
                  mainTbody.__sortable.destroy();
              }
              mainTbody.__sortable = Sortable.create(mainTbody, {
                  handle: '.variation_row_handle',
                  draggable: '.variation_row',
                  animation: 150,
                  forceFallback: true,
                  ghostClass: 'pos-dragging',
                  dragClass: 'sortable-drag',
                  onEnd: function (evt) {
                      reindex_variations();
                  }
              });
          }

          // Value tables for each variation
          document.querySelectorAll('table.variation_value_table > tbody').forEach(function(valTbody) {
              if (valTbody.__sortable) {
                  valTbody.__sortable.destroy();
              }
              valTbody.__sortable = Sortable.create(valTbody, {
                  handle: '.variation_value_handle',
                  draggable: 'tr',
                  animation: 150,
                  forceFallback: true,
                  ghostClass: 'pos-dragging',
                  dragClass: 'sortable-drag',
                  onEnd: function (evt) {
                      reindex_variations();
                  }
              });
          });
      }

      function reindex_variations() {
          $('#product_variation_form_part > tbody > tr.variation_row').each(function(i) {
              var tr = $(this);
              var is_edit_group = tr.find('.row_edit').val() === 'edit';
              var old_group_idx = tr.find('.row_index').val();
              var new_group_idx = is_edit_group ? old_group_idx : i;

              if (!is_edit_group && old_group_idx !== String(new_group_idx)) {
                  tr.find('.row_index').val(new_group_idx);

                  tr.find('input, select, textarea').each(function() {
                      var name = $(this).attr('name');
                      if (name) {
                          var new_name = name.replace('product_variation[' + old_group_idx + ']', 'product_variation[' + new_group_idx + ']');
                          $(this).attr('name', new_name);
                      }
                  });

                  tr.find('input[type="file"].variation_images').each(function() {
                      var name = $(this).attr('name');
                      if (name) {
                          var new_name = name.replace('variation_images_' + old_group_idx + '_', 'variation_images_' + new_group_idx + '_');
                          new_name = new_name.replace('edit_variation_images_' + old_group_idx + '_', 'edit_variation_images_' + new_group_idx + '_');
                          $(this).attr('name', new_name);
                      }
                  });
              }

              var val_table = tr.find('table.variation_value_table');
              val_table.find('tbody > tr').each(function(j) {
                  var val_tr = $(this);
                  var is_edit_val = val_tr.find('.row_variation_id').length > 0 || val_tr.attr('data-variation_value_id') !== undefined;
                  var has_edit_name = val_tr.find('input[name*="[variations_edit]"]').length > 0;
                  
                  if (!is_edit_val && !has_edit_name) {
                      var old_val_idx = val_tr.find('.variation_row_index').val();
                      
                      if (old_val_idx !== String(j)) {
                          val_tr.find('.variation_row_index').val(j);
                          
                          val_tr.find('input, select, textarea').each(function() {
                              var name = $(this).attr('name');
                              if (name) {
                                  var new_name = name.replace(/\[variations\]\[\d+\]/, '[variations][' + j + ']');
                                  $(this).attr('name', new_name);
                              }
                          });

                          val_tr.find('input[type="file"].variation_images').each(function() {
                              var name = $(this).attr('name');
                              if (name) {
                                  var regex = new RegExp('(variation_images_' + new_group_idx + '_)\\d+(\\[\\])?');
                                  var new_name = name.replace(regex, '$1' + j + '$2');
                                  
                                  var regex_edit = new RegExp('(edit_variation_images_' + new_group_idx + '_)\\d+(\\[\\])?');
                                  new_name = new_name.replace(regex_edit, '$1' + j + '$2');
                                  
                                  $(this).attr('name', new_name);
                              }
                          });
                      }
                  }
              });
          });
      }

      // Initialize sortables
      initialize_variation_sortable();

      // Re-initialize sortables after Ajax calls load variations or variation value rows
      $(document).ajaxComplete(function(event, xhr, settings) {
          if (settings.url.indexOf('/products/product_form_part') !== -1 || 
              settings.url.indexOf('/products/get_variation_value_row') !== -1 ||
              settings.url.indexOf('/products/get_variation_template') !== -1) {
              initialize_variation_sortable();
          }
      });
    });
  </script>
@endsection