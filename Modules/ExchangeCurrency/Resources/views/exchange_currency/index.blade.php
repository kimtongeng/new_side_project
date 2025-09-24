@extends('layouts.app')
@section('title', __('ExchangeCurrency::lang.currency'))

@section('content')
    <section class="content-header">
        <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">@lang('ExchangeCurrency::lang.exchange_currency')
            <small class="tw-text-sm md:tw-text-base tw-text-gray-700 tw-font-semibold">@lang('ExchangeCurrency::lang.manage_your_currency')</small>
        </h1>
        <!-- <ol class="breadcrumb">
                      <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
                      <li class="active">Here</li>
                  </ol> -->
    </section>
    <section class="content">
        @component('components.widget', [
            'class' => 'box-primary',
            'title' => __('ExchangeCurrency::lang.all_your_current'),
        ])
            @can('exchange_currency.create')
                @slot('tool')
                    <div class="box-tools">
                        <a class="tw-dw-btn tw-bg-gradient-to-r tw-from-indigo-600 tw-to-blue-500 tw-font-bold tw-text-white tw-border-none tw-rounded-full btn-modal pull-right"
                            data-href="{{ action([\Modules\ExchangeCurrency\Http\Controllers\ExchangeCurrencyController::class, 'create']) }}"
                            data-container=".exchange_currency_modal">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                class="icon icon-tabler icons-tabler-outline icon-tabler-plus">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                <path d="M12 5l0 14" />
                                <path d="M5 12l14 0" />
                            </svg> @lang('messages.add')
                        </a>
                    </div>
                @endslot
            @endcan
            @can('exchange_currency.view')
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="exchange_currency">
                        <thead>
                            <tr>
                                <th>@lang('ExchangeCurrency::lang.country')</th>
                                <th>@lang('ExchangeCurrency::lang.currency')</th>
                                <th>@lang('ExchangeCurrency::lang.code')</th>
                                <th>@lang('ExchangeCurrency::lang.symbol')</th>
                                <th>@lang('ExchangeCurrency::lang.exchange_rate')</th>
                                <th>@lang('ExchangeCurrency::lang.is_use')</th>
                                <th>@lang('messages.action')</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            @endcan
        @endcomponent
        <div class="modal fade exchange_currency_modal" tabindex="-1" role="dialog"
            aria-labelledby="gridSystemModalLabel">
        </div>

    </section>
@endsection
