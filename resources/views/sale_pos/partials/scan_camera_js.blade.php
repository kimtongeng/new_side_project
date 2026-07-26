<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
<script type="text/javascript">
    $(document).ready(function () {
        var html5QrcodeScanner = null;
        var activeInputTarget = null;

        function startScannerWithCamera(cameraConfig) {
            var qrboxFunction = function (viewfinderWidth, viewfinderHeight) {
                var minEdgeSize = Math.min(viewfinderWidth, viewfinderHeight);
                var boxWidth = Math.floor(minEdgeSize * 0.85);
                var boxHeight = Math.floor(boxWidth * 0.5);
                return {
                    width: Math.max(boxWidth, 250),
                    height: Math.max(boxHeight, 130)
                };
            };

            var scanConfig = {
                fps: 25,
                qrbox: qrboxFunction,
                disableFlip: true
            };

            html5QrcodeScanner.start(
                cameraConfig,
                scanConfig,
                function (decodedText, decodedResult) {
                    var $input = $(activeInputTarget);
                    if ($input.length) {
                        $input.val(decodedText).focus();
                        
                        // Trigger jQuery UI Autocomplete search automatically
                        if ($input.data('ui-autocomplete')) {
                            $input.autocomplete('search', decodedText);
                        } else {
                            $input.trigger('change');
                        }
                    }
                    stopScanning();
                },
                function (errorMessage) {
                    // ignore per-frame decode errors
                }
            ).catch(err => {
                console.error("Unable to start camera scanner.", err);
                var msg = "Camera access error: " + err;
                if (location.protocol !== 'https:' && location.hostname !== 'localhost' && location.hostname !== '127.0.0.1') {
                    msg = "⚠️ Camera access blocked by mobile browser! Mobile phone cameras require HTTPS. Please open the site using https://";
                }
                alert(msg);
                $('#barcode-reader-container').hide();
            });
        }

        function initCamera() {
            if (typeof Html5Qrcode === 'undefined') {
                alert("Barcode scanner library is loading, please try again in a moment.");
                return;
            }

            if (html5QrcodeScanner) {
                try {
                    html5QrcodeScanner.clear();
                } catch (e) { }
            }

            var supportedFormats = window.Html5QrcodeSupportedFormats || (typeof Html5QrcodeSupportedFormats !== 'undefined' ? Html5QrcodeSupportedFormats : {});
            var formatsToSupport = [];

            if (supportedFormats.EAN_13 !== undefined) formatsToSupport.push(supportedFormats.EAN_13);
            if (supportedFormats.EAN_8 !== undefined) formatsToSupport.push(supportedFormats.EAN_8);
            if (supportedFormats.CODE_128 !== undefined) formatsToSupport.push(supportedFormats.CODE_128);
            if (supportedFormats.CODE_39 !== undefined) formatsToSupport.push(supportedFormats.CODE_39);
            if (supportedFormats.UPC_A !== undefined) formatsToSupport.push(supportedFormats.UPC_A);
            if (supportedFormats.UPC_E !== undefined) formatsToSupport.push(supportedFormats.UPC_E);
            if (supportedFormats.QR_CODE !== undefined) formatsToSupport.push(supportedFormats.QR_CODE);

            var config = {
                experimentalFeatures: {
                    useBarCodeDetectorIfSupported: true
                }
            };
            if (formatsToSupport.length > 0) {
                config.formatsToSupport = formatsToSupport;
            }

            html5QrcodeScanner = new Html5Qrcode("barcode-reader", config);

            Html5Qrcode.getCameras().then(cameras => {
                if (cameras && cameras.length > 0) {
                    // Find main rear/back camera on mobile phones
                    var backCam = cameras.find(c => /back|rear|environment|main|0/i.test(c.label)) || cameras[cameras.length - 1];
                    var targetCamera = backCam ? backCam.id : cameras[0].id;
                    startScannerWithCamera(targetCamera);
                } else {
                    startScannerWithCamera({ facingMode: "environment" });
                }
            }).catch(err => {
                startScannerWithCamera({ facingMode: "environment" });
            });
        }

        $(document).off('click', '.btn-scan-barcode').on('click', '.btn-scan-barcode', function () {
            activeInputTarget = $(this).data('target');
            $('#barcode-reader-container').show();
            initCamera();
        });

        function stopScanning() {
            if (html5QrcodeScanner) {
                html5QrcodeScanner.stop().then(ignore => {
                    html5QrcodeScanner.clear();
                    $('#barcode-reader-container').hide();
                }).catch(err => {
                    $('#barcode-reader-container').hide();
                });
            } else {
                $('#barcode-reader-container').hide();
            }
        }

        $(document).off('click', '#btn-stop-scan').on('click', '#btn-stop-scan', function () {
            stopScanning();
        });
    });
</script>
