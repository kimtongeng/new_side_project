<div class="modal-dialog" role="document">
    <div class="modal-content">
        {!! Form::open(['url' => action([\App\Http\Controllers\ProductController::class, 'updateCategory'], [$product->id]), 'method' => 'post', 'id' => 'edit_category_form']) !!}
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                    aria-hidden="true">&times;</span></button>
            <h4 class="modal-title">@lang('category.category') - {{ $product->name }}</h4>
        </div>
        <div class="modal-body">
            <div class="form-group">
                {!! Form::label('category_id', __('product.category') . ':*') !!}
                {!! Form::select('category_id', $categories, $product->category_id, ['class' => 'form-control select2', 'id' => 'modal_category_id', 'placeholder' => __('messages.please_select'), 'style' => 'width: 100%;']) !!}
            </div>

            <div class="form-group">
                {!! Form::label('sub_category_id', __('product.sub_category') . ':') !!}
                {!! Form::select('sub_category_id', $sub_categories, $product->sub_category_id, ['class' => 'form-control select2', 'id' => 'modal_sub_category_id', 'placeholder' => __('messages.please_select'), 'style' => 'width: 100%;']) !!}
            </div>
        </div>
        <div class="modal-footer">
            <button type="submit" class="tw-dw-btn tw-dw-btn-primary tw-text-white"
                id="update_category_btn">@lang('messages.save')</button>
            <button type="button" class="tw-dw-btn tw-dw-btn-neutral tw-text-white"
                data-dismiss="modal">@lang('messages.close')</button>
        </div>
        {!! Form::close() !!}
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        $('form#edit_category_form').find('.select2').select2();

        $(document).off('change', '#modal_category_id').on('change', '#modal_category_id', function () {
            var cat_id = $(this).val();
            $.ajax({
                method: 'POST',
                url: '/products/get_sub_categories',
                dataType: 'html',
                data: { cat_id: cat_id },
                success: function (result) {
                    if (result) {
                        $('#modal_sub_category_id').html(result).trigger('change');
                    }
                },
            });
        });
    });
</script>
