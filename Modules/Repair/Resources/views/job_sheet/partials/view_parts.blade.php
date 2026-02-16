<style>
    /* --- General Modal Styling --- */
    .custom-modal-header {
        background-color: #ffffff;
        color: #000000;
        border-bottom: 2px solid #f0f0f0;
        padding: 15px 20px;
        text-align: center;
    }

    .custom-modal-header .modal-title {
        font-size: 18px;
        font-weight: 700;
        margin: 0;
    }

    .custom-modal-header .close {
        position: absolute;
        right: 20px;
        top: 15px;
        opacity: 0.5;
        font-size: 24px;
    }

    .custom-modal-body {
        padding: 20px;
    }

    .view-part-title {
        font-size: 16px;
        font-weight: 600;
        margin-bottom: 15px;
        color: #444;
    }

    /* --- Parts Table Styling --- */
    .parts-table {
        width: 100%;
        border-collapse: collapse;
    }

    .parts-table th,
    .parts-table td {
        padding: 12px 0;
    }

    .parts-table th {
        font-weight: 600;
        color: #888;
        text-align: left;
        border-bottom: 1px solid #ddd;
    }

    .parts-table tr:not(:last-child) td {
        border-bottom: 1px solid #f5f5f5;
    }

    .parts-table .part-name {
        font-weight: 500;
        color: #333;
    }

    .parts-table .qty-text {
        font-size: 13px;
        color: #666;
    }

    /* --- SELECT ELEMENT --- */
    .status-select-wrapper {
        position: relative;
        display: inline-block;
        border-radius: 4px;
        overflow: hidden;
        height: 32px;
        min-width: 130px;
        background: #ffffff !important;
        border: 2px solid #dcdcdc;
        transition: border-color 0.2s;
    }

    .status-select {
        appearance: none;
        background: white !important;
        border: none;
        width: 100%;
        height: 100%;
        padding: 6px 22px 6px 10px;
        font-weight: 600;
        cursor: pointer;
    }

    /* Custom Arrow */
    .status-select-wrapper::after {
        content: '▼';
        position: absolute;
        top: 50%;
        right: 6px;
        transform: translateY(-50%);
        font-size: 9px;
        pointer-events: none;
        color: var(--arrow-color, #333);
    }

    /* Footer */
    .custom-modal-footer {
        padding: 15px 20px;
        text-align: right;
        border-top: 1px solid #eee;
    }

    .btn-close {
        background-color: #fff;
        color: #6c757d;
        border: 1px solid #ced4da;
        padding: 8px 15px;
        border-radius: 4px;
        font-weight: 600;
        cursor: pointer;
    }

    .btn-save-changes {
        background-color: #28a745;
        color: #fff;
        border: none;
        padding: 8px 15px;
        border-radius: 4px;
        font-weight: 600;
        margin-left: 10px;
        cursor: pointer;
    }

    .note-wrapper {
        display: none;
    }
</style>
@php
    $hasStatus = collect($parts)->contains(function ($part) {
        return !blank($part['status']);
    });
@endphp
<div class="modal-dialog" role="document" style="width: 50%">
    <div class="modal-content">
        <div class="custom-modal-header">
            <button type="button" class="close" data-dismiss="modal">
                <span aria-hidden="true">&times;</span>
            </button>
            <h4 class="modal-title">Manage Parts Status: #2025/011</h4>
        </div>
        {!! Form::open([
            'url' => action([\Modules\Repair\Http\Controllers\JobSheetController::class, 'updatePartsStatus'], $job_sheet->id),
            'method' => 'post',
            // 'id' => 'add_part_form',
        ]) !!}
        <div class="custom-modal-body">



            <div class="view-part-title">View part</div>

            <table class="parts-table">
                <thead>
                    <tr>
                        <th style="width: 40%;">PART</th>
                        <th style="width: 15%;">QTY</th>
                        @if ($hasStatus)
                            <th style="width: 25%;">STATUS/ACTION</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach ($parts as $index => $part)
                        <tr>
                            <td class="part-name">{{ $part['variation_name'] }}</td>

                            <td>
                                {{ $part['quantity'] }}
                                <span class="qty-text">{{ $part['unit'] }}</span>
                            </td>
                            @if ($part['status'] != null)
                                <td>
                                    <div class="status-select-wrapper" data-part-id="part-1" style="width: 100%">
                                        <select class="status-select" name="parts[{{ $part['variation_id'] }}][status]">

                                            <option value="confirm" @cannot('repair.confirm') disabled @endcannot
                                                @selected($part['status'] == 'confirm')>Confirm</option>

                                            <option value="request" @selected($part['status'] == 'request')>Request</option>

                                            <option value="reject" @cannot('repair.reject_and_note') disabled @endcannot
                                                @selected($part['status'] == 'reject')>Reject</option>

                                        </select>
                                    </div>
                                    
                                    @if (auth()->user()->can('repair.reject_and_note') || $part['status'] === 'reject')
                                    <div class="note-wrapper">
                                            <span>Note:</span>
                                            <input type="text" class="form-control" @cannot('repair.reject_and_note') disabled @endcannot
                                                name="parts[{{ $part['variation_id'] }}][note]"
                                                value="{{ $part['note'] }}">
                                        </div>
                                    @endif

                                </td>
                            @endif

                        </tr>
                    @endforeach

                </tbody>
            </table>

        </div>


        <div class="custom-modal-footer">
            <button type="button" class="btn-close" data-dismiss="modal">Close</button>
            @canany(['repair.confirm', 'repair.reject_and_note'])
                @if ($hasStatus)
                    <button type="submit" class="btn-save-changes" id="saveChangesBtn">Save Changes</button>
                @endif
            @endcanany
        </div>
        {!! Form::close() !!}

    </div>
</div>
<script>
document.querySelectorAll('.status-select').forEach(select => {

    function toggleNote() {
        const wrapper = select.closest('td');
        if (!wrapper) return;

        const noteDiv = wrapper.querySelector('.note-wrapper');
        if (!noteDiv) return; // ✅ prevent crash

        if (select.value === 'reject') {
            noteDiv.style.display = 'block';
        } else {
            noteDiv.style.display = 'none';

            const input = noteDiv.querySelector('input');
            if (input) input.value = '';
        }
    }

    select.addEventListener('change', toggleNote);
    toggleNote();
});

</script>
