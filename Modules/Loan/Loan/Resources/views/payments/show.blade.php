@extends('layouts.app')

@section('title', __('Loan::lang.payment_details'))

@section('content')

@include('Loan::layouts.nav')

<div class="container my-5">
    <div class="row mb-5">
        <div class="col-md-12 text-center">
            <h1 class="display-4 text-primary font-weight-bold">{{ __('Loan::lang.payment_details') }}</h1>
            <p class="lead text-muted">{{ __('Loan::lang.view_payment_details') }}</p>
            <hr class="w-25 mx-auto border-primary">
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-10 col-lg-8">
            <div class="card shadow-lg border-0 rounded-lg overflow-hidden">
                <div class="card-header bg-gradient-primary text-white text-center py-4">
                    <h4 class="mb-0">
                        <i class="fa-solid fa-credit-card"></i> {{ __('Loan::lang.payment_info') }}
                    </h4>
                </div>
                <div class="card-body bg-light">
                    <div class="table-responsive">
                        <table class="table table-borderless table-hover">
                            <tbody>
                                <tr>
                                    <th class="text-left">📅 {{ __('Loan::lang.payment_date') }}</th>
                                    <td class="text-right text-dark font-weight-bold">{{ $payment->payment_date }}</td>
                                </tr>
                                <tr>
                                    <th class="text-left">💵 {{ __('Loan::lang.payment_amount') }}</th>
                                    <td class="text-right text-success font-weight-bold">{{ number_format($payment->amount, 2) }}</td>
                                </tr>
                                <tr>
                                    <th class="text-left">👤 {{ __('Loan::lang.recipient') }}</th>
                                    <td class="text-right text-info font-weight-bold">{{ $payment->loan->recipient_name }}</td>
                                </tr>
                                <tr>
                                    <th class="text-left">🆔 {{ __('Loan::lang.loan_id') }}</th>
                                    <td class="text-right text-warning font-weight-bold">{{ $payment->loan->id }}</td>
                                </tr>
                                <tr>
                                    <th class="text-left">💰 {{ __('Loan::lang.total_amount') }}</th>
                                    <td class="text-right text-dark font-weight-bold">{{ number_format($payment->loan->total_amount, 2) }}</td>
                                </tr>
                                <tr>
                                    <th class="text-left">💳 {{ __('Loan::lang.total_paid') }}</th>
                                    <td class="text-right text-dark font-weight-bold">{{ number_format($payment->loan->total_paid, 2) }}</td>
                                </tr>
                                <tr>
                                    <th class="text-left">💸 {{ __('Loan::lang.remaining_balance') }}</th>
                                    <td class="text-right text-dark font-weight-bold">{{ number_format($payment->loan->remaining_balance, 2) }}</td>
                                </tr>
                                <tr>
                                    <th class="text-left">🏢 {{ __('Loan::lang.branch') }}</th>
                                    <td class="text-right text-secondary font-weight-bold">{{ $payment->loan->location->name }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-white text-center">
                    <a href="{{ route('Loan.payments.index') }}" class="btn btn-primary btn-lg">
                        ⬅️ {{ __('Loan::lang.back_to_list') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection