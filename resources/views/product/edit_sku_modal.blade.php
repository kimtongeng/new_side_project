<div class="modal-dialog" role="document">
    <div class="modal-content">
        {!! Form::open(['url' => action([\App\Http\Controllers\ProductController::class, 'updateSku'], [$product->id]), 'method' => 'post', 'id' => 'edit_sku_form']) !!}
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                    aria-hidden="true">&times;</span></button>
            <h4 class="modal-title">Edit SKU - {{ $product->name }}</h4>
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

            @if($product->type == 'single' || $product->type == 'combo')
                <div class="form-group">
                    {!! Form::label('sku', __('product.sku') . ':*') !!}
                    <div class="input-group">
                        {!! Form::text('sku', $product->sku, ['class' => 'form-control', 'required', 'placeholder' => __('product.sku'), 'id' => 'sku']) !!}
                        <span class="input-group-btn">
                            <button type="button" class="btn btn-default btn-scan-barcode" data-target="#sku"><i
                                    class="fa fa-camera"></i> Scan</button>
                        </span>
                    </div>
                </div>
            @elseif($product->type == 'variable')
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>@lang('lang_v1.variation')</th>
                            <th>@lang('product.sku')</th>
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
                                            <button type="button" class="btn btn-default btn-scan-barcode"
                                                data-target="#sku_{{ $variation->id }}"><i class="fa fa-camera"></i>
                                                Scan</button>
                                        </span>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
        <div class="modal-footer">
            <button type="submit" class="tw-dw-btn tw-dw-btn-primary tw-text-white" id="update_sku_btn"
                disabled>@lang('messages.save')</button>
            <button type="button" class="tw-dw-btn tw-dw-btn-neutral tw-text-white"
                data-dismiss="modal">@lang('messages.close')</button>
        </div>
        {!! Form::close() !!}
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        var isSubmitting = false;

        $.validator.addMethod("notEqualToOld", function (value, element) {
            if (isSubmitting) {
                return true;
            }
            var originalVal = $(element).data('original-val');
            if ($(element).hasClass('touched') && value === originalVal) {
                return false;
            }
            return true;
        }, "New SKU must be different from the old SKU.");

        var $skuInputs = $('form#edit_sku_form').find('input[name="sku"], input[name^="variations"]');
        $skuInputs.each(function () {
            var $this = $(this);
            $this.data('original-val', $this.val());
        });

        function checkSkuChanges() {
            var changed = false;
            $skuInputs.each(function () {
                var $this = $(this);
                if ($this.val() !== $this.data('original-val')) {
                    changed = true;
                }
            });
            $('#update_sku_btn').prop('disabled', !changed);
        }

        $skuInputs.on('input change keyup paste', function () {
            $(this).addClass('touched');
            checkSkuChanges();
            $('form#edit_sku_form').validate().element($(this));
        });

        $('#update_sku_btn').on('click', function () {
            isSubmitting = true;
            setTimeout(function () {
                isSubmitting = false;
            }, 0);
        });

        checkSkuChanges();

        $('form#edit_sku_form').validate({
            rules: {
                sku: {
                    notEqualToOld: true,
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

        $('form#edit_sku_form').find('[name^="variations"]').each(function () {
            var element = $(this);
            var var_id = element.attr('id').replace('sku_', '');
            element.rules('add', {
                notEqualToOld: true,
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
                    $(activeInputTarget).val(decodedText).trigger('change');
                    stopScanning();
                },
                function (errorMessage) {
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


        function scanFile(file) {
            html5QrcodeScanner.scanFile(file, true)
                .then(decodedText => {
                    $(activeInputTarget).val(decodedText).trigger('change');
                    html5QrcodeScanner.clear();
                    $('#barcode-reader-container').hide();
                })
                .catch(err => {
                    console.error("Error scanning file: ", err);
                    alert("Could not scan barcode from image. Please ensure it is clear and try again.");
                });
        }

        $('#btn-stop-scan').click(function () {
            stopScanning();
        });

        $('#edit_sku_form').closest('.modal').on('hidden.bs.modal', function () {
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