<link rel="stylesheet" href="{{ asset('css/app.css?v=' . $asset_v) }}">
<style type="text/css">
    .box {
        border: 1px solid;
    }

    .table-pdf {
        width: 100%;
    }

    .table-pdf td,
    .table-pdf th {
        padding: 6px;
        text-align: left;
    }

    .w-20 {
        width: 20%;
        float: left;
    }

    .checklist {
        padding: 5px 15px;
        width: 100%;
    }

    .checkbox {
        width: 20%;
        float: left;
    }

    .checkbox-text {
        width: 80%;
        float: left;
    }

    .content-div {
        padding: 6px;
    }

    .table-slim {
        width: 100%;
    }

    .table-slim td,
    .table-slim th {
        padding: 1px !important;
        font-size: 12px;
    }

    .font-14 {
        font-size: 14px;
    }

    .font-12 {
        font-size: 12px;
    }

    body {
        font-size: 11px;
    }
</style>
@php
    $settings = json_decode($business->hms_settings);
@endphp
<div class="width-100 box mb-10">
    @if (!empty(Session::get('business.logo')))
        <div class="f-left" style="width:25%">
            <img src="{{ asset('uploads/business_logos/' . Session::get('business.logo')) }}" alt="Logo"
                style="width: auto; max-height: 90px; margin: auto;">
        </div>
    @endif
    <div class="f-left" align="center" style="width:25%">
        <p style="text-align: center;">
            <strong class="font-14">
                {{ $business->name }}
            </strong>
        </p>
    </div>

    @if (!empty($settings->booking_pdf->address))
        <div class="f-left" align="center" style="width:25%">
            <p style="text-align: left;">
                <strong>{!! nl2br(e($settings->booking_pdf->address)) !!}</strong>
            </p>
        </div>
    @endif
    <div class="f-left" align="center" style="width:25%">
        <p style="text-align: left;">
            @if (!empty($settings->booking_pdf->phone))
                <strong style="font-size: 10px;">
                    @lang('contact.contact'): {{ $settings->booking_pdf->phone }}
                </strong>
            @endif
            @if (!empty($settings->booking_pdf->email))
                <br>
                <strong style="font-size: 10px">
                    @lang('hms::lang.email'): {{ $settings->booking_pdf->email }}
                </strong>
            @endif

            @if (!empty($settings->booking_pdf->website))
                <br>
                <strong style="font-size: 10px;">
                    @lang('hms::lang.website'): {{ $settings->booking_pdf->website }}
                </strong>
            @endif
        </p>
    </div>
</div>
<div class="width-100 box mb-10">
    <table class="no-border table-pdf">
        <tr>
            <th>@lang('hms::lang.booking_Id'):</th>
            <th>@lang('hms::lang.arrival_date_time'):</th>
            <th>@lang('hms::lang.departure_date_time'):</th>
            <th>@lang('hms::lang.nights'):</th>

            @if (!empty($transaction->check_in))
                <th>@lang('hms::lang.check_in'):</th>
            @endif

            @if (!empty($transaction->check_out))
                <th>@lang('hms::lang.check_out'):</th>
            @endif

            <th>@lang('hms::lang.status'):</th>
        </tr>
        <tr>
            <td style="padding-top: 8">
                {{ $transaction->ref_no }}
            </td>
            <td style="padding-top: 8">
                {{ @format_datetime($transaction->hms_booking_arrival_date_time) }}
            </td>
            <td style="padding-top: 8">
                {{ @format_datetime($transaction->hms_booking_departure_date_time) }}
            </td>
            <td style="padding-top: 8">
                {{ $no_of_nights }} {{ $no_of_nights == 1 ? __('hms::lang.night') : __('hms::lang.nights') }}
            </td>
            @if (!empty($transaction->check_in))
                <td style="padding-top: 8">
                    {{ @format_datetime($transaction->check_in) }}
                </td>
            @endif
            @if (!empty($transaction->check_out))
                <td style="padding-top: 8">
                    {{ @format_datetime($transaction->check_out) }}
                </td>
            @endif
            <td style="padding-top: 8">
                @if($transaction->status == 'confirmed')
                    {{ __('hms::lang.confirmed') }}
                @elseif($transaction->status == 'pending')
                    {{ __('hms::lang.pending') }}
                @elseif($transaction->status == 'cancelled')
                    {{ __('hms::lang.cancelled') }}
                @else
                    {{ ucfirst($transaction->status) }}
                @endif
            </td>
        </tr>
    </table>
</div>
<div class="box mb-10">
    <table class="table-pdf">
        <tr>
            <td colspan="2" style="vertical-align: top;">
                <table class="width-100">
                    <tr>
                        <th>@lang('sale.customer_name'):</th>
                        <td>{{ $transaction->contact->name }}</td>
                    </tr>
                    <tr>
                        <th>@lang('hms::lang.address'):</th>
                        <td>
                            @if ($transaction->contact->landmark)
                                {{ $transaction->contact->landmark }},
                            @endif

                            {{ $transaction->contact->city }}

                            @if ($transaction->contact->state)
                                {{ ', ' . $transaction->contact->state }}
                            @endif
                            <br>
                            @if ($transaction->contact->country)
                                {{ $transaction->contact->country }}
                            @endif
                        </td>
                    </tr>
                    @if ($transaction->contact->mobile)
                        <tr>
                            <th>@lang('contact.mobile'):</th>
                            <td>{{ $transaction->contact->mobile }}</td>
                        </tr>
                    @endif
                    @if ($transaction->contact->alternate_number)
                        <tr>
                            <th>@lang('contact.alternate_contact_number'):</th>
                            <td>{{ $transaction->contact->alternate_number }}</td>
                        </tr>
                    @endif
                    @if ($transaction->contact->landline)
                        <tr>
                            <th>@lang('contact.landline'):</th>
                            <td>{{ $transaction->contact->alternate_number }}</td>
                        </tr>
                    @endif
                </table>
            </td>
        </tr>
    </table>
</div>

<div class="width-100 box mb-10">
    <table class="no-border table-pdf">
        <thead>
            <tr>
                <th>@lang('hms::lang.rooms')</th>
            </tr>
        </thead>
        <tr>
            <th>@lang('hms::lang.type')</th>
            <th>@lang('hms::lang.room_no')</th>
            <th>@lang('hms::lang.no_of_adult')</th>
            <th>@lang('hms::lang.no_of_child')</th>
            <th>@lang('hms::lang.price_per_night')</th>
            <th>@lang('hms::lang.price')</th>
        </tr>
        @foreach ($booking_rooms as $room)
            <tr>
                <td style="padding-top: 8">
                    {{ $room->type }}

                </td>
                <td style="padding-top: 8">
                    {{ $room->room_number }}
                </td>
                <td style="padding-top: 8">
                    {{ $room->adults }}
                </td>
                <td style="padding-top: 8">
                    {{ $room->childrens }}
                </td>
                <td style="padding-top: 8">
                    @format_currency($room->price)
                </td>
                <td style="padding-top: 8">
                    @format_currency($room->total_price)
                </td>
            </tr>
        @endforeach
    </table>
</div>
<div class="width-100 box mb-10">
    <table class="no-border table-pdf">
        <thead>
            <tr>
                <th>@lang('hms::lang.extras')</th>
            </tr>
        </thead>
        @foreach ($extras as $index => $extra)
            @if (in_array($extra->id, $extras_id))
                <tr>
                    <td>
                        {{ $extra->name }} / @format_currency($extra->price) - {{ str_replace('_', ' ', $extra->price_per) }}
                    </td>
                </tr>
            @endif
        @endforeach
    </table>
</div>

@php 
    // Determine discount type and calculate actual discount amount
    $discount_type = $transaction->discount_type; // Default
    $discount_display_value = 0;
    
    // If coupon is applied, use discount_amount directly
    if (!empty($transaction->hms_coupon_id)) {
        $discount_display_value = $transaction->discount_amount;
    } else {
        if ($transaction->discount_type == 'percentage') {
            $discount_type = 'percentage';
            $discount_display_value = ($transaction->discount_amount / 100) * $transaction->total_before_tax;
        } else {
            $discount_type = 'fixed';
            $discount_display_value = $transaction->discount_amount;
        }
    }
@endphp

<div class="width-100 box mb-10">
    <table class="no-border table-pdf">
        <tr>
            <th>@lang('hms::lang.room_price'):</th>
            <th>@lang('hms::lang.extra_price'):</th>
            @if ($discount_display_value > 0)
                @if ($discount_type == 'percentage')
                    <th>@lang('hms::lang.discount'): ({{ number_format($transaction->discount_amount, 2) }} % )</th>
                @else
                    <th>@lang('hms::lang.discount'):</th>
                @endif
            @else
                <th>@lang('hms::lang.discount'):</th>
            @endif
            @if($transaction->tax_amount > 0)
                <th>@lang('hms::lang.booking_tax'):</th>
            @endif
            <th>@lang('hms::lang.total'):</th>
            <th>@lang('hms::lang.total_paid'):</th>
            <th>@lang('hms::lang.due'):</th>
        </tr>
        <tr>
            <td style="padding-top: 8">
                @format_currency($transaction->room_price)
            </td>
            <td style="padding-top: 8">
                @format_currency($transaction->extra_price)
            </td>
            <td style="padding-top: 8">
                @format_currency($discount_display_value)
                @if (!empty($transaction->hms_coupon_id) && !empty($transaction->coupon_code))
                    <br><small>({{ $transaction->coupon_code }})</small>
                @endif
            </td>
            @if($transaction->tax_amount > 0)
                <td style="padding-top: 8">
                    @format_currency($transaction->tax_amount)
                    @if(!empty($transaction->tax) && !empty($transaction->tax->name))
                        <br><small>({{ $transaction->tax->name }})</small>
                    @endif
                </td>
            @endif

            <td style="padding-top: 8">
                @format_currency($transaction->final_total)
            </td>
            <td style="padding-top: 8">
                @format_currency($transaction->total_paid)
            </td>
            <td style="padding-top: 8">
                @format_currency($transaction->final_total - $transaction->total_paid)
            </td>
        </tr>
    </table>
</div>

@if(!empty($transaction->payment_lines) && count($transaction->payment_lines) > 0)
<div class="width-100 box mb-10">
    <table class="no-border table-pdf">
        <thead>
            <tr>
                <th colspan="6">@lang('sale.payment_info')</th>
            </tr>
            <tr>
                <th>#</th>
                <th>@lang('messages.date')</th>
                <th>@lang('purchase.ref_no')</th>
                <th>@lang('sale.amount')</th>
                <th>@lang('sale.payment_mode')</th>
                <th>@lang('sale.payment_note')</th>
            </tr>
        </thead>
        <tbody>
            @php
                $total_paid = 0;
            @endphp
            @foreach($transaction->payment_lines as $payment_line)
                @php
                    if($payment_line->is_return == 1){
                        $total_paid -= $payment_line->amount;
                    } else {
                        $total_paid += $payment_line->amount;
                    }
                @endphp
                <tr>
                    <td style="padding-top: 8">{{ $loop->iteration }}</td>
                    <td style="padding-top: 8">{{ @format_date($payment_line->paid_on) }}</td>
                    <td style="padding-top: 8">{{ $payment_line->payment_ref_no }}</td>
                    <td style="padding-top: 8">@format_currency($payment_line->amount)</td>
                    <td style="padding-top: 8">
                        {{ $payment_types[$payment_line->method] ?? $payment_line->method }}
                        @if($payment_line->is_return == 1)
                            <br/>
                            <small>(@lang('lang_v1.change_return'))</small>
                        @endif
                    </td>
                    <td style="padding-top: 8">
                        @if($payment_line->note) 
                            {{ ucfirst($payment_line->note) }}
                        @else
                            --
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

@if (!empty($settings->booking_pdf->text_after_table))
<div class="width-100 box mb-10">
    <div class="content-div">
        {!! $settings->booking_pdf->text_after_table !!}
    </div>
</div>
@endif

<footer class="container-fluid text-center" style="position: absolute; bottom: 80; width: 90%;">
    <div class="row">
        <div class="col-xs-12">
            <div class="footer-content">

                @if (!empty($settings->booking_pdf->footer_text))
                    <div>
                        {!! $settings->booking_pdf->footer_text !!}
                    </div>
                @endif
            </div>
        </div>
    </div>
</footer>
