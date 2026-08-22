<div class="col-md-{{ round(($dashboard_detail->size / 100) * 12) }}">
    <div
        class="tw-mb-4 tw-transition-all lg:tw-col-span-2 tw-duration-200 tw-bg-white tw-shadow-sm tw-rounded-xl tw-ring-1 hover:tw-shadow-md tw-ring-gray-200">
        <div class="tw-p-2 sm:tw-p-3">
            <div class="box-header text-center">
                <h3 class="box-title pull-left">{{ $dashboard_detail->heading }}</h3>
                @if(!empty($window_start) && !empty($window_end))
                    <h6 class="text-uppercase"
                        style="font-family: Arial, sans-serif; color: #333; letter-spacing: 2px; margin-top: 5px;">
                        {{ @format_date($window_start) }} - {{ @format_date($window_end) }}
                    </h6>
                @endif
            </div>

            <div class="tw-flow-root tw-mt-5 tw-border-gray-200">
                <div class="tw-overflow-x-auto">
                    <div class="tw-inline-block tw-min-w-full tw-py-2 tw-align-middle sm:tw-px-5">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>@lang('hms::lang.id')</th>
                                    <th>@lang('contact.customer')</th>
                                    <th>@lang('hms::lang.stay')</th>
                                    <th>@lang('sale.status')</th>
                                    <th class="not-export">@lang('messages.action')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($bookings as $row)
                                    <tr>
                                        <td>{{ $row->ref_no }}</td>
                                        <td>
                                            {{ $row->contact_name ?? '' }}
                                            @if(!empty($row->contact_mobile))
                                                <br>
                                                <small>{{ $row->contact_mobile }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            {{ @format_datetime($row->hms_booking_arrival_date_time) }}
                                            -
                                            {{ @format_datetime($row->hms_booking_departure_date_time) }}
                                        </td>
                                        <td>
                                            @if($row->status == 'confirmed')
                                                <span class="badge bg-green">{{ __('hms::lang.confirmed') }}</span>
                                            @elseif($row->status == 'pending')
                                                <span class="badge bg-yellow">{{ __('hms::lang.pending') }}</span>
                                            @elseif($row->status == 'cancelled')
                                                <span class="badge bg-red">{{ __('hms::lang.cancelled') }}</span>
                                            @else
                                                {{ $row->status }}
                                            @endif
                                        </td>
                                        <td>
                                            <a class="btn btn-xs btn-success"
                                                href="{{ action([\Modules\Hms\Http\Controllers\HmsBookingController::class, 'show'], ['booking' => $row->id]) }}">
                                                @lang('hms::lang.view')
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5">@lang('lang_v1.no_data')</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


