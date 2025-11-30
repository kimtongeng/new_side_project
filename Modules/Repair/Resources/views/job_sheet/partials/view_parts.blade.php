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
</style>

<div class="modal-dialog" role="document">
    <div class="modal-content">
        <div class="custom-modal-header">
            <button type="button" class="close" data-dismiss="modal">
                <span aria-hidden="true">&times;</span>
            </button>
            <h4 class="modal-title">Manage Parts Status: #2025/011</h4>
        </div>

        <div class="custom-modal-body">
            {!! Form::open(['url' => action([\Modules\Repair\Http\Controllers\JobSheetController::class, 'updateStatus'], [$job_sheet->id]), 'method' => 'put', 'id' => 'update_status_form']) !!}



            <div class="view-part-title">View part</div>

            <table class="parts-table">
                <thead>
                    <tr>
                        <th style="width: 50%;">PART</th>
                        <th style="width: 25%;">QTY</th>
                        <th style="width: 25%;">STATUS/ACTION</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="part-name">iPhone 13 Screen (0001)</td>
                        <td>1 <span class="qty-text">(Pcs)</span></td>
                        <td>
                            <div class="status-select-wrapper" data-part-id="part-1">
                                <select class="status-select">
                                    <option value="Completed" selected>Completed</option>
                                    <option value="Pending">Pending</option>
                                    <option value="Rejected">Rejected</option>
                                </select>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="custom-modal-footer">
            <button type="button" class="btn-close" data-dismiss="modal">Close</button>
            <button type="button" class="btn-save-changes" id="saveChangesBtn">Save Changes</button>
        </div>
        {!! Form::close() !!}
    </div>
</div>
