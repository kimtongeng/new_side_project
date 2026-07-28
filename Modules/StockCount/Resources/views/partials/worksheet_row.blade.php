@php
    $common_settings = session('business.common_settings', []);
    $setting_show_expected = isset($common_settings['stock_count_show_expected_qty']) ? (bool)$common_settings['stock_count_show_expected_qty'] : true;
    $hide_expected_qty = $session->blind_count || !$setting_show_expected;

    $allow_recount = isset($common_settings['stock_count_allow_recount']) ? $common_settings['stock_count_allow_recount'] : true;
    $is_recount_disabled = !$allow_recount && $line->counted_by !== null;
@endphp
<tr id="line_{{ $line->id }}" class="worksheet-row @if($line->counted_by !== null) is-counted @endif">

    <td>
        <strong>{{ $line->product->name ?? '' }}</strong> ({{ $line->variation->sub_sku ?? '' }})
        @if(!empty($line->variation->name) && $line->variation->name !== 'DUMMY')
            <br><span class="text-muted">{{ $line->variation->name }}</span>
        @endif
    </td>
    @if(!$hide_expected_qty)
        <td id="book_qty_{{ $line->id }}">{{ (float) $line->book_quantity }}</td>
        <td>
            @php
                if ($line->counted_by !== null) {
                    $diff = (float) $line->counted_quantity - (float) $line->book_quantity;
                    $type = $diff >= 0 ? '+' : '-';
                    $qty = abs($diff);
                    $new_qoh = (float) $line->counted_quantity;
                } else {
                    $type = '+';
                    $qty = 0;
                    $new_qoh = (float) $line->book_quantity;
                }
            @endphp
            <select class="form-control select-type" id="type_{{ $line->id }}" data-id="{{ $line->id }}"
                @if($is_recount_disabled) disabled @endif>
                <option value="+" {{ $type == '+' ? 'selected' : '' }}>+</option>
                <option value="-" {{ $type == '-' ? 'selected' : '' }}>-</option>
            </select>
        </td>
        <td>
            <div class="input-group">
                <span class="input-group-btn">
                    <button type="button" class="btn btn-default btn-flat btn-qty" data-action="minus"
                        data-id="{{ $line->id }}" @if($is_recount_disabled) disabled @endif><i
                            class="fa fa-minus"></i></button>
                </span>
                <input type="number" class="form-control text-center input-adjust-qty" id="qty_{{ $line->id }}"
                    value="{{ (float) $qty }}" data-id="{{ $line->id }}" step="any" min="0" @if($is_recount_disabled)
                    readonly @endif>
                <span class="input-group-btn">
                    <button type="button" class="btn btn-default btn-flat btn-qty" data-action="plus"
                        data-id="{{ $line->id }}" @if($is_recount_disabled) disabled @endif><i
                            class="fa fa-plus"></i></button>
                </span>
            </div>
        </td>
        <td>{{ $line->product->unit->short_name ?? '' }}</td>
    @else
        @php
            $new_qoh = (float) $line->counted_quantity;
        @endphp
    @endif
    <td>
        <input type="number" class="form-control text-center input-new-qoh" id="new_qoh_{{ $line->id }}"
            value="{{ (float) $new_qoh }}" step="any" @if($is_recount_disabled) readonly @endif
            data-book-qty="{{ (float) $line->book_quantity }}" data-id="{{ $line->id }}">
    </td>
    <td>{{ $line->product->unit->short_name ?? '' }}</td>
    <td>
        <input type="text" class="form-control input-note" id="note_{{ $line->id }}" value="{{ $line->note }}"
            data-id="{{ $line->id }}" placeholder="Add note..." @if($is_recount_disabled) readonly @endif>
    </td>
    <td class="text-center btn_toggle_line_status" id="status_cell_{{ $line->id }}" data-id="{{ $line->id }}" data-status="{{ $line->counted_by !== null ? 'counted' : 'pending' }}" style="cursor: pointer;" title="Click to change status">
        @if($line->counted_by !== null)
            <span class="label label-success">Counted</span>
        @else
            <span class="label label-warning">Pending</span>
        @endif
    </td>
</tr>