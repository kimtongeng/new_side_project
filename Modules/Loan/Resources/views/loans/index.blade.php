@extends('layouts.app')

@section('title', __('Loan::lang.loans'))

@section('content')

    @include('Loan::layouts.nav')

    <!-- Header Section -->
    <section class="content-header no-print">
        <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">
            {{ __('Loan::lang.loans') }}
        </h1>
    </section>

    <!-- Main content -->
    <section class="content no-print">
        <!-- Filters Section -->
        <form action="{{ route('Loan.loans.index') }}" method="GET" id="loan_filter_form">
            @component('components.filters', ['title' => __('report.filters'), 'closed' => false])
                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label('recipient_name', __('Loan::lang.recipient_name') . ':') !!}
                        {!! Form::select('recipient_name', $recipients, request('recipient_name'), [
                            'class' => 'form-control select2',
                            'style' => 'width:100%',
                            'placeholder' => __('messages.all'),
                            'id' => 'recipient_name',
                        ]) !!}
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label('loan_type', __('Loan::lang.loan_type') . ':') !!}
                        {!! Form::select('loan_type', [
                            'personal' => __('Loan::lang.personal_loan'),
                            'business' => __('Loan::lang.business_loan'),
                        ], request('loan_type'), [
                            'class' => 'form-control select2',
                            'style' => 'width:100%',
                            'placeholder' => __('Loan::lang.all_loan_types'),
                            'id' => 'loan_type',
                        ]) !!}
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label('status', __('Loan::lang.status') . ':') !!}
                        {!! Form::select('status', [
                            'active' => __('Loan::lang.active'),
                            'partially_paid' => __('Loan::lang.partially_paid'),
                            'fully_paid' => __('Loan::lang.fully_paid'),
                        ], request('status'), [
                            'class' => 'form-control select2',
                            'style' => 'width:100%',
                            'placeholder' => __('Loan::lang.all_statuses'),
                            'id' => 'loan_status',
                        ]) !!}
                    </div>
                </div>
            @endcomponent
        </form>

        @component('components.widget', ['class' => 'box-primary', 'title' => __('Loan::lang.manage_loan_details')])
            @slot('tool')
                <div class="box-tools pull-right">
                    <a class="tw-dw-btn tw-bg-gradient-to-r tw-from-indigo-600 tw-to-blue-500 tw-font-bold tw-text-white tw-border-none tw-rounded-full"
                        href="{{ route('Loan.loans.create') }}"
                        id="add_loan">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round"
                            class="icon icon-tabler icons-tabler-outline icon-tabler-plus">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                            <path d="M12 5l0 14" />
                            <path d="M5 12l14 0" />
                        </svg> @lang('Loan::lang.add_new_loan')
                    </a>
                </div>
            @endslot
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="loans_table">
                    <thead>
                        <tr>
                            <th>{{ __('messages.action') }}</th>
                            <th>{{ __('Loan::lang.recipient_name') }}</th>
                            <th>{{ __('Loan::lang.start_date') }}</th>
                            <th>{{ __('Loan::lang.amount') }}</th>
                            <th>{{ __('Loan::lang.total_amount') }}</th>
                            <th>{{ __('Loan::lang.duration') }}</th>
                            <th>{{ __('Loan::lang.interest_rate') }}</th>
                            <th>{{ __('Loan::lang.loan_type') }}</th>
                            <th>{{ __('Loan::lang.status') }}</th>
                            <th>{{ __('Loan::lang.branch') }}</th>
                            <th>{{ __('Loan::lang.description') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($loans as $loan)
                            <tr>
                                 <td>
                                    <div class="btn-group">
                                        <button class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline tw-dw-btn-info tw-w-max dropdown-toggle" type="button" data-toggle="dropdown" aria-expanded="false">
                                            @lang('messages.action')
                                            <span class="caret"></span>
                                            <span class="sr-only">
                                            @lang('messages.action')
                                            </span>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-left" role="menu">
                                            <li>
                                                <a href="{{ route('Loan.loans.show', $loan->id) }}" class="cursor-pointer">
                                                    <i class="fa fa-eye"></i> @lang('Loan::lang.details')
                                                </a>
                                            </li>
                                            <li>
                                                <a href="{{ route('Loan.loans.edit', $loan->id) }}" class="cursor-pointer">
                                                    <i class="fa fa-edit"></i> @lang('Loan::lang.edit')
                                                </a>
                                            </li>
                                            <li>
                                                <form action="{{ route('Loan.loans.destroy', $loan->id) }}" method="POST" style="display:none;" id="delete-loan-form-{{ $loan->id }}">
                                                    @csrf
                                                    @method('DELETE')
                                                </form>
                                                <a href="#" class="cursor-pointer" onclick="event.preventDefault(); if(confirm('{{ __('Loan::lang.confirm_delete') }}')) { document.getElementById('delete-loan-form-{{ $loan->id }}').submit(); }">
                                                    <i class="fa fa-trash"></i> @lang('Loan::lang.delete')
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                                <td>{{ $loan->recipient_name }}</td>
                                <td>{{ $loan->start_date }}</td>
                                <td>{{ number_format($loan->amount, 3) }}</td>
                                <td>{{ number_format($loan->total_amount, 3) }}</td>
                                <td>{{ $loan->duration_count }} {{ __('Loan::lang.months') }}</td>
                                <td>{{ $loan->interest_rate }}%</td>
                                <td>{{ $loan->loan_type == 'personal' ? __('Loan::lang.personal_loan') : __('Loan::lang.business_loan') }}</td>
                                <td>
                                    @if ($loan->status == 'active')
                                        <span class="label" style="background-color: #28a745; color: #fff; font-size: 11px; padding: 4px 8px; border-radius: 12px; display: inline-block;">
                                            {{ __('Loan::lang.active') }}
                                        </span>
                                    @elseif ($loan->status == 'partially_paid')
                                        <span class="label" style="background-color: #ff9800; color: #fff; font-size: 11px; padding: 4px 8px; border-radius: 12px; display: inline-block;">
                                            {{ __('Loan::lang.partially_paid') }}
                                        </span>
                                    @elseif ($loan->status == 'fully_paid')
                                        <span class="label" style="background-color: #007bff; color: #fff; font-size: 11px; padding: 4px 8px; border-radius: 12px; display: inline-block;">
                                            {{ __('Loan::lang.fully_paid') }}
                                        </span>
                                    @endif
                                </td>
                                <td>{{ $loan->location->name }}</td>
                                <td>{{ $loan->description ?? '—' }}</td>
                               
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="text-center text-muted">🚫 {{ __('Loan::lang.no_loans_found') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <!-- Pagination -->
            <div class="text-right">
                {{ $loans->links() }}
            </div>
        @endcomponent
    </section>
@endsection

@section('javascript')
    <script>
        $(document).ready(function () {
            // Auto-submit when any select2 filter changes
            $('#recipient_name, #loan_type, #loan_status').on('change', function () {
                $('#loan_filter_form').submit();
            });

            // Initialize client-side DataTable for sorting and structure
            $('#loans_table').DataTable({
                searching: false,
                paging: false,
                info: false,
                columnDefs: [
                    {
                        targets: [0], // Action column
                        orderable: false,
                        searchable: false
                    }
                ]
            });
        });
    </script>
@endsection