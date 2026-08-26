@extends('layouts.app')
@section('title', __( 'user.users' ))

@section('css')
<style type="text/css">
    .daterangepicker {
        color-scheme: light !important;
    }
    .daterangepicker select.monthselect,
    .daterangepicker select.yearselect,
    .daterangepicker select.hourselect,
    .daterangepicker select.minuteselect,
    .daterangepicker select.secondselect,
    .daterangepicker select.ampmselect {
        color-scheme: light !important;
        background-color: #ffffff !important;
        color: #1f2937 !important;
        border: 1px solid #d1d5db !important;
        border-radius: 6px !important;
        font-size: 13px !important;
        font-weight: 500 !important;
        padding: 3px 6px !important;
        height: 30px !important;
        margin: 0 2px !important;
        cursor: pointer !important;
        outline: none !important;
        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05) !important;
        -webkit-appearance: menulist !important;
        -moz-appearance: menulist !important;
        appearance: menulist !important;
    }
    .daterangepicker select.monthselect:focus,
    .daterangepicker select.yearselect:focus,
    .daterangepicker select.monthselect:hover,
    .daterangepicker select.yearselect:hover {
        border-color: #3b82f6 !important;
        background-color: #ffffff !important;
        color: #111827 !important;
        outline: none !important;
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2) !important;
    }
    .daterangepicker select.monthselect option,
    .daterangepicker select.yearselect option,
    .daterangepicker select.hourselect option,
    .daterangepicker select.minuteselect option,
    .daterangepicker select.secondselect option,
    .daterangepicker select.ampmselect option {
        color-scheme: light !important;
        background-color: #ffffff !important;
        color: #1f2937 !important;
        font-size: 13px !important;
        padding: 4px 8px !important;
    }
</style>
@endsection

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">@lang( 'user.users' )
        <small class="tw-text-sm md:tw-text-base tw-text-gray-700 tw-font-semibold">@lang( 'user.manage_users' )</small>
    </h1>
    <!-- <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
        <li class="active">Here</li>
    </ol> -->
</section>

<!-- Main content -->
<section class="content">
    @component('components.filters', ['title' => __('report.filters')])
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('user_list_filter_location_id', __('business.business_location') . ':') !!}
                {!! Form::select('user_list_filter_location_id', $business_locations, null, [
                    'class' => 'form-control select2',
                    'style' => 'width:100%',
                    'placeholder' => __('lang_v1.all'),
                    'id' => 'user_list_filter_location_id',
                ]) !!}
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('user_list_filter_username', __('business.username') . ':') !!}
                {!! Form::select('user_list_filter_username', $users_filter, null, [
                    'class' => 'form-control select2',
                    'style' => 'width:100%',
                    'placeholder' => __('lang_v1.all'),
                    'id' => 'user_list_filter_username',
                ]) !!}
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('user_list_filter_role_id', __('user.role') . ':') !!}
                {!! Form::select('user_list_filter_role_id', $roles, null, [
                    'class' => 'form-control select2',
                    'style' => 'width:100%',
                    'placeholder' => __('lang_v1.all'),
                    'id' => 'user_list_filter_role_id',
                ]) !!}
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('user_list_filter_status', __('sale.status') . ':') !!}
                {!! Form::select(
                    'user_list_filter_status',
                    ['active' => __('business.is_active'), 'inactive' => __('lang_v1.inactive')],
                    null,
                    ['class' => 'form-control select2', 'style' => 'width:100%', 'id' => 'user_list_filter_status', 'placeholder' => __('lang_v1.all')],
                ) !!}
            </div>
        </div>
        <div class="clearfix"></div>
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('user_list_filter_dob', __('lang_v1.dob') . ':') !!}
                <div class="input-group">
                    <span class="input-group-addon">
                        <i class="fa fa-calendar"></i>
                    </span>
                    {!! Form::text('user_list_filter_dob', null, [
                        'class' => 'form-control',
                        'id' => 'user_list_filter_dob',
                        'placeholder' => __('lang_v1.dob'),
                        'readonly',
                    ]) !!}
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('user_list_filter_gender', __('lang_v1.gender') . ':') !!}
                {!! Form::select(
                    'user_list_filter_gender',
                    ['male' => __('lang_v1.male'), 'female' => __('lang_v1.female'), 'others' => __('lang_v1.others')],
                    null,
                    ['class' => 'form-control select2', 'style' => 'width:100%', 'id' => 'user_list_filter_gender', 'placeholder' => __('lang_v1.all')],
                ) !!}
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('user_list_filter_department_id', __('lang_v1.department') . ':') !!}
                {!! Form::select('user_list_filter_department_id', $departments, null, [
                    'class' => 'form-control select2',
                    'style' => 'width:100%',
                    'placeholder' => __('lang_v1.all'),
                    'id' => 'user_list_filter_department_id',
                ]) !!}
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('user_list_filter_designation_id', __('lang_v1.designation') . ':') !!}
                {!! Form::select('user_list_filter_designation_id', $designations, null, [
                    'class' => 'form-control select2',
                    'style' => 'width:100%',
                    'placeholder' => __('lang_v1.all'),
                    'id' => 'user_list_filter_designation_id',
                ]) !!}
            </div>
        </div>
        <div class="clearfix"></div>
        <div class="col-md-12 text-right">
            <button type="button" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline tw-dw-btn-error" id="reset_user_filters">
                <i class="fa fa-undo"></i> @lang('report.reset_filters')
            </button>
        </div>
    @endcomponent

    @component('components.widget', ['class' => 'box-primary', 'title' => __( 'user.all_users' )])
        @can('user.create')
            @slot('tool')
                <div class="box-tools">
                    <a class="tw-dw-btn tw-bg-gradient-to-r tw-from-indigo-600 tw-to-blue-500 tw-font-bold tw-text-white tw-border-none tw-rounded-full" href="{{action([\App\Http\Controllers\ManageUserController::class, 'create'])}}">
                        <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-plus"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14" /><path d="M5 12l14 0" /></svg>                        @lang( 'messages.add' )
                    </a>
                 </div>
            @endslot
        @endcan
        @can('user.view')
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="users_table">
                    <thead>
                        <tr>
                            <th>@lang( 'business.username' )</th>
                            <th>@lang( 'user.name' )</th>
                            <th>@lang( 'user.role' )</th>
                            <th>@lang( 'business.email' )</th>
                            <th>@lang( 'messages.action' )</th>
                        </tr>
                    </thead>
                </table>
            </div>
        @endcan
    @endcomponent

    <div class="modal fade user_modal" tabindex="-1" role="dialog" 
    	aria-labelledby="gridSystemModalLabel">
    </div>

</section>
<!-- /.content -->
@stop
@section('javascript')
<script type="text/javascript">
    //Roles table
    $(document).ready( function(){
        var users_table = $('#users_table').DataTable({
                    processing: true,
                    serverSide: true,
                    fixedHeader:false,
                    ajax: {
                        url: '/users',
                        data: function(d) {
                            d.location_id = $('#user_list_filter_location_id').val();
                            d.username = $('#user_list_filter_username').val();
                            d.role_id = $('#user_list_filter_role_id').val();
                            d.status = $('#user_list_filter_status').val();
                            d.gender = $('#user_list_filter_gender').val();
                            d.department_id = $('#user_list_filter_department_id').val();
                            d.designation_id = $('#user_list_filter_designation_id').val();

                            if ($('#user_list_filter_dob').val()) {
                                var start = $('#user_list_filter_dob').data('daterangepicker').startDate.format('YYYY-MM-DD');
                                var end = $('#user_list_filter_dob').data('daterangepicker').endDate.format('YYYY-MM-DD');
                                d.dob_start_date = start;
                                d.dob_end_date = end;
                            }
                        }
                    },
                    columnDefs: [ {
                        "targets": [4],
                        "orderable": false,
                        "searchable": false
                    } ],
                    "columns":[
                        {"data":"username"},
                        {"data":"full_name"},
                        {"data":"role"},
                        {"data":"email"},
                        {"data":"action"}
                    ]
                });

        $('#user_list_filter_location_id, #user_list_filter_username, #user_list_filter_role_id, #user_list_filter_status, #user_list_filter_gender, #user_list_filter_department_id, #user_list_filter_designation_id').change(function() {
            users_table.ajax.reload();
        });

        $('#user_list_filter_dob').daterangepicker(
            $.extend(true, {}, dateRangeSettings, {
                autoUpdateInput: false,
            })
        );
        $('#user_list_filter_dob').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format(moment_date_format) + ' ~ ' + picker.endDate.format(moment_date_format));
            users_table.ajax.reload();
        });
        $('#user_list_filter_dob').on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('');
            users_table.ajax.reload();
        });

        $(document).on('click', '#reset_user_filters', function() {
            $('#user_list_filter_location_id').val('').trigger('change');
            $('#user_list_filter_username').val('').trigger('change');
            $('#user_list_filter_role_id').val('').trigger('change');
            $('#user_list_filter_status').val('').trigger('change');
            $('#user_list_filter_gender').val('').trigger('change');
            $('#user_list_filter_department_id').val('').trigger('change');
            $('#user_list_filter_designation_id').val('').trigger('change');
            $('#user_list_filter_dob').val('');
            users_table.ajax.reload();
        });

        $(document).on('click', 'button.delete_user_button', function(){
            swal({
              title: LANG.sure,
              text: LANG.confirm_delete_user,
              icon: "warning",
              buttons: true,
              dangerMode: true,
            }).then((willDelete) => {
                if (willDelete) {
                    var href = $(this).data('href');
                    var data = $(this).serialize();
                    $.ajax({
                        method: "DELETE",
                        url: href,
                        dataType: "json",
                        data: data,
                        success: function(result){
                            if(result.success == true){
                                toastr.success(result.msg);
                                users_table.ajax.reload();
                            } else {
                                toastr.error(result.msg);
                            }
                        }
                    });
                }
             });
        });

        $(document).on('click', 'a.update_user_status', function(e){
            e.preventDefault();
            var href = $(this).attr('href');
            $.ajax({
                method: "GET",
                url: href,
                dataType: "json",
                success: function(result){
                    if(result.success == true){
                        toastr.success(result.msg);
                        users_table.ajax.reload();
                    } else {
                        toastr.error(result.msg);
                    }
                }
            });
        });
    });
</script>
@endsection
