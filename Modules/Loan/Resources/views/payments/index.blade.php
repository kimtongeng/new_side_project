@extends('layouts.app')

@section('title', __('Loan::lang.payments'))

@section('content')

    @include('Loan::layouts.nav')

    <div class="container my-5">
        <!-- Header Section -->
        <div class="row mb-4">
            <div class="col-md-12 text-center">
                <h1 class="display-4 text-primary font-weight-bold">💵 {{ __('Loan::lang.payments') }}</h1>
                <p class="lead text-muted">{{ __('Loan::lang.manage_all_payments') }}</p>
                <hr class="w-25 mx-auto border-primary">
            </div>
        </div>

        <!-- Filters Section -->
        <form action="{{ route('Loan.payments.index') }}" method="GET" id="payments_filter_form">
            <input type="hidden" name="payment_start_date" id="payment_start_date" value="{{ $request->payment_start_date ?? '' }}">
            <input type="hidden" name="payment_end_date" id="payment_end_date" value="{{ $request->payment_end_date ?? '' }}">

            @component('components.filters', ['title' => __('Loan::lang.filters')])
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('recipient_name', __('Loan::lang.recipient_name') . ':') !!}
                        {!! Form::select('recipient_name', $recipients, $request->recipient_name ?? null, [
                            'id'          => 'recipient_name',
                            'class'       => 'form-control select2',
                            'style'       => 'width:100%',
                            'placeholder' => __('messages.all'),
                        ]) !!}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('loan_id', __('Loan::lang.loan_id') . ':') !!}
                        {!! Form::text('loan_id', $request->loan_id ?? null, [
                            'id'    => 'loan_id',
                            'class' => 'form-control',
                        ]) !!}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('payment_date_range', __('Loan::lang.payment_date') . ':') !!}
                        {!! Form::text('payment_date_range', null, [
                            'id'          => 'payment_date_range',
                            'class'       => 'form-control',
                            'placeholder' => __('lang_v1.select_a_date_range'),
                            'readonly'    => true,
                        ]) !!}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('total_amount_min', __('Loan::lang.total_amount_min') . ':') !!}
                        {!! Form::number('total_amount_min', $request->total_amount_min ?? null, [
                            'id'    => 'total_amount_min',
                            'class' => 'form-control',
                            'step'  => '0.01',
                        ]) !!}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('total_amount_max', __('Loan::lang.total_amount_max') . ':') !!}
                        {!! Form::number('total_amount_max', $request->total_amount_max ?? null, [
                            'id'    => 'total_amount_max',
                            'class' => 'form-control',
                            'step'  => '0.01',
                        ]) !!}
                    </div>
                </div>
                @slot('tool')
                    <div class="box-tools pull-right" style="margin-top: 6px; margin-right: 10px;">
                        <a href="{{ route('Loan.payments.export.excel') }}" class="tw-dw-btn tw-dw-btn-success tw-dw-btn-sm tw-text-white">
                            <i class="fa fa-file-excel-o"></i> {{ __('Loan::lang.export_excel') }}
                        </a>
                    </div>
                @endslot
            @endcomponent
        </form>

        <!-- Payments Table -->
        <div class="card shadow-lg border-0">
            <div class="card-header bg-info text-white text-center">
                <h4 class="mb-0">📋 {{ __('Loan::lang.all_payments') }}</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="payments-table" class="table table-striped table-hover">
                        <thead class="bg-primary text-white">
                            <tr>
                                <th>#</th>
                                <th>📅 {{ __('Loan::lang.payment_date') }}</th>
                                <th>💵 {{ __('Loan::lang.payment_amount') }}</th>
                                <th>👤 {{ __('Loan::lang.recipient') }}</th>
                                <th>🆔 {{ __('Loan::lang.loan_id') }}</th>
                                <th>💰 {{ __('Loan::lang.total_amount') }}</th>
                                <th>💳 {{ __('Loan::lang.total_paid') }}</th>
                                <th>💸 {{ __('Loan::lang.remaining_balance') }}</th>
                                <th>⚙️ {{ __('Loan::lang.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($payments as $payment)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $payment->payment_date }}</td>
                                    <td class="text-success font-weight-bold">{{ number_format($payment->amount, 2) }}</td>
                                    <td>{{ $payment->loan->recipient_name }}</td>
                                    <td>{{ $payment->loan->id }}</td>
                                    <td>{{ number_format($payment->loan->total_amount, 2) }}</td>
                                    <td>{{ number_format($payment->loan->total_paid, 2) }}</td>
                                    <td>{{ number_format($payment->loan->remaining_balance, 2) }}</td>
                                    <td>
                                        <a href="{{ route('Loan.payments.show', $payment->id) }}" class="btn btn-info btn-sm">
                                            👁️ {{ __('Loan::lang.view') }}
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted">🚫 {{ __('Loan::lang.no_payments_found') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-center mt-4">
            {{ $payments->links() }}
        </div>
    </div>

@endsection

@section('javascript')
<script type="text/javascript">
    $(document).ready(function () {
        var startDate = '{{ $request->payment_start_date ?? '' }}';
        var endDate   = '{{ $request->payment_end_date ?? '' }}';

        var pickerOptions = {
            ranges: {
                'Today':        [moment(), moment()],
                'Yesterday':    [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                'Last 7 days':  [moment().subtract(6, 'days'), moment()],
                'Last 30 days': [moment().subtract(29, 'days'), moment()],
                'This month':   [moment().startOf('month'), moment().endOf('month')],
                'Last month':   [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            },
            locale: {
                format: '{{ session('business.date_format') }}',
                cancelLabel: '{{ __('messages.cancel') }}',
                applyLabel: '{{ __('messages.apply') }}'
            },
            autoUpdateInput: false
        };

        if (startDate && endDate) {
            pickerOptions.startDate = moment(startDate, 'YYYY-MM-DD');
            pickerOptions.endDate   = moment(endDate, 'YYYY-MM-DD');
            var fmt = '{{ session('business.date_format') }}';
            $('#payment_date_range').val(
                moment(startDate, 'YYYY-MM-DD').format(fmt) + ' - ' + moment(endDate, 'YYYY-MM-DD').format(fmt)
            );
        }

        $('#payment_date_range').daterangepicker(pickerOptions);

        // Auto-submit on date range apply
        $('#payment_date_range').on('apply.daterangepicker', function (ev, picker) {
            $(this).val(
                picker.startDate.format('{{ session('business.date_format') }}') + ' - ' +
                picker.endDate.format('{{ session('business.date_format') }}')
            );
            $('#payment_start_date').val(picker.startDate.format('YYYY-MM-DD'));
            $('#payment_end_date').val(picker.endDate.format('YYYY-MM-DD'));
            $('#payments_filter_form').submit();
        });

        // Clear and auto-submit on cancel
        $('#payment_date_range').on('cancel.daterangepicker', function () {
            $(this).val('');
            $('#payment_start_date').val('');
            $('#payment_end_date').val('');
            $('#payments_filter_form').submit();
        });

        // Auto-submit on select2 change
        $('#recipient_name').on('change', function () {
            $('#payments_filter_form').submit();
        });

        // Auto-submit on amount fields after user stops typing (debounced)
        var amountTimer;
        $('#total_amount_min, #total_amount_max, #loan_id').on('input', function () {
            clearTimeout(amountTimer);
            amountTimer = setTimeout(function () {
                $('#payments_filter_form').submit();
            }, 800);
        });
    });
</script>
@endsection