@extends('layouts.app')

@section('title', __('Loan::lang.reports'))

@section('content')
@include('Loan::layouts.nav')

<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">@lang('Loan::lang.reports')</h1>
</section>

<section class="content">
    <!-- Overview Section -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card shadow-lg">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title"><i class="fa fa-money-bill"></i> @lang('Loan::lang.total_loans')</h5>
                </div>
                <div class="card-body">
                    <h4>{{ number_format($total_loans, 2) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-lg">
                <div class="card-header bg-success text-white">
                    <h5 class="card-title"><i class="fa fa-credit-card"></i> @lang('Loan::lang.total_payments')</h5>
                </div>
                <div class="card-body">
                    <h4>{{ number_format($total_payments, 2) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-lg">
                <div class="card-header bg-info text-white">
                    <h5 class="card-title"><i class="fa fa-percent"></i> @lang('Loan::lang.total_interest')</h5>
                </div>
                <div class="card-body">
                    <h4>{{ number_format($total_interest, 2) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-lg">
                <div class="card-header bg-warning text-white">
                    <h5 class="card-title"><i class="fa fa-balance-scale"></i> @lang('Loan::lang.total_outstanding')</h5>
                </div>
                <div class="card-body">
                    <h4>{{ number_format($total_outstanding, 2) }}</h4>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="card shadow-lg mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0">@lang('Loan::lang.filters')</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('Loan.reports.index') }}" method="GET">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="start_date">@lang('Loan::lang.start_date')</label>
                            <input type="date" name="start_date" id="start_date" class="form-control" value="{{ request('start_date') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="end_date">@lang('Loan::lang.end_date')</label>
                            <input type="date" name="end_date" id="end_date" class="form-control" value="{{ request('end_date') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="loan_type">@lang('Loan::lang.loan_type')</label>
                            <select name="loan_type" id="loan_type" class="form-control">
                                <option value="">@lang('Loan::lang.all_loan_types')</option>
                                <option value="personal" {{ request('loan_type') == 'personal' ? 'selected' : '' }}>@lang('Loan::lang.personal_loan')</option>
                                <option value="business" {{ request('loan_type') == 'business' ? 'selected' : '' }}>@lang('Loan::lang.business_loan')</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="status">@lang('Loan::lang.status')</label>
                            <select name="status" id="status" class="form-control">
                                <option value="">@lang('Loan::lang.all_statuses')</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>@lang('Loan::lang.active')</option>
                                <option value="partially_paid" {{ request('status') == 'partially_paid' ? 'selected' : '' }}>@lang('Loan::lang.partially_paid')</option>
                                <option value="fully_paid" {{ request('status') == 'fully_paid' ? 'selected' : '' }}>@lang('Loan::lang.fully_paid')</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="location_id">@lang('Loan::lang.branch')</label>
                            <select name="location_id" id="location_id" class="form-control">
                                <option value="">All Branches</option>
                                @foreach ($locations as $id => $name)
                                    <option value="{{ $id }}" {{ request('location_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="total_amount_min">@lang('Loan::lang.total_amount_min')</label>
                            <input type="number" step="0.01" name="total_amount_min" id="total_amount_min" class="form-control" value="{{ request('total_amount_min') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="total_amount_max">@lang('Loan::lang.total_amount_max')</label>
                            <input type="number" step="0.01" name="total_amount_max" id="total_amount_max" class="form-control" value="{{ request('total_amount_max') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group mt-4">
                            <button type="submit" class="btn btn-primary btn-block">@lang('Loan::lang.apply_filters')</button>
                            <a href="{{ route('Loan.reports.index') }}" class="btn btn-secondary btn-block mt-2">@lang('Loan::lang.reset')</a>
                            <a href="{{ route('Loan.reports.export') }}" class="btn btn-success btn-block mt-2">@lang('Loan::lang.export_excel')</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card shadow-lg">
                <div class="card-header bg-info text-white">
                    <h5 class="card-title">@lang('Loan::lang.loans_status_distribution')</h5>
                </div>
                <div class="card-body">
                    <canvas id="statusChart" height="200"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-lg">
                <div class="card-header bg-success text-white">
                    <h5 class="card-title">@lang('Loan::lang.loans_by_type')</h5>
                </div>
                <div class="card-body">
                    <canvas id="typeChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card shadow-lg">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title">@lang('Loan::lang.total_payments_overview')</h5>
                </div>
                <div class="card-body">
                    <canvas id="paymentChart" height="100"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Average Loan Stats Table -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card shadow-lg">
                <div class="card-header bg-dark text-white">
                    <h5 class="card-title">@lang('Loan::lang.avg_loan_stats')</h5>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>@lang('Loan::lang.loan_type')</th>
                                <th>@lang('Loan::lang.avg_amount')</th>
                                <th>@lang('Loan::lang.avg_duration')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($avg_loan_stats as $stat)
                                <tr>
                                    <td>@lang('Loan::lang.' . $stat->loan_type . '_loan')</td>
                                    <td>{{ number_format($stat->avg_amount, 2) }}</td>
                                    <td>{{ number_format($stat->avg_duration, 1) }} @lang('Loan::lang.months')</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Loans Table -->
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow-lg">
                <div class="card-header bg-dark text-white">
                    <h3 class="card-title">@lang('Loan::lang.detailed_loans')</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="bg-primary text-white">
                                <tr>
                                    <th>@lang('Loan::lang.recipient')</th>
                                    <th>@lang('Loan::lang.start_date')</th>
                                    <th>@lang('Loan::lang.amount')</th>
                                    <th>@lang('Loan::lang.total_amount')</th>
                                    <th>@lang('Loan::lang.total_paid')</th>
                                    <th>@lang('Loan::lang.remaining_balance')</th>
                                    <th>@lang('Loan::lang.duration')</th>
                                    <th>@lang('Loan::lang.interest_rate')</th>
                                    <th>@lang('Loan::lang.loan_type')</th>
                                    <th>@lang('Loan::lang.status')</th>
                                    <th>@lang('Loan::lang.branch')</th>
                                    <th>@lang('Loan::lang.actions')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($detailed_loans as $loan)
                                    <tr>
                                        <td>{{ $loan->recipient_name }}</td>
                                        <td>{{ $loan->start_date }}</td>
                                        <td>{{ number_format($loan->amount, 2) }}</td>
                                        <td>{{ number_format($loan->total_amount, 2) }}</td>
                                        <td>{{ number_format($loan->total_paid, 2) }}</td>
                                        <td>{{ number_format($loan->remaining_balance, 2) }}</td>
                                        <td>{{ $loan->duration }} @lang('Loan::lang.months')</td>
                                        <td>{{ $loan->interest_rate }}%</td>
                                        <td>@lang('Loan::lang.' . $loan->loan_type . '_loan')</td>
                                        <td>
                                            @if ($loan->status == 'active')
                                                <span class="badge badge-success">@lang('Loan::lang.active')</span>
                                            @elseif ($loan->status == 'partially_paid')
                                                <span class="badge badge-warning">@lang('Loan::lang.partially_paid')</span>
                                            @elseif ($loan->status == 'fully_paid')
                                                <span class="badge badge-secondary">@lang('Loan::lang.fully_paid')</span>
                                            @endif
                                        </td>
                                        <td>{{ $loan->location->name }}</td>
                                        <td>
                                            <a href="{{ route('Loan.loans.show', $loan->id) }}" class="btn btn-info btn-sm">@lang('Loan::lang.view')</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="12" class="text-center">@lang('Loan::lang.no_loans_found')</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-center mt-4">
                        {{ $detailed_loans->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@section('javascript')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Status Pie Chart
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    new Chart(statusCtx, {
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
                legend: { position: 'top' },
                title: { display: true, text: '@lang('Loan::lang.loans_status_distribution')' }
            }
        }
    });

    // Type Bar Chart
    const typeCtx = document.getElementById('typeChart').getContext('2d');
    new Chart(typeCtx, {
        type: 'bar',
        data: {
            labels: @json($chart_data['type_labels']),
            datasets: [{
                label: '@lang('Loan::lang.count')',
                data: @json($chart_data['type_counts']),
                backgroundColor: '#28a745',
                borderColor: '#218838',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: { beginAtZero: true, title: { display: true, text: '@lang('Loan::lang.count')' } }
            },
            plugins: {
                legend: { display: false },
                title: { display: true, text: '@lang('Loan::lang.loans_by_type')' }
            }
        }
    });

    // Payment Trend Line Chart
    const paymentCtx = document.getElementById('paymentChart').getContext('2d');
    new Chart(paymentCtx, {
        type: 'line',
        data: {
            labels: @json($chart_data['payment_months']),
            datasets: [{
                label: '@lang('Loan::lang.total_payments')',
                data: @json($chart_data['payment_totals']),
                fill: false,
                borderColor: '#007bff',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: { beginAtZero: true, title: { display: true, text: '@lang('Loan::lang.total_payments')' } },
                x: { title: { display: true, text: 'Month' } }
            },
            plugins: {
                legend: { display: true },
                title: { display: true, text: '@lang('Loan::lang.total_payments_overview')' }
            }
        }
    });
</script>
@endsection

@endsection