@extends('layouts.app')
@section('title', __('lang_v1.payment_accounts'))

@section('content')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">@lang('lang_v1.payment_accounts')
            <small
                class="tw-text-sm md:tw-text-base tw-text-gray-700 tw-font-semibold">@lang('account.manage_your_account')</small>
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">
        @if (!empty($not_linked_payments))
            <div class="row">
                <div class="col-sm-12">
                    <div class="alert alert-danger">
                        <ul>
                            @if (!empty($not_linked_payments))
                                <li>{!! __('account.payments_not_linked_with_account', ['payments' => $not_linked_payments]) !!} <a
                                        href="{{ action([\App\Http\Controllers\AccountReportsController::class, 'paymentAccountReport']) }}">@lang('account.view_details')</a>
                                </li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
        @endif
        @can('account.access')
            @component('components.filters', ['title' => __('report.filters')])
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('account_list_filter_location_id', __('business.business_location') . ':') !!}
                        {!! Form::select('account_list_filter_location_id', $business_locations, null, [
                            'class' => 'form-control select2',
                            'style' => 'width:100%',
                            'id' => 'account_list_filter_location_id',
                            'placeholder' => __('lang_v1.all'),
                        ]) !!}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('account_list_filter_account_type_id', __('lang_v1.account_type') . ':') !!}
                        {!! Form::select('account_list_filter_account_type_id', $account_types_dropdown, null, [
                            'class' => 'form-control select2',
                            'style' => 'width:100%',
                            'id' => 'account_list_filter_account_type_id',
                            'placeholder' => __('lang_v1.all'),
                        ]) !!}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('account_list_filter_created_by', __('lang_v1.added_by') . ':') !!}
                        {!! Form::select('account_list_filter_created_by', $users, null, [
                            'class' => 'form-control select2',
                            'style' => 'width:100%',
                            'id' => 'account_list_filter_created_by',
                            'placeholder' => __('lang_v1.all'),
                        ]) !!}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('account_list_filter_user_level', __('lang_v1.user_level') . ':') !!}
                        {!! Form::select('account_list_filter_user_level', $user_levels, null, [
                            'class' => 'form-control select2',
                            'style' => 'width:100%',
                            'id' => 'account_list_filter_user_level',
                            'placeholder' => __('lang_v1.all'),
                        ]) !!}
                    </div>
                </div>
            @endcomponent
            <div class="row">
                @component('components.widget')
                <div class="col-sm-12">
                    <div class="nav-tabs-custom">
                        <ul class="nav nav-tabs">
                            <li class="active">
                                <a href="#other_accounts" data-toggle="tab">
                                    <i class="fa fa-book"></i> <strong>@lang('account.accounts')</strong>
                                </a>
                            </li>
                            {{--
                            <li>
                                <a href="#capital_accounts" data-toggle="tab">
                                    <i class="fa fa-book"></i> <strong>
                                        @lang('account.capital_accounts') </strong>
                                </a>
                            </li>
                            --}}
                            <li>
                                <a href="#account_types" data-toggle="tab">
                                    <i class="fa fa-list"></i> <strong>
                                        @lang('lang_v1.account_types') </strong>
                                </a>
                            </li>
                        </ul>
                        <div class="tab-content">
                            <div class="tab-pane active" id="other_accounts">
                                <div class="row">
                                        <div class="tw-flex tw-items-center tw-justify-between tw-gap-2 tw-mb-4 tw-w-full">
                                            <div class="tw-w-44 sm:tw-w-64">
                                                {!! Form::select(
                                                    'account_status',
                                                    ['active' => __('business.is_active'), 'closed' => __('account.closed')],
                                                    null,
                                                    ['class' => 'form-control select2', 'style' => 'width:100%', 'id' => 'account_status'],
                                                ) !!}
                                            </div>
                                            <div>
                                                @if(auth()->user()->can('account.create') || auth()->user()->can('add_account'))
                                                    <button type="button"
                                                        class="tw-dw-btn tw-bg-gradient-to-r tw-from-indigo-600 tw-to-blue-500 tw-font-bold tw-text-white tw-border-none tw-rounded-full btn-modal"
                                                        data-container=".account_model"
                                                        data-href="{{ action([\App\Http\Controllers\AccountController::class, 'create']) }}"
                                                        style="cursor: pointer; touch-action: manipulation; -webkit-tap-highlight-color: transparent; position: relative; z-index: 10;">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                            stroke-linecap="round" stroke-linejoin="round"
                                                            class="icon icon-tabler icons-tabler-outline icon-tabler-plus"
                                                            style="pointer-events: none; display: inline-block; vertical-align: middle;">
                                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                            <path d="M12 5l0 14" />
                                                            <path d="M5 12l14 0" />
                                                        </svg> <span style="pointer-events: none;">@lang('messages.add')</span>
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-12">
                                        <br>
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-striped" id="other_account_table">
                                                <thead>
                                                    <tr>
                                                        <th>@lang('lang_v1.name')</th>
                                                        <th>@lang('lang_v1.account_type')</th>
                                                        <th>@lang('lang_v1.account_sub_type')</th>
                                                        <th>@lang('business.business_location')</th>
                                                        <th>@lang('lang_v1.user_level')</th>
                                                        <th>@lang('account.account_number')</th>
                                                        <th>@lang('brand.note')</th>
                                                        <th>@lang('lang_v1.balance')</th>
                                                        <th>@lang('lang_v1.account_details')</th>
                                                        <th>@lang('lang_v1.added_by')</th>
                                                        <th>@lang('messages.action')</th>
                                                    </tr>
                                                </thead>
                                                <tfoot>
                                                    <tr class="bg-gray font-17 footer-total text-center">
                                                        <td colspan="7"><strong>@lang('sale.total'):</strong></td>
                                                        <td class="footer_total_balance"></td>
                                                        <td colspan="3"></td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            {{--
                            <div class="tab-pane" id="capital_accounts">
                                <table class="table table-bordered table-striped" id="capital_account_table"
                                    style="width: 100%;">
                                    <thead>
                                        <tr>
                                            <th>@lang( 'lang_v1.name' )</th>
                                            <th>@lang('account.account_number')</th>
                                            <th>@lang( 'brand.note' )</th>
                                            <th>@lang('lang_v1.balance')</th>
                                            <th>@lang( 'messages.action' )</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                            --}}
                            <div class="tab-pane" id="account_types">
                                <div class="row">
                                    <div class="col-md-12">
                                        <button type="button"
                                            class="tw-dw-btn tw-dw-btn-primary tw-text-white tw-dw-btn-sm btn-modal pull-right"
                                            data-href="{{ action([\App\Http\Controllers\AccountTypeController::class, 'create']) }}"
                                            data-container="#account_type_modal">
                                            <i class="fa fa-plus"></i> @lang('messages.add')</button>
                                    </div>
                                </div>
                                <br>
                                <div class="row">
                                    <div class="col-md-12">
                                        <table class="table table-striped table-bordered" id="account_types_table"
                                            style="width: 100%;">
                                            <thead>
                                                <tr>
                                                    <th>@lang('lang_v1.name')</th>
                                                    <th>@lang('messages.action')</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($account_types as $account_type)
                                                                                    <tr class="account_type_{{ $account_type->id }}">
                                                                                        <th>{{ $account_type->name }}</th>
                                                                                        <td>

                                                                                            {!! Form::open([
                                                        'url' => action([\App\Http\Controllers\AccountTypeController::class, 'destroy'], $account_type->id),
                                                        'method' => 'delete',
                                                    ]) !!}
                                                                                            <button type="button"
                                                                                                class="tw-dw-btn tw-dw-btn-primary tw-dw-btn-outline tw-dw-btn-xs btn-modal"
                                                                                                data-href="{{ action([\App\Http\Controllers\AccountTypeController::class, 'edit'], $account_type->id) }}"
                                                                                                data-container="#account_type_modal">
                                                                                                <i class="fa fa-edit"></i> @lang('messages.edit')</button>

                                                                                            <button type="button"
                                                                                                class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline  tw-dw-btn-error delete_account_type">
                                                                                                <i class="fa fa-trash"></i> @lang('messages.delete')</button>
                                                                                            {!! Form::close() !!}
                                                                                        </td>
                                                                                    </tr>
                                                                                    @foreach ($account_type->sub_types as $sub_type)
                                                                                                                    <tr>
                                                                                                                        <td>&nbsp;&nbsp;-- {{ $sub_type->name }}</td>
                                                                                                                        <td>


                                                                                                                            {!! Form::open([
                                                                                            'url' => action([\App\Http\Controllers\AccountTypeController::class, 'destroy'], $sub_type->id),
                                                                                            'method' => 'delete',
                                                                                        ]) !!}
                                                                                                                            <button type="button"
                                                                                                                                class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline  tw-dw-btn-primary btn-modal"
                                                                                                                                data-href="{{ action([\App\Http\Controllers\AccountTypeController::class, 'edit'], $sub_type->id) }}"
                                                                                                                                data-container="#account_type_modal">
                                                                                                                                <i class="fa fa-edit"></i> @lang('messages.edit')</button>
                                                                                                                            <button type="button"
                                                                                                                                class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline  tw-dw-btn-error delete_account_type">
                                                                                                                                <i class="fa fa-trash"></i> @lang('messages.delete')</button>
                                                                                                                            {!! Form::close() !!}
                                                                                                                        </td>
                                                                                                                    </tr>
                                                                                    @endforeach
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endcomponent
            </div>
        @endcan

        <div class="modal fade account_model" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
        </div>

        <div class="modal fade" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel" id="account_type_modal">
        </div>
    </section>
    <!-- /.content -->

@endsection

@section('javascript')
    <script>
        $(document).ready(function () {

            $(document).on('click', 'button.close_account', function () {
                swal({
                    title: LANG.sure,
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                }).then((willDelete) => {
                    if (willDelete) {
                        var url = $(this).data('url');

                        $.ajax({
                            method: "get",
                            url: url,
                            dataType: "json",
                            success: function (result) {
                                if (result.success == true) {
                                    toastr.success(result.msg);
                                    if (typeof capital_account_table !== 'undefined' && $('#capital_account_table').length && capital_account_table.ajax) {
                                        capital_account_table.ajax.reload();
                                    }
                                    if (typeof other_account_table !== 'undefined' && other_account_table.ajax) {
                                        other_account_table.ajax.reload();
                                    }
                                } else {
                                    toastr.error(result.msg);
                                }

                            }
                        });
                    }
                });
            });

            $(document).on('submit', 'form#edit_payment_account_form', function (e) {
                e.preventDefault();
                var form = $(this);
                var data = form.serialize();
                var submit_btn = form.find('button[type="submit"]');

                if (typeof __disable_submit_button === 'function') {
                    __disable_submit_button(submit_btn);
                } else {
                    submit_btn.attr('disabled', 'disabled').prop('disabled', true);
                }

                $.ajax({
                    method: "POST",
                    url: form.attr("action"),
                    dataType: "json",
                    data: data,
                    success: function (result) {
                        if (result.success == true) {
                            $('.account_model').modal('hide');
                            $(form).closest('.modal').modal('hide');
                            $('div.account_model').modal('hide');
                            toastr.success(result.msg);
                            if (typeof capital_account_table !== 'undefined' && $('#capital_account_table').length && capital_account_table.ajax) {
                                capital_account_table.ajax.reload();
                            }
                            if (typeof other_account_table !== 'undefined' && other_account_table.ajax) {
                                other_account_table.ajax.reload();
                            }
                        } else {
                            toastr.error(result.msg);
                            if (typeof __enable_submit_button === 'function') {
                                __enable_submit_button(submit_btn);
                            } else {
                                submit_btn.removeAttr('disabled').prop('disabled', false);
                            }
                        }
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        toastr.error((typeof LANG !== 'undefined' && LANG.something_went_wrong) ? LANG.something_went_wrong : 'Something went wrong');
                        if (typeof __enable_submit_button === 'function') {
                            __enable_submit_button(submit_btn);
                        } else {
                            submit_btn.removeAttr('disabled').prop('disabled', false);
                        }
                    }
                });
            });

            $(document).on('submit', 'form#payment_account_form', function (e) {
                e.preventDefault();
                var form = $(this);
                var data = form.serialize();
                var submit_btn = form.find('button[type="submit"]');

                if (typeof __disable_submit_button === 'function') {
                    __disable_submit_button(submit_btn);
                } else {
                    submit_btn.attr('disabled', 'disabled').prop('disabled', true);
                }

                $.ajax({
                    method: "post",
                    url: form.attr("action"),
                    dataType: "json",
                    data: data,
                    success: function (result) {
                        if (result.success == true) {
                            $('.account_model').modal('hide');
                            $(form).closest('.modal').modal('hide');
                            $('div.account_model').modal('hide');
                            toastr.success(result.msg);
                            if (typeof capital_account_table !== 'undefined' && $('#capital_account_table').length && capital_account_table.ajax) {
                                capital_account_table.ajax.reload();
                            }
                            if (typeof other_account_table !== 'undefined' && other_account_table.ajax) {
                                other_account_table.ajax.reload();
                            }
                        } else {
                            toastr.error(result.msg);
                            if (typeof __enable_submit_button === 'function') {
                                __enable_submit_button(submit_btn);
                            } else {
                                submit_btn.removeAttr('disabled').prop('disabled', false);
                            }
                        }
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        toastr.error((typeof LANG !== 'undefined' && LANG.something_went_wrong) ? LANG.something_went_wrong : 'Something went wrong');
                        if (typeof __enable_submit_button === 'function') {
                            __enable_submit_button(submit_btn);
                        } else {
                            submit_btn.removeAttr('disabled').prop('disabled', false);
                        }
                    }
                });
            });

            // capital_account_table
            capital_account_table = $('#capital_account_table').DataTable({
                processing: true,
                serverSide: true,
                fixedHeader: false,
                ajax: '/account/account?account_type=capital',
                columnDefs: [{
                    "targets": 5,
                    "orderable": false,
                    "searchable": false
                }],
                columns: [{
                    data: 'name',
                    name: 'name'
                },
                {
                    data: 'account_number',
                    name: 'account_number'
                },
                {
                    data: 'note',
                    name: 'note'
                },
                {
                    data: 'balance',
                    name: 'balance',
                    searchable: false
                },
                {
                    data: 'action',
                    name: 'action'
                }
                ],
                "fnDrawCallback": function (oSettings) {
                    __currency_convert_recursively($('#capital_account_table'));
                }
            });
            // capital_account_table
            other_account_table = $('#other_account_table').DataTable({
                processing: true,
                serverSide: true,
                fixedHeader: false,
                ajax: {
                    url: '/account/account?account_type=other',
                    data: function (d) {
                        d.account_status = $('#account_status').val();
                        d.location_id = $('#account_list_filter_location_id').val();
                        d.account_type_id = $('#account_list_filter_account_type_id').val();
                        d.created_by = $('#account_list_filter_created_by').val();
                        d.user_level = $('#account_list_filter_user_level').val();
                    }
                },
                columnDefs: [{
                    "targets": [3, 4, 8, 10],
                    "orderable": false,
                    "searchable": false
                }],
                columns: [{
                    data: 'name',
                    name: 'accounts.name'
                },
                {
                    data: 'parent_account_type_name',
                    name: 'pat.name'
                },
                {
                    data: 'account_type_name',
                    name: 'ats.name'
                },
                {
                    data: 'location_name',
                    name: 'location_name'
                },
                {
                    data: 'role_name',
                    name: 'role_name'
                },
                {
                    data: 'account_number',
                    name: 'accounts.account_number'
                },
                {
                    data: 'note',
                    name: 'accounts.note'
                },
                {
                    data: 'balance',
                    name: 'balance',
                    searchable: false
                },
                {
                    data: 'account_details',
                    name: 'account_details'
                },
                {
                    data: 'added_by',
                    name: 'u.first_name'
                },
                {
                    data: 'action',
                    name: 'action'
                }
                ],
                "fnDrawCallback": function (oSettings) {
                    __currency_convert_recursively($('#other_account_table'));
                },
                "footerCallback": function (row, data, start, end, display) {
                    var footer_total_balance = 0;
                    for (var r in data) {
                        footer_total_balance += $(data[r].balance).data('orig-value') ? parseFloat($(
                            data[r].balance).data('orig-value')) : 0;
                    }

                    $('.footer_total_balance').html(__currency_trans_from_en(footer_total_balance));
                }
            });

        });

        $(document).on('change', '#account_status, #account_list_filter_location_id, #account_list_filter_account_type_id, #account_list_filter_created_by, #account_list_filter_user_level', function () {
            other_account_table.ajax.reload();
        });

        $(document).on('submit', 'form#deposit_form', function (e) {
            e.preventDefault();
            var data = $(this).serialize();

            $.ajax({
                method: "POST",
                url: $(this).attr("action"),
                dataType: "json",
                data: data,
                success: function (result) {
                    if (result.success == true) {
                        $('div.view_modal').modal('hide');
                        toastr.success(result.msg);
                        if (typeof capital_account_table !== 'undefined' && $('#capital_account_table').length && capital_account_table.ajax) {
                            capital_account_table.ajax.reload();
                        }
                        if (typeof other_account_table !== 'undefined' && other_account_table.ajax) {
                            other_account_table.ajax.reload();
                        }
                    } else {
                        toastr.error(result.msg);
                    }
                }
            });
        });

        $(document).on('shown.bs.modal', '.account_model', function (e) {
            $('.modal-backdrop').css('z-index', 1040);
            $(this).css('z-index', 1050);
            $(this).find('.select2').each(function () {
                if ($(this).hasClass('select2-hidden-accessible')) {
                    $(this).select2('destroy');
                }
                $(this).select2({
                    dropdownParent: $(this).closest('.modal-content')
                });
            });
        });

        $(document).on('click', 'button.delete_account_type', function () {
            swal({
                title: LANG.sure,
                icon: "warning",
                buttons: true,
                dangerMode: true,
            }).then((willDelete) => {
                if (willDelete) {
                    $(this).closest('form').submit();
                }
            });
        })

        $(document).on('click', 'button.activate_account', function () {
            swal({
                title: LANG.sure,
                icon: "warning",
                buttons: true,
                dangerMode: true,
            }).then((willActivate) => {
                if (willActivate) {
                    var url = $(this).data('url');
                    $.ajax({
                        method: "get",
                        url: url,
                        dataType: "json",
                        success: function (result) {
                            if (result.success == true) {
                                toastr.success(result.msg);
                                if (typeof capital_account_table !== 'undefined' && $('#capital_account_table').length && capital_account_table.ajax) {
                                    capital_account_table.ajax.reload();
                                }
                                if (typeof other_account_table !== 'undefined' && other_account_table.ajax) {
                                    other_account_table.ajax.reload();
                                }
                            } else {
                                toastr.error(result.msg);
                            }

                        }
                    });
                }
            });
        });
    </script>
@endsection