@extends('layouts.app')

@section('title', __('Loan::lang.loan_details'))

@section('content')

@include('Loan::layouts.nav')

<div class="container my-5">
    <div class="row mb-5">
        <div class="col-md-12 text-center">
            <h1 class="display-4 text-primary font-weight-bold">📋 {{ __('Loan::lang.loan_details') }}</h1>
            <p class="lead text-muted">{{ __('Loan::lang.manage_loan_details') }}</p>
            <hr class="w-25 mx-auto border-primary">
        </div>
    </div>

    <div class="card mb-5 shadow-lg border-0">
        <div class="card-header bg-primary text-white py-3 text-center">
            <h4 class="mb-0">💼 {{ __('Loan::lang.loan_info') }}</h4>
        </div>
        <div class="card-body bg-light">
            <div class="row mb-4">
                <div class="col-lg-6 col-md-12">
                    <div class="info-item mb-3">
                        <strong>👤 {{ __('Loan::lang.recipient') }}:</strong>
                        <span class="text-dark">{{ $loan->recipient_name }}</span>
                    </div>
                    <div class="info-item mb-3">
                        <strong>📅 {{ __('Loan::lang.start_date') }}:</strong>
                        <span class="text-dark">{{ $loan->start_date }}</span>
                    </div>
                    <div class="info-item mb-3">
                        <strong>💵 {{ __('Loan::lang.amount') }}:</strong>
                        <span class="text-dark">{{ number_format($loan->amount, 2) }}</span>
                    </div>
                    <div class="info-item mb-3">
                        <strong>💰 {{ __('Loan::lang.total_amount') }}:</strong>
                        <span class="text-dark">{{ number_format($loan->total_amount, 2) }}</span>
                    </div>
                </div>
                <div class="col-lg-6 col-md-12">
                    <div class="info-item mb-3">
                        <strong>⏳ {{ __('Loan::lang.duration') }}:</strong>
                        <span class="text-dark">{{ $loan->duration }} {{ __('Loan::lang.months') }}</span>
                    </div>
                    <div class="info-item mb-3">
                        <strong>📈 {{ __('Loan::lang.interest_rate') }}:</strong>
                        <span class="text-dark">{{ $loan->interest_rate }}%</span>
                    </div>
                    <div class="info-item mb-3">
                        <strong>📊 {{ __('Loan::lang.loan_type') }}:</strong>
                        <span class="badge badge-info">
                            {{ $loan->loan_type == 'personal' ? __('Loan::lang.personal_loan') : __('Loan::lang.business_loan') }}
                        </span>
                    </div>
                    <div class="info-item mb-3">
                        <strong>💳 {{ __('Loan::lang.total_paid') }}:</strong>
                        <span class="text-dark">{{ number_format($loan->total_paid, 2) }}</span>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6 col-md-12">
                    <div class="info-item mb-3">
                        <strong>⚙️ {{ __('Loan::lang.status') }}:</strong>
                        @if ($loan->status == 'active')
                            <span class="badge badge-success">✔️ {{ __('Loan::lang.active') }}</span>
                        @elseif ($loan->status == 'partially_paid')
                            <span class="badge badge-warning">💰 {{ __('Loan::lang.partially_paid') }}</span>
                        @elseif ($loan->status == 'fully_paid')
                            <span class="badge badge-secondary">✅ {{ __('Loan::lang.fully_paid') }}</span>
                        @endif
                    </div>
                    <div class="info-item mb-3">
                        <strong>💸 {{ __('Loan::lang.remaining_balance') }}:</strong>
                        <span class="text-dark">{{ number_format($loan->remaining_balance, 2) }}</span>
                    </div>
                </div>
                <div class="col-lg-6 col-md-12">
                    <div class="info-item mb-3">
                        <strong>🏢 {{ __('Loan::lang.branch') }}:</strong>
                        <span class="text-dark">{{ $loan->location->name }}</span>
                    </div>
                    <div class="info-item mb-3">
                        <strong>📝 {{ __('Loan::lang.description') }}:</strong>
                        <span class="text-dark">{{ $loan->description ?? __('Loan::lang.no_description') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-5 shadow-lg border-0">
        <div class="card-header bg-info text-white py-3 text-center">
            <h4 class="mb-0">💳 {{ __('Loan::lang.loan_payments') }}</h4>
        </div>
        <div class="card-body bg-light">
            <h5 class="mb-4">📜 {{ __('Loan::lang.payment_history') }}</h5>
            <table class="table table-bordered table-hover">
                <thead class="bg-primary text-white">
                    <tr>
                        <th>📅 {{ __('Loan::lang.payment_date') }}</th>
                        <th>💵 {{ __('Loan::lang.payment_amount') }}</th>
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
                            <td colspan="2" class="text-center text-muted">🚫 {{ __('Loan::lang.no_payments') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            @if ($loan->isFullyPaid())
                <div class="alert alert-success text-center mt-4">
                    <strong>🎉 {{ __('Loan::lang.loan_fully_paid') }}</strong>
                </div>
            @else
                <h5 class="mt-4">➕ {{ __('Loan::lang.add_payment') }}</h5>
                <form action="{{ route('Loan.payments.store', $loan->id) }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="payment_date">📅 {{ __('Loan::lang.payment_date') }}</label>
                                <input type="date" name="payment_date" id="payment_date" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="amount">💵 {{ __('Loan::lang.payment_amount') }}</label>
                                <input type="number" step="0.01" name="amount" id="amount" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="account_id">🏦 {{ __('Loan::lang.account') }}</label>
                                <select name="account_id" id="account_id" class="form-control" required>
                                    @foreach ($accounts as $id => $account)
                                        <option value="{{ $id }}">{{ $account }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-success">✔️ {{ __('Loan::lang.submit_payment') }}</button>
                </form>
            @endif
        </div>
    </div>

    <div class="text-center">
        <a href="{{ route('Loan.loans.index') }}" class="btn btn-secondary btn-lg">⬅️ {{ __('Loan::lang.back_to_list') }}</a>
    </div>
</div>

@endsection