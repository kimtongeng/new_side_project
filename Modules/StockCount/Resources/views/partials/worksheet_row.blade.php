<tr id="line_{{ $line->id }}">
    <td>
        <strong>{{ $line->product->name ?? '' }}</strong><br>
        <span class="text-muted">{{ $line->variation->name !== 'DUMMY' ? $line->variation->name : '' }}</span>
    </td>
    <td>{{ $line->variation->sub_sku ?? '' }}</td>
    @if(!$session->blind_count)
        <td>{{ number_format($line->book_quantity, 2) }}</td>
    @endif
    <td style="width: 150px;">
        <div class="input-group">
            <span class="input-group-btn">
                <button type="button" class="btn btn-default btn-flat btn-qty" data-action="minus" data-id="{{ $line->id }}"><i class="fa fa-minus"></i></button>
            </span>
            <input type="number" class="form-control text-center input-qty" id="qty_{{ $line->id }}" value="{{ (float)$line->counted_quantity }}" data-id="{{ $line->id }}" step="any" min="0">
            <span class="input-group-btn">
                <button type="button" class="btn btn-default btn-flat btn-qty" data-action="plus" data-id="{{ $line->id }}"><i class="fa fa-plus"></i></button>
            </span>
        </div>
    </td>
    <td>
        <input type="text" class="form-control input-note" id="note_{{ $line->id }}" value="{{ $line->note }}" data-id="{{ $line->id }}" placeholder="Add note...">
    </td>
</tr>
