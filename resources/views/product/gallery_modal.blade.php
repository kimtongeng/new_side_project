<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content"
        style="border-radius: 16px; overflow: hidden; border: none; box-shadow: 0 20px 40px rgba(0,0,0,0.15);">
        <div class="modal-header" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 18px 24px;">
            <div style="display: flex; align-items: center; justify-content: space-between; width: 100%;">
                <h4 class="modal-title" id="modalTitle"
                    style="font-weight: 700; color: #0f172a; font-size: 1.25rem; margin: 0; display: flex; align-items: center; gap: 10px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                        stroke="#6366f1" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="feather feather-image">
                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                        <circle cx="8.5" cy="8.5" r="1.5"></circle>
                        <polyline points="21 15 16 10 5 21"></polyline>
                    </svg>
                    <span>{!! \App\Utils\ProductUtil::getFormattedProductName($product->name, $product->secondary_name, true) !!} - @lang('lang_v1.product_gallery')</span>
                </h4>
                <div style="display: flex; align-items: center; gap: 12px;">
                    @if(auth()->user()->can('product.update') || auth()->user()->can('product.upload_image') || auth()->user()->can('product.create'))
                        <button type="button" class="btn btn-sm btn-primary btn-add-gallery-img-trigger"
                            style="background: linear-gradient(135deg, #6366f1, #4f46e5); border: none; border-radius: 8px; font-weight: 600; padding: 6px 14px; display: flex; align-items: center; gap: 6px; color: #fff; box-shadow: 0 4px 10px rgba(99,102,241,0.25); cursor: pointer;">
                            <i class="fa fa-plus"></i> @lang('messages.add')
                        </button>
                    @endif
                    <button type="button" class="close no-print" data-dismiss="modal" aria-label="Close"
                        style="font-size: 28px; font-weight: 400; color: #64748b; opacity: 0.8; background: none; border: none; padding: 0; line-height: 1; transition: color 0.2s; cursor: pointer; outline: none; float: none; margin: 0;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            </div>
        </div>
        <div class="modal-body" style="padding: 24px; background: #ffffff;">
            <!-- Hidden file input for gallery upload -->
            <input type="file" id="gallery-image-upload-input" accept="image/*" multiple style="display: none;">

            <div class="row">
                @if(count($gallery_images) > 0 || !empty($product->image))
                    <!-- Large Image Preview Area -->
                    <div class="col-md-8 text-center" style="margin-bottom: 20px;">
                        <div class="main-preview-container"
                            style="background: #f8fafc; border-radius: 12px; padding: 20px; display: flex; align-items: center; justify-content: center; height: 420px; border: 1px solid #f1f5f9; position: relative; overflow: hidden; box-shadow: inset 0 2px 4px rgba(0,0,0,0.02);">
                            <!-- Navigation Arrows -->
                            <button type="button" class="btn-gallery-nav prev" aria-label="Previous"
                                style="position: absolute; left: 15px; z-index: 10; background: rgba(255,255,255,0.9); border: none; width: 44px; height: 44px; border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(0,0,0,0.08); cursor: pointer; color: #0f172a; font-size: 16px; transition: all 0.2s; outline: none;">
                                <i class="fa fa-chevron-left"></i>
                            </button>

                            <img id="gallery-main-img" src="{{$product->image_url}}" alt="{{$product->name}}"
                                style="max-width: 100%; max-height: 100%; object-fit: contain; border-radius: 8px; transition: opacity 0.2s ease-in-out;">

                            <button type="button" class="btn-gallery-nav next" aria-label="Next"
                                style="position: absolute; right: 15px; z-index: 10; background: rgba(255,255,255,0.9); border: none; width: 44px; height: 44px; border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(0,0,0,0.08); cursor: pointer; color: #0f172a; font-size: 16px; transition: all 0.2s; outline: none;">
                                <i class="fa fa-chevron-right"></i>
                            </button>

                            @if(auth()->user()->can('product.update') || auth()->user()->can('product.delete'))
                                <!-- Remove button on main preview -->
                                <button type="button" class="btn-delete-active-img" id="btn-delete-main-preview" title="Remove current image"
                                    style="position: absolute; top: 15px; right: 15px; z-index: 10; background: rgba(239, 68, 68, 0.9); border: none; width: 38px; height: 38px; border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(239,68,68,0.3); cursor: pointer; color: #ffffff; font-size: 14px; transition: all 0.2s; outline: none;">
                                    <i class="fa fa-trash"></i>
                                </button>
                            @endif
                        </div>
                    </div>

                    <!-- Thumbnails List Area -->
                    <div class="col-md-4">
                        <h5
                            style="font-weight: 700; color: #334155; margin-top: 0; margin-bottom: 16px; font-size: 1rem; letter-spacing: -0.01em; display: flex; justify-content: space-between; align-items: center;">
                            <span>@lang('lang_v1.images')</span>
                            <span id="gallery-counter"
                                style="font-size: 0.8rem; font-weight: 500; color: #64748b; background: #f1f5f9; padding: 2px 8px; border-radius: 12px;">1
                                of 1</span>
                        </h5>
                        <!-- Thumbnail list with grid display -->
                        <div class="gallery-thumbnails-grid"
                            style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; max-height: 380px; overflow-y: auto; padding-right: 6px;">
                            
                            @if(!empty($product->image))
                                <!-- Main product image as first thumbnail -->
                                <div class="gallery-thumb-item active" data-url="{{$product->image_url}}" data-is-main="1"
                                    style="position: relative; cursor: pointer; border-radius: 8px; border: 2px solid #6366f1; padding: 3px; background: #fff; transition: all 0.2s; aspect-ratio: 1; display: flex; align-items: center; justify-content: center; overflow: hidden; box-sizing: border-box;">
                                    <img src="{{$product->image_url}}"
                                        style="max-width: 100%; max-height: 100%; object-fit: cover; border-radius: 4px;"
                                        alt="Primary thumbnail">
                                    @if(auth()->user()->can('product.update') || auth()->user()->can('product.delete'))
                                        <button type="button" class="btn-delete-gallery-img" data-is-main="1" title="Remove Image">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    @endif
                                </div>
                            @endif

                            <!-- Gallery images -->
                            @foreach($gallery_images as $media)
                                <div class="gallery-thumb-item" data-url="{{$media->display_url}}" data-is-main="0" data-media-id="{{$media->id}}"
                                    style="position: relative; cursor: pointer; border-radius: 8px; border: 2px solid #e2e8f0; padding: 3px; background: #fff; transition: all 0.2s; aspect-ratio: 1; display: flex; align-items: center; justify-content: center; overflow: hidden; box-sizing: border-box;">
                                    <img src="{{$media->display_url}}"
                                        style="max-width: 100%; max-height: 100%; object-fit: cover; border-radius: 4px;"
                                        alt="Gallery thumbnail">
                                    @if(auth()->user()->can('product.update') || auth()->user()->can('product.delete'))
                                        <button type="button" class="btn-delete-gallery-img" data-is-main="0" data-media-id="{{$media->id}}" title="Remove Image">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    @endif
                                </div>
                            @endforeach

                            @if(auth()->user()->can('product.update') || auth()->user()->can('product.upload_image') || auth()->user()->can('product.create'))
                                <!-- Add Image grid tile -->
                                <div class="gallery-add-thumb-item btn-add-gallery-img-trigger" title="Upload Image"
                                    style="cursor: pointer; border-radius: 8px; border: 2px dashed #a5b4fc; padding: 3px; background: #eef2ff; transition: all 0.2s; aspect-ratio: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; color: #4f46e5; box-sizing: border-box;">
                                    <i class="fa fa-plus" style="font-size: 18px; margin-bottom: 2px;"></i>
                                    <span style="font-size: 11px; font-weight: 600;">@lang('messages.add')</span>
                                </div>
                            @endif
                        </div>
                    </div>
                @else
                    <!-- No Images Area -->
                    <div class="col-md-12 text-center" style="padding: 40px 20px;">
                        <div style="color: #64748b; margin-bottom: 20px;">
                            <i class="fa fa-image" style="font-size: 48px; opacity: 0.4;"></i>
                            <p style="margin-top: 10px; font-size: 16px; font-weight: 600;">No images available for this product</p>
                        </div>
                        @if(auth()->user()->can('product.update') || auth()->user()->can('product.upload_image') || auth()->user()->can('product.create'))
                            <div>
                                <button type="button" class="btn btn-primary btn-add-gallery-img-trigger"
                                    style="background: linear-gradient(135deg, #6366f1, #4f46e5); border: none; border-radius: 8px; font-weight: 600; padding: 8px 20px;">
                                    <i class="fa fa-plus"></i> @lang('messages.add') Image
                                </button>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
    .gallery-thumb-item:hover {
        border-color: #818cf8 !important;
        transform: translateY(-2px);
        box-shadow: 0 6px 12px rgba(99, 102, 241, 0.1);
    }

    .gallery-thumb-item.active {
        border-color: #6366f1 !important;
        box-shadow: 0 4px 12px rgba(99, 102, 241, 0.25);
    }

    .btn-delete-gallery-img {
        position: absolute;
        top: 3px;
        right: 3px;
        background: rgba(239, 68, 68, 0.9);
        border: none;
        border-radius: 50%;
        width: 22px;
        height: 22px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 11px;
        cursor: pointer;
        z-index: 5;
        opacity: 0;
        transition: opacity 0.2s, transform 0.15s;
    }

    .gallery-thumb-item:hover .btn-delete-gallery-img {
        opacity: 1 !important;
    }

    .btn-delete-gallery-img:hover {
        background: #dc2626 !important;
        transform: scale(1.15);
    }

    #btn-delete-main-preview:hover {
        background: #dc2626 !important;
        transform: scale(1.1);
    }

    .gallery-add-thumb-item:hover {
        border-color: #6366f1 !important;
        background: #e0e7ff !important;
        transform: translateY(-2px);
    }

    .btn-gallery-nav:hover {
        background: #ffffff !important;
        color: #6366f1 !important;
        transform: scale(1.06);
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.12) !important;
    }

    .btn-gallery-nav:active {
        transform: scale(0.96);
    }

    .close:hover {
        color: #0f172a !important;
    }

    /* Scrollbar Styling for thumbnails grid */
    .gallery-thumbnails-grid::-webkit-scrollbar {
        width: 6px;
    }

    .gallery-thumbnails-grid::-webkit-scrollbar-track {
        background: #f8fafc;
        border-radius: 10px;
    }

    .gallery-thumbnails-grid::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 10px;
    }

    .gallery-thumbnails-grid::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }
</style>

<script>
    $(document).ready(function () {
        var thumbs = $('.gallery-thumb-item');
        var mainImg = $('#gallery-main-img');
        var currentIndex = 0;

        @if(!empty($active_url))
            var activeUrl = "{!! $active_url !!}";
            thumbs.each(function (index) {
                if ($(this).attr('data-url') === activeUrl) {
                    currentIndex = index;
                }
            });
        @endif

        function updateMainImage(index) {
            if (index < 0 || index >= thumbs.length) return;

            currentIndex = index;
            var newUrl = $(thumbs[currentIndex]).attr('data-url');

            // Fade transition effect
            mainImg.css('opacity', 0);
            setTimeout(function () {
                mainImg.attr('src', newUrl);
                mainImg.css('opacity', 1);
            }, 120);

            // Update active thumbnail class
            thumbs.removeClass('active');
            $(thumbs[currentIndex]).addClass('active');

            // Update image counter text
            $('#gallery-counter').text((currentIndex + 1) + ' of ' + thumbs.length);

            // Auto scroll active thumbnail into view inside scroll container
            var container = $('.gallery-thumbnails-grid');
            var activeThumb = $(thumbs[currentIndex]);
            var containerHeight = container.height();
            var activeTop = activeThumb.position().top;
            var activeHeight = activeThumb.outerHeight();

            if (activeTop < 0 || activeTop + activeHeight > containerHeight) {
                container.scrollTop(container.scrollTop() + activeTop - 10);
            }
        }

        // Initialize active state
        if (thumbs.length > 0) {
            updateMainImage(currentIndex);
        }

        thumbs.on('click', function (e) {
            if ($(e.target).closest('.btn-delete-gallery-img').length > 0) return;
            var idx = thumbs.index(this);
            updateMainImage(idx);
        });

        $('.btn-gallery-nav.prev').on('click', function (e) {
            e.stopPropagation();
            var newIdx = currentIndex - 1;
            if (newIdx < 0) {
                newIdx = thumbs.length - 1; // Wrap around to end
            }
            updateMainImage(newIdx);
        });

        $('.btn-gallery-nav.next').on('click', function (e) {
            e.stopPropagation();
            var newIdx = currentIndex + 1;
            if (newIdx >= thumbs.length) {
                newIdx = 0; // Wrap around to start
            }
            updateMainImage(newIdx);
        });

        // Keyboard navigation for gallery modal
        $(document).off('keydown.gallery').on('keydown.gallery', function (e) {
            var activeModal = $('.view_modal').is(':visible') ? '.view_modal' : '#view_product_modal';
            if ($(activeModal).is(':visible')) {
                if (e.keyCode === 37) { // Left arrow
                    $(activeModal).find('.btn-gallery-nav.prev').click();
                } else if (e.keyCode === 39) { // Right arrow
                    $(activeModal).find('.btn-gallery-nav.next').click();
                }
            }
        });

        // Add gallery image triggers
        $(document).off('click', '.btn-add-gallery-img-trigger').on('click', '.btn-add-gallery-img-trigger', function (e) {
            e.preventDefault();
            $('#gallery-image-upload-input').trigger('click');
        });

        $(document).off('change', '#gallery-image-upload-input').on('change', '#gallery-image-upload-input', function () {
            var files = this.files;
            if (!files || files.length === 0) return;

            var formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            for (var i = 0; i < files.length; i++) {
                formData.append('file[' + i + ']', files[i]);
            }

            var $btn = $('.btn-add-gallery-img-trigger');
            var originalText = $btn.html();
            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Uploading...');

            $.ajax({
                url: "{{ action([\App\Http\Controllers\ProductController::class, 'addGalleryImage'], [$product->id]) }}",
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function (result) {
                    if (result.success) {
                        toastr.success(result.msg);
                        var activeModal = $('.view_modal').is(':visible') ? '.view_modal' : '#view_product_modal';
                        $(activeModal).load("{{ action([\App\Http\Controllers\ProductController::class, 'showGallery'], [$product->id]) }}");
                        if (typeof product_table !== 'undefined') {
                            product_table.ajax.reload(null, false);
                        }
                    } else {
                        toastr.error(result.msg);
                        $btn.prop('disabled', false).html(originalText);
                    }
                },
                error: function (xhr) {
                    toastr.error('Error uploading image.');
                    $btn.prop('disabled', false).html(originalText);
                }
            });
        });

        // Remove / Delete image handler
        function deleteGalleryImage(isMain, mediaId, productId) {
            var confirmDelete = function() {
                var url = isMain ? '/products/delete-main-image/' + productId : '/delete-media/' + mediaId;
                $.ajax({
                    url: url,
                    type: 'GET',
                    dataType: 'json',
                    success: function (result) {
                        if (result.success) {
                            toastr.success(result.msg);
                            var activeModal = $('.view_modal').is(':visible') ? '.view_modal' : '#view_product_modal';
                            $(activeModal).load("{{ action([\App\Http\Controllers\ProductController::class, 'showGallery'], [$product->id]) }}");
                            if (typeof product_table !== 'undefined') {
                                product_table.ajax.reload(null, false);
                            }
                        } else {
                            toastr.error(result.msg);
                        }
                    },
                    error: function (xhr) {
                        toastr.error('Error deleting image.');
                    }
                });
            };

            if (typeof swal === 'function') {
                swal({
                    title: "Are you sure?",
                    text: "Do you want to remove this image?",
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                }).then((willDelete) => {
                    if (willDelete) {
                        confirmDelete();
                    }
                });
            } else if (confirm("Are you sure you want to remove this image?")) {
                confirmDelete();
            }
        }

        $(document).off('click', '.btn-delete-gallery-img').on('click', '.btn-delete-gallery-img', function (e) {
            e.stopPropagation();
            var isMain = $(this).data('is-main') == 1;
            var mediaId = $(this).data('media-id');
            var productId = "{{ $product->id }}";
            deleteGalleryImage(isMain, mediaId, productId);
        });

        $(document).off('click', '#btn-delete-main-preview').on('click', '#btn-delete-main-preview', function (e) {
            e.stopPropagation();
            if (thumbs.length === 0) return;
            var activeThumb = $(thumbs[currentIndex]);
            var isMain = activeThumb.data('is-main') == 1;
            var mediaId = activeThumb.data('media-id');
            var productId = "{{ $product->id }}";
            deleteGalleryImage(isMain, mediaId, productId);
        });

        // Clean up keyboard bindings when the modal is closed
        $('#view_product_modal, .view_modal').on('hidden.bs.modal', function () {
            $(document).off('keydown.gallery');
        });
    });
</script>