@extends('layouts.app')
@section('title', __( 'productcatalogue::lang.catalogue_qr' ))

@section('content')
<!-- Content Header (Page header) -->
<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">@lang( 'productcatalogue::lang.catalogue_qr' )</h1>
</section>
<section class="content">
    <div class="row">
        <div class="col-md-7">
    	@component('components.widget', ['class' => 'box-solid'])
            <div class="form-group">
                {!! Form::label('location_id', __('purchase.business_location').':') !!}
                {!! Form::select('location_id', $business_locations, null, ['class' => 'form-control', 'placeholder' => __('messages.please_select')]); !!}
            </div>
            <div class="form-group">
                {!! Form::label('color', __('productcatalogue::lang.qr_code_color').':') !!}
                {!! Form::text('color', '#000000', ['class' => 'form-control']); !!}
            </div>
            <div class="form-group">
                {!! Form::label('title', __('productcatalogue::lang.title').':') !!}
                {!! Form::text('title', $business->name, ['class' => 'form-control']); !!}
            </div>
            <div class="form-group">
                {!! Form::label('subtitle', __('productcatalogue::lang.subtitle').':') !!}
                {!! Form::text('subtitle', __('productcatalogue::lang.product_catalogue'), ['class' => 'form-control']); !!}
            </div>
            <div class="form-group">
                <div class="checkbox">
                    <label>
                        {!! Form::checkbox('add_logo', 1, true, ['id' => 'show_logo', 'class' => 'input-icheck']); !!} @lang('productcatalogue::lang.show_business_logo_on_qrcode')
                    </label>
                </div>
            </div>
            <div class="col-md-6">
            <button type="button" class="tw-dw-btn tw-dw-btn-primary tw-dw-btn-sm tw-text-white" id="generate_qr">@lang('productcatalogue::lang.generate_qr')</button>
            </div>
            <div class="col-md-6">
                    <strong>@lang('lang_v1.instruction'):</strong>
                    <ul class="tw-mt-2 tw-mb-0 tw-pl-0">
                        <li class="tw-flex tw-gap-2">
                            <span class="tw-font-semibold tw-min-w-[1.25rem] tw-text-right">1.</span>
                            <span>@lang( 'productcatalogue::lang.catalogue_instruction_1' )</span>
                        </li>
                        <li class="tw-flex tw-gap-2">
                            <span class="tw-font-semibold tw-min-w-[1.25rem] tw-text-right">2.</span>
                            <span>@lang( 'productcatalogue::lang.catalogue_instruction_2' )</span>
                        </li>
                        <li class="tw-flex tw-gap-2">
                            <span class="tw-font-semibold tw-min-w-[1.25rem] tw-text-right">3.</span>
                            <span>@lang( 'productcatalogue::lang.catalogue_instruction_3' )</span>
                        </li>
                    </ul>
                </div>


        @endcomponent

        @component('components.widget', ['class' => 'box-solid'])
            <div class="row">
                <div class="col-md-12">
                    <h4>@lang('productcatalogue::lang.setting'):</h4>
                    {!! Form::open(['url' => action([\Modules\ProductCatalogue\Http\Controllers\ProductCatalogueController::class, 'productCatalogueSetting']), 'method' => 'post']) !!}
                        {!! Form::label('is_show', __('productcatalogue::lang.outofstock_products').':') !!}
                        <div class="form-inline">
                        <div class="form-group">
                        @php
                            $settings = json_decode($business->productcatalogue_settings, true);
                            $is_show = $settings['is_show'] ?? '';
                            $whatsapp_number = $settings['whatsapp_number'] ?? '';
                        @endphp
                            <div class="checkbox">
                                <label>
                                    {!! Form::radio('is_show', 1, $is_show == 1 ? true : false, ['id' => 'show_logo', 'class' => 'input-icheck', 'required']); !!} @lang('productcatalogue::lang.show')
                                </label>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="checkbox">
                                <label>
                                    {!! Form::radio('is_show', 0, $is_show == 0 ? true : false, ['id' => 'show_logo', 'class' => 'input-icheck', 'required']); !!} @lang('productcatalogue::lang.hide')
                                </label>
                            </div>
                        </div>
                    </div> <br>
                    <button type="submit" class="tw-dw-btn tw-dw-btn-primary tw-dw-btn-sm tw-text-white" id="">@lang('productcatalogue::lang.save')</button>
                {!! Form::close() !!}
                </div>
              
            </div>
        @endcomponent

        @component('components.widget', ['class' => 'box-solid'])
            <div class="row">
                <div class="col-md-6">
                    <h4>@lang('productcatalogue::lang.whatsapp_order_settings'):</h4>
                    {!! Form::open(['url' => action([\Modules\ProductCatalogue\Http\Controllers\ProductCatalogueController::class, 'productCatalogueSetting']), 'method' => 'post']) !!}
                        @php
                            $settings = json_decode($business->productcatalogue_settings, true);
                            $enable_whatsapp_ordering = $settings['enable_whatsapp_ordering'] ?? 0;
                            $order_receiving_whatsapp_number = $settings['order_receiving_whatsapp_number'] ?? '';
                            $customer_field_settings = $settings['customer_field_settings'] ?? [];
                            $customer_fields_config = [
                                'name' => __('productcatalogue::lang.your_name'),
                                'email' => __('productcatalogue::lang.email_address'),
                                'phone' => __('productcatalogue::lang.phone_number'),
                            ];
                        @endphp
                        <div class="form-group">
                            {!! Form::hidden('enable_whatsapp_ordering', 0) !!}
                            <div class="checkbox">
                                <label>
                                    {!! Form::checkbox('enable_whatsapp_ordering', 1, $enable_whatsapp_ordering == 1 ? true : false, ['id' => 'enable_whatsapp_ordering', 'class' => 'input-icheck']); !!} @lang('productcatalogue::lang.enable_whatsapp_ordering')
                                </label>
                            </div>
                        </div>
                        <div class="form-group">
                            {!! Form::label('order_receiving_whatsapp_number', __('productcatalogue::lang.order_receiving_whatsapp_number').':') !!}
                            {!! Form::text('order_receiving_whatsapp_number', $order_receiving_whatsapp_number, ['class' => 'form-control', 'placeholder' => __('productcatalogue::lang.order_receiving_whatsapp_number_placeholder')]); !!}
                        </div>

                        <div class="form-group">
                            <label class="tw-font-bold">@lang('productcatalogue::lang.customer_details_fields'):</label>
                            <p class="tw-text-sm tw-text-gray-500 tw-mb-2">@lang('productcatalogue::lang.customer_details_fields_help')</p>
                            <div class="table-responsive">
                                <table class="table table-bordered table-condensed">
                                    <thead>
                                        <tr>
                                            <th>@lang('productcatalogue::lang.field')</th>
                                            <th class="tw-text-center">@lang('productcatalogue::lang.required')</th>
                                            <th class="tw-text-center">@lang('productcatalogue::lang.optional')</th>
                                            <th class="tw-text-center">@lang('productcatalogue::lang.remove')</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($customer_fields_config as $field_key => $field_label)
                                            @php
                                                $current_value = $customer_field_settings[$field_key] ?? 'required';
                                            @endphp
                                            <tr>
                                                <td>{{ $field_label }}</td>
                                                <td class="tw-text-center">
                                                    {!! Form::radio('customer_field_settings['.$field_key.']', 'required', $current_value === 'required', ['class' => 'input-icheck']) !!}
                                                </td>
                                                <td class="tw-text-center">
                                                    {!! Form::radio('customer_field_settings['.$field_key.']', 'optional', $current_value === 'optional', ['class' => 'input-icheck']) !!}
                                                </td>
                                                <td class="tw-text-center">
                                                    {!! Form::radio('customer_field_settings['.$field_key.']', 'remove', $current_value === 'remove', ['class' => 'input-icheck']) !!}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <button type="submit" class="tw-dw-btn tw-dw-btn-primary tw-dw-btn-sm tw-text-white" id="">@lang('productcatalogue::lang.save')</button>
                    {!! Form::close() !!}
                </div>
                <div class="col-md-6">
                    <strong>@lang('lang_v1.instruction'):</strong>
                    <ul class="tw-mt-2 tw-mb-0 tw-pl-0">
                        <li class="tw-flex tw-gap-2">
                            <span class="tw-font-semibold tw-min-w-[1.25rem] tw-text-right">1.</span>
                            <span>@lang('productcatalogue::lang.whatsapp_ordering_instruction_1')</span>
                        </li>
                        <li class="tw-flex tw-gap-2">
                            <span class="tw-font-semibold tw-min-w-[1.25rem] tw-text-right">2.</span>
                            <span>@lang('productcatalogue::lang.whatsapp_ordering_instruction_2')</span>
                        </li>
                        <li class="tw-flex tw-gap-2">
                            <span class="tw-font-semibold tw-min-w-[1.25rem] tw-text-right">3.</span>
                            <span>@lang('productcatalogue::lang.whatsapp_ordering_instruction_3')</span>
                        </li>
                    </ul>
                </div>
            </div>
        @endcomponent
    
        </div>
        <div class="col-md-5">
            @component('components.widget', ['class' => 'box-solid'])
                <div class="text-center">
                    <div id="qrcode"></div>
                    <span id="catalogue_link"></span>
                    <br>
                    <a href="#" class="tw-dw-btn tw-dw-btn-success tw-text-white hide" id="download_image">@lang( 'productcatalogue::lang.download_image' )</a>
                </div>
            @endcomponent
        </div>
    </div>
</section>
@stop
@section('javascript')
<script src="{{ asset('modules/productcatalogue/plugins/easy.qrcode.min.js') }}"></script>
<script type="text/javascript">
    (function($) {
        "use strict";

    $(document).ready( function(){
        $('#color').colorpicker();
    });
    
    $(document).on('click', '#generate_qr', function(e){
        $('#qrcode').html('');
        if ($('#location_id').val()) {
            var link = "{{url('catalogue/' . session('business.id'))}}/" + $('#location_id').val();
            var color = '#000000';
            if ($('#color').val().trim() != '') {
                color = $('#color').val();
            }
            var opts = {
                text: link,
                margin: 4,
                width: 256,
                height: 256,
                quietZone: 20,
                colorDark: color,
                colorLight: "#ffffffff", 
            }

            if ($('#title').val().trim() !== '') {
                opts.title = $('#title').val();
                opts.titleFont = "bold 18px Arial";
                opts.titleColor = "#004284";
                opts.titleBackgroundColor = "#ffffff";
                opts.titleHeight = 60;
                opts.titleTop = 20;
            }

            if ($('#subtitle').val().trim() !== '') {
                opts.subTitle = $('#subtitle').val();
                opts.subTitleFont = "14px Arial";
                opts.subTitleColor = "#4F4F4F";
                opts.subTitleTop = 40;
            }

            if ($('#show_logo').is(':checked')) {
                opts.logo = "{{asset( 'uploads/business_logos/' . $business->logo)}}";
            }

            new QRCode(document.getElementById("qrcode"), opts);
            $('#catalogue_link').html('<a target="_blank" href="'+ link +'">Link</a>');
            $('#download_image').removeClass('hide');
            $('#qrcode').find('canvas').attr('id', 'qr_canvas')

            
        } else {
            alert("{{__('productcatalogue::lang.select_business_location')}}")
        }
    });
    })(jQuery);

    $('#download_image').click(function(e) {
        e.preventDefault();
        var link = document.createElement('a');
        link.download = 'qrcode.png';
        link.href = document.getElementById('qr_canvas').toDataURL()
        link.click();
    });
</script>
@endsection