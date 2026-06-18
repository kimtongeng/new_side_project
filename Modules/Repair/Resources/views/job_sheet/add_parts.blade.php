@extends('layouts.app')
@section('title', __('repair::lang.add_jobsheet_parts'))

@section('content')
    @include('repair::layouts.nav')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">@lang('repair::lang.add_jobsheet_parts')</h1>
    </section>

    <!-- Main content -->
    <section class="content">
        @component('components.widget', ['class' => 'box-solid'])
            <table class="table">
                <tr>
                    <th>@lang('repair::lang.job_sheet_no'):</th>
                    <td>{{ $job_sheet->job_sheet_no }}</td>
                    <th>@lang('receipt.date'):</th>
                    <td>{{ @format_datetime($job_sheet->created_at) }}</td>
                </tr>
                <tr>
                    <th>
                        @lang('role.customer'):
                    </th>
                    <td>{{ $job_sheet->customer->name }}</td>
                    <th>@lang('business.location'):</th>
                    <td>
                        {{ $job_sheet->businessLocation?->name }}
                    </td>
                </tr>
            </table>
        @endcomponent
        {!! Form::open([
            'url' => action([\Modules\Repair\Http\Controllers\JobSheetController::class, 'saveParts'], $job_sheet->id),
            'method' => 'post',
            'id' => 'add_part_form',
        ]) !!}
        {!! Form::hidden('job_sheet_location_id', $job_sheet->location_id, ['id' => 'job_sheet_location_id']) !!}
        @component('components.widget', ['class' => 'box-solid', 'title' => __('repair::lang.add_parts')])
            <div class="row">
                <div class="col-sm-8 col-sm-offset-2">
                    <div class="form-group">
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa fa-search"></i>
                            </span>

                            {!! Form::text('search_product', null, [
                                'class' => 'form-control',
                                'id' => 'search_job_sheet_parts',
                                'placeholder' => __('repair::lang.search_parts'),
                                'disabled' => !auth()->user()->can('repair.request_and_save'),
                            ]) !!}

                            {{-- @can('repair.show_only_available_part')
                                <span class="input-group-addon">
                                    <input type="checkbox" id="available_part" name="only_available">
                                </span>
                            @endcan --}}

                        </div>

                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-10 col-sm-offset-1">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-condensed" id="job_sheet_parts_table">
                            <thead>
                                <tr>
                                    <th class="col-sm-4 text-center">
                                        @lang('repair::lang.part')
                                    </th>
                                    <th class="col-sm-2 text-center">
                                        @lang('sale.qty')
                                    </th>
                                    <th class="col-sm-2 text-center"><i class="fa fa-trash" aria-hidden="true"></i></th>
                                </tr>
                            </thead>
                            <tbody>
                                @if (!empty($parts))
                                    @foreach ($parts as $key => $part)
                                        @include('repair::job_sheet.partials.job_sheet_part_row', [
                                            'variation_name' => $part['variation_name'],
                                            'unit' => $part['unit'],
                                            'quantity' => $part['quantity'],
                                            'variation_id' => $part['variation_id'],
                                            'part_key' => $key,
                                            'status' => $part['status'],
                                            'user_id' => $part['user_id'] ?? null,
                                            'product_image' => $part['product_image'] ?? null,
                                            'current_stock' => $part['current_stock'] ?? null,
                                            'allow_overselling' => $allow_overselling ?? false,
                                            'can_not_edit' => !auth()->user()->can('repair.edit_part'),
                                        ])
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                        <input type="hidden" id="product_row_index" value="{{ count($parts ?? []) }}">
                    </div>
                </div>
            </div>
        @endcomponent
        @if (!empty($status_update_data) && $status_update_data['job_sheet_id'] == $job_sheet->id)
            @component('components.widget', ['class' => 'box-solid'])
                @include('repair::job_sheet.partials.edit_status_form', [
                    'status_update_data' => $status_update_data,
                ])
            @endcomponent
        @endif
        <div class="row">
            <div class="col-sm-12">
                @if (auth()->user()->can('superadmin'))
                    {{-- Admin: always save as confirmed --}}
                    <button type="button" id="submit_add_part_form"
                        class="tw-dw-btn tw-dw-btn-primary tw-text-white pull-right">
                        @lang('messages.save')
                    </button>
                @elsecan('repair.request_and_save')
                    {{-- Has request permission: show Request button with confirmation --}}
                    <button type="button" id="request_btn" class="tw-dw-btn tw-dw-btn-success tw-text-white pull-right">
                        Request
                    </button>
                @else
                    {{-- Has no request permission but can still save --}}
                    <button type="button" id="submit_add_part_form"
                        class="tw-dw-btn tw-dw-btn-primary tw-text-white pull-right">
                        @lang('messages.save')
                    </button>
                @endif
            </div>
        </div>
        {!! Form::close() !!}
    </section>
@stop
@section('javascript')
    <script type="text/javascript">
        var allow_overselling = {{ json_encode($allow_overselling ?? false) }};
        $(document).ready(function() {
            $(document).on("click", "#request_btn", function(e) {
                swal({
                        title: "Are you sure?",
                        text: "Do you want to request all these parts?",
                        icon: "warning",
                        buttons: true,
                        dangerMode: false,
                        buttons: {
                            cancel: {
                                text: "Cancel",
                                visible: true,
                                className: 'btn btn-danger',
                                closeModal: true,
                            },
                            confirm: {
                                text: "Yes, request them!",
                                className: "btn btn-success",
                            },
                        },
                    })
                    .then((willRequest) => {
                        if (willRequest) {
                            if (!allow_overselling && !validatePartsQty()) return;

                            $('form#add_part_form').submit();
                        }
                    });
            });
            $('#search_job_sheet_parts')
                .autocomplete({
                    source: function(request, response) {
                        let available_part = $("#available_part")?.prop('checked') ?? false;
                        $.getJSON(
                            '/products/list', {
                                term: request.term,
                                check_qty: available_part ?? false
                            },
                            response
                        );
                    },
                    minLength: 2,
                    response: function(event, ui) {
                        if (ui.content.length == 1) {
                            ui.item = ui.content[0];
                            $(this)
                                .data('ui-autocomplete')
                                ._trigger('select', 'autocompleteselect', ui);
                            $(this).autocomplete('close');
                        } else if (ui.content.length == 0) {
                            swal(LANG.no_products_found);
                        }
                    },
                    select: function(event, ui) {
                        job_sheet_parts_row(ui.item.variation_id);
                    },
                })
                .autocomplete('instance')._renderItem = function(ul, item) {
                    var isOutOfStock = false;
                    if (item.enable_stock == 1) {
                        isOutOfStock = parseFloat(item.qty_available) <= 0 || item.qty_available == null;
                    }

                    var string = '<div>' + item.name;
                    if (item.type == 'variable') {
                        string += '-' + item.variation;
                    }
                    string += ' (' + item.sub_sku + ')';

                    if (isOutOfStock && !allow_overselling) {
                        string +=
                            ' <span style="color: #999; font-style: italic; font-size: 0.85em;">(Out of Stock)</span>';
                    }

                    string += '</div>';

                    var $li = $('<li>').append(string).appendTo(ul);

                    if (isOutOfStock && !allow_overselling) {
                        $li.addClass('ui-state-disabled')
                            .css({
                                'pointer-events': 'none',
                                'opacity': '0.5',
                                'cursor': 'default'
                            });
                    }

                    return $li;
                };

            //initialize editor
            tinymce.init({
                selector: 'textarea#email_body',
            });

            $('#send_sms').change(function() {
                if ($(this).is(":checked")) {
                    $('div.sms_body').fadeIn();
                } else {
                    $('div.sms_body').fadeOut();
                }
            });

            $('#send_email').change(function() {
                if ($(this).is(":checked")) {
                    $('div.email_template').fadeIn();
                } else {
                    $('div.email_template').fadeOut();
                }
            });

            if ($('#status_id_modal').length) {
                ;
                $("#sms_body").val($("#status_id_modal :selected").data('sms_template'));
                $("#email_subject").val($("#status_id_modal :selected").data('email_subject'));
                tinymce.activeEditor.setContent($("#status_id_modal :selected").data('email_body'));
            }

            $('#status_id_modal').on('change', function() {
                var sms_template = $(this).find(':selected').data('sms_template');
                var email_subject = $(this).find(':selected').data('email_subject');
                var email_body = $(this).find(':selected').data('email_body');

                $("#sms_body").val(sms_template);
                $("#email_subject").val(email_subject);
                tinymce.activeEditor.setContent(email_body);

                if ($('#status_modal .mark-as-complete-btn').length) {
                    if ($(this).find(':selected').data('is_completed_status') == 1) {
                        $('#status_modal').find('.mark-as-complete-btn').removeClass('hide');
                        $('#status_modal').find('.mark-as-incomplete-btn').addClass('hide');
                    } else {
                        $('#status_modal').find('.mark-as-complete-btn').addClass('hide');
                        $('#status_modal').find('.mark-as-incomplete-btn').removeClass('hide');
                    }
                }
            });
        });

        function job_sheet_parts_row(variation_id) {
            var row_index = parseInt($('#product_row_index').val()) || 0;
            var new_key = 'row_' + variation_id + '_' + row_index;
            var location_id = $('#job_sheet_location_id').val();

            // Calculate total qty already added for this variation across all rows
            var already_used_qty = 0;
            $('tr[data-variation-id]').filter(function() {
                var vid = $(this).attr('data-variation-id');
                return vid == variation_id || vid.startsWith('row_' + variation_id + '_');
            }).each(function() {
                var qty = parseFloat($(this).find('input[name*="[quantity]"]').val()) || 0;
                already_used_qty += qty;
            });

            $.ajax({
                method: 'POST',
                url: "{{ action([\Modules\Repair\Http\Controllers\JobSheetController::class, 'jobsheetPartRow']) }}",
                data: {
                    variation_id: variation_id,
                    part_key: new_key,
                    location_id: location_id,
                    already_used_qty: already_used_qty, // ← send to controller
                },
                dataType: 'json',
                success: function(response) {
                    if (!response.success) {
                        toastr.error(response.msg);
                        $('input#search_job_sheet_parts').val('').focus().select();
                        return;
                    }

                    var new_row_el = $(response.html);
                    var new_status = new_row_el.find('input[name*="[status]"]').val();

                    // Find all existing rows for this variation_id
                    var all_rows = $('tr[data-variation-id]').filter(function() {
                        var vid = $(this).attr('data-variation-id');
                        return vid == variation_id || vid.startsWith('row_' + variation_id + '_');
                    });

                    // Find a row with the SAME status
                    var matching_row = all_rows.filter(function() {
                        return $(this).find('input[name*="[status]"]').val() === new_status;
                    }).first();

                    if (matching_row.length > 0) {
                        // Same status exists — increment qty only
                        var qty_input = matching_row.find('input[name*="[quantity]"]');
                        var currentQty = parseFloat(qty_input.val()) || 0;
                        qty_input.val((currentQty + 1).toFixed(2));
                    } else {
                        // No matching status — add new row
                        new_row_el.attr('data-variation-id', new_key);
                        $('table#job_sheet_parts_table tbody').append(new_row_el);
                        $('#product_row_index').val(row_index + 1);
                    }

                    $('input#search_job_sheet_parts').val('').focus().select();
                },
                error: function() {
                    toastr.error('Something went wrong.');
                }
            });
        }

        $(document).on('click', '.remove_product_row', function() {
            $(this).closest('tr').remove();
        })

        function getVariationTotalQty($input) {
            var variationId = $input.closest('tr').find('input[name*="[variation_id]"]').val();
            if (!variationId) {
                return __read_number($input);
            }

            var totalQty = 0;
            $('input[name*="[variation_id]"]').filter(function() {
                return $(this).val() === variationId;
            }).each(function() {
                var $row = $(this).closest('tr');
                var status = $row.find('input[name*="[status]"]').val();

                if (status === 'rejected') {
                    return;
                }

                var $rowQtyInput = $row.find('input.input_quantity');
                if ($rowQtyInput.length) {
                    totalQty += __read_number($rowQtyInput);
                }
            });

            return totalQty;
        }

        $(document).on('change', 'input.input_quantity', function() {
            var $input = $(this);
            var max = parseFloat($input.data('rule-max-value'));
            var available = $input.data('qty_available');
            var unit = $input.data('unit') || '';
            var $message = $input.closest('td').find('.qty-available-feedback');
            var totalQty = getVariationTotalQty($input);

            if (!isNaN(max) && totalQty > max) {
                $message.text('Quantity ' + __number_f(available, false, false, __quantity_precision) + ' ' + unit +
                    ' available').show();
            } else {
                $message.hide();
            }
        });
        $(document).on('click', '#submit_add_part_form', function(e) {

            if (!allow_overselling && !validatePartsQty()) return;
            $('form#add_part_form').submit();
        })

        function validatePartsQty() {
            var valid = true;
            var checkedVariations = [];

            $('table#job_sheet_parts_table tbody tr[data-variation-id]').each(function() {
                var $row = $(this);
                var status = $row.find('input[name*="[status]"]').val();

                if (status === 'rejected') return;

                var $qtyInput = $row.find('input.input_quantity');
                var max = parseFloat($qtyInput.data('rule-max-value'));

                if (isNaN(max)) return; // no max set = overselling allowed for this item

                var variationId = $row.find('input[name*="[variation_id]"]').val();

                if (checkedVariations.indexOf(variationId) !== -1) return; // already checked this variation
                checkedVariations.push(variationId);

                // Use the existing function to get total qty across all rows for this variation
                var totalQty = getVariationTotalQty($qtyInput);
                var available = $qtyInput.data('qty_available');
                var unit = $qtyInput.data('unit') || '';
                var variationName = $row.find('td:first').text().trim();

                if (totalQty > max) {
                    toastr.error(
                        '"' + variationName + '" exceeds available stock. ' +
                        'Available: ' + __number_f(available, false, false, __quantity_precision) + ' ' + unit
                    );
                    valid = false;
                }
            });

            return valid;
        }
    </script>
@endsection
