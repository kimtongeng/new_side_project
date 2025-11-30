<tr class="product_row">
    <td>{{ $variation_name }}</td>

    <td>
        <input type="text" class="form-control input_number input_quantity" value="{{ @format_quantity($quantity) }}"
            name="parts[{{ $variation_id }}][quantity]">
        {{ $unit }}

        {{-- Hidden input for status --}}
        <input type="hidden" name="parts[{{ $variation_id }}][status]" value="{{ $status ?? 'request' }}">

        {{-- Status badge --}}
        @php
            $statusClass = match ($status ?? 'request') {
                'request' => 'bg-info text-dark',
                'complete' => 'bg-success text-white',
                'reject' => 'bg-danger text-white',
                default => 'bg-secondary text-white',
            };
            $statusText = ucfirst($status ?? 'Request');
        @endphp

        <span class="badge {{ $statusClass }} d-inline-block px-3 py-2">
            {{ $statusText }}
        </span>
    </td>

    <td class="text-center">
        <i class="fas fa-times remove_product_row cursor-pointer" aria-hidden="true"></i>
    </td>
</tr>
