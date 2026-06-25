@extends('layouts.app')

@section('title', __('Loan::lang.payment_details'))

@section('content')

    @include('Loan::layouts.nav')

    <!-- Header Section -->
    <section class="content-header no-print">
        <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">
            {{ __('Loan::lang.payment_details') }}
        </h1>
    </section>

    <!-- Main content -->
    <section class="content no-print">
        <div class="row">
            <div class="col-md-6 col-md-offset-3">
                @component('components.widget', ['class' => 'box-primary', 'title' => __('Loan::lang.payment_info')])
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <tbody>
                            <tr>
                                <th>{{ __('Loan::lang.payment_date') }}</th>
                                <td>{{ $payment->payment_date }}</td>
                            </tr>
                            <tr>
                                <th>{{ __('Loan::lang.payment_amount') }}</th>
                                <td><span class="display_currency"
                                        data-currency_symbol="true">{{ number_format($payment->amount, 2) }}</span></td>
                            </tr>
                            <tr>
                                <th>{{ __('Loan::lang.recipient') }}</th>
                                <td>{{ $payment->loan->recipient_name }}</td>
                            </tr>
                            <tr>
                                <th>{{ __('Loan::lang.loan_id') }}</th>
                                <td>{{ $payment->loan->id }}</td>
                            </tr>
                            <tr>
                                <th>{{ __('Loan::lang.total_amount') }}</th>
                                <td><span class="display_currency"
                                        data-currency_symbol="true">{{ number_format($payment->loan->total_amount, 2) }}</span>
                                </td>
                            </tr>
                            <tr>
                                <th>{{ __('Loan::lang.total_paid') }}</th>
                                <td><span class="display_currency"
                                        data-currency_symbol="true">{{ number_format($payment->loan->total_paid, 2) }}</span>
                                </td>
                            </tr>
                            <tr>
                                <th>{{ __('Loan::lang.remaining_balance') }}</th>
                                <td><span class="display_currency"
                                        data-currency_symbol="true">{{ number_format($payment->loan->remaining_balance, 2) }}</span>
                                </td>
                            </tr>
                            <tr>
                                <th>{{ __('Loan::lang.branch') }}</th>
                                <td>{{ $payment->loan->location->name }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                @endcomponent

                <div class="text-center no-print">
                    <a href="{{ route('Loan.payments.index') }}" class="tw-dw-btn tw-dw-btn-neutral tw-text-white">
                        <i class="fa fa-arrow-left"></i> Back
                    </a>
                </div>
            </div>
        </div>
    </section>

@endsection