<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">×</span>
            </button>
            <h4 class="modal-title">@lang('Loan::lang.recent_transactions')</h4>
        </div>
        <div class="modal-body">
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label>@lang('lang_v1.transaction_type'):</label>
                        <select class="form-control select2" id="transaction_type_filter">
                            <option value="final">@lang('lang_v1.final')</option>
                            <option value="draft">@lang('lang_v1.draft')</option>
                            <option value="quotation">@lang('lang_v1.quotation')</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="recent_transactions_table">
                            <thead>
                                <tr>
                                    <th>@lang('messages.action')</th>
                                    <th>@lang('lang_v1.date')</th>
                                    <th>@lang('lang_v1.invoice_no')</th>
                                    <th>@lang('lang_v1.customer')</th>
                                    <th>@lang('lang_v1.total')</th>
                                    <th>@lang('lang_v1.status')</th>
                                </tr>
                            </thead>
                            <tbody id="recent_transactions_body">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
        </div>
    </div>
</div>

@stop

@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {
            // Initialize Select2
            $('#transaction_type_filter').select2();

            // Load recent transactions
            function loadRecentTransactions() {
                var transaction_type = $('#transaction_type_filter').val();

                $.ajax({
                    url: '{{ action([\Modules\Loan\Http\Controllers\LoanPosUtilityController::class, 'getRecentTransactions']) }}',
                    method: 'GET',
                    data: {
                        status: transaction_type
                    },
                    success: function(response) {
                        $('#recent_transactions_body').html(response);
                        __currency_convert_recursively($('#recent_transactions_table'));
                    },
                    error: function() {
                        toastr.error('@lang("messages.something_went_wrong")');
                    }
                });
            }

            // Trigger load on modal open
            $('#recent_transactions_modal').on('shown.bs.modal', function() {
                loadRecentTransactions();
            });

            // Trigger load on transaction type change
            $('#transaction_type_filter').change(function() {
                loadRecentTransactions();
            });

            // Handle view transaction action
            $(document).on('click', '.view_transaction', function() {
                var url = $(this).data('href');
                $.ajax({
                    url: url,
                    method: 'GET',
                    success: function(response) {
                        $('#recent_transactions_modal').modal('hide');
                        // Assuming a modal or container exists to display the transaction details
                        // This should be handled in pos.js or another script
                        if (typeof show_transaction_details === 'function') {
                            show_transaction_details(response);
                        } else {
                            // Fallback: display in a new modal or alert
                            alert('Transaction details loaded. Implement show_transaction_details in pos.js.');
                        }
                    },
                    error: function() {
                        toastr.error('@lang("messages.something_went_wrong")');
                    }
                });
            });
        });
    </script>
@endsection