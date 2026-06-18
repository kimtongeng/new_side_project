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
    <div class="card shadow-lg mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0">{{ __('Loan::lang.filters') }}</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('Loan.dashboard') }}" method="GET">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="start_date">{{ __('Loan::lang.start_date') }}</label>
                            <input type="date" name="start_date" id="start_date" class="form-control" value="{{ request('start_date') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="end_date">{{ __('Loan::lang.end_date') }}</label>
                            <input type="date" name="end_date" id="end_date" class="form-control" value="{{ request('end_date') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="location_id">{{ __('Loan::lang.branch') }}</label>
                            <select name="location_id" id="location_id" class="form-control">
                                <option value="">{{ __('Loan::lang.all_branches') }}</option>
                                @foreach ($locations as $id => $name)
                                    <option value="{{ $id }}" {{ request('location_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="loan_type">{{ __('Loan::lang.loan_type') }}</label>
                            <select name="loan_type" id="loan_type" class="form-control">
                                <option value="">{{ __('Loan::lang.all_loan_types') }}</option>
                                <option value="personal" {{ request('loan_type') == 'personal' ? 'selected' : '' }}>{{ __('Loan::lang.personal_loan') }}</option>
                                <option value="business" {{ request('loan_type') == 'business' ? 'selected' : '' }}>{{ __('Loan::lang.business_loan') }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="status">{{ __('Loan::lang.status') }}</label>
                            <select name="status" id="status" class="form-control">
                                <option value="">{{ __('Loan::lang.all_statuses') }}</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>{{ __('Loan::lang.active') }}</option>
                                <option value="partially_paid" {{ request('status') == 'partially_paid' ? 'selected' : '' }}>{{ __('Loan::lang.partially_paid') }}</option>
                                <option value="fully_paid" {{ request('status') == 'fully_paid' ? 'selected' : '' }}>{{ __('Loan::lang.fully_paid') }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group mt-4">
                            <button type="submit" class="btn btn-primary btn-block">{{ __('Loan::lang.apply_filters') }}</button>
                            <a href="{{ route('Loan.dashboard') }}" class="btn btn-secondary btn-block mt-2">{{ __('Loan::lang.reset') }}</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Quick Summary Section -->
    <div class="row g-4 mb-4">
        <!-- Total Loans -->
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-lg h-100 text-center hover-card" style="background: linear-gradient(135deg, #6366F1, #4F46E5);">
                <div class="card-body d-flex flex-column justify-content-center align-items-center text-white">
                    <i class="fas fa-wallet fa-3x mb-3"></i>
                    <h6 class="card-title text-uppercase mt-3">{{ __('Loan::lang.total_loans') }}</h6>
                    <h3 class="display-6 mt-2">{{ $total_loans }}</h3>
                </div>
            </div>
        </div>

        <!-- Total Payments -->
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-lg h-100 text-center hover-card" style="background: linear-gradient(135deg, #10B981, #059669);">
                <div class="card-body d-flex flex-column justify-content-center align-items-center text-white">
                    <i class="fas fa-dollar-sign fa-3x mb-3"></i>
                    <h6 class="card-title text-uppercase mt-3">{{ __('Loan::lang.total_payments') }}</h6>
                    <h3 class="display-6 mt-2">{{ number_format($total_payments, 2) }}</h3>
                </div>
            </div>
        </div>

        <!-- Total Interest -->
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-lg h-100 text-center hover-card" style="background: linear-gradient(135deg, #F59E0B, #D97706);">
                <div class="card-body d-flex flex-column justify-content-center align-items-center text-white">
                    <i class="fas fa-percent fa-3x mb-3"></i>
                    <h6 class="card-title text-uppercase mt-3">{{ __('Loan::lang.total_interest') }}</h6>
                    <h3 class="display-6 mt-2">{{ number_format($total_interest, 2) }}</h3>
                </div>
            </div>
        </div>

        <!-- Total Outstanding -->
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-lg h-100 text-center hover-card" style="background: linear-gradient(135deg, #EF4444, #B91C1C);">
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
        <!-- Loan Status Chart -->
        <div class="col-md-4 mb-4">
            <div class="card shadow-lg">
                <div class="card-header bg-primary text-white text-center">{{ __('Loan::lang.loans_status_distribution') }}</div>
                <div class="card-body">
                    <canvas id="loanStatusChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Loan Type Chart -->
        <div class="col-md-4 mb-4">
            <div class="card shadow-lg">
                <div class="card-header bg-success text-white text-center">{{ __('Loan::lang.loans_by_type') }}</div>
                <div class="card-body">
                    <canvas id="loanTypeChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Payments Chart -->
        <div class="col-md-4 mb-4">
            <div class="card shadow-lg">
                <div class="card-header bg-info text-white text-center">{{ __('Loan::lang.total_payments_overview') }}</div>
                <div class="card-body">
                    <canvas id="paymentsChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Floating Buttons -->
    <div class="floating-buttons">
        <a href="https://wa.me/201144420094" target="_blank" class="floating-btn whatsapp-btn" title="Contact Support">
            <i class="fab fa-whatsapp"></i>
            <span>{{ __('Loan::lang.contact_support') }}</span>
        </a>
        <a href="https://hooksource.com/product/loan-management-module-for-ultimatepos/" target="_blank" class="floating-btn purchase-btn" title="Purchase Module">
            <i class="fas fa-shopping-cart"></i>
            <span>{{ __('Loan::lang.purchase_module') }}</span>
        </a>
    </div>
</div>

<!-- Include FontAwesome for Icons -->
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>

<!-- Include Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    // Loan Status Chart (Pie)
    const loanStatusCtx = document.getElementById('loanStatusChart').getContext('2d');
    new Chart(loanStatusCtx, {
        type: 'pie',
        data: {
            labels: @json($chart_data['status_labels']),
            datasets: [{
                data: @json($chart_data['status_counts']),
                backgroundColor: ['#28a745', '#ffc107', '#6c757d'],
                borderColor: '#fff',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom' },
                title: { display: true, text: '{{ __('Loan::lang.loans_status_distribution') }}' }
            }
        }
    });

    // Loan Type Chart (Bar)
    const loanTypeCtx = document.getElementById('loanTypeChart').getContext('2d');
    new Chart(loanTypeCtx, {
        type: 'bar',
        data: {
            labels: @json($chart_data['type_labels']),
            datasets: [{
                label: '{{ __('Loan::lang.count') }}',
                data: @json($chart_data['type_counts']),
                backgroundColor: '#28a745',
                borderColor: '#218838',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: { beginAtZero: true, title: { display: true, text: '{{ __('Loan::lang.count') }}' } }
            },
            plugins: {
                legend: { display: false },
                title: { display: true, text: '{{ __('Loan::lang.loans_by_type') }}' }
            }
        }
    });

    // Payments Chart (Line)
    const paymentsCtx = document.getElementById('paymentsChart').getContext('2d');
    new Chart(paymentsCtx, {
        type: 'line',
        data: {
            labels: @json($chart_data['payment_months']),
            datasets: [{
                label: '{{ __('Loan::lang.total_payments') }}',
                data: @json($chart_data['payment_totals']),
                fill: false,
                borderColor: '#007bff',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: { beginAtZero: true, title: { display: true, text: '{{ __('Loan::lang.total_payments') }}' } },
                x: { title: { display: true, text: '{{ __('Loan::lang.month') }}' } }
            },
            plugins: {
                legend: { display: true },
                title: { display: true, text: '{{ __('Loan::lang.total_payments_overview') }}' }
            }
        }
    });
</script>
@endsection