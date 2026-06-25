@extends('layouts.app')

@section('title', __('Loan::lang.settings'))

@section('content')
    @include('Loan::layouts.nav')

    <!-- Content Header (Page header) -->
    <section class="content-header no-print">
        <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">
            {{ __('Loan::lang.settings') }}
        </h1>
    </section>

    <!-- Main content -->
    <section class="content no-print">
        <div class="row">
            <div class="col-md-12">
                @component('components.widget', ['class' => 'box-primary', 'title' => __('Loan::lang.update_settings')])
                    <form action="{{ route('Loan.settings.update') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <!-- Interest Rate -->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="interest_rate">{{ __('Loan::lang.interest_rate') }} (%)</label>
                                    <input type="number" step="0.01" name="interest_rate" id="interest_rate" class="form-control"
                                           value="{{ $settings->interest_rate ?? 0 }}" required>
                                </div>
                            </div>

                            <!-- Loan Limit -->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="loan_limit">{{ __('Loan::lang.loan_limit') }}</label>
                                    <input type="number" step="0.01" name="loan_limit" id="loan_limit" class="form-control"
                                           value="{{ $settings->loan_limit ?? 0 }}" required>
                                </div>
                            </div>

                            <!-- Maximum Loan Duration -->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="max_loan_duration">{{ __('Loan::lang.max_loan_duration') }}</label>
                                    <input type="number" name="max_loan_duration" id="max_loan_duration" class="form-control"
                                           value="{{ $settings->max_loan_duration ?? 12 }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Administrative Fee -->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="administrative_fee">{{ __('Loan::lang.administrative_fee') }} ({{ __('Loan::lang.currency') }})</label>
                                    <input type="number" step="0.01" name="administrative_fee" id="administrative_fee" class="form-control"
                                           value="{{ $settings->administrative_fee ?? 0 }}" required>
                                </div>
                            </div>

                            <!-- Interest Type -->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="interest_type">{{ __('Loan::lang.interest_type') }}</label>
                                    <select name="interest_type" id="interest_type" class="form-control">
                                        <option value="none" {{ ($settings->interest_type ?? 'none') == 'none' ? 'selected' : '' }}>
                                            {{ __('Loan::lang.no_interest') }}
                                        </option>
                                        <option value="simple" {{ ($settings->interest_type ?? '') == 'simple' ? 'selected' : '' }}>
                                            {{ __('Loan::lang.simple_interest') }}
                                        </option>
                                        <option value="compound" {{ ($settings->interest_type ?? '') == 'compound' ? 'selected' : '' }}>
                                            {{ __('Loan::lang.compound_interest') }}
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <!-- Allow Early Payment -->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="allow_early_payment">{{ __('Loan::lang.allow_early_payment') }}</label>
                                    <select name="allow_early_payment" id="allow_early_payment" class="form-control">
                                        <option value="1" {{ ($settings->allow_early_payment ?? 1) == 1 ? 'selected' : '' }}>
                                            {{ __('Loan::lang.yes') }}
                                        </option>
                                        <option value="0" {{ ($settings->allow_early_payment ?? 1) == 0 ? 'selected' : '' }}>
                                            {{ __('Loan::lang.no') }}
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="row">
                            <div class="col-md-12 text-center" style="margin-top: 15px;">
                                <button type="submit" class="tw-dw-btn tw-dw-btn-primary tw-text-white">{{ __('Loan::lang.save') }}</button>
                            </div>
                        </div>
                    </form>
                @endcomponent
            </div>
        </div>
    </section>
@endsection
