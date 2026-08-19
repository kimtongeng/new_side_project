<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content" style="border-radius: 8px; overflow: hidden; box-shadow: 0 10px 25px rgba(0,0,0,0.15);">

        <div class="modal-header" style="background-color: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 15px 20px;">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="margin-top: -2px; font-size: 24px;">
                <span aria-hidden="true">&times;</span>
            </button>
            <h4 class="modal-title" data-pending-url="{{ action([\App\Http\Controllers\AccountController::class, 'getPendingTransfers'], [$account->id]) }}" style="font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 8px;">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-clock" width="18" height="18" viewBox="0 0 24 24" stroke-width="2" stroke="#f59e0b" fill="none" stroke-linecap="round" stroke-linejoin="round" style="display: inline-block; vertical-align: middle;">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                    <circle cx="12" cy="12" r="9" />
                    <polyline points="12 7 12 12 15 15" />
                </svg>
                <span>@lang('account.pending_transfers') - <span class="text-primary">{{ $account->name }}</span></span>
            </h4>
        </div>

        <div class="modal-body" style="padding: 20px;">
            @if($pending_transactions->count() > 0)
                @php
                    $is_admin = auth()->user()->hasRole('Admin#' . session('business.id')) || auth()->user()->can('superadmin');
                    $current_user_id = auth()->id();
                @endphp
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" style="margin-bottom: 0;">
                        <thead>
                            <tr style="background-color: #f1f5f9; color: #475569;">
                                <th>@lang('messages.date')</th>
                                <th>@lang('lang_v1.description')</th>
                                <th>@lang('sale.amount')</th>
                                <th>@lang('brand.note')</th>
                                <th>@lang('lang_v1.added_by')</th>
                                <th style="min-width: 175px;">@lang('messages.action')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pending_transactions as $trans)
                                @php
                                    $is_creator = ($trans->created_by == $current_user_id) || (!empty($trans->transfer_transaction) && $trans->transfer_transaction->created_by == $current_user_id);
                                    $is_last_editor = (!empty($trans->last_edited_by) && $trans->last_edited_by == $current_user_id) || (!empty($trans->transfer_transaction) && !empty($trans->transfer_transaction->last_edited_by) && $trans->transfer_transaction->last_edited_by == $current_user_id);

                                    $can_approve = !$is_creator && !$is_last_editor && (auth()->user()->can('account.accept_pending_transfer') || auth()->user()->can('superadmin'));
                                    $can_reject  = !$is_creator && !$is_last_editor && (auth()->user()->can('account.reject_pending_transfer') || auth()->user()->can('superadmin'));
                                    $can_edit    = auth()->user()->can('account.edit_pending_transfer') || auth()->user()->can('superadmin');

                                    $last_editor_user = !empty($trans->last_editor) ? $trans->last_editor : (!empty($trans->transfer_transaction) && !empty($trans->transfer_transaction->last_editor) ? $trans->transfer_transaction->last_editor : null);
                                @endphp
                                <tr>
                                    <td style="vertical-align: middle; white-space: nowrap;">{{ @format_datetime($trans->operation_date) }}</td>
                                    <td style="vertical-align: middle;">
                                        <strong>@lang('account.fund_transfer')</strong>
                                        @if(!empty($trans->transfer_transaction) && !empty($trans->transfer_transaction->account))
                                            <span class="text-muted" style="font-size: 12px;">
                                                @if($trans->type == 'credit')
                                                    ({{ __('account.from') }}: {{ $trans->transfer_transaction->account->name }})
                                                @else
                                                    ({{ __('account.to') }}: {{ $trans->transfer_transaction->account->name }})
                                                @endif
                                            </span>
                                        @endif
                                        <div style="margin-top: 3px;">
                                            <span class="label bg-yellow" style="font-size: 10px; padding: 2px 6px; border-radius: 4px; display: inline-flex; align-items: center; gap: 3px;">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-clock" width="10" height="10" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                    <circle cx="12" cy="12" r="9" />
                                                    <polyline points="12 7 12 12 15 15" />
                                                </svg>
                                                <span>@lang('account.pending')</span>
                                            </span>
                                        </div>
                                    </td>
                                    <td style="vertical-align: middle; white-space: nowrap;">
                                        <span class="{{ $trans->type == 'credit' ? 'text-success' : 'text-danger' }}" style="font-weight: 700;">
                                            @format_currency($trans->amount)
                                        </span>
                                    </td>
                                    <td style="vertical-align: middle;">{{ $trans->note ?: '-' }}</td>
                                    <td style="vertical-align: middle;">
                                        <div>{{ $trans->user->user_full_name ?? '-' }}</div>
                                        @if(!empty($last_editor_user))
                                            <div class="text-muted" style="font-size: 11px; margin-top: 3px;">
                                                <i class="fa fa-edit"></i> @lang('lang_v1.edited_by'): {{ $last_editor_user->user_full_name }}
                                            </div>
                                        @endif
                                    </td>
                                    <td style="vertical-align: middle;">
                                        <div class="tw-flex tw-items-center tw-gap-1 tw-flex-wrap" style="white-space: nowrap;">
                                            @if($can_approve)
                                                <button type="button" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline tw-dw-btn-success change_transfer_status" data-href="{{ action([\App\Http\Controllers\AccountController::class, 'changeTransferStatus'], [$trans->id]) }}?status=final" title="@lang('account.accept')" style="display: inline-flex; align-items: center; gap: 4px;">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-check" width="14" height="14" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0;">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                        <path d="M5 12l5 5l10 -10" />
                                                    </svg>
                                                    <span>@lang('account.accept')</span>
                                                </button>
                                            @endif
                                            @if($can_reject)
                                                <button type="button" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline tw-dw-btn-error change_transfer_status" data-href="{{ action([\App\Http\Controllers\AccountController::class, 'changeTransferStatus'], [$trans->id]) }}?status=rejected" title="@lang('account.reject')" style="display: inline-flex; align-items: center; gap: 4px;">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-x" width="14" height="14" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0;">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                        <path d="M18 6l-12 12" />
                                                        <path d="M6 6l12 12" />
                                                    </svg>
                                                    <span>@lang('account.reject')</span>
                                                </button>
                                            @endif
                                            @if($can_edit)
                                                <button type="button" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline tw-dw-btn-primary btn-modal" data-container="#edit_account_transaction" data-href="{{ action([\App\Http\Controllers\AccountController::class, 'editAccountTransaction'], [$trans->id]) }}" title="@lang('messages.edit')" style="display: inline-flex; align-items: center; gap: 4px;">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-edit" width="14" height="14" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0;">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                        <path d="M9 7h-3a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-3" />
                                                        <path d="M9 15h3l8.5 -8.5a1.5 1.5 0 0 0 -3 -3l-8.5 8.5v3" />
                                                        <line x1="16" y1="5" x2="19" y2="8" />
                                                    </svg>
                                                    <span>@lang('messages.edit')</span>
                                                </button>
                                            @endif

                                            @if($is_creator)
                                                <span class="label bg-gray" style="font-size: 11px; padding: 3px 8px; border-radius: 4px; display: inline-flex; align-items: center; gap: 4px;" title="@lang('account.creator_cannot_approve')">
                                                    <i class="fa fa-lock"></i> @lang('account.created_by_you')
                                                </span>
                                            @elseif($is_last_editor)
                                                <span class="label bg-gray" style="font-size: 11px; padding: 3px 8px; border-radius: 4px; display: inline-flex; align-items: center; gap: 4px;" title="@lang('account.editor_cannot_approve')">
                                                    <i class="fa fa-lock"></i> @lang('account.edited_by_you')
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center" style="padding: 30px 0;">
                    <i class="fa fa-info-circle text-muted" style="font-size: 32px; margin-bottom: 10px;"></i>
                    <p class="text-muted" style="font-size: 15px; margin: 0;">@lang('messages.no_data_found')</p>
                </div>
            @endif
        </div>

        <div class="modal-footer" style="background-color: #f8fafc; border-top: 1px solid #e2e8f0; padding: 12px 20px;">
            <button type="button" class="tw-dw-btn tw-dw-btn-neutral tw-text-white tw-dw-btn-sm" data-dismiss="modal">@lang('messages.close')</button>
        </div>

    </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->
