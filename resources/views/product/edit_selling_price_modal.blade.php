<div class="modal-dialog" role="document">
    <div class="modal-content">
        {!! Form::open(['url' => action([\App\Http\Controllers\ProductController::class, 'updateSellingPrice'], [$product->id]), 'method' => 'post', 'id' => 'edit_selling_price_form']) !!}
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                    aria-hidden="true">&times;</span></button>
            <h4 class="modal-title">@lang('lang_v1.edit_selling_price') - {{ $product->name }}</h4>
        </div>
        <div class="modal-body">
            @if($product->type == 'single' || $product->type == 'combo')
                @php
                    $variation = $product->variations->first();
                @endphp
                <div class="form-group">
                    {!! Form::label('selling_price',  'Selling price (Exc. Tax):*') !!}
                    {!! Form::text('selling_price', @num_format($variation->default_sell_price), ['class' => 'form-control input_number', 'required', 'placeholder' => __('product.selling_price'), 'data-cost-price' => $variation->default_purchase_price]) !!}
                </div>
            @elseif($product->type == 'variable')
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>@lang('lang_v1.variation')</th>
                            <th>@lang('product.selling_price') (Exc. Tax)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($product->variations as $variation)
                            <tr>
                                <td>{{ optional($variation->product_variation)->name }} - {{ $variation->name }}</td>
                                <td>
                                    {!! Form::text('variations[' . $variation->id . '][selling_price]',
                                    @num_format($variation->default_sell_price), ['class' => 'form-control input_number', 'required', 'data-cost-price' => $variation->default_purchase_price]) !!}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
        <div class="modal-footer">
            <button type="submit" class="tw-dw-btn tw-dw-btn-primary tw-text-white"
                id="update_selling_price_btn">@lang('messages.save')</button>
            <button type="button" class="tw-dw-btn tw-dw-btn-neutral tw-text-white"
                data-dismiss="modal">@lang('messages.close')</button>
        </div>
        {!! Form::close() !!}
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        $('form#edit_selling_price_form').validate();

        $('form#edit_selling_price_form').submit(function (e) {
            var form = $(this);
            var is_valid = true;
            form.find('input[data-cost-price]').each(function() {
                var selling_price = __read_number($(this));
                var cost_price = parseFloat($(this).data('cost-price'));
                if (selling_price < cost_price) {
                    is_valid = false;
                    $(this).focus();
                    return false;
                }
            });

            if (!is_valid) {
                toastr.error("{{__('lang_v1.selling_price_less_than_cost_price')}}");
                e.preventDefault();
                e.stopImmediatePropagation();
                return false;
            }
        });
    });
</script>
