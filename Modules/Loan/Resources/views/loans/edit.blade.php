@extends('layouts.app')

@section('title', __('Loan::lang.edit_loan'))

@section('content')

    @include('Loan::layouts.nav')

    <style>
        .sticky-calculator {
            position: -webkit-sticky;
            position: sticky;
            top: 20px;
        }
        .info-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 8px;
        }
    </style>

    <!-- Content Header (Page header) -->
    <section class="content-header no-print">
        <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">
            {{ __('Loan::lang.edit_loan') }}
        </h1>
    </section>

    <!-- Main content -->
    <section class="content no-print">
        {!! Form::model($loan, ['url' => route('Loan.loans.update', $loan->id), 'method' => 'PUT']) !!}
        <div class="row">
            <!-- Left Side: Loan Configuration Forms -->
            <div class="col-lg-8 col-md-12">

                @component('components.widget', ['class' => 'box-primary', 'title' => __('Loan::lang.recipient_type') . ' & ' . __('Loan::lang.branch')])
                    <div class="row">
                        <!-- Recipient Type -->
                        <div class="col-md-6">
                            <div class="form-group">
                                {!! Form::label('recipient_type', __('Loan::lang.recipient_type') . ':*') !!}
                                {!! Form::select('recipient_type', [
                                    'customer' => __('Loan::lang.customer'),
                                    'user' => __('Loan::lang.user')
                                ], $loan->customer_id ? 'customer' : 'user', [
                                    'id' => 'recipient_type',
                                    'class' => 'form-control select2',
                                    'style' => 'width:100%',
                                    'required' => true,
                                    'onchange' => 'toggleRecipientFields()'
                                ]) !!}
                            </div>
                        </div>

                        <!-- Customer -->
                        <div class="col-md-6" id="customer_field" style="{{ $loan->user_id ? 'display: none;' : '' }}">
                            <div class="form-group">
                                {!! Form::label('customer_id', __('Loan::lang.customer') . ':') !!}
                                @php
                                    $customer_opts = ['id' => 'customer_id', 'class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('messages.please_select')];
                                    if ($loan->customer_id) {
                                        $customer_opts['required'] = true;
                                    }
                                @endphp
                                {!! Form::select('customer_id', $customers, null, $customer_opts) !!}
                            </div>
                        </div>

                        <!-- User -->
                        <div class="col-md-6" id="user_field" style="{{ $loan->user_id ? '' : 'display: none;' }}">
                            <div class="form-group">
                                {!! Form::label('user_id', __('Loan::lang.user') . ':') !!}
                                @php
                                    $user_opts = ['id' => 'user_id', 'class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('messages.please_select')];
                                    if ($loan->user_id) {
                                        $user_opts['required'] = true;
                                    }
                                @endphp
                                {!! Form::select('user_id', $users, null, $user_opts) !!}
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Branch -->
                        <div class="col-md-12">
                            <div class="form-group">
                                {!! Form::label('location_id', __('Loan::lang.branch') . ':*') !!}
                                {!! Form::select('location_id', $locations, null, [
                                    'id' => 'location_id',
                                    'class' => 'form-control select2',
                                    'style' => 'width:100%',
                                    'placeholder' => __('messages.please_select'),
                                    'required' => true,
                                ]) !!}
                            </div>
                        </div>
                    </div>
                @endcomponent

                @component('components.widget', ['class' => 'box-primary', 'title' => __('Loan::lang.loan_info') . ' & ' . __('Loan::lang.amount')])
                    <div class="row">
                        <!-- Start Date -->
                        <div class="col-md-6">
                            <div class="form-group">
                                {!! Form::label('start_date', __('Loan::lang.start_date') . ':*') !!}
                                {!! Form::text('start_date', !empty($loan->start_date) ? @format_date($loan->start_date) : null, ['id' => 'start_date', 'class' => 'form-control', 'required' => true]) !!}
                            </div>
                        </div>

                        <!-- Loan Type -->
                        <div class="col-md-6">
                            <div class="form-group">
                                {!! Form::label('loan_type', __('Loan::lang.loan_type') . ':*') !!}
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
                        <div class="col-md-4">
                            <div class="form-group">
                                {!! Form::label('amount', __('Loan::lang.amount') . ':*') !!}
                                {!! Form::number('amount', null, [
                                    'id' => 'amount',
                                    'class' => 'form-control',
                                    'required' => true,
                                    'oninput' => 'updateTotalAmount()'
                                ]) !!}
                            </div>
                        </div>

                        <!-- Loan Duration -->
                        <div class="col-md-4">
                            <div class="form-group">
                                {!! Form::label('duration', __('Loan::lang.duration') . ' (' . __('Loan::lang.months') . '):*') !!}
                                {!! Form::number('duration', null, [
                                    'id' => 'duration',
                                    'class' => 'form-control',
                                    'required' => true,
                                    'oninput' => 'updateTotalAmount()'
                                ]) !!}
                            </div>
                        </div>

                        <!-- Interest Rate -->
                        <div class="col-md-4">
                            <div class="form-group">
                                {!! Form::label('interest_rate', __('Loan::lang.interest_rate') . ' (%):*') !!}
                                {!! Form::number('interest_rate', null, [
                                    'id' => 'interest_rate',
                                    'step' => '0.01',
                                    'class' => 'form-control',
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
                                {!! Form::label('total_amount', __('Loan::lang.total_amount') . ':') !!}
                                {!! Form::number('total_amount', null, [
                                    'id' => 'total_amount',
                                    'step' => '0.01',
                                    'class' => 'form-control',
                                    'readonly' => true
                                ]) !!}
                            </div>
                        </div>
                    </div>
                @endcomponent

                @component('components.widget', ['class' => 'box-primary', 'title' => __('Loan::lang.description') . ' & ' . __('Loan::lang.status')])
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                {!! Form::label('status', __('Loan::lang.status') . ':*') !!}
                                {!! Form::select('status', $loan_statuses, null, [
                                    'id' => 'status',
                                    'class' => 'form-control select2',
                                    'style' => 'width:100%',
                                    'placeholder' => __('messages.please_select'),
                                    'required' => true
                                ]) !!}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                {!! Form::label('description', __('Loan::lang.description') . ':') !!}
                                {!! Form::textarea('description', null, [
                                    'id' => 'description',
                                    'class' => 'form-control',
                                    'rows' => 3
                                ]) !!}
                            </div>
                        </div>
                    </div>
                @endcomponent
            </div>

            <!-- Right Side: Sticky Calculator Widget -->
            <div class="col-lg-4 col-md-12">
                <div class="sticky-calculator">
                    @component('components.widget', ['class' => 'box-solid', 'title' => 'Loan Calculator'])
                        <!-- Big Monthly Payment Display -->
                        <div class="text-center mb-4" style="background: #f8fafc; padding: 15px; border-radius: 10px; border: 1px solid #e3e8ee; margin-bottom: 20px;">
                            <span class="text-uppercase font-weight-bold text-muted" style="font-size: 0.8rem; letter-spacing: 1px;">Est. Monthly Payment</span>
                            <h2 id="preview_monthly" class="text-primary font-weight-bold my-2" style="font-size: 2.2rem; margin: 10px 0; word-break: break-all;">0.00</h2>
                            <span id="preview_term" class="text-muted" style="font-size: 0.9rem;">0 months term</span>
                        </div>

                        <!-- Key Statistics / Breakdown -->
                        <div style="margin-bottom: 20px;">
                            <div class="d-flex justify-content-between align-items-center" style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                                <span class="text-muted"><span class="info-dot bg-primary" style="background-color: #3c8dbc;"></span>Principal</span>
                                <span id="preview_principal" class="font-weight-bold">0.00</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center" style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                                <span class="text-muted"><span class="info-dot bg-warning" style="background-color: #f39c12;"></span>Total Interest</span>
                                <span id="preview_interest" class="font-weight-bold">0.00</span>
                            </div>
                            <hr style="margin: 10px 0;">
                            <div class="d-flex justify-content-between align-items-center" style="display: flex; justify-content: space-between; margin-bottom: 15px;">
                                <span class="font-weight-bold"><span class="info-dot" style="background-color: #605ca8;"></span>Total Payable</span>
                                <span id="preview_total" class="font-weight-bold text-primary" style="color: #605ca8; font-size: 1.15rem;">0.00</span>
                            </div>

                            <!-- Visual Stacked Bar -->
                            <div class="progress" style="height: 10px; border-radius: 6px; background-color: #f1f5f9; overflow: hidden; margin-top: 10px;">
                                <div id="preview_principal_bar" class="progress-bar progress-bar-primary" role="progressbar" style="width: 100%; transition: width 0.4s ease; background-color: #3c8dbc;"></div>
                                <div id="preview_interest_bar" class="progress-bar progress-bar-warning" role="progressbar" style="width: 0%; transition: width 0.4s ease; background-color: #f39c12;"></div>
                            </div>
                        </div>

                        <!-- Live Recipient Status Indicator -->
                        <div style="background-color: #f8fafc; border: 1px solid #e3e8ee; padding: 12px; border-radius: 8px; margin-bottom: 20px;">
                            <div class="d-flex align-items-center justify-content-between" style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                                <span class="text-muted" style="font-size: 0.85rem;">Recipient:</span>
                                <span id="preview_recipient" class="font-weight-bold text-truncate" style="font-size: 0.85rem; max-width: 180px; text-align: right;">Not selected</span>
                            </div>
                            <div class="d-flex align-items-center justify-content-between" style="display: flex; justify-content: space-between;">
                                <span class="text-muted" style="font-size: 0.85rem;">Start Date:</span>
                                <span id="preview_start_date" class="font-weight-bold" style="font-size: 0.85rem; text-align: right;">Today</span>
                            </div>
                        </div>

                        <!-- Action Buttons inside sticky sidebar -->
                        <button type="submit" class="tw-dw-btn tw-dw-btn-primary tw-dw-btn-lg tw-text-white btn-block">
                            {{ __('Loan::lang.update') }}
                        </button>
                    @endcomponent
                </div>
            </div>
        </div>
        {!! Form::close() !!}
    </section>

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

        // Function to update the live preview recipient name
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

            $(document).on('change', 'select#location_id', function() {
                var location_id = $(this).val();
                if ($('select#account_id').length) {
                    var current_account = $('select#account_id').val();
                    $.ajax({
                        url: '/get-location-accounts/' + (location_id ? location_id : ''),
                        dataType: 'json',
                        success: function(accounts) {
                            var $acc = $('select#account_id');
                            if ($acc.hasClass('select2-hidden-accessible')) {
                                $acc.select2('destroy');
                            }
                            $acc.empty();
                            var items = [];
                            if (Array.isArray(accounts)) {
                                $.each(accounts, function(i, item) {
                                    if (typeof item === 'object' && item !== null && item.hasOwnProperty('name')) {
                                        items.push({ id: item.id, name: item.name });
                                    } else {
                                        items.push({ id: i, name: item });
                                    }
                                });
                            } else if (typeof accounts === 'object' && accounts !== null) {
                                $.each(accounts, function(key, value) {
                                    if (typeof value === 'object' && value !== null && value.hasOwnProperty('name')) {
                                        items.push({ id: value.id, name: value.name });
                                    } else {
                                        items.push({ id: key, name: value });
                                    }
                                });
                            }
                            var has_current = false;
                            $.each(items, function(i, item) {
                                $acc.append($('<option>', {
                                    value: item.id,
                                    text: item.name
                                }));
                                if (current_account && item.id == current_account) {
                                    has_current = true;
                                }
                            });
                            if (has_current) {
                                $acc.val(current_account);
                            }
                            $acc.select2();
                        }
                    });
                }
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