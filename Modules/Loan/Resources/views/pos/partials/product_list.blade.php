<div class="product-list">
    @if ($products->isEmpty())
        <p class="text-center">@lang('Loan::lang.no_products_found')</p>
    @else
        <div class="row">
            @foreach ($products as $product)
                <div class="col-md-4 col-sm-6 col-xs-12">
                    <div class="product-item tw-border tw-border-gray-200 tw-rounded-lg tw-p-3 tw-mb-4 tw-bg-white tw-shadow-sm hover:tw-shadow-md">
                        <div class="product-image tw-mb-2">
                            @if (!empty($product->media) && !empty($product->media->first()->file_path))
                                <img src="{{ url($product->media->first()->file_path) }}" alt="{{ $product->name }}" class="tw-w-full tw-h-24 tw-object-cover tw-rounded">
                            @else
                                <img src="{{ asset('img/default_product.png') }}" alt="Default Product" class="tw-w-full tw-h-24 tw-object-cover tw-rounded">
                            @endif
                        </div>
                        <div class="product-details">
                            <h5 class="tw-text-sm tw-font-semibold tw-text-gray-800">{{ $product->name }}</h5>
                            @if ($product->type == 'variable')
                                <small class="tw-text-gray-600">{{ $product->variation }}</small>
                            @endif
                            @if (!empty($product->sub_sku))
                                <small class="tw-text-gray-600">@lang('lang_v1.sku'): {{ $product->sub_sku }}</small>
                            @endif
                            @if ($show_prices)
                                <p class="tw-text-sm tw-font-medium tw-text-green-600 tw-mt-1">
                                    {{ $receipt_details->currency['symbol'] ?? '' }} {{ number_format($product->selling_price, 2) }}
                                </p>
                            @endif
                            @if ($product->enable_stock)
                                <p class="tw-text-xs tw-text-gray-500">@lang('lang_v1.available'): {{ $product->qty_available }} {{ $product->unit }}</p>
                            @endif
                        </div>
                        <div class="product-actions tw-mt-2">
                            <button class="btn btn-primary btn-xs add-product-to-cart" 
                                    data-variation_id="{{ $product->id }}"
                                    data-product_id="{{ $product->product_id }}"
                                    title="@lang('Loan::lang.add_to_cart')">
                                <i class="fa fa-plus"></i> @lang('Loan::lang.add')
                            </button>
                            @if (!empty($allowed_group_prices))
                                <select class="form-control select2 price-group-select tw-mt-2 tw-text-xs" 
                                        data-variation_id="{{ $product->id }}">
                                    <option value="">@lang('lang_v1.default_price')</option>
                                    @foreach ($allowed_group_prices as $group_id => $group_name)
                                        @if (!empty($product->group_prices[$group_id]))
                                            <option value="{{ $group_id }}">{{ $group_name }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="text-center">
            {{ $products->links() }}
        </div>
    @endif
</div>

@stop

@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {
            // Initialize Select2 for price group dropdowns
            $('.price-group-select').select2();

            // Add product to cart
            $('.add-product-to-cart').click(function() {
                var variation_id = $(this).data('variation_id');
                var product_id = $(this).data('product_id');
                var price_group = $(this).closest('.product-actions').find('.price-group-select').val();
                var quantity = 1;

                $.ajax({
                    url: '{{ action([\Modules\Loan\Http\Controllers\LoanPosUtilityController::class, 'getProductRow']) }}',
                    method: 'GET',
                    data: {
                        variation_id: variation_id,
                        location_id: $('#location_id').val(),
                        quantity: quantity,
                        product_row: $('.product_row').length,
                        price_group: price_group
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#pos_table_body').append(response.html_content);
                            if (response.html_modifier) {
                                $('#pos_table_body').append(response.html_modifier);
                            }
                            toastr.success('@lang("Loan::lang.product_added")');
                            // Update totals (function assumed to be in pos.js)
                            if (typeof update_pos_totals === 'function') {
                                update_pos_totals();
                            }
                        } else {
                            toastr.error(response.msg);
                        }
                    },
                    error: function() {
                        toastr.error('@lang("messages.something_went_wrong")');
                    }
                });
            });
        });
    </script>
@endsection