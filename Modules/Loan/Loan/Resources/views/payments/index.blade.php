@extends('layouts.app')

@section('title', __('Loan::lang.payments'))

@section('content')

@include('Loan::layouts.nav')

<div class="container my-5">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-md-12 text-center">
            <h1 class="display-4 text-primary font-weight-bold">💵 {{ __('Loan::lang.payments') }}</h1>
            <p class="lead text-muted">{{ __('Loan::lang.manage_all_payments') }}</p>
            <hr class="w-25 mx-auto border-primary">
        </div>
    </div>

    <!-- Filters Section -->
    <div class="card shadow-lg mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0">🔍 {{ __('Loan::lang.filters') }}</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('Loan.payments.index') }}" method="GET">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="recipient_name">👤 {{ __('Loan::lang.recipient_name') }}</label>
                            <input type="text" name="recipient_name" id="recipient_name" class="form-control"
                                   value="{{ $request->recipient_name ?? '' }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="loan_id">🆔 {{ __('Loan::lang.loan_id') }}</label>
                            <input type="text" name="loan_id" id="loan_id" class="form-control"
                                   value="{{ $request->loan_id ?? '' }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="payment_date">📅 {{ __('Loan::lang.payment_date') }}</label>
                            <input type="date" name="payment_date" id="payment_date" class="form-control"
                                   value="{{ $request->payment_date ?? '' }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="total_amount_min">💰 {{ __('Loan::lang.total_amount_min') }}</label>
                            <input type="number" step="0.01" name="total_amount_min" id="total_amount_min" class="form-control"
                                   value="{{ $request->total_amount_min ?? '' }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="total_amount_max">💰 {{ __('Loan::lang.total_amount_max') }}</label>
                            <input type="number" step="0.01" name="total_amount_max" id="total_amount_max" class="form-control"
                                   value="{{ $request->total_amount_max ?? '' }}">
                        </div>
                    </div>
                </div>
                <div class="text-center">
                    <button type="submit" class="btn btn-primary">✔️ {{ __('Loan::lang.apply_filters') }}</button>
                    <a href="{{ route('Loan.payments.index') }}" class="btn btn-secondary">🔄 {{ __('Loan::lang.reset') }}</a>
                    <a href="{{ route('Loan.payments.export.excel') }}" class="btn btn-success">📊 {{ __('Loan::lang.export_excel') }}</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Payments Table -->
    <div class="card shadow-lg border-0">
        <div class="card-header bg-info text-white text-center">
            <h4 class="mb-0">📋 {{ __('Loan::lang.all_payments') }}</h4>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="payments-table" class="table table-striped table-hover">
                    <thead class="bg-primary text-white">
                        <tr>
                            <th>#</th>
                            <th>📅 {{ __('Loan::lang.payment_date') }}</th>
                            <th>💵 {{ __('Loan::lang.payment_amount') }}</th>
                            <th>👤 {{ __('Loan::lang.recipient') }}</th>
                            <th>🆔 {{ __('Loan::lang.loan_id') }}</th>
                            <th>💰 {{ __('Loan::lang.total_amount') }}</th>
                            <th>💳 {{ __('Loan::lang.total_paid') }}</th>
                            <th>💸 {{ __('Loan::lang.remaining_balance') }}</th>
                            <th>⚙️ {{ __('Loan::lang.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($payments as $payment)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $payment->payment_date }}</td>
                                <td class="text-success font-weight-bold">{{ number_format($payment->amount, 2) }}</td>
                                <td>{{ $payment->loan->recipient_name }}</td>
                                <td>{{ $payment->loan->id }}</td>
                                <td>{{ number_format($payment->loan->total_amount, 2) }}</td>
                                <td>{{ number_format($payment->loan->total_paid, 2) }}</td>
                                <td>{{ number_format($payment->loan->remaining_balance, 2) }}</td>
                                <td>
                                    <a href="{{ route('Loan.payments.show', $payment->id) }}" class="btn btn-info btn-sm">
                                        👁️ {{ __('Loan::lang.view') }}
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted">🚫 {{ __('Loan::lang.no_payments_found') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Pagination -->
    <div class="d-flex justify-content-center mt-4">
        {{ $payments->links() }}
    </div>
</div>

@endsection