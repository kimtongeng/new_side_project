@if ($big)
    <span class=" tw-text-green-900 tw-font-bold tw-text-base md:tw-text-2xl number">|</span>
    <div style="display:flex;gap:10px">
        @foreach ($currency_exchange->take(4) as $key => $item)
            <span id="total_payable_exchange" data-item='@json($item)'
                class="tw-text-green-900 tw-font-bold tw-text-base md:tw-text-2xl number">0.00</span>

            @if (!$loop->last)
                <span class="tw-text-green-900 tw-font-bold tw-text-base md:tw-text-2xl number">|</span>
            @endif
        @endforeach
    </div>
@else
    <span class="tw-text-green-900 tw-font-bold tw-text-base md:tw-text-2xl number">|</span>
    <div style="display:flex;gap:10px">
        @foreach ($currency_exchange->take(4) as $key => $item)
            <span id="total_payable_exchange" data-item='@json($item)'
                class="tw-text-green-900 tw-font-bold tw-text-sm number">0.00</span>

            @if (!$loop->last)
                <span class="tw-text-green-900 tw-font-bold tw-text-base md:tw-text-2xl number">|</span>
            @endif
        @endforeach
    </div>
@endif
