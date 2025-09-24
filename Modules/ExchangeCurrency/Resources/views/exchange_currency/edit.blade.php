<div class="modal-dialog" role="document">
    <div class="modal-content">

        {!! Form::open([
            'url' => action(
                [\Modules\ExchangeCurrency\Http\Controllers\ExchangeCurrencyController::class, 'update'],
                [$exchange_currency->id],
            ),
            'method' => 'PUT',
            'id' => 'edit_exchange_currency',
        ]) !!}


        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                    aria-hidden="true">&times;</span></button>
            <h4 class="modal-title">@lang('ExchangeCurrency::lang.edit_currency')</h4>
        </div>

        <div class="modal-body">
            {{-- COUNTRY --}}
            <div class="form-group">
                {!! Form::label('country', __('ExchangeCurrency::lang.country') . ':*') !!}
                {!! Form::text('country', $exchange_currency->country, [
                    'class' => 'form-control',
                    'required',
                    'placeholder' => __('ExchangeCurrency::lang.country'),
                ]) !!}
            </div>
            {{-- END COUNTRY --}}
            {{-- CURRENCY --}}
            <div class="form-group">
                {!! Form::label('currency', __('ExchangeCurrency::lang.currency') . ':*') !!}
                {!! Form::text('currency', $exchange_currency->currency, [
                    'class' => 'form-control',
                    'required',
                    'placeholder' => __('ExchangeCurrency::lang.currency'),
                ]) !!}
            </div>
            {{-- END CURRENCY --}}
            {{-- CODE --}}
            <div class="form-group">
                {!! Form::label('code', __('ExchangeCurrency::lang.code') . ':*') !!}
                {!! Form::text('code', $exchange_currency->code, [
                    'class' => 'form-control',
                    'required',
                    'placeholder' => __('ExchangeCurrency::lang.code'),
                ]) !!}
            </div>
            {{-- END CODE --}}
            {{-- SYMBOL --}}
            <div class="form-group">
                {!! Form::label('symbol', __('ExchangeCurrency::lang.symbol') . ':*') !!}
                {!! Form::text('symbol', $exchange_currency->symbol, [
                    'class' => 'form-control',
                    'required',
                    'placeholder' => __('ExchangeCurrency::lang.symbol'),
                ]) !!}
            </div>
            {{-- END SYMBOL --}}
            {{-- EXCHANGE RATE --}}
            <div class="form-group">
                {!! Form::label('exchange_rate', __('ExchangeCurrency::lang.exchange_rate') . ':*') !!}
                {!! Form::text('exchange_rate', rtrim(rtrim($exchange_currency->exchange_rate, '0'), '.'), [
                    'class' => 'form-control',
                    'required',
                    'step' => '0.0000001',
                    'min' => '0', // allow numbers close to 0
                    'placeholder' => __('ExchangeCurrency::lang.exchange_rate'),
                ]) !!}
            </div>

            {{-- END EXCHANGE RATE --}}
            @php
                $isUse = $exchange_currency->is_use;
            @endphp
            <div class="form-group">
                {!! Form::label('toggle-status', __('ExchangeCurrency::lang.is_use') . ':*') !!}
                <br>
                {!! Form::button($isUse ? 'Enable' : 'Disable', [
                    'type' => 'button',
                    'id' => 'toggle-status',
                    'class' => 'btn ' . ($isUse ? 'btn-success' : 'btn-danger'),
                ]) !!}
                {!! Form::hidden('is_use', $isUse, ['id' => 'is_use']) !!}
            </div>

            {{-- @if ($is_repair_installed)
            <div class="form-group">
                <label>
                    {!! Form::checkbox('use_for_repair', 1, false, ['class' => 'input-icheck']) !!}
                    {{ __('repair::lang.use_for_repair') }}
                </label>
                @show_tooltip(__('repair::lang.use_for_repair_help_text'))
            </div>
        @endif --}}

        </div>

        <div class="modal-footer">
            <button type="submit" class="tw-dw-btn tw-dw-btn-primary tw-text-white">@lang('messages.save')</button>
            <button type="button" class="tw-dw-btn tw-dw-btn-neutral tw-text-white"
                data-dismiss="modal">@lang('messages.close')</button>
        </div>

        {!! Form::close() !!}

    </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->