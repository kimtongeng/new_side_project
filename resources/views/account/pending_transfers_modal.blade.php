<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">

        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title">@lang('account.pending_transfers') - {{ $account->name }}</h4>
        </div>

        <div class="modal-body">
            @if($pending_transactions->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>@lang('messages.date')</th>
                                <th>@lang('lang_v1.description')</th>
                                <th>@lang('sale.amount')</th>
                                <th>@lang('brand.note')</th>
                                <th>@lang('lang_v1.added_by')</th>
                                <th>@lang('messages.action')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pending_transactions as $trans)
                                <tr>
                                    <td>{{ @format_datetime($trans->operation_date) }}</td>
                                    <td>
                                        @lang('account.fund_transfer')
                                        @if(!empty($trans->transfer_transaction) && !empty($trans->transfer_transaction->account))
                                            @if($trans->type == 'credit')
                                                ({{ __('account.from') }}: {{ $trans->transfer_transaction->account->name }})
                                            @else
                                                ({{ __('account.to') }}: {{ $trans->transfer_transaction->account->name }})
                                            @endif
                                        @endif
                                        <span class="label label-warning" style="margin-left: 5px;">@lang('account.pending')</span>
                                    </td>
                                    <td>
                                        <span class="{{ $trans->type == 'credit' ? 'text-success' : 'text-danger' }}">
                                            @format_currency($trans->amount)
                                        </span>
                                    </td>
                                    <td>{{ $trans->note }}</td>
                                    <td>{{ $trans->user->user_full_name ?? '' }}</td>
                                    <td>
                                        @if(auth()->user()->can('account.fund_transfer') || auth()->user()->can('fund_transfer') || auth()->user()->can('superadmin'))
                                            <button type="button" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline tw-dw-btn-success change_transfer_status" data-href="{{ action([\App\Http\Controllers\AccountController::class, 'changeTransferStatus'], [$trans->id]) }}?status=final">
                                                <i class="fa fa-check"></i> @lang('account.accept')
                                            </button>
                                            <button type="button" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline tw-dw-btn-error change_transfer_status" data-href="{{ action([\App\Http\Controllers\AccountController::class, 'changeTransferStatus'], [$trans->id]) }}?status=rejected">
                                                <i class="fa fa-times"></i> @lang('account.reject')
                                            </button>
                                        @endif
                                        @if(auth()->user()->can('edit_account_transaction'))
                                            <button type="button" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline tw-dw-btn-primary btn-modal" data-container="#edit_account_transaction" data-href="{{ action([\App\Http\Controllers\AccountController::class, 'editAccountTransaction'], [$trans->id]) }}">
                                                <i class="fa fa-edit"></i> @lang('messages.edit')
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-center text-muted">@lang('messages.no_data_found')</p>
            @endif
        </div>

        <div class="modal-footer">
            <button type="button" class="tw-dw-btn tw-dw-btn-neutral tw-text-white" data-dismiss="modal">@lang('messages.close')</button>
        </div>

    </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->
