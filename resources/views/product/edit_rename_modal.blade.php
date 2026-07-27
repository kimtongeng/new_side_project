<div class="modal-dialog" role="document">
    <div class="modal-content">
        {!! Form::open(['url' => action([\App\Http\Controllers\ProductController::class, 'updateRename'], [$product->id]), 'method' => 'post', 'id' => 'edit_rename_form']) !!}
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                    aria-hidden="true">&times;</span></button>
            <h4 class="modal-title">{{ __('lang_v1.edit_rename') }} - {{ $product->name }}</h4>
        </div>
        <div class="modal-body">
            <div id="barcode-reader-container"
                style="display: none; margin-bottom: 15px; border: 1px solid #ccc; padding: 10px; border-radius: 5px; position: relative;">
                <style>
                    .scan-laser-line {
                        position: absolute;
                        top: 20%;
                        left: 5%;
                        width: 90%;
                        height: 2px;
                        background: #ff0000;
                        box-shadow: 0 0 8px 2px rgba(255, 0, 0, 0.8);
                        z-index: 99;
                        pointer-events: none;
                        animation: scanLaser 3.5s infinite ease-in-out;
                    }
                    @keyframes scanLaser {
                        0% {
                            top: 25%;
                            opacity: 0.8;
                        }
                        50% {
                            top: 75%;
                            opacity: 1;
                        }
                        100% {
                            top: 25%;
                            opacity: 0.8;
                        }
                    }
                </style>
                <div id="barcode-reader-wrapper" style="position: relative; overflow: hidden; border-radius: 4px;">
                    <div id="barcode-reader" style="width: 100%;"></div>
                    <div class="scan-laser-line"></div>
                </div>
                <input type="file" id="barcode-image-input" accept="image/*" style="display: none;">
                <button type="button" class="btn btn-warning btn-block" id="btn-stop-scan"
                    style="margin-top: 10px;">Cancel Scan</button>
            </div>

            <div class="form-group">
                {!! Form::label('name', __('product.product_name') . ':*') !!}
                {!! Form::text('name', $product->name, ['class' => 'form-control', 'required', 'placeholder' => __('product.product_name'), 'readonly' => !auth()->user()->can('product.rename_product')]) !!}
            </div>

            <div class="form-group">
                {!! Form::label('secondary_name', 'Second Language Name:') !!}
                {!! Form::text('secondary_name', $product->secondary_name, ['class' => 'form-control', 'placeholder' => 'Second Language Name (e.g. Khmer Name)', 'readonly' => !auth()->user()->can('product.rename_product')]) !!}
            </div>

            @if($product->type == 'single' || $product->type == 'combo')
                @php
                    $variation = $product->variations->first();
                @endphp
                <div class="form-group">
                    {!! Form::label('sku', __('product.sku') . ':*') !!}
                    <div class="input-group">
                        {!! Form::text('sku', $product->sku, ['class' => 'form-control', 'required', 'placeholder' => __('product.sku'), 'id' => 'sku', 'readonly' => !auth()->user()->can('product.rename_sku')]) !!}
                        <span class="input-group-btn">
                            <button type="button" class="btn btn-default btn-scan-barcode" data-target="#sku"
                                @if(!auth()->user()->can('product.rename_sku')) disabled @endif><i class="fa fa-camera"></i>
                                Scan</button>
                        </span>
                    </div>
                </div>
                <div class="form-group">
                    {!! Form::label('selling_price', 'Selling price (Exc. Tax):*') !!}
                    {!! Form::text('selling_price', @num_format($variation->default_sell_price), ['class' => 'form-control input_number', 'required', 'placeholder' => __('product.selling_price'), 'readonly' => !auth()->user()->can('product.update_price'), 'data-cost-price' => $variation->default_purchase_price]) !!}
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
                                        {!! Form::text('variations[' . $variation->id . '][sku]', $variation->sub_sku, ['class' => 'form-control', 'required', 'id' => 'sku_' . $variation->id, 'readonly' => !auth()->user()->can('product.rename_sku')]) !!}
                                        <span class="input-group-btn">
                                            <button type="button" class="btn btn-default btn-scan-barcode"
                                                data-target="#sku_{{ $variation->id }}"
                                                @if(!auth()->user()->can('product.rename_sku')) disabled @endif><i
                                                    class="fa fa-camera"></i> Scan</button>
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    {!! Form::text('variations[' . $variation->id . '][selling_price]',
                                    @num_format($variation->default_sell_price), ['class' => 'form-control input_number', 'required', 'readonly' => !auth()->user()->can('product.update_price'), 'data-cost-price' => $variation->default_purchase_price]) !!}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
        <div class="modal-footer">
            <button type="submit" class="tw-dw-btn tw-dw-btn-primary tw-text-white"
                id="update_rename_btn">@lang('messages.save')</button>
            <button type="button" class="tw-dw-btn tw-dw-btn-neutral tw-text-white"
                data-dismiss="modal">@lang('messages.close')</button>
        </div>
        {!! Form::close() !!}
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        $('form#edit_rename_form').validate({
            rules: {
                sku: {
                    remote: {
                        url: '/products/check_product_sku',
                        type: 'post',
                        data: {
                            sku: function () {
                                return $('#sku').val();
                            },
                            product_id: '{{ $product->id }}'
                        },
                    },
                },
            },
            messages: {
                sku: {
                    remote: LANG.sku_already_exists,
                },
            },
        });

        $('form#edit_rename_form').submit(function (e) {
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

        $('form#edit_rename_form').find('[name^="variations"]').each(function () {
            var element = $(this);
            var var_id = element.attr('id').replace('sku_', '');
            element.rules('add', {
                remote: {
                    url: '/products/check_product_sku',
                    type: 'post',
                    data: {
                        sku: function () {
                            return element.val();
                        },
                        product_id: '{{ $product->id }}',
                        variation_id: var_id
                    }
                },
                messages: {
                    remote: LANG.sku_already_exists
                }
            });
        });

        if (typeof Html5Qrcode === 'undefined') {
            var script = document.createElement('script');
            script.src = 'https://unpkg.com/html5-qrcode';
            script.type = 'text/javascript';
            document.getElementsByTagName('head')[0].appendChild(script);
        }

        var html5QrcodeScanner = null;
        var activeInputTarget = null;

        $(document).off('click', '.btn-scan-barcode').on('click', '.btn-scan-barcode', function () {
            activeInputTarget = $(this).data('target');
            $('#barcode-reader-container').show();

            if (html5QrcodeScanner) {
                try {
                    html5QrcodeScanner.clear();
                } catch (e) { }
            }

            var formats = [];
            if (typeof Html5QrcodeSupportedFormats !== 'undefined') {
                formats = [
                    Html5QrcodeSupportedFormats.QR_CODE,
                    Html5QrcodeSupportedFormats.EAN_13,
                    Html5QrcodeSupportedFormats.EAN_8,
                    Html5QrcodeSupportedFormats.CODE_128,
                    Html5QrcodeSupportedFormats.CODE_39,
                    Html5QrcodeSupportedFormats.UPC_A,
                    Html5QrcodeSupportedFormats.UPC_E
                ];
            }

            html5QrcodeScanner = new Html5Qrcode("barcode-reader", {
                experimentalFeatures: {
                    useBarCodeDetectorIfSupported: true
                },
                formatsToSupport: formats
            });

            html5QrcodeScanner.start(
                { facingMode: "environment" },
                {
                    fps: 25,
                    qrbox: { width: 300, height: 180 }
                },
                function (decodedText, decodedResult) {
                    $(activeInputTarget).val(decodedText);
                    stopScanning();
                },
                function (errorMessage) {
                    // ignore failure to decode
                }
            ).catch(err => {
                console.error("Unable to start scanning.", err);
                toastr.error("Camera access denied or error starting camera: " + err);
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

        $(document).off('click', '.btn-upload-barcode-img').on('click', '.btn-upload-barcode-img', function () {
            $('#barcode-image-input').trigger('click');
        });

        $(document).off('change', '#barcode-image-input').on('change', '#barcode-image-input', function (e) {
            if (e.target.files.length == 0) {
                return;
            }
            var file = e.target.files[0];

            if (html5QrcodeScanner) {
                html5QrcodeScanner.stop().then(ignore => {
                    html5QrcodeScanner.clear();
                    html5QrcodeScanner = new Html5Qrcode("barcode-reader");
                    scanFile(file);
                }).catch(err => {
                    html5QrcodeScanner.clear();
                    html5QrcodeScanner = new Html5Qrcode("barcode-reader");
                    scanFile(file);
                });
            } else {
                html5QrcodeScanner = new Html5Qrcode("barcode-reader");
                scanFile(file);
            }
        });

        function scanFile(file) {
            html5QrcodeScanner.scanFile(file, true)
                .then(decodedText => {
                    $(activeInputTarget).val(decodedText);
                    html5QrcodeScanner.clear();
                    $('#barcode-reader-container').hide();
                })
                .catch(err => {
                    console.error("Error scanning file: ", err);
                    toastr.error("Could not scan barcode from image. Please ensure it is clear and try again.");
                });
        }

        $('#btn-stop-scan').click(function () {
            stopScanning();
        });

        $('#edit_rename_form').closest('.modal').on('hidden.bs.modal', function () {
            if (html5QrcodeScanner) {
                try {
                    html5QrcodeScanner.stop().then(ignore => {
                        html5QrcodeScanner.clear();
                    }).catch(e => { });
                } catch (e) { }
            }
        });
    });
</script>