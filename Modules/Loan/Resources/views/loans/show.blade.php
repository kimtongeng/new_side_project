@extends('layouts.app')

@section('title', __('Loan::lang.loan_details'))

@section('content')

    @include('Loan::layouts.nav')

    <!-- Header Section -->
    <section class="content-header no-print">
        <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">
            {{ __('Loan::lang.loan_details') }}
        </h1>
    </section>

    <!-- Main content -->
    <section class="content no-print">
        @component('components.widget', ['class' => 'box-primary', 'title' => __('Loan::lang.loan_info')])
        <div class="row">
            <div class="col-md-4 col-sm-6">
                <strong>{{ __('Loan::lang.recipient') }}:</strong>
                <p class="text-muted">{{ $loan->recipient_name }}</p>

                <strong>{{ __('Loan::lang.start_date') }}:</strong>
                <p class="text-muted">{{ $loan->start_date }}</p>

                <strong>{{ __('Loan::lang.amount') }}:</strong>
                <p class="text-muted">{{ number_format($loan->amount, 2) }}</p>

                <strong>{{ __('Loan::lang.total_amount') }}:</strong>
                <p class="text-muted">{{ number_format($loan->total_amount, 2) }}</p>
            </div>
            <div class="col-md-4 col-sm-6">
                <strong>{{ __('Loan::lang.duration') }}:</strong>
                <p class="text-muted">{{ $loan->duration }} {{ __('Loan::lang.months') }}</p>

                <strong>{{ __('Loan::lang.interest_rate') }}:</strong>
                <p class="text-muted">{{ $loan->interest_rate }}%</p>

                <strong>{{ __('Loan::lang.loan_type') }}:</strong>
                <p class="text-muted">
                    <span class="label bg-info">
                        {{ $loan->loan_type == 'personal' ? __('Loan::lang.personal_loan') : __('Loan::lang.business_loan') }}
                    </span>
                </p>

                <strong>{{ __('Loan::lang.total_paid') }}:</strong>
                <p class="text-muted">{{ number_format($loan->total_paid, 2) }}</p>
            </div>
            <div class="col-md-4 col-sm-6">
                <strong>{{ __('Loan::lang.status') }}:</strong>
                <p class="text-muted">
                    @if ($loan->status == 'active')
                        <span class="label"
                            style="background-color: #28a745; color: #fff; font-size: 11px; padding: 4px 8px; border-radius: 12px; display: inline-block;">
                            {{ __('Loan::lang.active') }}
                        </span>
                    @elseif ($loan->status == 'partially_paid')
                        <span class="label"
                            style="background-color: #ff9800; color: #fff; font-size: 11px; padding: 4px 8px; border-radius: 12px; display: inline-block;">
                            {{ __('Loan::lang.partially_paid') }}
                        </span>
                    @elseif ($loan->status == 'fully_paid')
                        <span class="label"
                            style="background-color: #007bff; color: #fff; font-size: 11px; padding: 4px 8px; border-radius: 12px; display: inline-block;">
                            {{ __('Loan::lang.fully_paid') }}
                        </span>
                    @endif
                </p>

                <strong>{{ __('Loan::lang.remaining_balance') }}:</strong>
                <p class="text-muted">{{ number_format($loan->remaining_balance, 2) }}</p>

                <strong>{{ __('Loan::lang.branch') }}:</strong>
                <p class="text-muted">{{ $loan->location->name }}</p>

                <strong>{{ __('Loan::lang.description') }}:</strong>
                <p class="text-muted">{{ $loan->description ?? __('Loan::lang.no_description') }}</p>
            </div>
        </div>
        @endcomponent

        <div class="row">
            <div class="col-md-6">
                @component('components.widget', ['class' => 'box-primary', 'title' => __('Loan::lang.payment_history')])
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>{{ __('Loan::lang.payment_date') }}</th>
                                <th>{{ __('Loan::lang.payment_amount') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($loan->payments as $payment)
                                <tr>
                                    <td>{{ $payment->payment_date }}</td>
                                    <td>{{ number_format($payment->amount, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center text-muted">{{ __('Loan::lang.no_payments') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @endcomponent
            </div>

            <div class="col-md-6">
                @component('components.widget', ['class' => 'box-primary', 'title' => __('Loan::lang.add_payment')])
                @if ($loan->isFullyPaid())
                    <div class="alert alert-success text-center">
                        <strong>{{ __('Loan::lang.loan_fully_paid') }}</strong>
                    </div>
                @else
                    <form action="{{ route('Loan.payments.store', $loan->id) }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="payment_date">{{ __('Loan::lang.payment_date') }}:</label>
                            <input type="date" name="payment_date" id="payment_date" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="amount">{{ __('Loan::lang.payment_amount') }}:</label>
                            <input type="number" step="0.01" name="amount" id="amount" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="account_id">{{ __('Loan::lang.account') }}:</label>
                            <select name="account_id" id="account_id" class="form-control select2" style="width:100%" required>
                                @foreach ($accounts as $id => $account)
                                    <option value="{{ $id }}">{{ $account }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit"
                            class="tw-dw-btn tw-dw-btn-success tw-text-white">{{ __('Loan::lang.submit_payment') }}</button>
                    </form>
                @endif
                @endcomponent
            </div>
        </div>

        <div class="text-center no-print">
            <a href="{{ route('Loan.loans.index') }}" class="tw-dw-btn tw-dw-btn-neutral tw-text-white">
                <i class="fa fa-arrow-left"></i> Back
            </a>
        </div>
    </section>

@endsection