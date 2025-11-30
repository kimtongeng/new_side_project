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
        background-color: #fff;
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
        display: none; /* shown when file selected */
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

    /* NEW: visual state when dragging files over the fake input */
    .file-upload-fake-input.dragover {
        outline: 2px dashed rgba(13,110,253,0.4);
        background-color: #f0f7ff;
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
                                        <div class="file-upload-fake-input" role="button" aria-label="Upload product image">
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

                                        <!-- main clear button -->
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
                            <div class="row variation-form-group" data-variation-id="{{ $variation->id }}">
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <label for="upload_image_variation_{{ $variation->id }}">
                                            Variation: {{ $variation->name ?? 'Unnamed Variation' }} (Multiple Images)
                                        </label>
                                        <div class="file-upload-wrapper mt-1">
                                            <div class="file-upload-fake-input" role="button" aria-label="Upload variation images">
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

                                            <!-- NEW: per-variation clear button -->
                                            <button type="button" id="clear_image_variation_{{ $variation->id }}"
                                                class="btn btn-outline-danger btn-remove" style="display:none;">
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
                                        <div class="variation-image-wrapper mt-2" id="variation_image_wrapper_{{ $variation->id }}">
                                            @if (!empty($variation->media) && count($variation->media) > 0)
                                                @foreach ($variation->media as $media)
                                                    <div class="variation-thumb" data-media-id="{{ $media->id }}">
                                                        <img src="{{ asset('uploads/media/' . $media->file_name) }}"
                                                            alt="variation">
                                                        <button type="button" class="remove-btn"
                                                            data-media-id="{{ $media->id }}">&times;</button>
                                                    </div>
                                                @endforeach
                                            @endif
                                        </div>
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
/***** Helper utilities *****/
function fileListToDataTransfer(files) {
    const dt = new DataTransfer();
    for (const f of files) dt.items.add(f);
    return dt;
}

/***** Create variation thumb (improved: stores file metadata for matching) *****/
function createVariationThumb(fileOrUrl, opts = {}) {
    // fileOrUrl can be a File object or a string (existing uploaded URL)
    const wrapper = document.createElement('div');
    wrapper.className = 'variation-thumb';

    // If this is a client-side File, mark as temp and store metadata
    if (!opts.mediaId && fileOrUrl && fileOrUrl instanceof File) {
        wrapper.setAttribute('data-temp', '1');
        wrapper.setAttribute('data-name', fileOrUrl.name);
        wrapper.setAttribute('data-size', String(fileOrUrl.size));
        wrapper.setAttribute('data-lastmod', String(fileOrUrl.lastModified || 0));
    } else if (opts.mediaId) {
        // existing server media thumb
        wrapper.setAttribute('data-media-id', opts.mediaId);
    }

    const img = document.createElement('img');

    if (typeof fileOrUrl === 'string') {
        img.src = fileOrUrl;
    } else {
        // File object — convert to data URL
        const reader = new FileReader();
        reader.onload = function (e) { img.src = e.target.result; };
        reader.readAsDataURL(fileOrUrl);
    }
    wrapper.appendChild(img);

    const removeBtn = document.createElement('button');
    removeBtn.type = 'button';
    removeBtn.className = 'remove-btn';
    removeBtn.innerHTML = '&times;';
    if (opts.mediaId) removeBtn.setAttribute('data-media-id', opts.mediaId);

    removeBtn.addEventListener('click', function (e) {
        e.preventDefault();

        const mediaId = this.getAttribute('data-media-id');
        const isTemp = wrapper.getAttribute('data-temp') === '1';

        if (mediaId) {
            // existing server media -> mark deleted
            if (!window.deletedMediaIds.includes(mediaId)) {
                window.deletedMediaIds.push(mediaId);
                const el = document.getElementById('deleted_media_ids');
                if (el) el.value = window.deletedMediaIds.join(',');
            }
            wrapper.remove();
            return;
        }

        if (isTemp) {
            // Remove matching File from the corresponding variation input
            // We need to find the parent variation id from wrapper's nearest variation-image-wrapper id
            let parent = wrapper.closest('.variation-image-wrapper');
            if (!parent) {
                // fallback: find by scanning all variation wrappers and matching name/size/lastmod
                // but usually parent exists
                parent = document.querySelector('.variation-image-wrapper');
            }

            // Find variationId from parent id pattern "variation_image_wrapper_{id}"
            let variationId = null;
            if (parent && parent.id) {
                const m = parent.id.match(/variation_image_wrapper_(\d+)/);
                if (m) variationId = m[1];
            } else {
                // try to find nearest .variation-form-group
                const formGroup = wrapper.closest('.variation-form-group');
                if (formGroup) variationId = formGroup.getAttribute('data-variation-id');
            }

            if (variationId) {
                // Read metadata of this thumb
                const name = wrapper.getAttribute('data-name');
                const size = Number(wrapper.getAttribute('data-size') || 0);
                const lastmod = Number(wrapper.getAttribute('data-lastmod') || 0);

                removeFileFromVariationInput(variationId, { name, size, lastmod });
            }
        }

        // Remove the thumb element from DOM
        wrapper.remove();
    });

    wrapper.appendChild(removeBtn);
    return wrapper;
}

/***** Remove a specific File from a variation input's FileList by matching metadata *****/
function removeFileFromVariationInput(variationId, fileMeta) {
    const input = document.getElementById('upload_image_variation_' + variationId);
    const display = document.getElementById('filename_display_variation_' + variationId);
    const clearBtn = document.getElementById('clear_image_variation_' + variationId);

    if (!input) return;

    const existingFiles = Array.from(input.files || []);
    // keep only files that do NOT match the metadata
    const remaining = existingFiles.filter(f => {
        const sameName = f.name === fileMeta.name;
        const sameSize = f.size === fileMeta.size;
        // lastModified may be 0 or undefined for some browsers; compare if available
        const sameLast = (typeof f.lastModified !== 'undefined') ? (f.lastModified === fileMeta.lastmod) : true;
        // If all three match => it's the file to remove
        return !(sameName && sameSize && sameLast);
    });

    // Rebuild FileList and assign back to input
    const dt = fileListToDataTransfer(remaining);
    input.files = dt.files;

    // update filename display and clear button visibility
    if (display) display.value = remaining.length ? remaining.map(f => f.name).join(', ') : 'No file selected';
    if (clearBtn) clearBtn.style.display = remaining.length ? 'inline-block' : 'none';

    // dispatch change so any listeners update state
    const changeEvent = new Event('change', { bubbles: true });
    input.dispatchEvent(changeEvent);
}

/***** Main product image setup (unchanged) *****/
function setupImageInput(id) {
    const input = document.getElementById(`upload_image_${id}`);
    const display = document.getElementById(`filename_display_${id}`);
    const preview = document.getElementById(`image_preview_${id}`);
    const wrapper = document.getElementById(`image_preview_wrapper_${id}`);
    const clearBtn = document.getElementById(`clear_image_btn_${id}`);
    const removeBtn = document.getElementById(`image_remove_btn_${id}`);
    const browseBtn = document.querySelector(`.file-upload-btn[data-input-id=\"${id}\"]`);
    const oldImageInput = document.getElementById(`old_image_${id}`);

    if (!input) return;

    browseBtn?.addEventListener('click', function(e) {
        e.preventDefault();
        input.click();
    });

    input.addEventListener('change', function() {
        const files = this.files || [];

        if (display) display.value = files.length ? Array.from(files).map(f => f.name).join(', ') : 'No file selected';

        if (id === 1 && files[0] && files[0].type && files[0].type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                wrapper.style.display = 'block';
                if (clearBtn) clearBtn.style.display = 'inline-block';
            };
            reader.readAsDataURL(files[0]);

            if (oldImageInput) oldImageInput.value = '';
        } else {
            if (clearBtn) clearBtn.style.display = files.length ? 'inline-block' : 'none';
            if (wrapper) wrapper.style.display = 'none';
            if (oldImageInput) oldImageInput.value = '';
        }
    });

    clearBtn?.addEventListener('click', function(e) {
        e.preventDefault();
        input.value = '';
        if (display) display.value = 'No file selected';
        if (preview) preview.src = '#';
        if (wrapper) wrapper.style.display = 'none';
        if (clearBtn) clearBtn.style.display = 'none';
        if (oldImageInput) oldImageInput.value = '';
    });

    removeBtn?.addEventListener('click', function(e) {
        e.preventDefault();
        input.value = '';
        if (display) display.value = 'No file selected';
        if (preview) preview.src = '#';
        if (wrapper) wrapper.style.display = 'none';
        if (clearBtn) clearBtn.style.display = 'none';
        if (oldImageInput) oldImageInput.value = '';
    });

    const fake = input.closest('.file-upload-wrapper')?.querySelector('.file-upload-fake-input');
    if (fake) {
        fake.addEventListener('click', function(e) {
            if (e.target.closest('.file-upload-btn')) return;
            input.click();
        });
        fake.tabIndex = fake.tabIndex || 0;
        fake.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                input.click();
            }
        });
    }
}
setupImageInput(1);

/***** Variation inputs wiring (rendering & drag/drop) *****/
window.deletedMediaIds = window.deletedMediaIds || [];

document.querySelectorAll('.file-upload-btn').forEach(button => {
    const inputId = button.getAttribute('data-input-id');
    const variationInput = document.getElementById('upload_image_variation_' + inputId);
    const variationDisplay = document.getElementById('filename_display_variation_' + inputId);
    const clearBtn = document.getElementById('clear_image_variation_' + inputId);

    if (variationInput && variationDisplay) {
        button.addEventListener('click', function() { variationInput.click(); });

        variationInput.addEventListener('change', function() {
            const files = Array.from(this.files || []);
            variationDisplay.value = files.length ? files.map(f => f.name).join(', ') : 'No file selected';
            if (clearBtn) clearBtn.style.display = files.length ? 'inline-block' : 'none';
            // render thumbs (change handler is single source of truth)
            renderVariationPreviewsFromFiles(inputId, files);
        });

        if (clearBtn) {
            clearBtn.addEventListener('click', function(e) {
                e.preventDefault();
                clearVariationSelection(inputId);
            });
        }

        // make fake clickable / keyboard accessible
        const fake = variationInput.closest('.file-upload-wrapper')?.querySelector('.file-upload-fake-input');
        if (fake) {
            fake.addEventListener('click', function(e) {
                if (e.target.closest('.file-upload-btn')) return;
                variationInput.click();
            });
            fake.tabIndex = fake.tabIndex || 0;
            fake.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    variationInput.click();
                }
            });
        }
    }
});

function clearVariationSelection(variationId) {
    const input = document.getElementById('upload_image_variation_' + variationId);
    const display = document.getElementById('filename_display_variation_' + variationId);
    const clearBtn = document.getElementById('clear_image_variation_' + variationId);
    const wrapper = document.getElementById('variation_image_wrapper_' + variationId);

    if (input) input.value = '';
    if (display) display.value = 'No file selected';
    if (clearBtn) clearBtn.style.display = 'none';
    if (wrapper) wrapper.querySelectorAll('.variation-thumb[data-temp="1"]').forEach(t => t.remove());
}

function renderVariationPreviewsFromFiles(variationId, files) {
    const wrapperId = 'variation_image_wrapper_' + variationId;
    let wrapper = document.getElementById(wrapperId);

    if (!wrapper) {
        const formGroup = document.querySelector(`.variation-form-group[data-variation-id="${variationId}"]`);
        if (formGroup) {
            wrapper = document.createElement('div');
            wrapper.className = 'variation-image-wrapper mt-2';
            wrapper.id = wrapperId;
            const labelEl = formGroup.querySelector('.form-group > small');
            if (labelEl && labelEl.parentNode) labelEl.parentNode.appendChild(wrapper);
            else formGroup.querySelector('.form-group').appendChild(wrapper);
        } else {
            wrapper = document.createElement('div');
            wrapper.className = 'variation-image-wrapper mt-2';
            wrapper.id = wrapperId;
            document.body.appendChild(wrapper);
        }
    }

    // remove only temp thumbs to avoid duplications
    wrapper.querySelectorAll('.variation-thumb[data-temp="1"]').forEach(t => t.remove());

    files.forEach(file => {
        if (!(file && file.type && file.type.startsWith('image/'))) return;
        const thumb = createVariationThumb(file);
        wrapper.appendChild(thumb);
    });
}

/***** Existing server thumbs removal -> update deletedMediaIds *****/
document.querySelectorAll('.variation-image-wrapper .remove-btn').forEach(btn => {
    // This attaches to existing server thumbs rendered by Blade; keep behavior
    btn.addEventListener('click', function() {
        const mediaId = this.getAttribute('data-media-id');
        if (mediaId && !window.deletedMediaIds.includes(mediaId)) {
            window.deletedMediaIds.push(mediaId);
            const el = document.getElementById('deleted_media_ids');
            if (el) el.value = window.deletedMediaIds.join(',');
        }
        const thumb = this.closest('.variation-thumb');
        if (thumb) thumb.remove();
    });
});

/***** Drag & drop wiring for fake inputs (both main and variations) *****/
document.querySelectorAll('.file-upload-fake-input').forEach(fake => {
    let realInput = fake.querySelector('.real-file-input');
    if (!realInput) {
        const btn = fake.querySelector('.file-upload-btn');
        if (btn) {
            const id = btn.getAttribute('data-input-id');
            realInput = document.getElementById('upload_image_' + id) || document.getElementById('upload_image_variation_' + id);
        }
    }
    if (!realInput) return;

    fake.addEventListener('dragover', function (e) {
        e.preventDefault();
        e.dataTransfer.dropEffect = 'copy';
        fake.classList.add('dragover');
    });
    fake.addEventListener('dragleave', function (e) { fake.classList.remove('dragover'); });

    fake.addEventListener('drop', function (e) {
        e.preventDefault();
        fake.classList.remove('dragover');

        const dtFiles = e.dataTransfer && e.dataTransfer.files;
        if (!dtFiles || dtFiles.length === 0) return;

        const imageFiles = Array.from(dtFiles).filter(f => f.type && f.type.startsWith('image/'));
        if (imageFiles.length === 0) return;

        if (!realInput.hasAttribute('multiple')) {
            realInput.files = fileListToDataTransfer([imageFiles[0]]).files;
        } else {
            realInput.files = fileListToDataTransfer(imageFiles).files;
        }

        // dispatch change -> filename/clear button/preview will update from the single change handler
        const changeEvent = new Event('change', { bubbles: true });

        
        realInput.dispatchEvent(changeEvent);

        // update visible filename text
        const display = fake.querySelector('input[type="text"]');
        if (display) display.value = Array.from(realInput.files).map(f => f.name).join(', ');
    });

    fake.addEventListener('click', function (e) {
        if (e.target.closest('.file-upload-btn')) return;
        realInput.click();
    });
    fake.tabIndex = fake.tabIndex || 0;
    fake.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            realInput.click();
        }
    });
});
</script>
