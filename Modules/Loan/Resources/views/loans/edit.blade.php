@extends('layouts.app')

@section('title', __('Loan::lang.edit_loan'))

@section('content')

@include('Loan::layouts.nav')

<div class="container my-5">
    <div class="row mb-4">
        <div class="col-md-12 text-center">
            <h1 class="display-4 text-primary font-weight-bold">✏️ {{ __('Loan::lang.edit_loan') }}</h1>
            <p class="lead text-muted">{{ __('Loan::lang.manage_loan_details') }}</p>
            <hr class="w-25 mx-auto border-primary">
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10">
            <div class="card shadow-lg border-0">
                <div class="card-header bg-gradient-primary text-white text-center py-3">
                    <h4 class="mb-0">💼 {{ __('Loan::lang.loan_info') }}</h4>
                </div>
                <div class="card-body bg-light">
                    <form action="{{ route('Loan.loans.update', $loan->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <!-- Recipient Type -->
                        <div class="form-group mb-4">
                            <label for="recipient_type" class="font-weight-bold">👤 {{ __('Loan::lang.recipient_type') }}</label>
                            <select name="recipient_type" id="recipient_type" class="form-control" required onchange="toggleRecipientFields()">
                                <option value="customer" {{ $loan->customer_id ? 'selected' : '' }}>{{ __('Loan::lang.customer') }}</option>
                                <option value="user" {{ $loan->user_id ? 'selected' : '' }}>{{ __('Loan::lang.user') }}</option>
                            </select>
                        </div>

                        <!-- Customer -->
                        <div class="form-group mb-4" id="customer_field" style="{{ $loan->user_id ? 'display: none;' : '' }}">
                            <label for="customer_id" class="font-weight-bold">👤 {{ __('Loan::lang.customer') }}</label>
                            <select name="customer_id" id="customer_id" class="form-control" {{ $loan->customer_id ? 'required' : '' }}>
                                @foreach ($customers as $id => $name)
                                    <option value="{{ $id }}" {{ $loan->customer_id == $id ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- User -->
                        <div class="form-group mb-4" id="user_field" style="{{ $loan->user_id ? '' : 'display: none;' }}">
                            <label for="user_id" class="font-weight-bold">👤 {{ __('Loan::lang.user') }}</label>
                            <select name="user_id" id="user_id" class="form-control" {{ $loan->user_id ? 'required' : '' }}>
                                @foreach ($users as $id => $name)
                                    <option value="{{ $id }}" {{ $loan->user_id == $id ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Start Date -->
                        <div class="form-group mb-4">
                            <label for="start_date" class="font-weight-bold">📅 {{ __('Loan::lang.start_date') }}</label>
                            <input type="date" name="start_date" id="start_date" class="form-control" value="{{ $loan->start_date }}" required>
                        </div>

                        <!-- Branch -->
                        <div class="form-group mb-4">
                            <label for="location_id" class="font-weight-bold">🏢 {{ __('Loan::lang.branch') }}</label>
                            <select name="location_id" id="location_id" class="form-control" required>
                                @foreach ($locations as $id => $name)
                                    <option value="{{ $id }}" {{ $loan->location_id == $id ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Loan Type -->
                        <div class="form-group mb-4">
                            <label for="loan_type" class="font-weight-bold">📊 {{ __('Loan::lang.loan_type') }}</label>
                            <select name="loan_type" id="loan_type" class="form-control" required>
                                @foreach ($loan_types as $key => $label)
                                    <option value="{{ $key }}" {{ $loan->loan_type == $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Loan Amount -->
                        <div class="form-group mb-4">
                            <label for="amount" class="font-weight-bold">💵 {{ __('Loan::lang.amount') }}</label>
                            <input type="number" name="amount" id="amount" class="form-control" value="{{ $loan->amount }}" required oninput="updateTotalAmount()">
                        </div>

                        <!-- Loan Duration -->
                        <div class="form-group mb-4">
                            <label for="duration" class="font-weight-bold">⏳ {{ __('Loan::lang.duration') }}</label>
                            <input type="number" name="duration" id="duration" class="form-control" value="{{ $loan->duration }}" required>
                        </div>

                        <!-- Interest Rate -->
                        <div class="form-group mb-4">
                            <label for="interest_rate" class="font-weight-bold">📈 {{ __('Loan::lang.interest_rate') }}</label>
                            <input type="number" step="0.01" name="interest_rate" id="interest_rate" class="form-control" value="{{ $loan->interest_rate }}" required oninput="updateTotalAmount()">
                        </div>

                        <!-- Total Amount -->
                        <div class="form-group mb-4">
                            <label for="total_amount" class="font-weight-bold">💰 {{ __('Loan::lang.total_amount') }}</label>
                            <input type="number" step="0.01" id="total_amount" class="form-control" value="{{ $loan->total_amount }}" readonly>
                        </div>

                        <!-- Description -->
                        <div class="form-group mb-4">
                            <label for="description" class="font-weight-bold">📝 {{ __('Loan::lang.description') }}</label>
                            <textarea name="description" id="description" class="form-control" rows="3">{{ $loan->description }}</textarea>
                        </div>

                        <!-- Loan Status -->
                        <div class="form-group mb-4">
                            <label for="status" class="font-weight-bold">⚙️ {{ __('Loan::lang.status') }}</label>
                            <select name="status" id="status" class="form-control" required>
                                @foreach ($loan_statuses as $key => $label)
                                    <option value="{{ $key }}" {{ $loan->status == $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Submit Button -->
                        <div class="text-center">
                            <button type="submit" class="btn btn-success btn-lg px-5">✔️ {{ __('Loan::lang.update') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleRecipientFields() {
    const recipientType = document.getElementById('recipient_type').value;
    const customerField = document.getElementById('customer_field');
    const userField = document.getElementById('user_field');
    const customerSelect = document.getElementById('customer_id');
    const userSelect = document.getElementById('user_id');

    if (recipientType === 'customer') {
        customerField.style.display = 'block';
        userField.style.display = 'none';
        customerSelect.required = true;
        userSelect.required = false;
        userSelect.value = '';
    } else {
        customerField.style.display = 'none';
        userField.style.display = 'block';
        customerSelect.required = false;
        userSelect.required = true;
        customerSelect.value = '';
    }
}

function updateTotalAmount() {
    const amount = parseFloat(document.getElementById('amount').value) || 0;
    const interestRate = parseFloat(document.getElementById('interest_rate').value) || 0;
    const totalAmount = amount + (amount * (interestRate / 100));
    document.getElementById('total_amount').value = totalAmount.toFixed(2);
}

// Initialize total amount on page load
document.addEventListener('DOMContentLoaded', function() {
    updateTotalAmount();
});
</script>

@endsection