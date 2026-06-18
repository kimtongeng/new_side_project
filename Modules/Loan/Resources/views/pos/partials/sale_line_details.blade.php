<div class="sale-line-details">
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>@lang('Loan::lang.product')</th>
                <th>@lang('lang_v1.quantity')</th>
                <th>@lang('lang_v1.unit_price')</th>
                <th>@lang('lang_v1.discount')</th>
                <th>@lang('lang_v1.tax')</th>
                <th>@lang('lang_v1.sub_total')</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($sell_details as $line)
                <tr>
                    <td>
                        {{ $line->product_name }}
                        @if ($line->product_type == 'combo')
                            <i class="fa fa-info-circle text-info" data-toggle="tooltip" title="@lang('Loan::lang.combo_product')"></i>
                        @endif
                        @if (!empty($line->sell_line_note))
                            <br><small>{{ $line->sell_line_note }}</small>
                        @endif
                        @if (!empty($line->lot_number))
                            <br><small>@lang('lang_v1.lot_number'): {{ $line->lot_number }}</small>
                        @endif
                        @if (!empty($line->exp_date))
                            <br><small>@lang('lang_v1.expiry'): {{ \Carbon::createFromFormat('Y-m-d', $line->exp_date)->format('m/d/Y') }}</small>
                        @endif
                    </td>
                    <td>{{ $line->quantity_ordered }} {{ $line->unit }}</td>
                    <td>{{ number_format($line->unit_price_before_discount, 2) }}</td>
                    <td>
                        @if ($line->line_discount_amount > 0)
                            {{ number_format($line->line_discount_amount, 2) }}
                            ({{ $line->line_discount_type == 'percentage' ? $line->line_discount_amount . '%' : 'fixed' }})
                        @else
                            0
                        @endif
                    </td>
                    <td>{{ number_format($line->item_tax, 2) }}</td>
                    <td>{{ number_format($line->quantity_ordered * $line->unit_price, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

@stop

@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {
            // Initialize tooltips
            $('[data-toggle="tooltip"]').tooltip();
        });
    </script>
@endsection