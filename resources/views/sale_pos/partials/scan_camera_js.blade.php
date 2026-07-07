<script type="text/javascript">
    $(document).ready(function () {
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
                    var $input = $(activeInputTarget);
                    $input.val(decodedText).focus();
                    
                    // Trigger jQuery UI Autocomplete search automatically
                    if ($input.data('ui-autocomplete')) {
                        $input.autocomplete('search', decodedText);
                    } else {
                        $input.trigger('change');
                    }
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

        $('#btn-stop-scan').click(function () {
            stopScanning();
        });
    });
</script>
