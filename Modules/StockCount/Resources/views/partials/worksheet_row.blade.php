<tr id="line_{{ $line->id }}">
    <td>
        <strong>{{ $line->product->name ?? '' }}</strong> ({{ $line->variation->sub_sku ?? '' }})
        @if(!empty($line->variation->name) && $line->variation->name !== 'DUMMY')
            <br><span class="text-muted">{{ $line->variation->name }}</span>
        @endif
    </td>
    @if(!$session->blind_count)
        <td id="book_qty_{{ $line->id }}">{{ (float)$line->book_quantity }}</td>
        <td>
            @php
                $diff = (float)$line->counted_quantity - (float)$line->book_quantity;
                $type = $diff >= 0 ? '+' : '-';
                $qty = abs($diff);
            @endphp
            <select class="form-control select-type" id="type_{{ $line->id }}" data-id="{{ $line->id }}">
                <option value="+" {{ $type == '+' ? 'selected' : '' }}>+</option>
                <option value="-" {{ $type == '-' ? 'selected' : '' }}>-</option>
            </select>
        </td>
        <td>
            <div class="input-group">
                <span class="input-group-btn">
                    <button type="button" class="btn btn-default btn-flat btn-qty" data-action="minus" data-id="{{ $line->id }}"><i class="fa fa-minus"></i></button>
                </span>
                <input type="number" class="form-control text-center input-adjust-qty" id="qty_{{ $line->id }}" value="{{ (float)$qty }}" data-id="{{ $line->id }}" step="any" min="0">
                <span class="input-group-btn">
                    <button type="button" class="btn btn-default btn-flat btn-qty" data-action="plus" data-id="{{ $line->id }}"><i class="fa fa-plus"></i></button>
                </span>
            </div>
        </td>
        <td>{{ $line->product->unit->short_name ?? '' }}</td>
    @endif
    <td>
        <input type="number" class="form-control text-center input-new-qoh" id="new_qoh_{{ $line->id }}" value="{{ (float)$line->counted_quantity }}" @if(!$session->blind_count) readonly @endif data-book-qty="{{ (float)$line->book_quantity }}" data-id="{{ $line->id }}">
    </td>
    <td>{{ $line->product->unit->short_name ?? '' }}</td>
    <td>
        <input type="text" class="form-control input-note" id="note_{{ $line->id }}" value="{{ $line->note }}" data-id="{{ $line->id }}" placeholder="Add note...">
    </td>
</tr>
