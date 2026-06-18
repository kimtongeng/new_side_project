<div class="tw-shadow-[rgba(17,_17,_26,_0.1)_0px_0px_16px] tw-rounded-2xl tw-bg-white tw-p-2">
    <div class="box-body">
        <div class="form-group">
            <label for="category_id">@lang('Loan::lang.category'):</label>
            <select class="form-control select2" id="category_id">
                <option value="all">@lang('lang_v1.all_category')</option>
                @foreach ($categories as $category)
                    <option value="{{ $category['id'] }}">{{ $category['name'] }}</option>
                    @if (!empty($category['sub_categories']))
                        @foreach ($category['sub_categories'] as $sub_category)
                            <option value="{{ $sub_category['id'] }}">&nbsp;&nbsp;&nbsp;{{ $sub_category['name'] }}</option>
                        @endforeach
                    @endif
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="brand_id">@lang('lang_v1.brand'):</label>
            <select class="form-control select2" id="brand_id">
                <option value="all">@lang('lang_v1.all_brands')</option>
                @foreach ($brands as $brand_id => $brand_name)
                    <option value="{{ $brand_id }}">{{ $brand_name }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <input type="text" class="form-control" id="product_search" placeholder="@lang('lang_v1.search_product')">
        </div>

        <div class="form-group">
            <label>
                <input type="checkbox" id="is_enabled_stock" value="product"> @lang('lang_v1.only_stock_products')
            </label>
        </div>

        <div id="product_suggestions" class="tw-mt-4">
            @include('Loan::pos.partials.product_list', ['products' => [], 'allowed_group_prices' => [], 'show_prices' => !empty($pos_settings['show_pricing_on_product_sugesstion'])])
        </div>
    </div>
</div>

@stop

@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {
            // Initialize Select2
            $('#category_id, #brand_id').select2();

            // Product search and suggestion
            function loadProductSuggestions() {
                var category_id = $('#category_id').val();
                var brand_id = $('#brand_id').val();
                var term = $('#product_search').val();
                var is_enabled_stock = $('#is_enabled_stock').is(':checked') ? 'product' : '';

                $.ajax({
                    url: '{{ action([\Modules\Loan\Http\Controllers\LoanPosUtilityController::class, 'getProductSuggestion']) }}',
                    method: 'GET',
                    data: {
                        category_id: category_id,
                        brand_id: brand_id,
                        term: term,
                        location_id: $('#location_id').val(),
                        is_enabled_stock: is_enabled_stock
                    },
                    success: function(response) {
                        $('#product_suggestions').html(response);
                    },
                    error: function() {
                        toastr.error('@lang("messages.something_went_wrong")');
                    }
                });
            }

            // Trigger product suggestions on change
            $('#category_id, #brand_id, #is_enabled_stock').change(function() {
                loadProductSuggestions();
            });

            // Trigger product suggestions on search input
            $('#product_search').on('keyup', function() {
                loadProductSuggestions();
            });

            // Load initial suggestions
            loadProductSuggestions();
        });
    </script>
@endsection