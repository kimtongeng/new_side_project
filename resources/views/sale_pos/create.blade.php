@extends('layouts.app')

@section('title', __('sale.pos_sale'))

@section('content')
<section class="content no-print">
    <input type="hidden" id="amount_rounding_method" value="{{ $pos_settings['amount_rounding_method'] ?? '' }}">
    @if (!empty($pos_settings['allow_overselling']))
        <input type="hidden" id="is_overselling_allowed">
    @endif
    @if (session('business.enable_rp') == 1)
        <input type="hidden" id="reward_point_enabled">
    @endif
    @php
        $is_discount_enabled = $pos_settings['disable_discount'] != 1 ? true : false;
        $is_rp_enabled = session('business.enable_rp') == 1 ? true : false;
    @endphp
    {!! Form::open([
    'url' => action([\App\Http\Controllers\SellPosController::class, 'store']),
    'method' => 'post',
    'id' => 'add_pos_sell_form',
]) !!}
    <div class="row mb-12">
        <div class="col-md-12 tw-pt-0 tw-mb-14">
            <div
                class="row tw-flex lg:tw-flex-row md:tw-flex-col sm:tw-flex-col tw-flex-col tw-items-start md:tw-gap-4">
                {{-- <div
                    class="@if (empty($pos_settings['hide_product_suggestion'])) col-md-7 @else col-md-10 col-md-offset-1 @endif no-padding pr-12">
                    --}}
                    <div
                        class="tw-px-3 tw-w-full  lg:tw-px-0 lg:tw-pr-0 @if(empty($pos_settings['hide_product_suggestion'])) lg:tw-w-[60%]  @else lg:tw-w-[100%] @endif">

                        <div
                            class="tw-shadow-[rgba(17,_17,_26,_0.1)_0px_0px_16px] tw-rounded-2xl tw-bg-white tw-mb-2 md:tw-mb-8 tw-p-2">

                            {{-- <div class="box box-solid mb-12 @if (!isMobile()) mb-40 @endif"> --}}
                                <div class="box-body pb-0">
                                    {!! Form::hidden('location_id', $default_location->id ?? null, [
    'id' => 'location_id',
    'data-receipt_printer_type' => !empty($default_location->receipt_printer_type)
        ? $default_location->receipt_printer_type
        : 'browser',
    'data-default_payment_accounts' => $default_location->default_payment_accounts ?? '',
]) !!}
                                    <!-- sub_type -->
                                    {!! Form::hidden('sub_type', isset($sub_type) ? $sub_type : null) !!}
                                    <input type="hidden" id="item_addition_method"
                                        value="{{ $business_details->item_addition_method }}">
                                    @include('sale_pos.partials.pos_form')

                                    @include('sale_pos.partials.pos_form_totals')

                                    @include('sale_pos.partials.payment_modal')

                                    @if (empty($pos_settings['disable_suspend']))
                                        @include('sale_pos.partials.suspend_note_modal')
                                    @endif

                                    @if (empty($pos_settings['disable_recurring_invoice']))
                                        @include('sale_pos.partials.recurring_invoice_modal')
                                    @endif
                                </div>
                                {{--
                            </div> --}}
                        </div>
                    </div>
                    @if (empty($pos_settings['hide_product_suggestion']) && !isMobile())
                        <div class="md:tw-no-padding tw-w-full lg:tw-w-[40%] tw-px-5">
                            @include('sale_pos.partials.pos_sidebar')
                        </div>
                    @endif
                </div>
            </div>
        </div>
        @include('sale_pos.partials.pos_form_actions')
        {!! Form::close() !!}
</section>

<!-- This will be printed -->
<section class="invoice print_section" id="receipt_section">
</section>
<div class="modal fade contact_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    @include('contact.create', ['quick_add' => true])
</div>
@if (empty($pos_settings['hide_product_suggestion']) && isMobile())
    @include('sale_pos.partials.mobile_product_suggestions')
@endif
<!-- /.content -->
<div class="modal fade register_details_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
</div>
<div class="modal fade close_register_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
</div>
<!-- quick product modal -->
<div class="modal fade quick_add_product_modal" tabindex="-1" role="dialog" aria-labelledby="modalTitle"></div>

<div class="modal fade" id="expense_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
</div>

@include('sale_pos.partials.configure_search_modal')

@include('sale_pos.partials.recent_transactions_modal')

@include('sale_pos.partials.weighing_scale_modal')

@stop
@section('css')
<style>
        .pos_row_handle {
            cursor: grab;
            color: #aaa;
            transition: color 0.2s;
        }
        .pos_row_handle:hover {
            color: #333;
        }
        .pos_row_handle:active {
            cursor: grabbing;
        }
        /* Row being dragged */
        tr.pos-dragging {
            opacity: 0.4;
            background: #e8f4fd !important;
        }
        /* Drop target row */
        tr.pos-drag-over > td {
            border-top: 3px solid #007bff !important;
        }
        /* Allow drag ghost to escape the table-responsive overflow clip */
        .table-responsive {
            overflow: visible !important;
        }
        .ui-state-highlight {
            background-color: #fcf8e3;
            border: 1px dashed #fbeed5;
            height: 45px;
        }
</style>
<!-- include module css -->
@if (!empty($pos_module_data))
    @foreach ($pos_module_data as $key => $value)
        @if (!empty($value['module_css_path']))
            @includeIf($value['module_css_path'])
        @endif
    @endforeach
@endif
@stop
@section('javascript')
    <!-- SortableJS for drag-and-drop (local copy, conflict-free) -->
    <script src="{{ asset('js/sortable.min.js?v=' . $asset_v) }}"></script>
    <!-- CDN fallback in case local file is missing -->
    <script>
        if (typeof Sortable === 'undefined') {
            document.write('<scr' + 'ipt src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"><\/scr' + 'ipt>');
        }
    </script>
    <script src="{{ asset('js/pos.js?v=' . $asset_v) }}"></script>
    <script src="{{ asset('js/printer.js?v=' . $asset_v) }}"></script>
    <script src="{{ asset('js/product.js?v=' . $asset_v) }}"></script>
    <script src="{{ asset('js/opening_stock.js?v=' . $asset_v) }}"></script>
    @include('sale_pos.partials.keyboard_shortcuts')

    <!-- Call restaurant module if defined -->
    @if (
            in_array('tables', $enabled_modules) ||
            in_array('modifiers', $enabled_modules) ||
            in_array('service_staff', $enabled_modules)
        )
        <script src="{{ asset('js/restaurant.js?v=' . $asset_v) }}"></script>
    @endif
    <!-- include module js -->
    @if (!empty($pos_module_data))
        @foreach ($pos_module_data as $key => $value)
            @if (!empty($value['module_js_path']))
                @includeIf($value['module_js_path'], ['view_data' => $value['view_data']])
            @endif
        @endforeach
    @endif

    @include('sale_pos.partials.scan_camera_js')
    <script type="text/javascript">
        $(document).ready(function () {

            // ── Native HTML5 drag-and-drop for POS table rows ──────────────────
            // SortableJS can be blocked by table-responsive overflow:auto.
            // Native drag events work directly on <tr> elements without that issue.

            var dragSrcRow = null;

            function addDragHandlers(row) {
                if ($(row).data('drag-init')) return; // already wired
                $(row).data('drag-init', true);

                // Make the row draggable only while holding the handle
                var handle = row.querySelector('.pos_row_handle');
                if (!handle) return;

                handle.addEventListener('mousedown', function () {
                    row.setAttribute('draggable', 'true');
                });
                handle.addEventListener('mouseup', function () {
                    row.setAttribute('draggable', 'false');
                });
                document.addEventListener('mouseup', function () {
                    row.setAttribute('draggable', 'false');
                });

                row.addEventListener('dragstart', function (e) {
                    dragSrcRow = row;
                    e.dataTransfer.effectAllowed = 'move';
                    e.dataTransfer.setData('text/plain', ''); // required for Firefox
                    $(row).addClass('pos-dragging');
                });

                row.addEventListener('dragend', function () {
                    $(row).removeClass('pos-dragging');
                    $('tr.product_row').removeClass('pos-drag-over');
                    dragSrcRow = null;
                });

                row.addEventListener('dragover', function (e) {
                    e.preventDefault();
                    e.dataTransfer.dropEffect = 'move';
                    if (dragSrcRow && row !== dragSrcRow) {
                        $('tr.product_row').removeClass('pos-drag-over');
                        $(row).addClass('pos-drag-over');
                    }
                });

                row.addEventListener('dragleave', function () {
                    $(row).removeClass('pos-drag-over');
                });

                row.addEventListener('drop', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    if (dragSrcRow && row !== dragSrcRow) {
                        var tbody = row.closest('tbody');
                        var rows = Array.from(tbody.querySelectorAll('tr.product_row'));
                        var srcIdx = rows.indexOf(dragSrcRow);
                        var tgtIdx = rows.indexOf(row);

                        if (srcIdx < tgtIdx) {
                            tbody.insertBefore(dragSrcRow, row.nextSibling);
                        } else {
                            tbody.insertBefore(dragSrcRow, row);
                        }

                        $(row).removeClass('pos-drag-over');

                        if (typeof pos_total_row === 'function') {
                            pos_total_row();
                        }
                    }
                });
            }

            function initAllRows() {
                document.querySelectorAll('table#pos_table tbody tr.product_row').forEach(function (row) {
                    addDragHandlers(row);
                });
            }

            // Wire up existing rows (none on fresh load, but covers edit mode)
            initAllRows();

            // Wire up new rows added via AJAX
            $(document).ajaxComplete(function (event, xhr, settings) {
                if (settings.url && (
                    settings.url.indexOf('/sells/pos/get_product_row') !== -1 ||
                    settings.url.indexOf('/pos/get_product_row') !== -1
                )) {
                    // Small delay to let the DOM update after .append()
                    setTimeout(initAllRows, 50);
                }
            });

            // Also observe direct DOM changes (covers all cases)
            var tbody = document.querySelector('table#pos_table tbody');
            if (tbody) {
                new MutationObserver(function (mutations) {
                    mutations.forEach(function (m) {
                        m.addedNodes.forEach(function (node) {
                            if (node.nodeType === 1 && $(node).hasClass('product_row')) {
                                addDragHandlers(node);
                            }
                        });
                    });
                }).observe(tbody, { childList: true });
            }
        });
    </script>
@endsection