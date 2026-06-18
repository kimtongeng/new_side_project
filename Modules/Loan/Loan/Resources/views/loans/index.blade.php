@extends('layouts.app')

@section('title', __('Loan::lang.loans'))

@section('content')

@include('Loan::layouts.nav')

<div class="container my-5">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-md-12 text-center">
            <h1 class="display-4 text-primary font-weight-bold">💵 {{ __('Loan::lang.loans') }}</h1>
            <p class="lead text-muted">{{ __('Loan::lang.manage_loan_details') }}</p>
            <hr class="w-25 mx-auto border-primary">
        </div>
    </div>

    <!-- Add New Loan Button -->
    <div class="text-right mb-4">
        <a href="{{ route('Loan.loans.create') }}" class="btn btn-success btn-lg">
            ➕ {{ __('Loan::lang.add_new_loan') }}
        </a>
    </div>

    <!-- Filters Section -->
    <div class="card shadow-lg mb-4">
        <div class="card-header bg-gradient-primary text-white">
            <h5 class="mb-0">🔍 {{ __('Loan::lang.filters') }}</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('Loan.loans.index') }}" method="GET">
                <div class="row">
                    <div class="col-md-3">
                        <input type="text" name="recipient_name" value="{{ request('recipient_name') }}" class="form-control"
                               placeholder="👤 {{ __('Loan::lang.recipient_name') }}">
                    </div>
                    <div class="col-md-3">
                        <select name="loan_type" class="form-control">
                            <option value="">{{ __('Loan::lang.all_loan_types') }}</option>
                            <option value="personal" {{ request('loan_type') == 'personal' ? 'selected' : '' }}>
                                {{ __('Loan::lang.personal_loan') }}
                            </option>
                            <option value="business" {{ request('loan_type') == 'business' ? 'selected' : '' }}>
                                {{ __('Loan::lang.business_loan') }}
                            </option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="status" class="form-control">
                            <option value="">{{ __('Loan::lang.all_statuses') }}</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>
                                {{ __('Loan::lang.active') }}
                            </option>
                            <option value="partially_paid" {{ request('status') == 'partially_paid' ? 'selected' : '' }}>
                                {{ __('Loan::lang.partially_paid') }}
                            </option>
                            <option value="fully_paid" {{ request('status') == 'fully_paid' ? 'selected' : '' }}>
                                {{ __('Loan::lang.fully_paid') }}
                            </option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary btn-block">✔️ {{ __('Loan::lang.filter') }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Loans Table -->
    <div class="card shadow-lg border-0">
        <div class="card-header bg-info text-dark text-center">
            <h4 class="mb-0">📋 {{ __('Loan::lang.manage_loan_details') }}</h4>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="bg-primary text-white">
                        <tr>
                            <th>👤 {{ __('Loan::lang.recipient_name') }}</th>
                            <th>📅 {{ __('Loan::lang.start_date') }}</th>
                            <th>💰 {{ __('Loan::lang.amount') }}</th>
                            <th>💵 {{ __('Loan::lang.total_amount') }}</th>
                            <th>⏳ {{ __('Loan::lang.duration') }}</th>
                            <th>📈 {{ __('Loan::lang.interest_rate') }}</th>
                            <th>📊 {{ __('Loan::lang.loan_type') }}</th>
                            <th>⚙️ {{ __('Loan::lang.status') }}</th>
                            <th>🏢 {{ __('Loan::lang.branch') }}</th>
                            <th>📝 {{ __('Loan::lang.description') }}</th>
                            <th>🔧 {{ __('Loan::lang.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($loans as $loan)
                            <tr>
                                <td>{{ $loan->recipient_name }}</td>
                                <td>{{ $loan->start_date }}</td>
                                <td>{{ number_format($loan->amount, 3) }}</td>
                                <td>{{ number_format($loan->total_amount, 3) }}</td>
                                <td>{{ $loan->duration_count }} {{ __('Loan::lang.months') }}</td>
                                <td>{{ $loan->interest_rate }}%</td>
                                <td>{{ $loan->loan_type == 'personal' ? __('Loan::lang.personal_loan') : __('Loan::lang.business_loan') }}</td>
                                <td>
                                    @if ($loan->status == 'active')
                                        <span class="badge badge-success">✔️ {{ __('Loan::lang.active') }}</span>
                                    @elseif ($loan->status == 'partially_paid')
                                        <span class="badge badge-warning">💰 {{ __('Loan::lang.partially_paid') }}</span>
                                    @elseif ($loan->status == 'fully_paid')
                                        <span class="badge badge-secondary">✅ {{ __('Loan::lang.fully_completed') }}</span>
                                    @endif
                                </td>
                                <td>{{ $loan->location->name }}</td>
                                <td>{{ $loan->description ?? '—' }}</td>
                                <td>
                                    <a href="{{ route('Loan.loans.show', $loan->id) }}" class="badge badge-primary">👁️ {{ __('Loan::lang.details') }}</a>
                                    <a href="{{ route('Loan.loans.edit', $loan->id) }}" class="badge badge-warning">✏️ {{ __('Loan::lang.edit') }}</a>
                                    <form action="{{ route('Loan.loans.destroy', $loan->id) }}" method="POST" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="badge badge-danger" onclick="return confirm('{{ __('Loan::lang.confirm_delete') }}')">
                                            🗑️ {{ __('Loan::lang.delete') }}
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="text-center text-muted">🚫 {{ __('Loan::lang.no_loans_found') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Pagination -->
    <div class="d-flex justify-content-center mt-4">
        {{ $loans->links() }}
    </div>
</div>
@endsection