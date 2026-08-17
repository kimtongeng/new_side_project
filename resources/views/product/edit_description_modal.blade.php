<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        {!! Form::open(['url' => action([\App\Http\Controllers\ProductController::class, 'updateDescription'], [$product->id]), 'method' => 'post', 'id' => 'edit_description_form']) !!}
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                    aria-hidden="true">&times;</span></button>
            <h4 class="modal-title">@lang('lang_v1.product_description') - {!! \App\Utils\ProductUtil::getFormattedProductName($product->name, $product->secondary_name, true) !!}</h4>
        </div>
        <div class="modal-body">
            <div class="form-group">
                {!! Form::label('product_description', __('lang_v1.product_description') . ':') !!}
                {!! Form::textarea('product_description', $product->product_description, ['class' => 'form-control', 'rows' => 6, 'id' => 'modal_product_description', 'placeholder' => __('lang_v1.product_description')]) !!}
            </div>
        </div>
        <div class="modal-footer">
            <button type="submit" class="tw-dw-btn tw-dw-btn-primary tw-text-white"
                id="update_description_btn">@lang('messages.save')</button>
            <button type="button" class="tw-dw-btn tw-dw-btn-neutral tw-text-white"
                data-dismiss="modal">@lang('messages.close')</button>
        </div>
        {!! Form::close() !!}
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        if ($('textarea#modal_product_description').length > 0 && typeof tinymce !== 'undefined') {
            tinymce.init({
                selector: 'textarea#modal_product_description',
            });
        }
    });
</script>
