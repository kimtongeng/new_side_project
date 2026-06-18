<div class="modal fade" id="mobile_product_suggestions_modal" tabindex="-1" role="dialog" aria-labelledby="mobileProductSuggestionsLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
                <h4 class="modal-title" id="mobileProductSuggestionsLabel">@lang('Loan::lang.product_suggestions')</h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="mobile_category_id">@lang('Loan::lang.category'):</label>
                    <select class="form-control select2" id="mobile_category_id">
                        <option value="all">@lang('lang_v1.all_category')</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category['id'] }}">{{ $category['name'] }}</option>
                            @if (!empty($category['sub_categories']))
                                @foreach ($category['sub_categories'] as $sub_category)
                                    <option value="{{ $sub_category['id'] }}">   {{ $sub_category['name'] }}</option>
                                @endforeach
                            @endif
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="mobile_brand_id">@lang('lang_v1.brand'):</label>
                    <select class="form-control select2" id="mobile_brand_id">
                        <option value="all">@lang('lang_v1.all_brands')</option>
                        @foreach ($brands as $brand_id => $brand_name)
                            <option value="{{ $brand_id }}">{{ $brand_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <input type="text" class="form-control" id="mobile_product_search" placeholder="@lang('lang_v1.search_product')">
                </div>

                <div class="form-group">
                    <label>
                        <input type="checkbox" id="mobile_is_enabled_stock" value="product"> @lang('lang_v1.only_stock_products')
                    </label>
                </div>

                <div id="mobile_product_suggestions">
                    @include('Loan::pos.partials.product_list', ['products' => [], 'allowed_group_prices' => [], 'show_prices' => !empty($pos_settings['show_pricing_on_product_sugesstion'])])
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
            </div>
        </div>
    </div>
</div>

@stop

@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {
            // Initialize Select2
            $('#mobile_category_id, #mobile_brand_id').select2();

            // Product search and suggestion
            function loadMobileProductSuggestions() {
                var category_id = $('#mobile_category_id').val();
                var brand_id = $('#mobile_brand_id').val();
                var term = $('#mobile_product_search').val();
                var is_enabled_stock = $('#mobile_is_enabled_stock').is(':checked') ? 'product' : '';

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
                        $('#mobile_product_suggestions').html(response);
                    },
                    error: function() {
                        toastr.error('@lang("messages.something_went_wrong")');
                    }
                });
            }

            // Trigger product suggestions on change
            $('#mobile_category_id, #mobile_brand_id, #mobile_is_enabled_stock').change(function() {
                loadMobileProductSuggestions();
            });

            // Trigger product suggestions on search input
            $('#mobile_product_search').on('keyup', function() {
                loadMobileProductSuggestions();
            });

            // Load initial suggestions when modal is opened
            $('#mobile_product_suggestions_modal').on('shown.bs.modal', function() {
                loadMobileProductSuggestions();
            });
        });
    </script>
@endsection