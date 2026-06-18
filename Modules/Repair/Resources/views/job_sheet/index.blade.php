@extends('layouts.app')

@section('title', __('repair::lang.job_sheets'))

@section('content')
@include('repair::layouts.nav')
<section class="content-header no-print">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">
        @lang('repair::lang.job_sheets')
    </h1>
</section>
<section class="content no-print">
    @component('components.filters', ['title' => __('report.filters'), 'closed' => false])
    <div class="col-md-3">
        <div class="form-group">
            {!! Form::label('location_id', __('purchase.business_location') . ':') !!}
            {!! Form::select('location_id', $business_locations, null, [
    'class' => 'form-control select2',
    'style' => 'width:100%',
    'placeholder' => __('lang_v1.all'),
]) !!}
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            {!! Form::label('contact_id', __('role.customer') . ':') !!}
            {!! Form::select('contact_id', $customers, null, [
    'class' => 'form-control select2',
    'style' => 'width:100%',
    'placeholder' => __('lang_v1.all'),
]) !!}
        </div>
    </div>
    @if (in_array('service_staff', $enabled_modules) && !$is_user_service_staff)
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('technician', __('repair::lang.technician') . ':') !!}
                {!! Form::select('technician', $service_staffs, null, [
            'class' => 'form-control select2',
            'style' => 'width:100%',
            'placeholder' => __('lang_v1.all'),
        ]) !!}
            </div>
        </div>
    @endif
    <div class="col-md-3">
        <div class="form-group">
            {!! Form::label('status_id', __('sale.status') . ':') !!}
            {!! Form::select('status_id', $status_dropdown['statuses'], null, [
    'class' => 'form-control select2',
    'style' => 'width:100%',
    'placeholder' => __('lang_v1.all'),
]) !!}
        </div>
    </div>
    @endcomponent

    <div class="row">
        <div class="col-md-12">
            <div class="nav-tabs-custom">
                <ul class="nav nav-tabs">
                    <li class="active">
                        <a href="#pending_job_sheet_tab" data-toggle="tab" aria-expanded="true">
                            <i class="fas fa-exclamation-circle text-orange"></i>
                            @lang('repair::lang.pending')
                            @show_tooltip(__('repair::lang.common_pending_status_tooltip'))
                        </a>
                    </li>
                    <li>
                        <a href="#completed_job_sheet_tab" data-toggle="tab" aria-expanded="true">
                            <i class="fa fas fa-check-circle text-success"></i>
                            @lang('repair::lang.completed')
                            @show_tooltip(__('repair::lang.common_completed_status_tooltip'))
                        </a>
                    </li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active" id="pending_job_sheet_tab">
                        <div class="row">
                            <div class="col-md-12 mb-12">
                                <a type="button"
                                    class="tw-dw-btn tw-bg-gradient-to-r tw-from-indigo-600 tw-to-blue-500 tw-font-bold tw-text-white tw-border-none tw-rounded-full pull-right"
                                    href="{{ action([\Modules\Repair\Http\Controllers\JobSheetController::class, 'create']) }}"
                                    id="add_job_sheet">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round"
                                        class="icon icon-tabler icons-tabler-outline icon-tabler-plus">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <path d="M12 5l0 14" />
                                        <path d="M5 12l14 0" />
                                    </svg> @lang('messages.add')
                                </a>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="pending_job_sheets_table">
                                <thead>
                                    <tr>
                                        <th>@lang('messages.action')</th>
                                        <th>@lang('repair::lang.service_type')</th>
                                        <th>@lang('lang_v1.due_date')</th>
                                        <th>@lang('repair::lang.job_sheet_no')</th>
                                        <th>@lang('sale.invoice_no')</th>
                                        <th>@lang('sale.status')</th>
                                        @if (in_array('service_staff', $enabled_modules))
                                            <th>@lang('repair::lang.technician')</th>
                                        @endif
                                        <th>@lang('role.customer')</th>
                                        <th>@lang('business.location')</th>
                                        <th>@lang('product.brand')</th>
                                        <th>@lang('repair::lang.device')</th>
                                        <th>@lang('repair::lang.device_model')</th>
                                        <th>@lang('repair::lang.serial_no')</th>
                                        <th>@lang('repair::lang.estimated_cost')</th>
                                        @if (!empty($repair_settings['job_sheet_custom_field_1']))
                                            <th>{{ $repair_settings['job_sheet_custom_field_1'] }}</th>
                                        @endif
                                        @if (!empty($repair_settings['job_sheet_custom_field_2']))
                                            <th>{{ $repair_settings['job_sheet_custom_field_2'] }}</th>
                                        @endif
                                        @if (!empty($repair_settings['job_sheet_custom_field_3']))
                                            <th>{{ $repair_settings['job_sheet_custom_field_3'] }}</th>
                                        @endif
                                        @if (!empty($repair_settings['job_sheet_custom_field_4']))
                                            <th>{{ $repair_settings['job_sheet_custom_field_4'] }}</th>
                                        @endif
                                        @if (!empty($repair_settings['job_sheet_custom_field_5']))
                                            <th>{{ $repair_settings['job_sheet_custom_field_5'] }}</th>
                                        @endif
                                        <th>@lang('lang_v1.added_by')</th>
                                        <th>@lang('lang_v1.created_at')</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                    <div class="tab-pane" id="completed_job_sheet_tab">
                        <div class="row">
                            <div class="col-md-12 mb-12">
                                <a type="button"
                                    class="tw-dw-btn tw-dw-btn-sm tw-bg-gradient-to-r tw-from-indigo-600 tw-to-blue-500 tw-font-bold tw-text-white tw-border-none tw-rounded-full pull-right"
                                    href="{{ action([\Modules\Repair\Http\Controllers\JobSheetController::class, 'create']) }}"
                                    id="add_job_sheet">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round"
                                        class="icon icon-tabler icons-tabler-outline icon-tabler-plus">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <path d="M12 5l0 14" />
                                        <path d="M5 12l14 0" />
                                    </svg> @lang('messages.add')
                                </a>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="completed_job_sheets_table">
                                <thead>
                                    <tr>
                                        <th>@lang('messages.action')</th>
                                        <th>@lang('repair::lang.service_type')</th>
                                        <th>@lang('lang_v1.due_date')</th>
                                        <th>@lang('repair::lang.job_sheet_no')</th>
                                        <th>@lang('sale.invoice_no')</th>
                                        <th>@lang('sale.status')</th>
                                        @if (in_array('service_staff', $enabled_modules))
                                            <th>@lang('repair::lang.technician')</th>
                                        @endif
                                        <th>@lang('role.customer')</th>
                                        <th>@lang('business.location')</th>
                                        <th>@lang('product.brand')</th>
                                        <th>@lang('repair::lang.device')</th>
                                        <th>@lang('repair::lang.device_model')</th>
                                        <th>@lang('repair::lang.serial_no')</th>
                                        <th>@lang('repair::lang.estimated_cost')</th>
                                        @if (!empty($repair_settings['job_sheet_custom_field_1']))
                                            <th>{{ $repair_settings['job_sheet_custom_field_1'] }}</th>
                                        @endif
                                        @if (!empty($repair_settings['job_sheet_custom_field_2']))
                                            <th>{{ $repair_settings['job_sheet_custom_field_2'] }}</th>
                                        @endif
                                        @if (!empty($repair_settings['job_sheet_custom_field_3']))
                                            <th>{{ $repair_settings['job_sheet_custom_field_3'] }}</th>
                                        @endif
                                        @if (!empty($repair_settings['job_sheet_custom_field_4']))
                                            <th>{{ $repair_settings['job_sheet_custom_field_4'] }}</th>
                                        @endif
                                        @if (!empty($repair_settings['job_sheet_custom_field_5']))
                                            <th>{{ $repair_settings['job_sheet_custom_field_5'] }}</th>
                                        @endif
                                        <th>@lang('lang_v1.added_by')</th>
                                        <th>@lang('lang_v1.created_at')</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="status_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    </div>
    <div class="modal fade" id="view_part_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    </div>

</section>
@stop

@section('javascript')
    <script type="text/javascript">
        $(document).ready(function () {

            // ═══════════════════════════════════════════
            // DATATABLES
            // ═══════════════════════════════════════════

            pending_job_sheets_datatable = $("#pending_job_sheets_table").DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '/repair/job-sheet',
                    "data": function (d) {
                        d.location_id = $('#location_id').val();
                        d.contact_id = $('#contact_id').val();
                        d.status_id = $('#status_id').val();
                        d.is_completed_status = 0;
                        @if (in_array('service_staff', $enabled_modules))
                            d.technician = $('#technician').val();
                        @endif
                            }
                },
                columnDefs: [{
                    targets: [0],
                    orderable: false,
                    searchable: false
                }],
                aaSorting: [
                    [2, 'asc']
                ],
                columns: [{
                    data: 'action',
                    name: 'action'
                },
                {
                    data: 'service_type',
                    name: 'service_type'
                },
                {
                    data: 'delivery_date',
                    name: 'delivery_date'
                },
                {
                    data: 'job_sheet_no',
                    name: 'job_sheet_no'
                },
                {
                    data: 'repair_no',
                    name: 'repair_no',
                    searchable: false
                },
                {
                    data: 'status',
                    name: 'rs.name'
                },
                    @if (in_array('service_staff', $enabled_modules))
                                                {
                            data: 'technecian',
                            name: 'technecian',
                            searchable: false
                        },
                    @endif{
                    data: 'customer',
                    name: 'contacts.name'
                },
                {
                    data: 'location',
                    name: 'bl.name'
                },
                {
                    data: 'brand',
                    name: 'b.name'
                },
                {
                    data: 'device',
                    name: 'device.name'
                },
                {
                    data: 'device_model',
                    name: 'rdm.name'
                },
                {
                    data: 'serial_no',
                    name: 'serial_no'
                },
                {
                    data: 'estimated_cost',
                    name: 'estimated_cost'
                },
                    @if (!empty($repair_settings['job_sheet_custom_field_1']))
                                                {
                            data: 'custom_field_1',
                            name: 'repair_job_sheets.custom_field_1'
                        },
                    @endif
                @if (!empty($repair_settings['job_sheet_custom_field_2']))
                                        {
                        data: 'custom_field_2',
                        name: 'repair_job_sheets.custom_field_2'
                    },
                @endif
                @if (!empty($repair_settings['job_sheet_custom_field_3']))
                                        {
                        data: 'custom_field_3',
                        name: 'repair_job_sheets.custom_field_3'
                    },
                @endif
                @if (!empty($repair_settings['job_sheet_custom_field_4']))
                                        {
                        data: 'custom_field_4',
                        name: 'repair_job_sheets.custom_field_4'
                    },
                @endif
                @if (!empty($repair_settings['job_sheet_custom_field_5']))
                                        {
                        data: 'custom_field_5',
                        name: 'repair_job_sheets.custom_field_5'
                    },
                @endif{
                    data: 'added_by',
                    name: 'added_by',
                    searchable: false
                },
                {
                    data: 'created_at',
                    name: 'repair_job_sheets.created_at'
                }
                ],
                "fnDrawCallback": function (oSettings) {
                    __currency_convert_recursively($('#pending_job_sheets_table'));
                }
            });

            completed_job_sheets_datatable = $("#completed_job_sheets_table").DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '/repair/job-sheet',
                    "data": function (d) {
                        d.location_id = $('#location_id').val();
                        d.contact_id = $('#contact_id').val();
                        d.status_id = $('#status_id').val();
                        d.is_completed_status = 1;
                        @if (in_array('service_staff', $enabled_modules))
                            d.technician = $('#technician').val();
                        @endif
                            }
                },
                columnDefs: [{
                    targets: [0],
                    orderable: false,
                    searchable: false
                }],
                aaSorting: [
                    [2, 'asc']
                ],
                columns: [{
                    data: 'action',
                    name: 'action'
                },
                {
                    data: 'service_type',
                    name: 'service_type'
                },
                {
                    data: 'delivery_date',
                    name: 'delivery_date'
                },
                {
                    data: 'job_sheet_no',
                    name: 'job_sheet_no'
                },
                {
                    data: 'repair_no',
                    name: 'repair_no',
                    searchable: false
                },
                {
                    data: 'status',
                    name: 'rs.name'
                },
                    @if (in_array('service_staff', $enabled_modules))
                                                {
                            data: 'technecian',
                            name: 'technecian',
                            searchable: false
                        },
                    @endif{
                    data: 'customer',
                    name: 'contacts.name'
                },
                {
                    data: 'location',
                    name: 'bl.name'
                },
                {
                    data: 'brand',
                    name: 'b.name'
                },
                {
                    data: 'device',
                    name: 'device.name'
                },
                {
                    data: 'device_model',
                    name: 'rdm.name'
                },
                {
                    data: 'serial_no',
                    name: 'serial_no'
                },
                {
                    data: 'estimated_cost',
                    name: 'estimated_cost'
                },
                    @if (!empty($repair_settings['job_sheet_custom_field_1']))
                                                {
                            data: 'custom_field_1',
                            name: 'repair_job_sheets.custom_field_1'
                        },
                    @endif
                @if (!empty($repair_settings['job_sheet_custom_field_2']))
                                        {
                        data: 'custom_field_2',
                        name: 'repair_job_sheets.custom_field_2'
                    },
                @endif
                @if (!empty($repair_settings['job_sheet_custom_field_3']))
                                        {
                        data: 'custom_field_3',
                        name: 'repair_job_sheets.custom_field_3'
                    },
                @endif
                @if (!empty($repair_settings['job_sheet_custom_field_4']))
                                        {
                        data: 'custom_field_4',
                        name: 'repair_job_sheets.custom_field_4'
                    },
                @endif
                @if (!empty($repair_settings['job_sheet_custom_field_5']))
                                        {
                        data: 'custom_field_5',
                        name: 'repair_job_sheets.custom_field_5'
                    },
                @endif{
                    data: 'added_by',
                    name: 'added_by',
                    searchable: false
                },
                {
                    data: 'created_at',
                    name: 'repair_job_sheets.created_at'
                }
                ],
                "fnDrawCallback": function (oSettings) {
                    __currency_convert_recursively($('#completed_job_sheets_table'));
                }
            });

            // ═══════════════════════════════════════════
            // DELETE JOB SHEET
            // ═══════════════════════════════════════════

            $(document).on('click', '#delete_job_sheet', function (e) {
                e.preventDefault();
                var url = $(this).data('href');
                swal({
                    title: LANG.sure,
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                }).then((confirmed) => {
                    if (confirmed) {
                        $.ajax({
                            method: 'DELETE',
                            url: url,
                            dataType: 'json',
                            success: function (result) {
                                if (result.success) {
                                    toastr.success(result.msg);
                                    pending_job_sheets_datatable.ajax.reload();
                                    completed_job_sheets_datatable.ajax.reload();
                                } else {
                                    toastr.error(result.msg);
                                }
                            }
                        });
                    }
                });
            });

            // ═══════════════════════════════════════════
            // STATUS MODAL — open
            // ═══════════════════════════════════════════

            @if (auth()->user()->can('job_sheet.create') || auth()->user()->can('job_sheet.edit'))
                $(document).on('click', '.edit_job_sheet_status', function () {
                    var url = $(this).data('href');
                    $.ajax({
                        method: 'GET',
                        url: url,
                        dataType: 'html',
                        success: function (result) {
                            $('#status_modal').html(result).modal('show');
                        }
                    });
                });
            @endif

            // ═══════════════════════════════════════════
            // VIEW PARTS MODAL — open + store URL
            // ═══════════════════════════════════════════

            @if (
                    auth()->user()->can('repair.view_part') ||
                    auth()->user()->can('job_sheet.view_all') ||
                    auth()->user()->can('job_sheet.create')
                )
                $(document).on('click', '.view_part_sheet', function () {
                    var url = $(this).data('href');
                    $.ajax({
                        method: 'GET',
                        url: url,
                        dataType: 'html',
                        success: function (result) {
                            $('#view_part_modal')
                                .data('load-url', url) // ✅ store for later refresh
                                .html(result)
                                .modal('show');
                        }
                    });
                });
            @endif

            // ═══════════════════════════════════════════
            // EDIT PART STATUS MODAL — open
            // ✅ Defined ONCE here, NOT in any loaded blade
            // ═══════════════════════════════════════════

            $(document).ready(function () {
                $(document).on('click', '.edit-part-status', function () {
                    var url = $(this).data('href');
                    $.ajax({
                        method: 'GET',
                        url: url,
                        dataType: 'html',
                        success: function (result) {
                            $('#edit_part_status_modal').html(result).modal('show');
                        }
                    });
                });
            });
            // ═══════════════════════════════════════════
            // STATUS DROPDOWN CHANGE (edit_part_status modal)
            // ✅ Defined ONCE here, NOT in any loaded blade
            // ═══════════════════════════════════════════

            $(document).on('change', '#status_id_modal', function () {
                // Only handle if edit_part_status_modal is open
                if (!$('#edit_part_status_modal').hasClass('in')) return;

                var selected = $(this).val();
                if (selected === 'rejected') {
                    $('#note_container').show();
                    $('#note').attr('required', true);
                } else {
                    $('#note_container').hide();
                    $('#note').removeAttr('required');
                }
            });

            // ═══════════════════════════════════════════
            // STATUS MODAL — shown event (tinymce, sms, email)
            // ═══════════════════════════════════════════

            $('#status_modal').on('shown.bs.modal', function (e) {
                tinymce.init({
                    selector: 'textarea#email_body',
                });

                $('#send_sms').change(function () {
                    if ($(this).is(":checked")) {
                        $('div.sms_body').fadeIn();
                    } else {
                        $('div.sms_body').fadeOut();
                    }
                });

                $('#send_email').change(function () {
                    if ($(this).is(":checked")) {
                        $('div.email_template').fadeIn();
                    } else {
                        $('div.email_template').fadeOut();
                    }
                });

                if ($('#status_id_modal').length) {
                    $("#sms_body").val($("#status_id_modal :selected").data('sms_template'));
                    $("#email_subject").val($("#status_id_modal :selected").data('email_subject'));
                    tinymce.activeEditor.setContent($("#status_id_modal :selected").data('email_body'));
                }

                $('#status_id_modal').on('change', function () {
                    var sms_template = $(this).find(':selected').data('sms_template');
                    var email_subject = $(this).find(':selected').data('email_subject');
                    var email_body = $(this).find(':selected').data('email_body');

                    $("#sms_body").val(sms_template);
                    $("#email_subject").val(email_subject);
                    tinymce.activeEditor.setContent(email_body);

                    if ($('#status_modal .mark-as-complete-btn').length) {
                        if ($(this).find(':selected').data('is_completed_status') == 1) {
                            $('#status_modal').find('.mark-as-complete-btn').removeClass('hide');
                            $('#status_modal').find('.mark-as-incomplete-btn').addClass('hide');
                        } else {
                            $('#status_modal').find('.mark-as-complete-btn').addClass('hide');
                            $('#status_modal').find('.mark-as-incomplete-btn').removeClass('hide');
                        }
                    }
                });
            });

            $('#status_modal').on('hidden.bs.modal', function () {
                tinymce.remove("textarea#email_body");
            });

            // ═══════════════════════════════════════════
            // FORM SUBMIT — handles BOTH modals
            // ✅ Single handler, distinguishes by which modal is open
            // ═══════════════════════════════════════════

            $(document).on('click', '.update_status_button', function () {
                $('#status_form_redirect').val($(this).data('href'));
            });

            $(document).on('submit', 'form#update_status_form', function (e) {
                e.preventDefault();

                var data = $(this).serialize();

                // ── edit_part_status_modal submit ──
                if ($('#edit_part_status_modal').hasClass('in')) {
                    var url = $(this).attr('action');
                    $.ajax({
                        url: url,
                        method: 'POST', // Laravel spoofs PUT via _method field
                        data: data,
                        success: function (response) {
                            if (response.success) {
                                // 1. Close edit modal
                                $('#edit_part_status_modal').modal('hide');

                                // 2. Reload parent view_part_modal using stored URL
                                var parentUrl = $('#view_part_modal').data('load-url');
                                if (parentUrl) {
                                    $.ajax({
                                        method: 'GET',
                                        url: parentUrl,
                                        dataType: 'html',
                                        success: function (result) {
                                            $('#view_part_modal').html(result);
                                        }
                                    });
                                }

                                toastr.success(response.msg);
                            } else {
                                toastr.error(response.msg);
                            }
                        },
                        error: function (xhr) {
                            toastr.error('Something went wrong. Please try again.');
                        }
                    });

                    return; // ✅ stop — don't run status_modal logic below
                }

                // ── status_modal submit (original logic) ──
                var ladda = Ladda.create(document.querySelector('.ladda-button'));
                ladda.start();
                $.ajax({
                    method: $(this).attr("method"),
                    url: $(this).attr("action"),
                    dataType: "json",
                    data: data,
                    success: function (result) {
                        ladda.stop();
                        if (result.success == true) {
                            $('#status_modal').modal('hide');
                            if (result.msg) {
                                toastr.success(result.msg);
                            }
                            if ($('#status_form_redirect').val()) {
                                window.location = $('#status_form_redirect').val();
                            }
                            pending_job_sheets_datatable.ajax.reload();
                            completed_job_sheets_datatable.ajax.reload();
                        } else {
                            toastr.error(result.msg);
                        }
                    }
                });
            });

            // ═══════════════════════════════════════════
            // FILTER CHANGE
            // ═══════════════════════════════════════════

            $(document).on('change', '#location_id, #contact_id, #status_id, #technician', function () {
                pending_job_sheets_datatable.ajax.reload();
                completed_job_sheets_datatable.ajax.reload();
            });

            // ═══════════════════════════════════════════
            // AUTO OPEN VIEW PART MODAL FROM URL PARAMS
            // ═══════════════════════════════════════════
            var urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('action') === 'view_part' && urlParams.get('id')) {
                var jobSheetId = urlParams.get('id');
                var url = '/repair/job-sheet/' + jobSheetId + '/view_parts';

                $.ajax({
                    method: 'GET',
                    url: url,
                    dataType: 'html',
                    success: function (result) {
                        $('#view_part_modal')
                            .data('load-url', url)
                            .html(result)
                            .modal('show');

                        // Clean URL without page reload
                        window.history.replaceState({}, document.title, window.location.pathname);
                    },
                    error: function () {
                        toastr.error('Failed to load parts.');
                    }
                });
            }
            // ═══════════════════════════════════════════
            // DELETE PART — admin only
            // ═══════════════════════════════════════════
            $(document).on('click', '.delete-part', function () {
                var partKey = $(this).data('part-key');
                var jobSheetId = $(this).data('job-sheet-id');

                swal({
                    title: LANG.sure,
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                }).then((confirmed) => {
                    if (confirmed) {
                        $.ajax({
                            method: 'DELETE',
                            url: '/repair/job-sheet/' + jobSheetId + '/parts/' + partKey,
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            dataType: 'json',
                            success: function (result) {
                                if (result.success) {
                                    toastr.success(result.msg);

                                    // Reload parent view_part_modal
                                    var parentUrl = $('#view_part_modal').data(
                                        'load-url');
                                    if (parentUrl) {
                                        $.ajax({
                                            method: 'GET',
                                            url: parentUrl,
                                            dataType: 'html',
                                            success: function (html) {
                                                $('#view_part_modal').html(
                                                    html);
                                            }
                                        });
                                    }
                                } else {
                                    toastr.error(result.msg);
                                }
                            },
                            error: function () {
                                toastr.error('Something went wrong.');
                            }
                        });
                    }
                });
            });

        });
    </script>
@endsection