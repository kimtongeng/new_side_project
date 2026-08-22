<div>
    @lang('hms::lang.id'): {{ $info->ref_no }} <br>
    {{ $info->contact->name }} <br>
    {{ $info->contact->mobile }} <br>
    @if($info->status == 'confirmed')
        <h6 class="bg-green badge">{{ __('hms::lang.confirmed') }}</h6>
    @elseif($info->status == 'pending')
        <h6 class="bg-yellow badge">{{ __('hms::lang.pending') }}</h6>
    @elseif($info->status == 'cancelled')
        <h6 class="bg-red badge">{{ __('hms::lang.cancelled') }}</h6>
    @endif
    <br>
    @lang('hms::lang.stay') : {{@format_datetime($info->hms_booking_arrival_date_time)}} - {{ @format_datetime($info->hms_booking_departure_date_time) }}
    <hr>
    <br>
</div>