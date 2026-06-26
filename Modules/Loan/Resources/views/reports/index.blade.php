@extends('layouts.app')

@section('title', __('Loan::lang.reports'))

@section('content')
    @include('Loan::layouts.nav')

    <section class="content-header no-print">
        <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">
            @lang('Loan::lang.reports')
        </h1>
    </section>

    <section class="content no-print">
        <!-- Overview Section -->
        <div class="row">
            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box info-box-new-style">
                    <span class="info-box-icon bg-aqua"><i class="fa fa-money-bill"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">@lang('Loan::lang.total_loans')</span>
                        <span class="info-box-number">{{ number_format($total_loans, 2) }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box info-box-new-style">
                    <span class="info-box-icon bg-green"><i class="fa fa-credit-card"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">@lang('Loan::lang.total_payments')</span>
                        <span class="info-box-number">{{ number_format($total_payments, 2) }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box info-box-new-style">
                    <span class="info-box-icon bg-yellow"><i class="fa fa-percent"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">@lang('Loan::lang.total_interest')</span>
                        <span class="info-box-number">{{ number_format($total_interest, 2) }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box info-box-new-style">
                    <span class="info-box-icon bg-red"><i class="fa fa-balance-scale"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">@lang('Loan::lang.total_outstanding')</span>
                        <span class="info-box-number">{{ number_format($total_outstanding, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters Section -->
        <form action="{{ route('Loan.reports.index') }}" method="GET" id="reports_filter_form">
            <input type="hidden" name="start_date" id="start_date" value="{{ request('start_date') }}">
            <input type="hidden" name="end_date" id="end_date" value="{{ request('end_date') }}">
            @component('components.filters', ['title' => __('report.filters'), 'closed' => false])
            <div class="col-md-3">
                <div class="form-group">
                    <label for="date_range">@lang('report.date_range'):</label>
                    {!! Form::text('date_range', null, [
        'placeholder' => 'Select date range',
        'class' => 'form-control',
        'id' => 'date_range',
        'readonly' => false,
    ]) !!}
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label for="loan_type">@lang('Loan::lang.loan_type'):</label>
                    <select name="loan_type" id="loan_type" class="form-control select2" style="width:100%">
                        <option value="">@lang('Loan::lang.all_loan_types')</option>
                        <option value="personal" {{ request('loan_type') == 'personal' ? 'selected' : '' }}>
                            @lang('Loan::lang.personal_loan')
                        </option>
                        <option value="business" {{ request('loan_type') == 'business' ? 'selected' : '' }}>
                            @lang('Loan::lang.business_loan')
                        </option>
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label for="status">@lang('Loan::lang.status'):</label>
                    <select name="status" id="status" class="form-control select2" style="width:100%">
                        <option value="">@lang('Loan::lang.all_statuses')</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>
                            @lang('Loan::lang.active')
                        </option>
                        <option value="partially_paid" {{ request('status') == 'partially_paid' ? 'selected' : '' }}>
                            @lang('Loan::lang.partially_paid')
                        </option>
                        <option value="fully_paid" {{ request('status') == 'fully_paid' ? 'selected' : '' }}>
                            @lang('Loan::lang.fully_paid')
                        </option>
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label for="location_id">@lang('Loan::lang.branch'):</label>
                    <select name="location_id" id="location_id" class="form-control select2" style="width:100%">
                        <option value="">All Branches</option>
                        @foreach ($locations as $id => $name)
                            <option value="{{ $id }}" {{ request('location_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label for="total_amount_min">@lang('Loan::lang.total_amount_min'):</label>
                    <input type="number" step="0.01" name="total_amount_min" id="total_amount_min" class="form-control"
                        value="{{ request('total_amount_min') }}">
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label for="total_amount_max">@lang('Loan::lang.total_amount_max'):</label>
                    <input type="number" step="0.01" name="total_amount_max" id="total_amount_max" class="form-control"
                        value="{{ request('total_amount_max') }}">
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group" style="margin-top: 24px;">
                    <button type="submit" class="tw-dw-btn tw-dw-btn-primary tw-dw-btn-sm tw-text-white"
                        style="margin-right: 5px;">@lang('Loan::lang.apply_filters')</button>
                    <a href="{{ route('Loan.reports.index') }}"
                        class="tw-dw-btn tw-dw-btn-neutral tw-dw-btn-sm tw-text-white"
                        style="margin-right: 5px;">@lang('Loan::lang.reset')</a>
                    <a href="{{ route('Loan.reports.export') }}"
                        class="tw-dw-btn tw-dw-btn-success tw-dw-btn-sm tw-text-white">@lang('Loan::lang.export_excel')</a>
                </div>
            </div>
            @endcomponent
        </form>

        <!-- Charts Section -->
        <div class="row">
            <div class="col-md-6">
                @component('components.widget', ['class' => 'box-primary', 'title' => __('Loan::lang.loans_status_distribution')])
                <canvas id="statusChart" height="200"></canvas>
                @endcomponent
            </div>
            <div class="col-md-6">
                @component('components.widget', ['class' => 'box-primary', 'title' => __('Loan::lang.loans_by_type')])
                <canvas id="typeChart" height="200"></canvas>
                @endcomponent
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                @component('components.widget', ['class' => 'box-primary', 'title' => __('Loan::lang.total_payments_overview')])
                <canvas id="paymentChart" height="100"></canvas>
                @endcomponent
            </div>
        </div>

        <!-- Average Loan Stats Table -->
        <div class="row">
            <div class="col-md-12">
                @component('components.widget', ['class' => 'box-primary', 'title' => __('Loan::lang.avg_loan_stats')])
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
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
                @endcomponent
            </div>
        </div>

        <!-- Detailed Loans Table -->
        <div class="row">
            <div class="col-md-12">
                @component('components.widget', ['class' => 'box-primary', 'title' => __('Loan::lang.detailed_loans')])
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="detailed_loans_table">
                        <thead>
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
                                <th>{{ __('messages.action') }}</th>
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
                                            <span class="label"
                                                style="background-color: #28a745; color: #fff; font-size: 11px; padding: 4px 8px; border-radius: 12px; display: inline-block;">
                                                {{ __('Loan::lang.active') }}
                                            </span>
                                        @elseif ($loan->status == 'partially_paid')
                                            <span class="label"
                                                style="background-color: #ff9800; color: #fff; font-size: 11px; padding: 4px 8px; border-radius: 12px; display: inline-block;">
                                                {{ __('Loan::lang.partially_paid') }}
                                            </span>
                                        @elseif ($loan->status == 'fully_paid')
                                            <span class="label"
                                                style="background-color: #007bff; color: #fff; font-size: 11px; padding: 4px 8px; border-radius: 12px; display: inline-block;">
                                                {{ __('Loan::lang.fully_paid') }}
                                            </span>
                                        @endif
                                    </td>
                                    <td>{{ $loan->location->name }}</td>
                                    <td>
                                        <a href="{{ route('Loan.loans.show', $loan->id) }}"
                                            class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline tw-dw-btn-info">
                                            <i class="fa fa-eye"></i> @lang('messages.view')
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="12" class="text-center text-muted">@lang('Loan::lang.no_loans_found')</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <!-- Pagination -->
                <div class="text-right">
                    {{ $detailed_loans->links() }}
                </div>
                @endcomponent
            </div>
        </div>
    </section>

    @section('javascript')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            $(document).ready(function () {
                var startDate = '{{ request('start_date') ?? '' }}';
                var endDate = '{{ request('end_date') ?? '' }}';

                var pickerOptions = {
                    ranges: {
                        'Today': [moment(), moment()],
                        'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                        'Last 7 days': [moment().subtract(6, 'days'), moment()],
                        'Last 30 days': [moment().subtract(29, 'days'), moment()],
                        'This month': [moment().startOf('month'), moment().endOf('month')],
                        'Last month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                    },
                    locale: {
                        format: '{{ session('business.date_format') }}',
                        cancelLabel: '{{ __('messages.cancel') }}',
                        applyLabel: 'Apply'
                    },
                    autoUpdateInput: false
                };

                if (startDate && endDate) {
                    pickerOptions.startDate = moment(startDate, 'YYYY-MM-DD');
                    pickerOptions.endDate = moment(endDate, 'YYYY-MM-DD');
                    var fmt = '{{ session('business.date_format') }}';
                    $('#date_range').val(
                        moment(startDate, 'YYYY-MM-DD').format(fmt) + ' - ' + moment(endDate, 'YYYY-MM-DD').format(fmt)
                    );
                }

                $('#date_range').daterangepicker(pickerOptions);

                // Auto-submit on date range apply
                $('#date_range').on('apply.daterangepicker', function (ev, picker) {
                    $(this).val(
                        picker.startDate.format('{{ session('business.date_format') }}') + ' - ' +
                        picker.endDate.format('{{ session('business.date_format') }}')
                    );
                    $('#start_date').val(picker.startDate.format('YYYY-MM-DD'));
                    $('#end_date').val(picker.endDate.format('YYYY-MM-DD'));
                });

                // Clear and auto-submit on cancel
                $('#date_range').on('cancel.daterangepicker', function () {
                    $(this).val('');
                    $('#start_date').val('');
                    $('#end_date').val('');
                    $('#reports_filter_form').submit();
                });

                // Initialize client-side DataTable for sorting and structure
                $('#detailed_loans_table').DataTable({
                    searching: false,
                    paging: false,
                    info: false,
                    columnDefs: [
                        {
                            targets: [11], // Action column
                            orderable: false,
                            searchable: false
                        }
                    ]
                });

                // Status Pie Chart
                const statusCtx = document.getElementById('statusChart').getContext('2d');
                new Chart(statusCtx, {
                    type: 'pie',
                    data: {
                        labels: @json($chart_data['status_labels']),
                        datasets: [{
                            data: @json($chart_data['status_counts']),
                            backgroundColor: ['#28a745', '#ff9800', '#007bff'],
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
                            backgroundColor: '#007bff',
                            borderColor: '#0056b3',
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
                            borderColor: '#28a745',
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
            });
        </script>
    @endsection

@endsection