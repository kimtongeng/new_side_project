<tr class="product_row" data-row_index="{{ $row_count }}">
    <td class="tw-w-[40%]">
        <input type="hidden" name="products[{{ $row_count }}][product_id]" class="form-control product_id" value="{{ $product->product_id }}">
        <input type="hidden" name="products[{{ $row_count }}][variation_id]" class="form-control variation_id" value="{{ $product->variation_id }}">
        <input type="hidden" name="products[{{ $row_count }}][enable_stock]" class="form-control enable_stock" value="{{ $product->enable_stock }}" @if ($product->enable_stock) data-before_sale_qty="{{ $product->qty_available }}" @endif>
        <input type="hidden" name="products[{{ $row_count }}][transaction_sell_lines_id]" class="form-control transaction_sell_lines_id" value="{{ $product->transaction_sell_lines_id ?? '' }}">
        <input type="hidden" class="is_service">
        <input type="hidden" name="products[{{ $row_count }}][product_type]" class="product_type" value="{{ $product->product_type }}">
        @if ($product->product_type == 'combo')
            <input type="hidden" name="products[{{ $row_count }}][combo_products]" class="combo_products" value="{{ json_encode($product->combo_products ?? []) }}">
        @endif

        <div class="tw-flex tw-items-center tw-gap-2">
            <span class="tw-font-bold tw-text-black tw-text-[16px] tw-mb-2 tw-flex tw-items-center tw-gap-2">
                @if (!empty($product->product_image))
                    <img src="{{ url('uploads/products/' . $product->product_image) }}" class="tw-w-[30px] tw-h-[30px] tw-rounded-full tw-object-cover" alt="{{ $product->product_name }}">
                @endif
                <span>
                    {{ $product->product_name }}
                    @if ($product->product_type == 'variable')
                        ({{ $product->variation_name }})
                    @endif
                    @if ($product->product_type == 'combo')
                        <i class="fa fa-info-circle text-info combo_product_text" data-toggle="tooltip" title="@lang('Loan::lang.combo_product')"></i>
                    @endif
                </span>
            </span>
            <i class="fa fa-trash remove_product_row tw-cursor-pointer tw-text-red-500 hover:tw-text-red-700" data-toggle="tooltip" title="@lang('Loan::lang.remove')" aria-hidden="true"></i>
        </div>

        @if (!empty($product->lot_numbers) && $product->enable_stock)
            <div class="form-group lot_number_div">
                <select name="products[{{ $row_count }}][lot_no_line_id]" class="form-control lot_number tw-w-full tw-mt-2 tw-text-[14px] tw-border tw-border-gray-300 tw-rounded-md">
                    <option value="">@lang('lang_v1.lot_n_expiry')</option>
                    @foreach ($product->lot_numbers as $lot_number)
                        <option value="{{ $lot_number->purchase_line_id }}"
                            data-quantity="{{ $lot_number->qty_available }}"
                            data-expiry="{{ !empty($lot_number->exp_date) ? \Carbon::createFromFormat('Y-m-d', $lot_number->exp_date)->format('m/d/Y') : '' }}"
                            @if (!empty($lot_number->purchase_line_id) && $product->lot_no_line_id == $lot_number->purchase_line_id) selected @endif>
                            {{ $lot_number->lot_number }} @if (!empty($lot_number->exp_date)) - {{ \Carbon::createFromFormat('Y-m-d', $lot_number->exp_date)->format('m/d/Y') }} @endif - {{ $lot_number->qty_formated }}
                        </option>
                    @endforeach
                </select>
            </div>
        @endif

        @if (!empty($warranties))
            <div class="form-group warranty_div">
                <select name="products[{{ $row_count }}][warranty_id]" class="form-control warranty tw-w-full tw-mt-2 tw-text-[14px] tw-border tw-border-gray-300 tw-rounded-md">
                    <option value="">@lang('lang_v1.no_warranty')</option>
                    @foreach ($warranties as $warranty_id => $warranty_name)
                        <option value="{{ $warranty_id }}"
                            @if (!empty($product->warranties) && in_array($warranty_id, $product->warranties->pluck('id')->toArray())) selected @endif>
                            {{ $warranty_name }}
                        </option>
                    @endforeach
                </select>
            </div>
        @endif

        @if (!empty($product->enable_sr_no))
            <div class="form-group serial_number_div tw-mt-2">
                <textarea name="products[{{ $row_count }}][serial_number]" class="form-control serial_number tw-w-full tw-text-[14px] tw-border tw-border-gray-300 tw-rounded-md" placeholder="@lang('lang_v1.serial_number')">{{ $product->serial_number ?? '' }}</textarea>
            </div>
        @endif

        @if ($product->product_type == 'variable' && !empty($product->product_ms))
            <div class="form-group modifier_div">
                <select name="products[{{ $row_count }}][modifier_sets][]" class="form-control modifier_sets tw-w-full tw-mt-2 tw-text-[14px] tw-border tw-border-gray-300 tw-rounded-md" multiple>
                    @foreach ($product->product_ms as $modifier_set)
                        <option value="{{ $modifier_set->id }}"
                            @if (!empty($product->modifiers_ids) && in_array($modifier_set->id, $product->modifiers_ids)) selected @endif>
                            {{ $modifier_set->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        @endif

        @if (!empty($waiters) && !empty($pos_settings['inline_service_staff']))
            <div class="form-group service_staff_div">
                <select name="products[{{ $row_count }}][res_service_staff_id]" class="form-control service_staff tw-w-full tw-mt-2 tw-text-[14px] tw-border tw-border-gray-300 tw-rounded-md">
                    <option value="">@lang('lang_v1.service_staff')</option>
                    @foreach ($waiters as $waiter_id => $waiter_name)
                        <option value="{{ $waiter_id }}"
                            @if (!empty($product->res_service_staff_id) && $product->res_service_staff_id == $waiter_id) selected @endif>
                            {{ $waiter_name }}
                        </option>
                    @endforeach
                </select>
            </div>
        @endif

        @if (!empty($sub_units))
            <div class="form-group sub_unit_div">
                <select name="products[{{ $row_count }}][sub_unit_id]" class="form-control sub_unit tw-w-full tw-mt-2 tw-text-[14px] tw-border tw-border-gray-300 tw-rounded-md">
                    <option value="">@lang('lang_v1.unit')</option>
                    @foreach ($sub_units as $sub_unit_id => $sub_unit)
                        <option value="{{ $sub_unit_id }}"
                            data-multiplier="{{ $sub_unit['multiplier'] }}"
                            @if (!empty($product->sub_unit_id) && $product->sub_unit_id == $sub_unit_id) selected @endif>
                            {{ $sub_unit['name'] }}
                        </option>
                    @endforeach
                </select>
            </div>
        @endif

        <div class="form-group sell_line_note_div tw-mt-2">
            <textarea name="products[{{ $row_count }}][sell_line_note]" class="form-control sell_line_note tw-w-full tw-text-[14px] tw-border tw-border-gray-300 tw-rounded-md" placeholder="@lang('lang_v1.sell_line_note')">{{ $product->sell_line_note ?? '' }}</textarea>
        </div>
    </td>
    <td class="tw-w-[15%] tw-align-middle">
        <div class="input-group tw-w-full tw-mt-2">
            <span class="input-group-btn">
                <button type="button" class="btn btn-default btn-flat qty_minus tw-bg-gray-200 tw-text-gray-700 hover:tw-bg-gray-300"><i class="fa fa-minus"></i></button>
            </span>
            <input type="text" name="products[{{ $row_count }}][quantity]" class="form-control input_number product_quantity tw-text-center tw-text-[14px] tw-border tw-border-gray-300 tw-rounded-md" value="{{ $product->quantity_ordered ?? $quantity }}" data-allow-overselling="@if (empty($pos_settings['allow_overselling'])) false @else true @endif">
            <span class="input-group-btn">
                <button type="button" class="btn btn-default btn-flat qty_plus tw-bg-gray-200 tw-text-gray-700 hover:tw-bg-gray-300"><i class="fa fa-plus"></i></button>
            </span>
        </div>
        @if (!empty($product->second_unit) && !empty($product->secondary_unit_quantity))
            <div class="tw-mt-2 tw-text-[14px]">
                <input type="text" name="products[{{ $row_count }}][secondary_unit_quantity]" class="form-control input_number secondary_unit_quantity tw-w-full tw-text-[14px] tw-border tw-border-gray-300 tw-rounded-md" value="{{ $product->secondary_unit_quantity }}">
                <span>{{ $product->second_unit }}</span>
            </div>
        @endif
        @if ($product->enable_stock)
            <div class="tw-mt-2 tw-text-[14px]">
                <span class="stock_qty_label">@lang('lang_v1.available'): </span>
                <span class="stock_qty">{{ $product->formatted_qty_available }}</span>
                <span class="stock_unit">{{ $product->unit }}</span>
            </div>
        @endif
    </td>
    <td class="tw-w-[15%] tw-align-middle">
        <input type="text" name="products[{{ $row_count }}][unit_price]" class="form-control input_number unit_price tw-w-full tw-mt-2 tw-text-[14px] tw-border tw-border-gray-300 tw-rounded-md" value="{{ $product->default_sell_price }}" @if (!$edit_price) readonly @endif>
        @if (!empty($last_sell_line))
            <div class="tw-mt-2 tw-text-[12px] tw-text-gray-600">
                @lang('lang_v1.last_sale'): {{ $last_sell_line->unit_price }}
            </div>
        @endif
    </td>
    <td class="tw-w-[15%] tw-align-middle">
        <input type="text" name="products[{{ $row_count }}][line_discount_amount]" class="form-control input_number line_discount_amount tw-w-full tw-mt-2 tw-text-[14px] tw-border tw-border-gray-300 tw-rounded-md" value="{{ $product->line_discount_amount ?? 0 }}" @if (!$edit_discount) readonly @endif>
        <select name="products[{{ $row_count }}][line_discount_type]" class="form-control line_discount_type tw-w-full tw-mt-2 tw-text-[14px] tw-border tw-border-gray-300 tw-rounded-md" @if (!$edit_discount) readonly @endif>
            <option value="fixed" @if (($product->line_discount_type ?? 'fixed') == 'fixed') selected @endif>@lang('lang_v1.fixed')</option>
            <option value="percentage" @if (($product->line_discount_type ?? 'fixed') == 'percentage') selected @endif>@lang('lang_v1.percentage')</option>
        </select>
    </td>
    <td class="tw-w-[15%] tw-align-middle">
        <input type="text" name="products[{{ $row_count }}][item_tax]" class="form-control input_number item_tax tw-w-full tw-mt-2 tw-text-[14px] tw-border tw-border-gray-300 tw-rounded-md" value="{{ $product->item_tax ?? 0 }}" readonly>
        <select name="products[{{ $row_count }}][tax_id]" class="form-control tax_id tw-w-full tw-mt-2 tw-text-[14px] tw-border tw-border-gray-300 tw-rounded-md">
            <option value="">@lang('lang_v1.no_tax')</option>
            @foreach ($tax_dropdown['tax_rates'] as $tax_id => $tax_name)
                <option value="{{ $tax_id }}"
                    data-tax_amount="{{ $tax_dropdown['tax_attributes'][$tax_id]['amount'] }}"
                    @if ($product->tax_id == $tax_id) selected @endif>
                    {{ $tax_name }}
                </option>
            @endforeach
        </select>
    </td>
    <td class="tw-w-[15%] tw-align-middle">
        <input type="text" class="form-control input_number line_total tw-w-full tw-mt-2 tw-text-[14px] tw-border tw-border-gray-300 tw-rounded-md" value="0" readonly>
    </td>
</tr>