@php
    $form_key = isset($part_key) ? $part_key : $variation_id;
    $isAdmin = auth()->user()->can('superadmin');
    $canDelete = auth()->user()->can('repair.delete_part');
    $canConfirm = auth()->user()->can('repair.confirm');
    $canEdit = auth()->user()->can('repair.edit_part');
    $autoStatus = isset($status) ? $status : ($isAdmin ? 'confirmed' : 'requested');

    // Can edit if:
    // - has repair.edit_part AND status is NOT confirmed
    // - OR has repair.confirm AND status IS confirmed
    // - OR is admin
    $can_not_edit = !$isAdmin && !(
        ($canEdit && $autoStatus !== 'confirmed') ||
        ($canConfirm && $autoStatus === 'confirmed')
    );

    $statusStyle = match ($autoStatus) {
        'requested' => 'background-color: #17a2b8; color: #fff;',
        'confirmed' => 'background-color: #28a745; color: #fff;',
        'rejected'  => 'background-color: #dc3545; color: #fff;',
        default     => 'background-color: #6c757d; color: #fff;',
    };
    $statusText = match ($autoStatus) {
        'requested' => 'Requested',
        'confirmed' => 'Confirmed',
        'rejected'  => 'Rejected',
        default     => ucfirst($autoStatus),
    };
@endphp

<tr class="product_row" data-variation-id="{{ $form_key }}">
    <td style="min-width: 200px; vertical-align: middle;">
        <div style="display: flex; align-items: center; gap: 10px;">
            <div style="width: 50px; height: 50px; border: 1px solid #dee2e6; border-radius: 4px; overflow: hidden; background: #fff; display: inline-flex; align-items: center; justify-content: center;">
                <img src="{{ $product_image }}" alt="{{ $variation_name }}" style="width: 100%; height: 100%; object-fit: contain; display: block;">
            </div>
            <div style="min-width: 0;">
                <div style="font-weight: 600; font-size: 14px; line-height: 1.2;">{{ $variation_name }}</div>
                @if(isset($current_stock))
                    <div style="font-size: 12px; color: #6c757d; margin-top: 4px;">
                        {{ @format_quantity($current_stock) }} {{ $unit }} in stock
                    </div>
                @endif
            </div>
        </div>
    </td>

    <td>
        <input type="hidden" name="parts[{{ $form_key }}][variation_id]" value="{{ $variation_id }}">
        <div class="input-group input-number" style="max-width: 250px;">
            <span class="input-group-btn"><button type="button" @if ($can_not_edit) disabled @endif class="btn btn-default btn-flat quantity-down"><i class="fa fa-minus text-danger"></i></button></span>
            <input type="text"
                data-min="1"
                data-step="1"
                data-unit="{{ $unit }}"
                @if(isset($current_stock) && isset($allow_overselling) && !$allow_overselling)
                    data-rule-max-value="{{ $current_stock }}"
                    data-msg-max-value="{{ __('validation.custom-messages.quantity_not_available', ['qty' => $current_stock, 'unit' => $unit]) }}"
                    data-qty_available="{{ $current_stock }}"
                    data-msg_max_default="{{ __('validation.custom-messages.quantity_not_available', ['qty' => $current_stock, 'unit' => $unit]) }}"
                @endif
                class="form-control input_number input_quantity {{ isset($can_not_edit) && $can_not_edit ? 'readonly-input' : '' }}"
                value="{{ @format_quantity($quantity) }}"
                name="parts[{{ $form_key }}][quantity]"
                {{ isset($can_not_edit) && $can_not_edit ? 'readonly' : '' }}>
            <span class="input-group-btn"><button type="button" @if ($can_not_edit) disabled @endif class="btn btn-default btn-flat quantity-up"><i class="fa fa-plus text-success"></i></button></span>
        </div>
        <div class="qty-available-feedback" style="display:none; margin-top: 4px; font-size: 12px; color: #dc3545;"></div>
        <div style="display: inline-flex; align-items: center; gap: 8px; margin-top: 6px; font-size: 13px; color: #495057;">
            <span>{{ $unit }}</span>
            <span style="{{ $statusStyle }} padding: 4px 10px; border-radius: 4px; font-size: 12px; font-weight: 600;">
                {{ $statusText }}
            </span>
        </div>

        <input type="hidden" name="parts[{{ $form_key }}][user_id]" value="{{ isset($user_id) ? $user_id : null }}">
        <input type="hidden" name="parts[{{ $form_key }}][status]" value="{{ $autoStatus }}">
    </td>

    @if ($canDelete && isset($user_id))
        <td class="text-center">
            <i class="fas fa-times remove_product_row cursor-pointer" aria-hidden="true"></i>
        </td>
    @endif
</tr>

<style>
    .readonly-input {
        background: #e9ecef;
        cursor: not-allowed;
    }
</style>