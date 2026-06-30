<div class="modal-dialog" role="document">
    <div class="modal-content">
        {!! Form::open(['url' => action([\App\Http\Controllers\ProductController::class, 'updateRename'], [$product->id]), 'method' => 'post', 'id' => 'edit_rename_form' ]) !!}
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Rename & Update Price - {{ $product->name }}</h4>
            </div>
            <div class="modal-body">
                <div id="barcode-reader-container" style="display: none; margin-bottom: 15px; border: 1px solid #ccc; padding: 10px; border-radius: 5px;">
                    <div id="barcode-reader" style="width: 100%;"></div>
                    <button type="button" class="btn btn-warning btn-block" id="btn-stop-scan" style="margin-top: 10px;">Cancel Scan</button>
                </div>

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
                         <div class="input-group">
                             {!! Form::text('sku', $product->sku, ['class' => 'form-control', 'required', 'placeholder' => __('product.sku'), 'id' => 'sku']) !!}
                             <span class="input-group-btn">
                                 <button type="button" class="btn btn-default btn-scan-barcode" data-target="#sku"><i class="fa fa-camera"></i> Scan</button>
                             </span>
                         </div>
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
                                         <div class="input-group">
                                             {!! Form::text('variations[' . $variation->id . '][sku]', $variation->sub_sku, ['class' => 'form-control', 'required', 'id' => 'sku_' . $variation->id]) !!}
                                             <span class="input-group-btn">
                                                 <button type="button" class="btn btn-default btn-scan-barcode" data-target="#sku_{{ $variation->id }}"><i class="fa fa-camera"></i> Scan</button>
                                             </span>
                                         </div>
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

<script type="text/javascript">
    $(document).ready(function() {
        if (typeof Html5Qrcode === 'undefined') {
            var script = document.createElement('script');
            script.src = 'https://unpkg.com/html5-qrcode';
            script.type = 'text/javascript';
            document.getElementsByTagName('head')[0].appendChild(script);
        }

        var html5QrcodeScanner = null;
        var activeInputTarget = null;

        $(document).on('click', '.btn-scan-barcode', function() {
            activeInputTarget = $(this).data('target');
            $('#barcode-reader-container').show();
            
            if (html5QrcodeScanner) {
                try {
                    html5QrcodeScanner.clear();
                } catch(e) {}
            }

            html5QrcodeScanner = new Html5Qrcode("barcode-reader");
            
            html5QrcodeScanner.start(
                { facingMode: "environment" }, 
                {
                    fps: 10,
                    qrbox: { width: 250, height: 150 }
                },
                function(decodedText, decodedResult) {
                    $(activeInputTarget).val(decodedText);
                    stopScanning();
                },
                function(errorMessage) {
                    // ignore failure to decode
                }
            ).catch(err => {
                console.error("Unable to start scanning.", err);
                alert("Camera access denied or error starting camera: " + err);
                $('#barcode-reader-container').hide();
            });
        });

        function stopScanning() {
            if (html5QrcodeScanner) {
                html5QrcodeScanner.stop().then(ignore => {
                    html5QrcodeScanner.clear();
                    $('#barcode-reader-container').hide();
                }).catch(err => {
                    console.error("Failed to stop scanner.", err);
                    $('#barcode-reader-container').hide();
                });
            } else {
                $('#barcode-reader-container').hide();
            }
        }

        $('#btn-stop-scan').click(function() {
            stopScanning();
        });

        $('#edit_rename_form').closest('.modal').on('hidden.bs.modal', function () {
            if (html5QrcodeScanner) {
                try {
                    html5QrcodeScanner.stop().then(ignore => {
                        html5QrcodeScanner.clear();
                    }).catch(e => {});
                } catch(e) {}
            }
        });
    });
</script>
