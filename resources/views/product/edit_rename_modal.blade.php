<div class="modal-dialog" role="document">
    <div class="modal-content">
        {!! Form::open(['url' => action([\App\Http\Controllers\ProductController::class, 'updateRename'], [$product->id]), 'method' => 'post', 'id' => 'edit_rename_form' ]) !!}
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Rename & Update Price - {{ $product->name }}</h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    {!! Form::label('name', __('product.product_name') . ':*') !!}
                    {!! Form::text('name', $product->name, ['class' => 'form-control', 'required', 'placeholder' => __('product.product_name')]) !!}
                </div>

                @if($product->type == 'single' || $product->type == 'combo')
                    @php
                        $variation = $product->variations->first();
                    @endphp
                    <div class="form-group">
                        {!! Form::label('sku', __('product.sku') . ':*') !!}
                        {!! Form::text('sku', $product->sku, ['class' => 'form-control', 'required', 'placeholder' => __('product.sku')]) !!}
                    </div>
                    <div class="form-group">
                        {!! Form::label('selling_price', __('product.selling_price') . ' (Exc. Tax):*') !!}
                        {!! Form::text('selling_price', @num_format($variation->default_sell_price), ['class' => 'form-control input_number', 'required', 'placeholder' => __('product.selling_price')]) !!}
                    </div>
                    {!! Form::hidden('variation_id', $variation->id) !!}
                @elseif($product->type == 'variable')
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>@lang('lang_v1.variation')</th>
                                <th>@lang('product.sku')</th>
                                <th>@lang('product.selling_price') (Exc. Tax)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($product->variations as $variation)
                                <tr>
                                    <td>{{ optional($variation->product_variation)->name }} - {{ $variation->name }}</td>
                                    <td>
                                        {!! Form::text('variations[' . $variation->id . '][sku]', $variation->sub_sku, ['class' => 'form-control', 'required']) !!}
                                    </td>
                                    <td>
                                        {!! Form::text('variations[' . $variation->id . '][selling_price]', @num_format($variation->default_sell_price), ['class' => 'form-control input_number', 'required']) !!}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
            <div class="modal-footer">
                <button type="submit" class="tw-dw-btn tw-dw-btn-primary tw-text-white" id="update_rename_btn">@lang( 'messages.save' )</button>
                <button type="button" class="tw-dw-btn tw-dw-btn-neutral tw-text-white" data-dismiss="modal">@lang( 'messages.close' )</button>
            </div>
        {!! Form::close() !!}
    </div>
</div>
