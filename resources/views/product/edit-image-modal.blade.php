<style>
    .file-upload-wrapper {
        position: relative;
        width: 100%;
        display: flex;
        gap: 5px;
    }

    .file-upload-fake-input {
        flex-grow: 1;
        display: flex;
        align-items: center;
        border: 1px solid #ccc;
        border-radius: 4px;
        overflow: hidden;
        height: 38px;
        font-size: 14px;
        position: relative;
    }

    .file-upload-fake-input input[type="text"] {
        border: none;
        outline: none;
        flex: 1;
        padding: 0.375rem 0.75rem;
        background-color: #fff;
    }

    .file-upload-btn {
        padding: 0.375rem 0.75rem;
        background-color: #0d6efd;
        color: #fff;
        display: flex;
        align-items: center;
        font-weight: 500;
        cursor: pointer;
        z-index: 2;
    }

    .real-file-input {
        position: absolute;
        top: 0;
        left: 0;
        height: 38px;
        width: calc(100% - 90px);
        opacity: 0;
        cursor: pointer;
        z-index: 3;
    }

    .btn-remove {
        height: 38px;
        display: none;
        font-size: 14px;
    }

    .image-preview-wrapper {
        position: relative;
        margin-top: 10px;
    }

    .image_preview {
        width: 100%;
        border-radius: 4px;
        border: 1px solid #ccc;
    }

    .image-remove-btn {
        position: absolute;
        top: -8px;
        right: -8px;
        background-color: #dc3545;
        color: white;
        border: none;
        border-radius: 50%;
        width: 24px;
        height: 24px;
        font-size: 16px;
        line-height: 1;
        text-align: center;
        cursor: pointer;
        z-index: 5;
    }

    .variation-image-wrapper {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 10px;
    }

    .variation-thumb {
        position: relative;
        width: 80px;
        height: 80px;
    }

    .variation-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border: 1px solid #ccc;
        border-radius: 4px;
    }

    .variation-thumb .remove-btn {
        position: absolute;
        top: -8px;
        right: -8px;
        background-color: #dc3545;
        color: #fff;
        border: none;
        border-radius: 50%;
        width: 20px;
        height: 20px;
        font-size: 14px;
        line-height: 1;
        text-align: center;
        cursor: pointer;
        z-index: 2;
    }

    /* Add margin to separate each variation visually */
    .variation-form-group {
        border-top: 1px dashed #eee;
    }
</style>

@php
    $form_class = empty($duplicate_product) ? 'create' : '';
    $is_image_required = !empty($common_settings['is_product_image_required']);
    $product_image_url = !empty($product_image) ? asset('uploads/img/' . $product_image) : '';
@endphp

<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close no-print" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <h4 class="modal-title">Edit image product {{ $product->name }}</h4>
        </div>

        {!! Form::open([
            'url' => action([\App\Http\Controllers\ProductController::class, 'update_image'], ['id' => $product->id]),
            'method' => 'post',
            'id' => 'product_add_form',
            'class' => 'product_form ' . $form_class,
            'files' => true,
        ]) !!}

        <div class="modal-body">
            <section class="content">


                <div class="row">
                    <div class="col-sm-12 col-lg-6">
                        <!-- Product Image (always at the top) -->
                        <div class="row">
                            <div class="col-sm-12 ">
                                <div class="form-group">
                                    {!! Form::label('upload_image_1', 'Product Image') !!}
                                    <div class="file-upload-wrapper mt-1">
                                        <div class="file-upload-fake-input">
                                            <input type="text" id="filename_display_1" placeholder="No file selected"
                                                readonly>
                                            <div class="file-upload-btn" data-input-id="1">
                                                <i class="fas fa-folder-open mr-1"></i> Browse...
                                            </div>
                                            {!! Form::file('image_1', [
                                                'id' => 'upload_image_1',
                                                'accept' => 'image/*',
                                                'class' => 'real-file-input',
                                            ]) !!}
                                            {!! Form::hidden('old_image_1', $product_image, ['id' => 'old_image_1']) !!}
                                        </div>
                                        <button type="button" id="clear_image_btn_1"
                                            class="btn btn-outline-danger btn-remove">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </div>
                                    <small>
                                        <p class="help-block mt-2">
                                            @lang('purchase.max_file_size', ['size' => config('constants.document_size_limit') / 1000000])<br>
                                            @lang('lang_v1.aspect_ratio_should_be_1_1')<br>
                                            Previously uploaded image will be replaced
                                        </p>
                                    </small>
                                    <div class="image-preview-wrapper" id="image_preview_wrapper_1"
                                        style="{{ empty($product_image_url) ? 'display: none;' : '' }}">
                                        <img id="image_preview_1" class="image_preview" src="{{ $product_image_url }}"
                                            alt="Product Image" />
                                        <button type="button" id="image_remove_btn_1"
                                            class="image-remove-btn">&times;</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="col-sm-12 col-lg-6">
                        <!-- Each Variation: Full width, one under the other -->
                        @foreach ($variations as $variation)
                            <div class="row variation-form-group">
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <label for="upload_image_variation_{{ $variation->id }}">
                                            Variation: {{ $variation->name ?? 'Unnamed Variation' }} (Multiple Images)
                                        </label>
                                        <div class="file-upload-wrapper mt-1">
                                            <div class="file-upload-fake-input">
                                                <input type="text"
                                                    id="filename_display_variation_{{ $variation->id }}"
                                                    placeholder="No file selected" readonly>
                                                <div class="file-upload-btn" data-input-id="{{ $variation->id }}">
                                                    <i class="fas fa-folder-open mr-1"></i> Browse...
                                                </div>
                                                <input type="file" name="variation_images[{{ $variation->id }}][]"
                                                    id="upload_image_variation_{{ $variation->id }}" accept="image/*"
                                                    multiple class="real-file-input" />
                                            </div>
                                        </div>
                                        <small>
                                            <p class="help-block mt-2">
                                                @lang('purchase.max_file_size', ['size' => config('constants.document_size_limit') / 1000000])<br>
                                                @lang('lang_v1.aspect_ratio_should_be_1_1')<br>
                                                Previously uploaded image will be replaced
                                            </p>
                                        </small>
                                        @if (!empty($variation->media) && count($variation->media) > 0)
                                            <div class="variation-image-wrapper mt-2">
                                                @foreach ($variation->media as $media)
                                                    <div class="variation-thumb">
                                                        <img src="{{ asset('uploads/media/' . $media->file_name) }}"
                                                            alt="variation">
                                                        <button type="button" class="remove-btn"
                                                            data-media-id="{{ $media->id }}">&times;</button>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                        <input type="hidden" name="deleted_media_ids" id="deleted_media_ids" value="">
                    </div>
                </div>



            </section>
        </div>

        <div class="modal-footer">
            <button type="button" class="tw-dw-btn tw-dw-btn-neutral tw-text-white no-print" data-dismiss="modal">
                @lang('messages.close')
            </button>
            <button type="submit" class="tw-dw-btn tw-dw-btn-primary tw-text-white no-print">Update</button>
        </div>

        {!! Form::close() !!}
    </div>
</div>

<script>
    // Product image single file preview
    function setupImageInput(id) {
        const input = document.getElementById(`upload_image_${id}`);
        const display = document.getElementById(`filename_display_${id}`);
        const preview = document.getElementById(`image_preview_${id}`);
        const wrapper = document.getElementById(`image_preview_wrapper_${id}`);
        const clearBtn = document.getElementById(`clear_image_btn_${id}`);
        const removeBtn = document.getElementById(`image_remove_btn_${id}`);
        const browseBtn = document.querySelector(`.file-upload-btn[data-input-id=\"${id}\"]`);
        const oldImageInput = document.getElementById(`old_image_${id}`);

        browseBtn?.addEventListener('click', function(e) {
            e.preventDefault();
            input.click();
        });

        input.addEventListener('change', function() {
            const files = this.files;

            if (files.length > 0) {
                display.value = Array.from(files).map(f => f.name).join(', ');
            } else {
                display.value = 'No file selected';
            }

            if (id === 1 && files[0] && files[0].type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    wrapper.style.display = 'block';
                    clearBtn.style.display = 'inline-block';
                };
                reader.readAsDataURL(files[0]);

                if (oldImageInput) oldImageInput.value = '';
            } else {
                clearBtn.style.display = files.length ? 'inline-block' : 'none';
                if (wrapper) wrapper.style.display = 'none';
                if (oldImageInput) oldImageInput.value = '';
            }
        });

        clearBtn?.addEventListener('click', function(e) {
            e.preventDefault();
            clear();
        });

        removeBtn?.addEventListener('click', function(e) {
            e.preventDefault();
            clear();
        });

        function clear() {
            input.value = '';
            display.value = 'No file selected';
            if (id === 1) {
                preview.src = '#';
                wrapper.style.display = 'none';
            }
            clearBtn.style.display = 'none';
            if (oldImageInput) oldImageInput.value = '';
        }
    }

    setupImageInput(1);

    // For each variation: set up file input label change
    document.querySelectorAll('.file-upload-btn').forEach(button => {
        const inputId = button.getAttribute('data-input-id');
        const input = document.getElementById('upload_image_variation_' + inputId);
        const display = document.getElementById('filename_display_variation_' + inputId);

        if (input && display) {
            button.addEventListener('click', function() {
                input.click();
            });

            input.addEventListener('change', function() {
                const files = this.files;
                if (files.length > 0) {
                    display.value = Array.from(files).map(f => f.name).join(', ');
                } else {
                    display.value = 'No file selected';
                }
            });
        }
    });

    // Track deleted media (works for all variations)
    window.deletedMediaIds = [];
    document.querySelectorAll('.remove-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const mediaId = this.getAttribute('data-media-id');
            if (!window.deletedMediaIds.includes(mediaId)) {
                window.deletedMediaIds.push(mediaId);
                document.getElementById('deleted_media_ids').value = deletedMediaIds.join(',');
            }
            this.closest('.variation-thumb').remove();
        });
    });
</script>
