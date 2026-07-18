@extends('layouts.app')

@section('title', __('stockcount::lang.create_stock_count_session'))

@section('content')
    <section class="content-header no-print">
        <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">
            @lang('stockcount::lang.create_stock_count_session')
        </h1>
    </section>

    <section class="content no-print">
        {!! Form::open(['url' => action([\Modules\StockCount\Http\Controllers\StockCountController::class, 'store']), 'method' => 'post', 'id' => 'stock_count_form']) !!}

        @component('components.widget', ['class' => 'box-primary'])
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    {!! Form::label('name', __('stockcount::lang.session_name') . ':*') !!}
                    {!! Form::text('name', null, ['class' => 'form-control', 'required', 'placeholder' => __('stockcount::lang.session_name')]) !!}
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    {!! Form::label('reference_no', __('stockcount::lang.reference_no') . ':') !!}
                    {!! Form::text('reference_no', null, ['class' => 'form-control', 'placeholder' => __('stockcount::lang.reference_no')]) !!}
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    {!! Form::label('location_id', __('purchase.business_location') . ':*') !!}
                    {!! Form::select('location_id', $business_locations, null, ['class' => 'form-control select2', 'required', 'style' => 'width:100%', 'placeholder' => __('messages.please_select')]) !!}
                </div>
            </div>
        </div>

        <hr>
        <h4 class="tw-font-bold tw-text-black">@lang('stockcount::lang.blind_count')</h4>
        <div class="row">
            <div class="col-md-12">
                <div class="checkbox">
                    <label>
                        @php
                            $default_blind_count = isset($settings['stock_count_show_expected_qty']) ? !$settings['stock_count_show_expected_qty'] : false;
                        @endphp
                        {!! Form::checkbox('blind_count', 1, $default_blind_count, ['class' => 'input-icheck']) !!}
                        <strong>@lang('stockcount::lang.blind_count')</strong>
                    </label>
                    <p class="help-block">@lang('stockcount::lang.blind_count_help')</p>
                </div>
            </div>
        </div>

        <hr>
        <h4 class="tw-font-bold tw-text-black">@lang('stockcount::lang.variance_report') Filters (Optional)</h4>
        <p class="help-block">Leave filters empty to count all products at the selected location.</p>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    {!! Form::label('categories', __('stockcount::lang.categories') . ':') !!}
                    {!! Form::select('categories[]', $categories, null, ['class' => 'form-control select2', 'multiple', 'style' => 'width:100%']) !!}
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    {!! Form::label('brands', __('stockcount::lang.brands') . ':') !!}
                    {!! Form::select('brands[]', $brands, null, ['class' => 'form-control select2', 'multiple', 'style' => 'width:100%']) !!}
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    {!! Form::label('racks', __('stockcount::lang.racks') . ':') !!}
                    {!! Form::select('racks[]', $racks, null, ['class' => 'form-control select2', 'multiple', 'style' => 'width:100%']) !!}
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    {!! Form::label('products', __('stockcount::lang.products') . ':') !!}
                    {!! Form::select('products[]', $products, null, ['class' => 'form-control select2', 'multiple', 'style' => 'width:100%']) !!}
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12 text-center" style="margin-top: 15px; display: flex; justify-content: center; gap: 10px;">
                <a href="{{ action([\Modules\StockCount\Http\Controllers\StockCountController::class, 'index']) }}"
                    class="btn btn-default btn-big">
                    <i class="fa fa-arrow-left"></i> Back
                </a>
                <button type="submit" class="btn btn-primary btn-big">
                    <i class="fa fa-play-circle"></i> @lang('stockcount::lang.start_counting')
                </button>
            </div>
        </div>
        @endcomponent

        {!! Form::close() !!}
    </section>
@endsection