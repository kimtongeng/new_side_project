<div class="table-responsive">
    <table class="table table-bordered table-striped" id="sell_table">
        <thead>
            <tr>
                <th>@lang('messages.action')</th>
                <th>@lang('lang_v1.date')</th>
                <th>@lang('lang_v1.invoice_no')</th>
                <th>@lang('lang_v1.customer')</th>
                <th>@lang('lang_v1.location')</th>
                <th>@lang('lang_v1.payment_status')</th>
                <th>@lang('lang_v1.total')</th>
                <th>@lang('lang_v1.payment_due')</th>
                <th>@lang('lang_v1.sales_person')</th>
                @if ($is_cmsn_agent_enabled)
                    <th>@lang('lang_v1.commission_agent')</th>
                @endif
                <th>@lang('lang_v1.created_by')</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
</div>

@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {
            var sellTable = $('#sell_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ action([\Modules\Loan\Http\Controllers\LoanPosController::class, 'index']) }}',
                    data: function(d) {
                        d.location_id = $('#location_id').val();
                        d.customer_id = $('#customer_id').val();
                        d.payment_status = $('#payment_status').val();
                        d.sales_representative = $('#sales_representative').val();
                        d.commission_agent = $('#commission_agent').val();
                        d.start_date = $('#date_range').data('daterangepicker') ? $('#date_range').data('daterangepicker').startDate.format('YYYY-MM-DD') : '';
                        d.end_date = $('#date_range').data('daterangepicker') ? $('#date_range').data('daterangepicker').endDate.format('YYYY-MM-DD') : '';
                        d.is_direct_sale = $('#is_direct_sale').val();
                    }
                },
                columns: [
                    { data: 'action', name: 'action', orderable: false, searchable: false },
                    { data: 'transaction_date', name: 'transaction_date' },
                    { data: 'invoice_no', name: 'invoice_no' },
                    { data: 'contact_name', name: 'contacts.name' },
                    { data: 'location_name', name: 'bl.name' },
                    { data: 'payment_status', name: 'payment_status' },
                    { data: 'final_total', name: 'final_total' },
                    { data: 'payment_due', name: 'payment_due' },
                    { data: 'sales_person', name: 'sales_person' },
                    @if ($is_cmsn_agent_enabled)
                        { data: 'commission_agent', name: 'commission_agent' },
                    @endif
                    { data: 'created_by', name: 'created_by' }
                ],
                "fnDrawCallback": function(oSettings) {
                    __currency_convert_recursively($('#sell_table'));
                }
            });

            // Apply filters
            $('#location_id, #customer_id, #payment_status, #sales_representative, #commission_agent').change(function() {
                sellTable.ajax.reload();
            });

            $('#date_range').on('apply.daterangepicker', function(ev, picker) {
                sellTable.ajax.reload();
            });

            // Handle view transaction action
            $(document).on('click', '.view_transaction', function() {
                var url = $(this).data('href');
                $.ajax({
                    url: url,
                    method: 'GET',
                    success: function(response) {
                        // Assuming a modal exists to display transaction details
                        if (typeof show_transaction_details === 'function') {
                            show_transaction_details(response);
                        } else {
                            alert('Transaction details loaded. Implement show_transaction_details in pos.js.');
                        }
                    },
                    error: function() {
                        toastr.error('@lang("messages.something_went_wrong")');
                    }
                });
            });

            // Handle delete transaction action
            $(document).on('click', '.delete_transaction', function() {
                var url = $(this).data('href');
                swal({
                    title: '@lang("messages.confirm_delete")',
                    text: '@lang("messages.delete_confirmation")',
                    icon: 'warning',
                    buttons: true,
                    dangerMode: true,
                }).then((willDelete) => {
                    if (willDelete) {
                        $.ajax({
                            url: url,
                            method: 'DELETE',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                if (response.success) {
                                    toastr.success(response.msg);
                                    sellTable.ajax.reload();
                                } else {
                                    toastr.error(response.msg);
                                }
                            },
                            error: function() {
                                toastr.error('@lang("messages.something_went_wrong")');
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection