@extends('layouts.app')

@section('title', __('Loan::lang.settings'))

@section('content')
@include('Loan::layouts.nav')

<div class="container my-5">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-md-12 text-center">
            <h1 class="display-4 text-primary font-weight-bold">⚙️ {{ __('Loan::lang.settings') }}</h1>
            <p class="lead text-muted">{{ __('Loan::lang.manage_settings') }}</p>
            <hr class="w-25 mx-auto border-primary">
        </div>
    </div>

    <!-- Form Section -->
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-lg border-0 rounded-lg">
                <div class="card-header bg-gradient-primary text-white text-center py-3">
                    <h4 class="mb-0">🛠️ {{ __('Loan::lang.update_settings') }}</h4>
                </div>
                <div class="card-body bg-light">
                    <form action="{{ route('Loan.settings.update') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <!-- Interest Rate -->
                        <div class="form-group mb-4">
                            <label for="interest_rate" class="font-weight-bold">📈 {{ __('Loan::lang.interest_rate') }} (%)</label>
                            <input type="number" step="0.01" name="interest_rate" id="interest_rate" class="form-control"
                                   value="{{ $settings->interest_rate ?? 0 }}" required>
                        </div>

                        <!-- Loan Limit -->
                        <div class="form-group mb-4">
                            <label for="loan_limit" class="font-weight-bold">💵 {{ __('Loan::lang.loan_limit') }}</label>
                            <input type="number" step="0.01" name="loan_limit" id="loan_limit" class="form-control"
                                   value="{{ $settings->loan_limit ?? 0 }}" required>
                        </div>

                        <!-- Maximum Loan Duration -->
                        <div class="form-group mb-4">
                            <label for="max_loan_duration" class="font-weight-bold">⏳ {{ __('Loan::lang.max_loan_duration') }} ({{ __('Loan::lang.months') }})</label>
                            <input type="number" name="max_loan_duration" id="max_loan_duration" class="form-control"
                                   value="{{ $settings->max_loan_duration ?? 12 }}" required>
                        </div>

                        <!-- Administrative Fee -->
                        <div class="form-group mb-4">
                            <label for="administrative_fee" class="font-weight-bold">💼 {{ __('Loan::lang.administrative_fee') }} ({{ __('Loan::lang.currency') }})</label>
                            <input type="number" step="0.01" name="administrative_fee" id="administrative_fee" class="form-control"
                                   value="{{ $settings->administrative_fee ?? 0 }}" required>
                        </div>

                        <!-- Interest Type -->
                        <div class="form-group mb-4">
                            <label for="interest_type" class="font-weight-bold">📊 {{ __('Loan::lang.interest_type') }}</label>
                            <select name="interest_type" id="interest_type" class="form-control">
                                <option value="none" {{ $settings->interest_type == 'none' ? 'selected' : '' }}>
                                    {{ __('Loan::lang.no_interest') }}
                                </option>
                                <option value="simple" {{ $settings->interest_type == 'simple' ? 'selected' : '' }}>
                                    {{ __('Loan::lang.simple_interest') }}
                                </option>
                                <option value="compound" {{ $settings->interest_type == 'compound' ? 'selected' : '' }}>
                                    {{ __('Loan::lang.compound_interest') }}
                                </option>
                            </select>
                        </div>

                        <!-- Allow Early Payment -->
                        <div class="form-group mb-4">
                            <label for="allow_early_payment" class="font-weight-bold">🕒 {{ __('Loan::lang.allow_early_payment') }}</label>
                            <select name="allow_early_payment" id="allow_early_payment" class="form-control">
                                <option value="1" {{ $settings->allow_early_payment == 1 ? 'selected' : '' }}>
                                    {{ __('Loan::lang.yes') }}
                                </option>
                                <option value="0" {{ $settings->allow_early_payment == 0 ? 'selected' : '' }}>
                                    {{ __('Loan::lang.no') }}
                                </option>
                            </select>
                        </div>

                        <!-- Submit Button -->
                        <div class="text-center">
                            <button type="submit" class="btn btn-primary btn-lg px-5">💾 {{ __('Loan::lang.save') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
