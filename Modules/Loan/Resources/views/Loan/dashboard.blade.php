@extends('layouts.app')

@section('title', __('Loan::lang.Loan_module_dashboard'))

@section('content')

    @include('Loan::layouts.nav')

    <style>
        body {
            font-family: 'Cairo', sans-serif;
        }

        .hover-card:hover {
            transform: scale(1.05);
            transition: all 0.3s ease-in-out;
        }

        .card {
            border-radius: 15px;
            overflow: hidden;
        }

        .card-header {
            font-size: 1.2rem;
            font-weight: bold;
        }

        .floating-buttons {
            position: fixed;
            bottom: 20px;
            right: 20px;
            display: flex;
            flex-direction: column;
            gap: 10px;
            z-index: 1000;
        }

        .floating-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            color: white;
            font-size: 24px;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .floating-btn:hover {
            transform: scale(1.1);
        }

        .whatsapp-btn {
            background-color: #25D366;
        }

        .purchase-btn {
            background-color: #007BFF;
        }

        .floating-btn span {
            display: none;
            position: absolute;
            right: 70px;
            background-color: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 14px;
            white-space: nowrap;
        }

        .floating-btn:hover span {
            display: block;
        }
    </style>

    <div class="container my-5">
        <!-- Header Section -->
        <div class="row mb-4">
            <div class="col-md-12 text-center">
                <h1 class="display-4 text-primary font-weight-bold">{{ __('Loan::lang.Loan_module_dashboard') }}</h1>
                <p class="lead text-muted">{{ __('Loan::lang.overview') }}</p>
                <hr class="w-25 mx-auto border-primary">
            </div>
        </div>

        <!-- Filters Section -->
        <form action="{{ route('Loan.dashboard') }}" method="GET" id="dashboard_filter_form">
            <input type="hidden" name="start_date" id="start_date" value="{{ request('start_date') }}">
            <input type="hidden" name="end_date" id="end_date" value="{{ request('end_date') }}">

            @component('components.filters', ['title' => __('Loan::lang.filters')])
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('date_range', __('report.date_range') . ':') !!}
                    {!! Form::text('date_range', null, [
        'id' => 'date_range',
        'class' => 'form-control',
        'placeholder' => __('lang_v1.select_a_date_range'),
        'readonly' => true,
    ]) !!}
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('location_id', __('Loan::lang.branch') . ':') !!}
                    {!! Form::select('location_id', $locations, request('location_id'), [
        'id' => 'location_id',
        'class' => 'form-control select2',
        'style' => 'width:100%',
        'placeholder' => __('Loan::lang.all_branches'),
    ]) !!}
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('loan_type', __('Loan::lang.loan_type') . ':') !!}
                    {!! Form::select('loan_type', [
        'personal' => __('Loan::lang.personal_loan'),
        'business' => __('Loan::lang.business_loan'),
    ], request('loan_type'), [
        'id' => 'loan_type',
        'class' => 'form-control select2',
        'style' => 'width:100%',
        'placeholder' => __('Loan::lang.all_loan_types'),
    ]) !!}
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('status', __('Loan::lang.status') . ':') !!}
                    {!! Form::select('status', [
        'active' => __('Loan::lang.active'),
        'partially_paid' => __('Loan::lang.partially_paid'),
        'fully_paid' => __('Loan::lang.fully_paid'),
    ], request('status'), [
        'id' => 'loan_status',
        'class' => 'form-control select2',
        'style' => 'width:100%',
        'placeholder' => __('Loan::lang.all_statuses'),
    ]) !!}
                </div>
            </div>
            @endcomponent
        </form>

        <!-- Quick Summary Section -->
        <div class="row g-4 mb-4">
            <div class="col-md-3 col-sm-6">
                <div class="card border-0 shadow-lg h-100 text-center hover-card"
                    style="background: linear-gradient(135deg, #6366F1, #4F46E5);">
                    <div class="card-body d-flex flex-column justify-content-center align-items-center text-white">
                        <i class="fas fa-wallet fa-3x mb-3"></i>
                        <h6 class="card-title text-uppercase mt-3">{{ __('Loan::lang.total_loans') }}</h6>
                        <h3 class="display-6 mt-2">{{ $total_loans }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="card border-0 shadow-lg h-100 text-center hover-card"
                    style="background: linear-gradient(135deg, #10B981, #059669);">
                    <div class="card-body d-flex flex-column justify-content-center align-items-center text-white">
                        <i class="fas fa-dollar-sign fa-3x mb-3"></i>
                        <h6 class="card-title text-uppercase mt-3">{{ __('Loan::lang.total_payments') }}</h6>
                        <h3 class="display-6 mt-2">{{ number_format($total_payments, 2) }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="card border-0 shadow-lg h-100 text-center hover-card"
                    style="background: linear-gradient(135deg, #F59E0B, #D97706);">
                    <div class="card-body d-flex flex-column justify-content-center align-items-center text-white">
                        <i class="fas fa-percent fa-3x mb-3"></i>
                        <h6 class="card-title text-uppercase mt-3">{{ __('Loan::lang.total_interest') }}</h6>
                        <h3 class="display-6 mt-2">{{ number_format($total_interest, 2) }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="card border-0 shadow-lg h-100 text-center hover-card"
                    style="background: linear-gradient(135deg, #EF4444, #B91C1C);">
                    <div class="card-body d-flex flex-column justify-content-center align-items-center text-white">
                        <i class="fas fa-balance-scale fa-3x mb-3"></i>
                        <h6 class="card-title text-uppercase mt-3">{{ __('Loan::lang.total_outstanding') }}</h6>
                        <h3 class="display-6 mt-2">{{ number_format($total_outstanding, 2) }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="row mt-5">
            <div class="col-md-4 mb-4">
                <div class="card shadow-lg">
                    <div class="card-header bg-primary text-white text-center">
                        {{ __('Loan::lang.loans_status_distribution') }}</div>
                    <div class="card-body"><canvas id="loanStatusChart"></canvas></div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card shadow-lg">
                    <div class="card-header bg-success text-white text-center">{{ __('Loan::lang.loans_by_type') }}</div>
                    <div class="card-body"><canvas id="loanTypeChart"></canvas></div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card shadow-lg">
                    <div class="card-header bg-info text-white text-center">{{ __('Loan::lang.total_payments_overview') }}
                    </div>
                    <div class="card-body"><canvas id="paymentsChart"></canvas></div>
                </div>
            </div>
        </div>

        <!-- Floating Buttons -->
        <div class="floating-buttons">
            <a href="https://wa.me/201144420094" target="_blank" class="floating-btn whatsapp-btn" title="Contact Support">
                <i class="fab fa-whatsapp"></i>
                <span>{{ __('Loan::lang.contact_support') }}</span>
            </a>
            <a href="https://hooksource.com/product/loan-management-module-for-ultimatepos/" target="_blank"
                class="floating-btn purchase-btn" title="Purchase Module">
                <i class="fas fa-shopping-cart"></i>
                <span>{{ __('Loan::lang.purchase_module') }}</span>
            </a>
        </div>
    </div>

    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        const loanStatusCtx = document.getElementById('loanStatusChart').getContext('2d');
        new Chart(loanStatusCtx, {
            type: 'pie',
            data: {
                labels: @json($chart_data['status_labels']),
                datasets: [{ data: @json($chart_data['status_counts']), backgroundColor: ['#28a745', '#ffc107', '#6c757d'], borderColor: '#fff', borderWidth: 1 }]
            },
            options: { responsive: true, plugins: { legend: { position: 'bottom' }, title: { display: true, text: '{{ __('Loan::lang.loans_status_distribution') }}' } } }
        });

        const loanTypeCtx = document.getElementById('loanTypeChart').getContext('2d');
        new Chart(loanTypeCtx, {
            type: 'bar',
            data: {
                labels: @json($chart_data['type_labels']),
                datasets: [{ label: '{{ __('Loan::lang.count') }}', data: @json($chart_data['type_counts']), backgroundColor: '#28a745', borderColor: '#218838', borderWidth: 1 }]
            },
            options: { responsive: true, scales: { y: { beginAtZero: true } }, plugins: { legend: { display: false }, title: { display: true, text: '{{ __('Loan::lang.loans_by_type') }}' } } }
        });

        const paymentsCtx = document.getElementById('paymentsChart').getContext('2d');
        new Chart(paymentsCtx, {
            type: 'line',
            data: {
                labels: @json($chart_data['payment_months']),
                datasets: [{ label: '{{ __('Loan::lang.total_payments') }}', data: @json($chart_data['payment_totals']), fill: false, borderColor: '#007bff', tension: 0.1 }]
            },
            options: { responsive: true, scales: { y: { beginAtZero: true }, x: {} }, plugins: { legend: { display: true }, title: { display: true, text: '{{ __('Loan::lang.total_payments_overview') }}' } } }
        });
    </script>

@endsection

@section('javascript')
    <script type="text/javascript">
        $(document).ready(function () {
            var startDate = '{{ request('start_date') }}';
            var endDate = '{{ request('end_date') }}';

            var pickerOptions = {
                ranges: {
                    'Today': [moment(), moment()],
                    'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                    'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                    'This Month': [moment().startOf('month'), moment().endOf('month')],
                    'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                },
                locale: {
                    format: '{{ session("business.date_format") }}',
                    cancelLabel: 'Cancel',
                    applyLabel: 'Apply'
                },
                autoUpdateInput: false
            };

            if (startDate && endDate) {
                pickerOptions.startDate = moment(startDate, 'YYYY-MM-DD');
                pickerOptions.endDate = moment(endDate, 'YYYY-MM-DD');
                var fmt = '{{ session("business.date_format") }}';
                $('#date_range').val(
                    moment(startDate, 'YYYY-MM-DD').format(fmt) + ' - ' + moment(endDate, 'YYYY-MM-DD').format(fmt)
                );
            }

            $('#date_range').daterangepicker(pickerOptions);

            // Auto-submit on date range apply
            $('#date_range').on('apply.daterangepicker', function (ev, picker) {
                $(this).val(
                    picker.startDate.format('{{ session("business.date_format") }}') + ' - ' +
                    picker.endDate.format('{{ session("business.date_format") }}')
                );
                $('#start_date').val(picker.startDate.format('YYYY-MM-DD'));
                $('#end_date').val(picker.endDate.format('YYYY-MM-DD'));
                $('#dashboard_filter_form').submit();
            });

            // Clear dates and auto-submit on cancel
            $('#date_range').on('cancel.daterangepicker', function () {
                $(this).val('');
                $('#start_date').val('');
                $('#end_date').val('');
                $('#dashboard_filter_form').submit();
            });

            // Auto-submit on select2 filter changes
            $('#location_id, #loan_type, #loan_status').on('change', function () {
                $('#dashboard_filter_form').submit();
            });
        });
    </script>
@endsection