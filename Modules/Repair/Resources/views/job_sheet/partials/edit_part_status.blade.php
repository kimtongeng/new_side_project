<div class="modal-dialog" role="document">
    <div class="modal-content">
        {!! Form::open([
            'url' => action(
                [\Modules\Repair\Http\Controllers\JobSheetController::class, 'updatePartStatus'],
                ['part_key' => $part['part_key'], 'job_sheet_id' => $job_sheet->id],
            ),
            'method' => 'put',
            'id' => 'update_status_form',
        ]) !!}
        <div class="modal-header">
            <button type="button" class="close" aria-label="Close" onclick="$('#edit_part_status_modal').modal('hide')">
                <span aria-hidden="true">&times;</span>
            </button>
            <h4 class="modal-title">Edit Part Status</h4>
        </div>

        <div class="modal-body">
            <div class="row">
                <div class="col-md-12">
                    <strong>@lang('repair::lang.job_sheet_no'):</strong>
                    <span id="job_sheet_no">{{ $job_sheet->job_sheet_no }}</span>
                </div>
                <div class="col-md-12">
                    <strong>Part:</strong>
                    <span id="variation_name">{!! $part['variation_name'] !!}</span>
                </div>
            </div>

            <div class="row mt-15">
                <div class="col-md-12">
                    <div class="form-group">
                        {!! Form::label('Status :*') !!}
                        @php
                            $isAdmin = auth()->user()->can('superadmin');
                            $canConfirm = $isAdmin || auth()->user()->can('repair.confirm');
                            $canReject = $isAdmin || auth()->user()->can('repair.reject_and_note');
                            $current = $part['status'] ?? 'requested';
                        @endphp

                        {!! Form::select(
                            'status_id',
                            [
                                'confirmed' => 'Confirm',
                                'requested' => 'Request',
                                'rejected'  => 'Reject',
                            ],
                            $current,
                            [
                                'class'       => 'form-control select2',
                                'required'    => 'required',
                                'style'       => 'width:100%',
                                'placeholder' => __('messages.please_select'),
                                'id'          => 'status_id_modal',
                            ],
                        ) !!}

                        <script>
                            $(document).ready(function() {
                                @if (!$canConfirm)
                                    $('#status_id_modal option[value="confirmed"]').prop('disabled', true);
                                @endif
                                @if (!$canReject)
                                    $('#status_id_modal option[value="rejected"]').prop('disabled', true);
                                @endif
                                @if (!$isAdmin)
                                    $('#status_id_modal option[value="requested"]').prop('disabled', true);
                                @endif
                            });
                        </script>

                        <div id="note_container" style="display:none; margin-top:10px;">
                            {!! Form::label('Note :') !!}
                            {!! Form::textarea('Note', $part['note'] ?? null, [
                                'class'       => 'form-control',
                                'placeholder' => 'Note',
                                'rows'        => 4,
                                'id'          => 'note',
                            ]) !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <button type="button" class="tw-dw-btn tw-dw-btn-neutral tw-text-white"
                onclick="$('#edit_part_status_modal').modal('hide')">
                @lang('messages.close')
            </button>
            <button type="submit"
                class="tw-dw-btn tw-dw-btn-primary tw-text-white ladda-button update_status_button">
                @lang('messages.update')
            </button>
        </div>

        {!! Form::close() !!}
    </div>
</div>

<script>
    $('#status_id_modal').trigger('change');
</script>