<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content" style="border-radius: 16px; overflow: hidden; border: none; box-shadow: 0 20px 40px rgba(0,0,0,0.15);">
        <div class="modal-header" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 18px 24px;">
            <div style="display: flex; align-items: center; justify-content: space-between; width: 100%;">
                <h4 class="modal-title" id="modalTitle" style="font-weight: 700; color: #0f172a; font-size: 1.25rem; margin: 0; display: flex; align-items: center; gap: 10px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#6366f1" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-image">
                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                        <circle cx="8.5" cy="8.5" r="1.5"></circle>
                        <polyline points="21 15 16 10 5 21"></polyline>
                    </svg>
                    <span>{{$product->name}} - @lang('lang_v1.product_gallery')</span>
                </h4>
                <button type="button" class="close no-print" data-dismiss="modal" aria-label="Close" style="font-size: 28px; font-weight: 400; color: #64748b; opacity: 0.8; background: none; border: none; padding: 0; line-height: 1; transition: color 0.2s; cursor: pointer; outline: none; float: none; margin: 0;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        </div>
        <div class="modal-body" style="padding: 24px; background: #ffffff;">
            <div class="row">
                @if(count($gallery_images) > 0)
                    <!-- Large Image Preview Area -->
                    <div class="col-md-8 text-center" style="margin-bottom: 20px;">
                        <div class="main-preview-container" style="background: #f8fafc; border-radius: 12px; padding: 20px; display: flex; align-items: center; justify-content: center; height: 420px; border: 1px solid #f1f5f9; position: relative; overflow: hidden; box-shadow: inset 0 2px 4px rgba(0,0,0,0.02);">
                            <!-- Navigation Arrows -->
                            <button type="button" class="btn-gallery-nav prev" aria-label="Previous" style="position: absolute; left: 15px; z-index: 10; background: rgba(255,255,255,0.9); border: none; width: 44px; height: 44px; border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(0,0,0,0.08); cursor: pointer; color: #0f172a; font-size: 16px; transition: all 0.2s; outline: none;">
                                <i class="fa fa-chevron-left"></i>
                            </button>
                            
                            <img id="gallery-main-img" src="{{$product->image_url}}" alt="{{$product->name}}" style="max-width: 100%; max-height: 100%; object-fit: contain; border-radius: 8px; transition: opacity 0.2s ease-in-out;">
                            
                            <button type="button" class="btn-gallery-nav next" aria-label="Next" style="position: absolute; right: 15px; z-index: 10; background: rgba(255,255,255,0.9); border: none; width: 44px; height: 44px; border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(0,0,0,0.08); cursor: pointer; color: #0f172a; font-size: 16px; transition: all 0.2s; outline: none;">
                                <i class="fa fa-chevron-right"></i>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Thumbnails List Area -->
                    <div class="col-md-4">
                        <h5 style="font-weight: 700; color: #334155; margin-top: 0; margin-bottom: 16px; font-size: 1rem; letter-spacing: -0.01em; display: flex; justify-content: space-between; align-items: center;">
                            <span>@lang('lang_v1.images')</span>
                            <span id="gallery-counter" style="font-size: 0.8rem; font-weight: 500; color: #64748b; background: #f1f5f9; padding: 2px 8px; border-radius: 12px;">1 of 1</span>
                        </h5>
                        <!-- Thumbnail list with grid display -->
                        <div class="gallery-thumbnails-grid" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; max-height: 380px; overflow-y: auto; padding-right: 6px;">
                            <!-- Main product image as first thumbnail -->
                            <div class="gallery-thumb-item active" data-url="{{$product->image_url}}" style="cursor: pointer; border-radius: 8px; border: 2px solid #6366f1; padding: 3px; background: #fff; transition: all 0.2s; aspect-ratio: 1; display: flex; align-items: center; justify-content: center; overflow: hidden; box-sizing: border-box;">
                                <img src="{{$product->image_url}}" style="max-width: 100%; max-height: 100%; object-fit: cover; border-radius: 4px;" alt="Primary thumbnail">
                            </div>
                            
                            <!-- Gallery images -->
                            @foreach($gallery_images as $media)
                                <div class="gallery-thumb-item" data-url="{{$media->display_url}}" style="cursor: pointer; border-radius: 8px; border: 2px solid #e2e8f0; padding: 3px; background: #fff; transition: all 0.2s; aspect-ratio: 1; display: flex; align-items: center; justify-content: center; overflow: hidden; box-sizing: border-box;">
                                    <img src="{{$media->display_url}}" style="max-width: 100%; max-height: 100%; object-fit: cover; border-radius: 4px;" alt="Gallery thumbnail">
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    <!-- Full Width Single Image Area -->
                    <div class="col-md-12 text-center">
                        <div class="main-preview-container" style="background: #f8fafc; border-radius: 12px; padding: 20px; display: flex; align-items: center; justify-content: center; height: 420px; border: 1px solid #f1f5f9; overflow: hidden; box-shadow: inset 0 2px 4px rgba(0,0,0,0.02);">
                            <img id="gallery-main-img" src="{{$product->image_url}}" alt="{{$product->name}}" style="max-width: 100%; max-height: 100%; object-fit: contain; border-radius: 8px;">
                        </div>
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
    .btn-gallery-nav:hover {
        background: #ffffff !important;
        color: #6366f1 !important;
        transform: scale(1.06);
        box-shadow: 0 6px 16px rgba(0,0,0,0.12) !important;
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
    $(document).ready(function() {
        var thumbs = $('.gallery-thumb-item');
        var mainImg = $('#gallery-main-img');
        var currentIndex = 0;
        
        @if(!empty($active_url))
            var activeUrl = "{!! $active_url !!}";
            thumbs.each(function(index) {
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
            setTimeout(function() {
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
        updateMainImage(currentIndex);
        
        thumbs.on('click', function() {
            var idx = thumbs.index(this);
            updateMainImage(idx);
        });
        
        $('.btn-gallery-nav.prev').on('click', function(e) {
            e.stopPropagation();
            var newIdx = currentIndex - 1;
            if (newIdx < 0) {
                newIdx = thumbs.length - 1; // Wrap around to end
            }
            updateMainImage(newIdx);
        });
        
        $('.btn-gallery-nav.next').on('click', function(e) {
            e.stopPropagation();
            var newIdx = currentIndex + 1;
            if (newIdx >= thumbs.length) {
                newIdx = 0; // Wrap around to start
            }
            updateMainImage(newIdx);
        });
        
        // Keyboard navigation for gallery modal
        $(document).off('keydown.gallery').on('keydown.gallery', function(e) {
            var activeModal = $('.view_modal').is(':visible') ? '.view_modal' : '#view_product_modal';
            if ($(activeModal).is(':visible')) {
                if (e.keyCode === 37) { // Left arrow
                    $(activeModal).find('.btn-gallery-nav.prev').click();
                } else if (e.keyCode === 39) { // Right arrow
                    $(activeModal).find('.btn-gallery-nav.next').click();
                }
            }
        });
        
        // Clean up keyboard bindings when the modal is closed
        $('#view_product_modal, .view_modal').on('hidden.bs.modal', function () {
            $(document).off('keydown.gallery');
        });
    });
</script>
