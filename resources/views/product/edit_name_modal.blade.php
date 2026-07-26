<div class="modal-dialog" role="document">
    <div class="modal-content">
        {!! Form::open(['url' => action([\App\Http\Controllers\ProductController::class, 'updateName'], [$product->id]), 'method' => 'post', 'id' => 'edit_name_form']) !!}
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                    aria-hidden="true">&times;</span></button>
            <h4 class="modal-title">@lang('product.edit_product') - {{ $product->name }}</h4>
        </div>
        <div class="modal-body">
            <div class="form-group">
                {!! Form::label('name', __('product.product_name') . ':*') !!}
                {!! Form::text('name', $product->name, ['class' => 'form-control', 'required', 'placeholder' => __('product.product_name')]) !!}
            </div>
            <div class="form-group">
                {!! Form::label('secondary_name', 'Second Language Name (Optional):') !!}
                {!! Form::text('secondary_name', $product->secondary_name, ['class' => 'form-control', 'placeholder' => 'Second Language Name (e.g. Khmer Name)']) !!}
            </div>
        </div>
        <div class="modal-footer">
            <button type="submit" class="tw-dw-btn tw-dw-btn-primary tw-text-white"
                id="update_name_btn">@lang('messages.save')</button>
            <button type="button" class="tw-dw-btn tw-dw-btn-neutral tw-text-white"
                data-dismiss="modal">@lang('messages.close')</button>
        </div>
        {!! Form::close() !!}
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        $('form#edit_name_form').validate();
    });
</script>
