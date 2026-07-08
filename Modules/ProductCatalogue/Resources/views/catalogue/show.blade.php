<div class="modal-dialog modal-xl" role="document">
	<div class="modal-content">
		<div class="modal-header">
		    <button type="button" class="close no-print" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
		      <h4 class="modal-title" id="modalTitle">{{$product->name}}</h4>
	    </div>
	    <div class="modal-body">
      		<div class="row">
      			<div class="col-md-4">
      				@php
      					$gallery_images = [];
      					if (!empty($product->image)) {
      						$gallery_images[] = $product->image_url;
      					}
      					if (!empty($product->media)) {
      						foreach ($product->media->where('model_media_type', 'product_gallery') as $media) {
      							$gallery_images[] = $media->display_url;
      						}
      					}
      					if (!empty($product->variations)) {
      						foreach ($product->variations as $variation) {
      							foreach ($variation->media as $media) {
      								$gallery_images[] = $media->display_url;
      							}
      						}
      					}
      					if (empty($gallery_images)) {
      						$gallery_images[] = asset('/img/default.png');
      					}
      					$gallery_images = array_values(array_unique($gallery_images));
      				@endphp

      				<div class="product-gallery-container" style="position: relative; margin-bottom: 20px;">
      					<!-- Large Image Preview Area -->
      					<div class="main-preview-container" style="background: #f8fafc; border-radius: 12px; padding: 15px; display: flex; align-items: center; justify-content: center; height: 350px; border: 1px solid #f1f5f9; position: relative; overflow: hidden; box-shadow: inset 0 2px 4px rgba(0,0,0,0.02);">
      						@if(count($gallery_images) > 1)
      							<!-- Navigation Arrows -->
      							<button type="button" class="btn-gallery-nav prev" aria-label="Previous" style="position: absolute; left: 10px; z-index: 10; background: rgba(255,255,255,0.9); border: none; width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(0,0,0,0.08); cursor: pointer; color: #0f172a; font-size: 14px; transition: all 0.2s; outline: none;">
      								<i class="fa fa-chevron-left"></i>
      							</button>
      						@endif
      						
      						<img id="gallery-main-img" class="gallery-fade-in" src="{{ $gallery_images[0] }}" alt="{{ $product->name }}" style="max-width: 100%; max-height: 100%; object-fit: contain; border-radius: 8px;">
      						
      						@if($product->type == 'single' && !empty($discounts[$product->variations->first()->id]))
      							<span class="label label-warning discount-badge" style="position: absolute; top: 15px; right: 15px; z-index: 5; font-size: 14px; padding: 5px 10px; border-radius: 4px; font-weight: bold; background-color: #f0ad4e;">- {{@num_format($discounts[$product->variations->first()->id]->discount_amount)}}%</span>
      						@endif

      						@if(count($gallery_images) > 1)
      							<button type="button" class="btn-gallery-nav next" aria-label="Next" style="position: absolute; right: 10px; z-index: 10; background: rgba(255,255,255,0.9); border: none; width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(0,0,0,0.08); cursor: pointer; color: #0f172a; font-size: 14px; transition: all 0.2s; outline: none;">
      								<i class="fa fa-chevron-right"></i>
      							</button>
      						@endif
      					</div>

      					@if(count($gallery_images) > 1)
      						<!-- Thumbnails Grid Area -->
      						<div style="margin-top: 12px;">
      							<div class="gallery-thumbnails-grid" style="display: flex; gap: 8px; overflow-x: auto; padding: 4px 2px; scrollbar-width: thin; -ms-overflow-style: none;">
      								@foreach($gallery_images as $index => $img_url)
      									<div class="gallery-thumb-item {{ $index === 0 ? 'active' : '' }}" data-url="{{ $img_url }}" style="cursor: pointer; border-radius: 6px; border: 2px solid {{ $index === 0 ? '#6366f1' : '#e2e8f0' }}; padding: 2px; background: #fff; transition: all 0.2s; width: 55px; height: 55px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; overflow: hidden; box-sizing: border-box;">
      										<img src="{{ $img_url }}" style="max-width: 100%; max-height: 100%; object-fit: cover; border-radius: 3px;" alt="Product thumbnail {{ $index + 1 }}">
      									</div>
      								@endforeach
      							</div>
      						</div>
      					@endif
      				</div>
      			</div>
      			<div class="col-md-8">
      				@if($product->type == 'single' || $product->type == 'combo')
      					<div class="col-md-12">
      						<p class="lead">@lang('lang_v1.price'): &nbsp;&nbsp;&nbsp;<span class="display_currency" data-currency_symbol="true" data-show_code="true">{{ $product->variations->first()->sell_price_inc_tax }}</span></p><br>
      					</div>
      				@endif
      				<div class="col-md-12">
	      				<table class="table no-border table-slim">
	      					<tr>
	      						<th>@lang('product.sku'):</th>
	      						<td>{{$product->sku }}</td>
	      					</tr>
	      					<tr>
	      						<th>@lang('product.category'):</th>
	      						<td>{{$product->category->name ?? '--' }}</td>
	      					</tr>
	      					<tr>
	      						<th>@lang('product.sub_category'):</th>
	      						<td>{{$product->sub_category->name ?? '--' }}</td>
	      					</tr>
	      					<tr>
	      						<th>@lang('product.brand'):</th>
	      						<td>{{$product->brand->name ?? '--' }}</td>
	      					</tr>
	      					@php 
	    						$custom_labels = json_decode(session('business.custom_labels'), true);
							@endphp
							@if(!empty($product->product_custom_field1))
								<tr>
	      							<th>{{ $custom_labels['product']['custom_field_1'] ?? __('lang_v1.product_custom_field1') }}: </th>
									<td>{{$product->product_custom_field1 }}</td>
								</tr>
							@endif

							@if(!empty($product->product_custom_field2))
								<tr>
		      						<th>{{ $custom_labels['product']['custom_field_2'] ?? __('lang_v1.product_custom_field2') }}: </th>
									<td>{{$product->product_custom_field2 }}</td>
								</tr>
							@endif

							@if(!empty($product->product_custom_field3))
								<tr>
	      							<th>{{ $custom_labels['product']['custom_field_3'] ?? __('lang_v1.product_custom_field3') }}: </th>
									<td>{{$product->product_custom_field3 }}</td>
								</tr>
							@endif

							@if(!empty($product->product_custom_field4))
								<tr>
	      							<th>{{ $custom_labels['product']['custom_field_4'] ?? __('lang_v1.product_custom_field4') }}: </th>
									<td>{{$product->product_custom_field4 }}</td>
								</tr>
							@endif
	      					<tr>
	      						<td colspan="2"><br><br>{!! $product->product_description !!}</td>
	      					</tr>
							@if (($product->stock < 0 || empty($product->stock)) && $product->enable_stock)
								<tr>
									<td colspan="2"><small class="text-muted">@lang('productcatalogue::lang.out_of_stock') {{ $product->stock }}</small></td>
								</tr>
							@endif
							
	      				</table>
      				</div>
	      		</div>
      		</div>
      		@if($product->type == 'variable')
      			@include('productcatalogue::catalogue.partials.variable_product_details')
      		@elseif($product->type == 'combo')
      			@include('productcatalogue::catalogue.partials.combo_product_details')
      		@endif
      	</div>
      	<div class="modal-footer">
	      	<button type="button" class="tw-dw-btn tw-dw-btn-neutral tw-text-white no-print" data-dismiss="modal">@lang( 'messages.close' )</button>
	    </div>
	</div>
</div>

<style>
    @keyframes galleryFadeIn {
        from {
            opacity: 0.3;
            transform: scale(0.98);
        }
        to {
            opacity: 1;
            transform: scale(1);
        }
    }
    .gallery-fade-in {
        animation: galleryFadeIn 0.35s ease-out forwards;
    }
    .gallery-thumb-item:hover {
        border-color: #818cf8 !important;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(99, 102, 241, 0.15);
    }
    .gallery-thumb-item.active {
        border-color: #6366f1 !important;
        box-shadow: 0 4px 8px rgba(99, 102, 241, 0.25);
    }
    .btn-gallery-nav {
        opacity: 0.7;
    }
    .btn-gallery-nav:hover {
        opacity: 1;
        background: #ffffff !important;
        color: #6366f1 !important;
        transform: scale(1.08);
        box-shadow: 0 6px 16px rgba(0,0,0,0.12) !important;
    }
    .btn-gallery-nav:active {
        transform: scale(0.95);
    }
    .gallery-thumbnails-grid::-webkit-scrollbar {
        height: 5px;
    }
    .gallery-thumbnails-grid::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 4px;
    }
    .gallery-thumbnails-grid::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 4px;
    }
    .gallery-thumbnails-grid::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }
</style>

<script>
    $(document).ready(function() {
        var container = $('#gallery-main-img').closest('.product-gallery-container');
        var thumbs = container.find('.gallery-thumb-item');
        var mainImg = container.find('#gallery-main-img');
        var currentIndex = 0;
        var slideshowInterval = null;

        function updateMainImage(index) {
            if (index < 0 || index >= thumbs.length) return;
            currentIndex = index;
            var newUrl = $(thumbs[currentIndex]).attr('data-url');
            
            mainImg.removeClass('gallery-fade-in');
            
            // Force browser reflow to restart CSS keyframe animation
            void mainImg[0].offsetWidth;
            
            mainImg.attr('src', newUrl);
            mainImg.addClass('gallery-fade-in');
            
            thumbs.removeClass('active').css('border-color', '#e2e8f0');
            $(thumbs[currentIndex]).addClass('active').css('border-color', '#6366f1');

            // Scroll thumbnail into view
            var grid = container.find('.gallery-thumbnails-grid');
            if (grid.length > 0) {
                var activeThumb = $(thumbs[currentIndex]);
                var gridWidth = grid.width();
                var activeLeft = activeThumb.position().left;
                var activeWidth = activeThumb.outerWidth();
                
                if (activeLeft < 0 || activeLeft + activeWidth > gridWidth) {
                    grid.scrollLeft(grid.scrollLeft() + activeLeft - 10);
                }
            }
        }

        function startSlideshow() {
            if (thumbs.length <= 1) return;
            stopSlideshow();
            slideshowInterval = setInterval(function() {
                var nextIdx = currentIndex + 1;
                if (nextIdx >= thumbs.length) {
                    nextIdx = 0;
                }
                updateMainImage(nextIdx);
            }, 3000);
        }

        function stopSlideshow() {
            if (slideshowInterval) {
                clearInterval(slideshowInterval);
            }
        }

        // Initialize active state and start slideshow
        updateMainImage(currentIndex);
        startSlideshow();

        // Pause slideshow on hover
        container.hover(
            function() {
                stopSlideshow();
            },
            function() {
                startSlideshow();
            }
        );

        thumbs.on('click', function() {
            var idx = thumbs.index(this);
            updateMainImage(idx);
            startSlideshow();
        });

        container.find('.btn-gallery-nav.prev').on('click', function(e) {
            e.stopPropagation();
            var newIdx = currentIndex - 1;
            if (newIdx < 0) {
                newIdx = thumbs.length - 1;
            }
            updateMainImage(newIdx);
            startSlideshow();
        });

        container.find('.btn-gallery-nav.next').on('click', function(e) {
            e.stopPropagation();
            var newIdx = currentIndex + 1;
            if (newIdx >= thumbs.length) {
                newIdx = 0;
            }
            updateMainImage(newIdx);
            startSlideshow();
        });

        // Clean up interval when modal is closed
        container.closest('.modal').on('hidden.bs.modal', function () {
            stopSlideshow();
        });
    });
</script>

