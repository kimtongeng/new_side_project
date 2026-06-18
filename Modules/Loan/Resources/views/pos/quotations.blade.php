@extends('layouts.app')

@section('title', __('Loan::lang.quotations'))

@section('content')
<!-- Content Header (Page header) -->
<section class="content-header no-print">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">@lang('Loan::lang.quotations')</h1>
</section>

<!-- Main content -->
<section class="content no-print">
    <!-- Connectivity Status Indicator -->
    <div class="alert alert-info" id="connectivity-status">
        <i class="fa fa-spinner fa-spin"></i> @lang('Loan::lang.checking_connectivity')...
    </div>

    @component('components.filters', ['title' => __('report.filters')])
        <div class="row">
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('location_id', __('business.business_location') . ':') !!}
                    {!! Form::select('location_id', $business_locations, null, ['class' => 'form-control select2', 'placeholder' => __('messages.all')]) !!}
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('customer_id', __('contact.customer') . ':') !!}
                    {!! Form::select('customer_id', $customers, null, ['class' => 'form-control select2', 'placeholder' => __('messages.all')]) !!}
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('sales_representative', __('lang_v1.sales_representative') . ':') !!}
                    {!! Form::select('sales_representative', $sales_representative, null, ['class' => 'form-control select2', 'placeholder' => __('messages.all')]) !!}
                </div>
            </div>
            @if ($is_cmsn_agent_enabled)
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('commission_agent', __('lang_v1.commission_agent') . ':') !!}
                        {!! Form::select('commission_agent', $commission_agents, null, ['class' => 'form-control select2', 'placeholder' => __('messages.all')]) !!}
                    </div>
                </div>
            @endif
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('date_range', __('report.date_range') . ':') !!}
                    {!! Form::text('date_range', null, ['placeholder' => __('lang_v1.select_a_range'), 'class' => 'form-control', 'id' => 'date_range', 'readonly']) !!}
                </div>
            </div>
        </div>
    @endcomponent

    @component('components.widget', ['class' => 'box-primary', 'title' => __('Loan::lang.quotations')])
        <div class="table-responsive">
            <table class="table table-bordered table-striped" id="quotations_table">
                <thead>
                    <tr>
                        <th>@lang('messages.action')</th>
                        <th>@lang('lang_v1.date')</th>
                        <th>@lang('lang_v1.quotation_no')</th>
                        <th>@lang('lang_v1.customer')</th>
                        <th>@lang('lang_v1.location')</th>
                        <th>@lang('lang_v1.total')</th>
                        <th>@lang('lang_v1.sales_person')</th>
                        @if ($is_cmsn_agent_enabled)
                            <th>@lang('lang_v1.commission_agent')</th>
                        @endif
                        <th>@lang('lang_v1.created_by')</th>
                    </tr>
                </thead>
                <tbody id="online_quotations">
                </tbody>
                <tbody id="offline_quotations" style="display: none;">
                </tbody>
            </table>
        </div>
    @endcomponent
</section>

<!-- Modals -->
<div class="modal fade payment_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel"></div>
<div class="modal fade edit_payment_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel"></div>
<div class="modal fade register_details_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel"></div>
<div class="modal fade close_register_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel"></div>

<!-- Print Section -->
<section class="invoice print_section" id="receipt_section"></section>

@stop

@section('css')
    <style type="text/css">
        #connectivity-status {
            position: fixed;
            top: 10px;
            right: 10px;
            z-index: 1000;
            padding: 10px;
        }
        .online {
            background-color: #28a745 !important;
            color: white !important;
        }
        .offline {
            background-color: #dc3545 !important;
            color: white !important;
        }
        .offline_quotation {
            background-color: #f8f9fa;
        }
    </style>
@endsection

@section('javascript')
    <!-- Include Dexie.js for IndexedDB -->
    <script src="https://unpkg.com/dexie@3/dist/dexie.min.js"></script>
    <script src="{{ asset('js/payment.js?v=' . $asset_v) }}"></script>

    <script type="text/javascript">
        $(document).ready(function() {
            // Initialize Dexie database
            const db = new Dexie('LoanPosOfflineDB');
            db.version(1).stores({
                transactions: 'offline_id,transaction_data'
            });

            // Date range filter
            $('#date_range').daterangepicker({
                ranges: {
                    '@lang("lang_v1.today")': [moment(), moment()],
                    '@lang("lang_v1.yesterday")': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    '@lang("lang_v1.last_7_days")': [moment().subtract(6, 'days'), moment()],
                    '@lang("lang_v1.last_30_days")': [moment().subtract(29, 'days'), moment()],
                    '@lang("lang_v1.this_month")': [moment().startOf('month'), moment().endOf('month')],
                    '@lang("lang_v1.last_month")': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                },
                locale: {
                    format: '{{ session("business.date_format") }}',
                    cancelLabel: '@lang("messages.cancel")',
                    applyLabel: '@lang("messages.apply")'
                }
            });

            // Initialize DataTable
            var quotationsTable = $('#quotations_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ action([\Modules\Loan\Http\Controllers\LoanPosUtilityController::class, 'getRecentTransactions']) }}?status=quotation',
                    data: function(d) {
                        d.location_id = $('#location_id').val();
                        d.customer_id = $('#customer_id').val();
                        d.sales_representative = $('#sales_representative').val();
                        d.commission_agent = $('#commission_agent').val();
                        d.start_date = $('#date_range').data('daterangepicker').startDate.format('YYYY-MM-DD');
                        d.end_date = $('#date_range').data('daterangepicker').endDate.format('YYYY-MM-DD');
                    }
                },
                columns: [
                    { data: 'action', name: 'action', orderable: false, searchable: false },
                    { data: 'transaction_date', name: 'transaction_date' },
                    { data: 'invoice_no', name: 'invoice_no' },
                    { data: 'contact_name', name: 'contacts.name' },
                    { data: 'location_name', name: 'bl.name' },
                    { data: 'final_total', name: 'final_total' },
                    { data: 'sales_person', name: 'sales_person' },
                    @if ($is_cmsn_agent_enabled)
                        { data: 'commission_agent', name: 'commission_agent' },
                    @endif
                    { data: 'created_by', name: 'created_by' }
                ],
                "fnDrawCallback": function(oSettings) {
                    __currency_convert_recursively($('#quotations_table'));
                }
            });

            // Check connectivity status
            function checkConnectivity() {
                $.ajax({
                    url: '{{ action([\Modules\Loan\Http\Controllers\LoanPosOfflineController::class, 'checkConnectivity']) }}',
                    method: 'GET',
                    success: function(response) {
                        const statusElement = $('#connectivity-status');
                        if (response.is_online) {
                            statusElement.removeClass('alert-info alert-danger offline').addClass('alert-success online');
                            statusElement.html('<i class="fa fa-wifi"></i> @lang("Loan::lang.connected")');
                            $('#offline_quotations').hide();
                            $('#online_quotations').show();
                            syncOfflineTransactions();
                        } else {
                            statusElement.removeClass('alert-info alert-success online').addClass('alert-danger offline');
                            statusElement.html('<i class="fa fa-exclamation-triangle"></i> @lang("Loan::lang.offline_mode")');
                            $('#online_quotations').hide();
                            $('#offline_quotations').show();
                            loadOfflineQuotations();
                        }
                    },
                    error: function() {
                        const statusElement = $('#connectivity-status');
                        statusElement.removeClass('alert-info alert-success online').addClass('alert-danger offline');
                        statusElement.html('<i class="fa fa-exclamation-triangle"></i> @lang("Loan::lang.offline_mode")');
                        $('#online_quotations').hide();
                        $('#offline_quotations').show();
                        loadOfflineQuotations();
                    }
                });
            }

            // Sync offline transactions
            function syncOfflineTransactions() {
                db.transactions.toArray().then(transactions => {
                    if (transactions.length === 0) return;

                    $.ajax({
                        url: '{{ action([\Modules\Loan\Http\Controllers\LoanPosOfflineController::class, 'syncOfflineTransactions']) }}',
                        method: 'POST',
                        data: {
                            offline_transactions: transactions.map(t => t.transaction_data),
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if (response.success) {
                                response.synced_transactions.forEach(sync => {
                                    db.transactions.where('offline_id').equals(sync.offline_id).delete();
                                });
                                toastr.success(response.msg);
                                quotationsTable.ajax.reload();
                            } else {
                                toastr.error(response.msg);
                            }
                        },
                        error: function() {
                            toastr.error('@lang("messages.something_went_wrong")');
                        }
                    });
                });
            }

            // Load offline quotations
            function loadOfflineQuotations() {
                db.transactions.toArray().then(transactions => {
                    $('#offline_quotations').empty();
                    transactions.forEach(transaction => {
                        const data = transaction.transaction_data.reduce((obj, item) => {
                            obj[item.name] = item.value;
                            return obj;
                        }, {});
                        if (data.is_quotation === '1') {
                            let row = `
                                <tr class="offline_quotation">
                                    <td>
                                        <a href="{{ action([\Modules\Loan\Http\Controllers\LoanPosEditController::class, 'edit'], ['id']) }}?offline_id=${transaction.offline_id}" class="btn btn-xs btn-primary"><i class="fa fa-edit"></i> @lang("messages.edit")</a>
                                        <button class="btn btn-xs btn-danger delete_offline_quotation" data-offline_id="${transaction.offline_id}"><i class="fa fa-trash"></i> @lang("messages.delete")</button>
                                    </td>
                                    <td>${data.transaction_date || '-'}</td>
                                    <td>${data.offline_id || '-'}</td>
                                    <td>-</td>
                                    <td>-</td>
                                    <td>-</td>
                                    <td>-</td>
                                    @if ($is_cmsn_agent_enabled)
                                        <td>-</td>
                                    @endif
                                    <td>-</td>
                                </tr>`;
                            $('#offline_quotations').append(row);
                        }
                    });
                });
            }

            // Delete offline quotation
            $(document).on('click', '.delete_offline_quotation', function() {
                const offline_id = $(this).data('offline_id');
                swal({
                    title: '@lang("messages.confirm_delete")',
                    text: '@lang("messages.delete_confirmation")',
                    icon: 'warning',
                    buttons: true,
                    dangerMode: true,
                }).then((willDelete) => {
                    if (willDelete) {
                        db.transactions.where('offline_id').equals(offline_id).delete().then(() => {
                            toastr.success('@lang("messages.deleted")');
                            loadOfflineQuotations();
                        }).catch(error => {
                            toastr.error('@lang("messages.something_went_wrong")');
                        });
                    }
                });
            });

            // Filter table on change
            $(document).on('change', '#location_id, #customer_id, #sales_representative, #commission_agent', function() {
                quotationsTable.ajax.reload();
            });

            $('#date_range').on('apply.daterangepicker', function(ev, picker) {
                quotationsTable.ajax.reload();
            });

            // Check connectivity periodically
            checkConnectivity();
            setInterval(checkConnectivity, 10000); // Check every 10 seconds
        });
    </script>
@endsection