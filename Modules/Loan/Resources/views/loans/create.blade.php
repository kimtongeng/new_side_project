@extends('layouts.app')

@section('title', __('Loan::lang.add_new_loan'))

@section('content')

    @include('Loan::layouts.nav')

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap');

        .premium-wrapper {
            font-family: 'Outfit', sans-serif !important;
            background-color: #f8fafc;
            padding-top: 2rem;
            padding-bottom: 4rem;
        }

        .loan-card {
            margin-bottom: 10px;
            border-radius: 20px !important;
            overflow: hidden;
            background: #ffffff;
            border: none !important;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03), 0 10px 30px rgba(0, 0, 0, 0.02) !important;
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .loan-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 30px rgba(79, 70, 229, 0.08), 0 20px 40px rgba(0, 0, 0, 0.04) !important;
        }

        .loan-card-header-1 {
            background: linear-gradient(135deg, #4f46e5, #6366f1);
            color: white;
            padding: 1.25rem 1.5rem !important;
        }

        .loan-card-header-2 {
            background: linear-gradient(135deg, #0ea5e9, #2563eb);
            color: white;
            padding: 1.25rem 1.5rem !important;
        }

        .loan-card-header-3 {
            background: linear-gradient(135deg, #64748b, #475569);
            color: white;
            padding: 1.25rem 1.5rem !important;
        }

        .form-control-custom {
            border-radius: 12px !important;
            border: 1px solid #e2e8f0 !important;
            padding: 12px 16px !important;
            height: auto !important;
            font-size: 15px !important;
            color: #1e293b !important;
            background-color: #f8fafc !important;
            transition: all 0.25s ease-in-out !important;
        }

        .form-control-custom:focus {
            border-color: #6366f1 !important;
            background-color: #ffffff !important;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.12) !important;
            outline: none !important;
        }

        /* Select2 custom styles to fit the premium look */
        .select2-container--default .select2-selection--single {
            background-color: #f8fafc !important;
            border: 1px solid #e2e8f0 !important;
            border-radius: 12px !important;
            height: 50px !important;
            padding: 11px 16px !important;
            font-size: 15px !important;
            transition: all 0.25s ease-in-out !important;
        }

        .select2-container--default .select2-selection--single:focus,
        .select2-container--default.select2-container--focus .select2-selection--single {
            border-color: #6366f1 !important;
            background-color: #ffffff !important;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.12) !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 28px !important;
            padding-left: 0 !important;
            color: #1e293b !important;
            font-weight: 500;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 48px !important;
            right: 14px !important;
        }

        .form-group {
            position: relative;
        }

        select.select2-hidden-accessible {
            position: absolute !important;
            bottom: 4px !important;
            left: 50% !important;
            width: 1px !important;
            height: 1px !important;
            opacity: 0 !important;
            display: block !important;
            pointer-events: none !important;
        }

        .sticky-calculator {
            position: -webkit-sticky;
            position: sticky;
            top: 20px;
        }

        .calculator-card {
            border-radius: 24px !important;
            border: none !important;
            background: #ffffff;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.04), 0 1px 3px rgba(0, 0, 0, 0.02) !important;
            overflow: hidden;
        }

        .calculator-header {
            background: linear-gradient(135deg, #1e1b4b, #312e81);
            color: #ffffff;
            padding: 1.75rem 1.5rem;
        }

        .calculator-display {
            background: #f8fafc;
            border-radius: 16px;
            padding: 1.25rem;
            border: 1px solid #f1f5f9;
        }

        .btn-gradient-primary {
            background: linear-gradient(135deg, #4f46e5, #6366f1);
            color: white !important;
            border: none;
            border-radius: 14px;
            font-weight: 700;
            letter-spacing: 0.3px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 10px 20px -5px rgba(79, 70, 229, 0.3);
        }

        .btn-gradient-primary:hover {
            background: linear-gradient(135deg, #4338ca, #4f46e5);
            transform: translateY(-2px);
            box-shadow: 0 15px 25px -5px rgba(79, 70, 229, 0.4);
        }

        .btn-gradient-primary:active {
            transform: translateY(0);
        }

        .info-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 8px;
        }
    </style>

    <div class="premium-wrapper">
        <div class="container-fluid px-4">
            <!-- Title Banner -->
            <div class="row mb-5">
                <div class="col-md-12 text-center">
                    <h1 class="display-4 font-weight-bold"
                        style="background: linear-gradient(135deg, #4f46e5, #0ea5e9); -webkit-background-clip: text; -webkit-text-fill-color: transparent; letter-spacing: -0.5px;">
                        ➕ {{ __('Loan::lang.add_new_loan') }}</h1>
                    <p class="lead text-muted" style="font-size: 1.1rem; font-weight: 400;">
                        {{ __('Loan::lang.manage_loan_details') }}
                    </p>
                    <div class="mx-auto"
                        style="width: 60px; height: 4px; background: linear-gradient(135deg, #4f46e5, #0ea5e9); border-radius: 2px;">
                    </div>
                </div>
            </div>

            {!! Form::open(['url' => route('Loan.loans.store'), 'method' => 'POST']) !!}
            <div class="row">
                <!-- Left Side: Loan Configuration Forms -->
                <div class="col-lg-8 col-md-12 mb-4">

                    <!-- Card 1: Recipient & Location Info -->
                    <div class="card loan-card mb-4">
                        <div class="card-header loan-card-header-1">
                            <h4 class="mb-0 font-weight-bold d-flex align-items-center text-white"
                                style="color: #ffffff !important;">
                                <span class="mr-2">👤</span> {{ __('Loan::lang.recipient_type') }} &
                                {{ __('Loan::lang.branch') }}
                            </h4>
                        </div>
                        <div class="card-body p-5" style="padding: 10px;">
                            <div class="row">
                                <!-- Recipient Type -->
                                <div class="col-md-6 mb-4">
                                    <div class="form-group">
                                        {!! Form::label('recipient_type', '👤 ' . __('Loan::lang.recipient_type'), ['class' => 'font-weight-bold text-muted mb-2']) !!}
                                        {!! Form::select('recipient_type', [
        'customer' => __('Loan::lang.customer'),
        'user' => __('Loan::lang.user')
    ], null, [
        'id' => 'recipient_type',
        'class' => 'form-control select2',
        'style' => 'width:100%',
        'required' => true,
        'onchange' => 'toggleRecipientFields()'
    ]) !!}
                                    </div>
                                </div>

                                <!-- Customer -->
                                <div class="col-md-6 mb-4" id="customer_field">
                                    <div class="form-group">
                                        {!! Form::label('customer_id', '👤 ' . __('Loan::lang.customer'), ['class' => 'font-weight-bold text-muted mb-2']) !!}
                                        {!! Form::select('customer_id', $customers, null, [
        'id' => 'customer_id',
        'class' => 'form-control select2',
        'style' => 'width:100%',
        'placeholder' => __('messages.please_select')
    ]) !!}
                                    </div>
                                </div>

                                <!-- User -->
                                <div class="col-md-6 mb-4" id="user_field" style="display: none;">
                                    <div class="form-group">
                                        {!! Form::label('user_id', '👤 ' . __('Loan::lang.user'), ['class' => 'font-weight-bold text-muted mb-2']) !!}
                                        {!! Form::select('user_id', $users, null, [
        'id' => 'user_id',
        'class' => 'form-control select2',
        'style' => 'width:100%',
        'placeholder' => __('messages.please_select')
    ]) !!}
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <!-- Branch -->
                                <div class="col-md-6 mb-4">
                                    <div class="form-group">
                                        {!! Form::label('location_id', '🏢 ' . __('Loan::lang.branch'), ['class' => 'font-weight-bold text-muted mb-2']) !!}
                                        {!! Form::select('location_id', $locations, null, [
        'id' => 'location_id',
        'class' => 'form-control select2',
        'style' => 'width:100%',
        'placeholder' => __('messages.please_select'),
        'required' => true,
    ]) !!}
                                    </div>
                                </div>

                                <!-- Account -->
                                <div class="col-md-6 mb-4">
                                    <div class="form-group">
                                        {!! Form::label('account_id', '🏦 ' . __('Loan::lang.account'), ['class' => 'font-weight-bold text-muted mb-2']) !!}
                                        {!! Form::select('account_id', $accounts, null, [
        'id' => 'account_id',
        'class' => 'form-control select2',
        'style' => 'width:100%',
        'placeholder' => __('messages.please_select'),
        'required' => true
    ]) !!}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card 2: Loan Parameters Config -->
                    <div class="card loan-card mb-4">
                        <div class="card-header loan-card-header-2">
                            <h4 class="mb-0 font-weight-bold d-flex align-items-center text-white"
                                style="color: #ffffff !important;">
                                <span class="mr-2">🪙</span> {{ __('Loan::lang.loan_info') }} &
                                {{ __('Loan::lang.amount') }}
                            </h4>
                        </div>
                        <div class="card-body p-5" style="padding: 10px;">
                            <div class="row">
                                <!-- Start Date -->
                                <div class="col-md-6 mb-4">
                                    <div class="form-group">
                                        {!! Form::label('start_date', '📅 ' . __('Loan::lang.start_date'), ['class' => 'font-weight-bold text-muted mb-2']) !!}
                                        {!! Form::text('start_date', @format_date('now'), ['id' => 'start_date', 'class' => 'form-control form-control-custom', 'required' => true]) !!}
                                    </div>
                                </div>

                                <!-- Loan Type -->
                                <div class="col-md-6 mb-4">
                                    <div class="form-group">
                                        {!! Form::label('loan_type', '📊 ' . __('Loan::lang.loan_type'), ['class' => 'font-weight-bold text-muted mb-2']) !!}
                                        {!! Form::select('loan_type', $loan_types, null, [
        'id' => 'loan_type',
        'class' => 'form-control select2',
        'style' => 'width:100%',
        'placeholder' => __('messages.please_select'),
        'required' => true
    ]) !!}
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <!-- Loan Amount -->
                                <div class="col-md-4 mb-4">
                                    <div class="form-group">
                                        {!! Form::label('amount', '💵 ' . __('Loan::lang.amount'), ['class' => 'font-weight-bold text-muted mb-2']) !!}
                                        {!! Form::number('amount', null, [
        'id' => 'amount',
        'class' => 'form-control form-control-custom',
        'required' => true,
        'oninput' => 'updateTotalAmount()'
    ]) !!}
                                    </div>
                                </div>

                                <!-- Loan Duration -->
                                <div class="col-md-4 mb-4">
                                    <div class="form-group">
                                        {!! Form::label('duration', '⏳ ' . __('Loan::lang.duration') . ' (' . __('Loan::lang.months') . ')', ['class' => 'font-weight-bold text-muted mb-2']) !!}
                                        {!! Form::number('duration', null, [
        'id' => 'duration',
        'class' => 'form-control form-control-custom',
        'required' => true,
        'oninput' => 'updateTotalAmount()'
    ]) !!}
                                    </div>
                                </div>

                                <!-- Interest Rate -->
                                <div class="col-md-4 mb-4">
                                    <div class="form-group">
                                        {!! Form::label('interest_rate', '📈 ' . __('Loan::lang.interest_rate') . ' (%)', ['class' => 'font-weight-bold text-muted mb-2']) !!}
                                        {!! Form::number('interest_rate', null, [
        'id' => 'interest_rate',
        'step' => '0.01',
        'class' => 'form-control form-control-custom',
        'required' => true,
        'oninput' => 'updateTotalAmount()'
    ]) !!}
                                    </div>
                                </div>
                            </div>

                            <!-- Total Amount (Auto Calculated Input) -->
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group mb-0">
                                        {!! Form::label('total_amount', '💰 ' . __('Loan::lang.total_amount'), ['class' => 'font-weight-bold text-muted mb-2']) !!}
                                        {!! Form::number('total_amount', '0.00', [
        'id' => 'total_amount',
        'step' => '0.01',
        'class' => 'form-control form-control-custom bg-light font-weight-bold text-primary',
        'style' => 'font-size: 1.1rem !important;',
        'readonly' => true
    ]) !!}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card 3: Description & Status -->
                    <div class="card loan-card">
                        <div class="card-header loan-card-header-3">
                            <h4 class="mb-0 font-weight-bold d-flex align-items-center text-white"
                                style="color: #ffffff !important;">
                                <span class="mr-2">📝</span> {{ __('Loan::lang.description') }} &
                                {{ __('Loan::lang.status') }}
                            </h4>
                        </div>
                        <div class="card-body p-5" style="padding: 10px;">
                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <div class="form-group">
                                        {!! Form::label('status', '⚙️ ' . __('Loan::lang.status'), ['class' => 'font-weight-bold text-muted mb-2']) !!}
                                        {!! Form::select('status', $loan_statuses, null, [
        'id' => 'status',
        'class' => 'form-control select2',
        'style' => 'width:100%',
        'placeholder' => __('messages.please_select'),
        'required' => true
    ]) !!}
                                    </div>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <div class="form-group">
                                        {!! Form::label('description', '📝 ' . __('Loan::lang.description'), ['class' => 'font-weight-bold text-muted mb-2']) !!}
                                        {!! Form::textarea('description', null, [
        'id' => 'description',
        'class' => 'form-control form-control-custom',
        'rows' => 3
    ]) !!}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Side: Sticky Calculator Widget -->
                <div class="col-lg-4 col-md-12 mb-4">
                    <div class="sticky-calculator">
                        <div class="card calculator-card">
                            <div class="calculator-header text-center">
                                <span class="badge badge-pill badge-primary px-3 py-2 mb-2"
                                    style="background-color: rgba(255,255,255,0.15); font-weight: 600;">📊 Live
                                    Estimation</span>
                                <h3 class="mb-0 font-weight-bold text-white" style="color: #ffffff !important;">Loan
                                    Calculator</h3>
                            </div>
                            <div class="card-body p-5" style="padding: 10px;">
                                <!-- Big Monthly Payment Display -->
                                <div class="calculator-display text-center mb-4">
                                    <span class="text-uppercase font-weight-bold text-muted"
                                        style="font-size: 0.8rem; letter-spacing: 1px;">Est. Monthly Payment</span>
                                    <h2 id="preview_monthly" class="display-4 font-weight-bold text-primary my-2"
                                        style="word-break: break-all;">0.00</h2>
                                    <span id="preview_term" class="text-muted" style="font-size: 0.9rem;">0 months
                                        term</span>
                                </div>

                                <!-- Key Statistics / Breakdown -->
                                <div class="mb-4">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="text-muted font-weight-medium d-flex align-items-center"><span
                                                class="info-dot bg-primary"></span>Principal</span>
                                        <span id="preview_principal" class="font-weight-bold text-dark">0.00</span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="text-muted font-weight-medium d-flex align-items-center"><span
                                                class="info-dot bg-warning"></span>Total Interest</span>
                                        <span id="preview_interest" class="font-weight-bold text-dark">0.00</span>
                                    </div>
                                    <hr class="my-2">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <span class="font-weight-bold text-dark d-flex align-items-center"><span
                                                class="info-dot bg-indigo" style="background-color: #4f46e5;"></span>Total
                                            Payable</span>
                                        <span id="preview_total" class="font-weight-bold text-indigo"
                                            style="color: #4f46e5; font-size: 1.15rem;">0.00</span>
                                    </div>

                                    <!-- Visual Stacked Bar -->
                                    <div class="progress"
                                        style="height: 10px; border-radius: 6px; background-color: #f1f5f9; overflow: hidden;">
                                        <div id="preview_principal_bar" class="progress-bar bg-primary" role="progressbar"
                                            style="width: 100%; transition: width 0.4s ease;"></div>
                                        <div id="preview_interest_bar" class="progress-bar bg-warning" role="progressbar"
                                            style="width: 0%; transition: width 0.4s ease;"></div>
                                    </div>
                                </div>

                                <!-- Live Recipient Status Indicator -->
                                <div class="p-3 mb-4 rounded-lg"
                                    style="background-color: #f8fafc; border: 1px solid #f1f5f9; border-radius: 12px;">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <span class="text-muted font-weight-medium"
                                            style="font-size: 0.85rem;">Recipient:</span>
                                        <span id="preview_recipient" class="font-weight-bold text-truncate text-right ml-2"
                                            style="font-size: 0.85rem; max-width: 180px;">Not selected</span>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between">
                                        <span class="text-muted font-weight-medium" style="font-size: 0.85rem;">Start
                                            Date:</span>
                                        <span id="preview_start_date" class="font-weight-bold text-right"
                                            style="font-size: 0.85rem;">Today</span>
                                    </div>
                                </div>

                                <!-- Action Buttons inside sticky sidebar -->
                                <button type="submit" class="btn btn-gradient-primary btn-lg btn-block py-3 shadow-lg">
                                    ✔️ {{ __('Loan::lang.save') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            {!! Form::close() !!}
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

            updateCalculatorRecipient();
        }

        function updateCalculatorRecipient() {
            const recipientType = document.getElementById('recipient_type').value;
            const previewRecipient = document.getElementById('preview_recipient');
            if (!previewRecipient) return;

            if (recipientType === 'customer') {
                const select = document.getElementById('customer_id');
                const text = select.options[select.selectedIndex]?.text || 'Not selected';
                previewRecipient.innerText = text !== 'Please select' && text !== '' ? text : 'Customer';
            } else {
                const select = document.getElementById('user_id');
                const text = select.options[select.selectedIndex]?.text || 'Not selected';
                previewRecipient.innerText = text !== 'Please select' && text !== '' ? text : 'User';
            }
        }

        function updateTotalAmount() {
            const amount = parseFloat(document.getElementById('amount').value) || 0;
            const interestRate = parseFloat(document.getElementById('interest_rate').value) || 0;
            const duration = parseInt(document.getElementById('duration').value) || 0;

            const interestAmount = amount * (interestRate / 100);
            const totalAmount = amount + interestAmount;

            document.getElementById('total_amount').value = totalAmount.toFixed(2);

            // Update live calculator previews
            if (document.getElementById('preview_principal')) {
                document.getElementById('preview_principal').innerText = __format_currency_value(amount);
            }
            if (document.getElementById('preview_interest')) {
                document.getElementById('preview_interest').innerText = __format_currency_value(interestAmount);
            }
            if (document.getElementById('preview_total')) {
                document.getElementById('preview_total').innerText = __format_currency_value(totalAmount);
            }

            const monthlyPayment = duration > 0 ? (totalAmount / duration) : totalAmount;
            if (document.getElementById('preview_monthly')) {
                document.getElementById('preview_monthly').innerText = __format_currency_value(monthlyPayment);
            }
            if (document.getElementById('preview_term')) {
                document.getElementById('preview_term').innerText = duration + ' months term';
            }

            // Update visual percentage bar
            const principalBar = document.getElementById('preview_principal_bar');
            const interestBar = document.getElementById('preview_interest_bar');
            if (principalBar && interestBar) {
                if (totalAmount > 0) {
                    const principalPercent = (amount / totalAmount) * 100;
                    const interestPercent = (interestAmount / totalAmount) * 100;
                    principalBar.style.width = principalPercent.toFixed(1) + '%';
                    interestBar.style.width = interestPercent.toFixed(1) + '%';
                } else {
                    principalBar.style.width = '100%';
                    interestBar.style.width = '0%';
                }
            }
        }

        function __format_currency_value(value) {
            const symbol = document.getElementById('__symbol')?.value || '$';
            const precision = parseInt(document.getElementById('__precision')?.value) || 2;
            const placement = document.getElementById('__symbol_placement')?.value || 'before';

            const formattedVal = value.toLocaleString(undefined, {
                minimumFractionDigits: precision,
                maximumFractionDigits: precision
            });

            if (placement === 'before') {
                return symbol + ' ' + formattedVal;
            } else {
                return formattedVal + ' ' + symbol;
            }
        }

        // Initialize total amount and datepicker on page load
        document.addEventListener('DOMContentLoaded', function () {
            updateTotalAmount();
            updateCalculatorRecipient();

            const startDateInput = document.getElementById('start_date');
            if (startDateInput) {
                document.getElementById('preview_start_date').innerText = startDateInput.value || 'Today';
                $(startDateInput).on('change', function () {
                    document.getElementById('preview_start_date').innerText = this.value || 'Today';
                });
            }

            // jQuery event listeners for select2 changes
            $('#customer_id, #user_id').on('change', function () {
                updateCalculatorRecipient();
            });

            if (typeof $.fn.datepicker !== 'undefined') {
                $('#start_date').datepicker({
                    autoclose: true,
                    format: datepicker_date_format
                }).on('changeDate', function (e) {
                    const formattedDate = $(this).val();
                    document.getElementById('preview_start_date').innerText = formattedDate || 'Today';
                });
            }
        });
    </script>

@endsection