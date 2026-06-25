@extends('layouts.app')

@section('title', __('stockcount::lang.stock_counts'))

@section('content')
    <section class="content-header no-print">
        <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">
            @lang('stockcount::lang.stock_counts')
        </h1>
    </section>

    <section class="content no-print">
        @component('components.filters', ['title' => __('report.filters'), 'closed' => false])
        <div class="col-md-4">
            <div class="form-group">
                {!! Form::label('filter_location_id', __('purchase.business_location') . ':') !!}
                {!! Form::select('filter_location_id', $business_locations, null, [
        'class' => 'form-control select2',
        'style' => 'width:100%',
        'placeholder' => __('lang_v1.all'),
    ]) !!}
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                {!! Form::label('filter_status', __('sale.status') . ':') !!}
                {!! Form::select('filter_status', [
        'draft' => __('stockcount::lang.draft'),
        'active' => __('stockcount::lang.active'),
        'completed' => __('stockcount::lang.completed'),
        'cancelled' => __('stockcount::lang.cancelled')
    ], null, [
        'class' => 'form-control select2',
        'style' => 'width:100%',
        'placeholder' => __('lang_v1.all'),
    ]) !!}
            </div>
        </div>
        @endcomponent

        @component('components.widget', ['class' => 'box-primary', 'title' => __('stockcount::lang.all_stock_counts')])
        @slot('tool')
        <div class="box-tools" style="display: flex; gap: 5px; align-items: center;">
            <a class="btn btn-default" href="{{ url('/home') }}">
                <i class="fa fa-arrow-left"></i> @lang('messages.back')
            </a>
            @can('stock_count.create')
                <a class="btn btn-primary"
                    href="{{ action([\Modules\StockCount\Http\Controllers\StockCountController::class, 'create']) }}">
                    <i class="fa fa-plus"></i> @lang('messages.add')
                </a>
            @endcan
        </div>
        @endslot

        <div class="table-responsive">
            <table class="table table-bordered table-striped" id="stock_count_table" style="width: 100%;">
                <thead>
                    <tr>
                        <th>@lang('stockcount::lang.session_name')</th>
                        <th>@lang('stockcount::lang.location')</th>
                        <th>@lang('stockcount::lang.status')</th>
                        <th>Items Count</th>
                        <th>@lang('stockcount::lang.blind_count')</th>
                        <th>Added By</th>
                        <th>Created At</th>
                        <th>@lang('messages.action')</th>
                    </tr>
                </thead>
            </table>
        </div>
        @endcomponent
    </section>

@endsection

@section('javascript')
    <script>
        $(document).ready(function () {
            var stock_count_table = $('#stock_count_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ action([\Modules\StockCount\Http\Controllers\StockCountController::class, 'index']) }}",
                    data: function (d) {
                        d.location_id = $('#filter_location_id').val();
                        d.status = $('#filter_status').val();
                    }
                },
                columns: [
                    { data: 'name', name: 'name' },
                    { data: 'location.name', name: 'location.name', defaultContent: '' },
                    { data: 'status', name: 'status', searchable: false },
                    { data: 'items_count', name: 'items_count', orderable: false, searchable: false },
                    { data: 'blind_count', name: 'blind_count', orderable: false, searchable: false },
                    { data: 'creator.first_name', name: 'creator.first_name', defaultContent: '' },
                    { data: 'created_at', name: 'created_at' },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ],
                rawColumns: ['action', 'status', 'created_at']
            });

            $(document).on('change', '#filter_location_id, #filter_status', function () {
                stock_count_table.ajax.reload();
            });

            $(document).on('click', 'a.delete_stock_count', function (e) {
                e.preventDefault();
                var url = $(this).data('href');
                swal({
                    title: LANG.sure,
                    text: "You won't be able to revert this count session!",
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                }).then((willDelete) => {
                    if (willDelete) {
                        $.ajax({
                            method: "DELETE",
                            url: url,
                            dataType: "json",
                            data: {
                                _token: "{{ csrf_token() }}"
                            },
                            success: function (result) {
                                if (result.success) {
                                    toastr.success(result.msg);
                                    stock_count_table.ajax.reload();
                                } else {
                                    toastr.error(result.msg);
                                }
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection