<div class="well well-sm" style="background-color: #f8f9fa; border: 1px solid #d2d6de; border-radius: 4px; padding: 12px 15px; margin-bottom: 20px;">
    <div class="row">
        <!-- Title & Counter Badge -->
        <div class="col-md-2 col-sm-12">
            <label style="font-size: 15px; font-weight: 600; margin-top: 6px; margin-bottom: 0;">
                <i class="fa fa-shield text-primary"></i> @lang('user.permissions'):
            </label>
            <span id="permission_search_count" class="badge bg-aqua" style="display: none; margin-left: 5px; font-size: 11px; padding: 3px 7px;"></span>
        </div>

        <!-- Search Input -->
        <div class="col-md-3 col-sm-6">
            <div class="input-group">
                <span class="input-group-addon" style="background-color: #fff;"><i class="fa fa-search text-muted"></i></span>
                <input type="text" id="search_permissions" class="form-control" placeholder="{{ __('lang_v1.search') }} {{ __('user.permissions') }}..." autocomplete="off">
                <span class="input-group-btn">
                    <button class="btn btn-default" type="button" id="clear_permission_search" style="display: none;" title="{{ __('lang_v1.clear') }}">
                        <i class="fa fa-times text-danger"></i>
                    </button>
                </span>
            </div>
        </div>

        <!-- Group Filter Dropdown -->
        <div class="col-md-2 col-sm-6">
            <select id="filter_permission_group" class="form-control" title="Filter by Group">
                <option value="">-- All Groups --</option>
            </select>
        </div>

        <!-- Status Filter Dropdown -->
        <div class="col-md-2 col-sm-6">
            <select id="filter_permission_status" class="form-control" title="Filter by Status">
                <option value="all">All Status</option>
                <option value="checked">Checked Only</option>
                <option value="unchecked">Unchecked Only</option>
            </select>
        </div>

        <!-- Expand/Collapse All + Presets Dropdown -->
        <div class="col-md-3 col-sm-12 text-right">
            <button type="button" class="btn btn-default btn-sm" id="btn_toggle_expand" style="margin-right: 5px;" title="Expand / Collapse All">
                <i class="fa fa-angle-double-down"></i> <span id="toggle_expand_text">Expand All</span>
            </button>
            <div class="btn-group">
                <button type="button" class="btn btn-primary btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fa fa-check-square-o"></i> Presets <span class="caret"></span>
                </button>
                <ul class="dropdown-menu dropdown-menu-right" style="min-width: 250px;">
                    <li><a href="javascript:void(0);" class="preset-option" data-preset="all"><i class="fa fa-check-square-o text-success"></i> Select All Role</a></li>
                    <li><a href="javascript:void(0);" class="preset-option" data-preset="except_delete"><i class="fa fa-minus-circle text-warning"></i> Select All Role (except delete)</a></li>
                    <li><a href="javascript:void(0);" class="preset-option" data-preset="except_edit"><i class="fa fa-minus-circle text-warning"></i> Select All Role (except edit)</a></li>
                    <li><a href="javascript:void(0);" class="preset-option" data-preset="except_edit_delete"><i class="fa fa-minus-circle text-info"></i> Select All Role (except edit & delete)</a></li>
                    <li role="separator" class="divider"></li>
                    <li><a href="javascript:void(0);" class="preset-option" data-preset="none"><i class="fa fa-square-o text-danger"></i> Deselect All Role</a></li>
                </ul>
            </div>
        </div>
    </div>
</div>

@section('javascript')
@parent
<script type="text/javascript">
$(document).ready(function() {
    // Unbind app.js default check_all listeners to prevent cascading unchecks on programmatic updates
    $(document).off('ifChecked', '.check_all').off('ifUnchecked', '.check_all');

    var isProgrammaticCheckAll = false;

    // Custom check_all click handlers for permission groups
    $(document).on('ifChecked', '.permission_group .check_all', function() {
        if (isProgrammaticCheckAll) return;

        var $group = $(this).closest('.permission_group');
        isProgrammaticCheckAll = true;
        $group.find('.input-icheck').not('.check_all').iCheck('check');
        isProgrammaticCheckAll = false;

        updateSelectedBadges();
    });

    $(document).on('ifUnchecked', '.permission_group .check_all', function() {
        if (isProgrammaticCheckAll) return;

        var $group = $(this).closest('.permission_group');
        isProgrammaticCheckAll = true;
        $group.find('.input-icheck').not('.check_all').iCheck('uncheck');
        isProgrammaticCheckAll = false;

        updateSelectedBadges();
    });

    // 1. Update Selected Count Badges on Dropdown Cards
    function updateSelectedBadges() {
        $('.permission_group').each(function() {
            var $group = $(this);
            var total = $group.find('.input-icheck:not(.check_all)').length;
            var checked = $group.find('.input-icheck:not(.check_all):checked').length;
            var $badge = $group.find('.selected-count-badge');

            if (checked > 0) {
                $badge.text(checked + ' / ' + total + ' selected')
                      .removeClass('bg-gray')
                      .addClass('bg-blue');
            } else {
                $badge.text('0 selected')
                      .removeClass('bg-blue')
                      .addClass('bg-gray');
            }
        });
    }

    // 2. Sync .check_all Checkbox Status with Group Item Checkboxes
    function updateCheckAllStatusForGroup($groupRow) {
        var $checkAll = $groupRow.find('.check_all');
        if (!$checkAll.length) return;

        var $items = $groupRow.find('.input-icheck').not('.check_all');
        var totalCount = $items.length;
        var checkedCount = 0;

        $items.each(function() {
            if ($(this).is(':checked')) {
                checkedCount++;
            }
        });

        isProgrammaticCheckAll = true;
        if (totalCount > 0 && checkedCount === totalCount) {
            $checkAll.iCheck('check');
        } else {
            $checkAll.iCheck('uncheck');
        }
        isProgrammaticCheckAll = false;
    }

    function updateAllCheckAllStatuses() {
        $('.permission_group').each(function() {
            updateCheckAllStatusForGroup($(this));
        });
    }

    // Initial setup of badges and check_all checkboxes
    updateAllCheckAllStatuses();
    updateSelectedBadges();

    // 3. Collapsible Dropdown Card Header Toggle
    $(document).on('click', '.permission-header', function(e) {
        if ($(e.target).closest('input, label, button, .iCheck-helper').length && !$(e.target).hasClass('btn-box-tool')) {
            return;
        }

        var $card = $(this).closest('.permission_group');
        var $body = $card.find('.permission-body');
        var $chevron = $card.find('.group-chevron');
        var $iconBtn = $card.find('.btn-box-tool i');

        $body.slideToggle(200, function() {
            if ($body.is(':visible')) {
                $chevron.removeClass('fa-chevron-right').addClass('fa-chevron-down');
                $iconBtn.removeClass('fa-plus').addClass('fa-minus');
            } else {
                $chevron.removeClass('fa-chevron-down').addClass('fa-chevron-right');
                $iconBtn.removeClass('fa-minus').addClass('fa-plus');
            }
        });
    });

    // 4. Expand All / Collapse All Toggle Button
    var isAllExpanded = false;
    $(document).on('click', '#btn_toggle_expand', function() {
        isAllExpanded = !isAllExpanded;
        if (isAllExpanded) {
            $('.permission_group:visible .permission-body').slideDown(200);
            $('.permission_group:visible .group-chevron').removeClass('fa-chevron-right').addClass('fa-chevron-down');
            $('.permission_group:visible .btn-box-tool i').removeClass('fa-plus').addClass('fa-minus');
            $('#toggle_expand_text').text('Collapse All');
            $(this).find('i').removeClass('fa-angle-double-down').addClass('fa-angle-double-up');
        } else {
            $('.permission_group .permission-body').slideUp(200);
            $('.permission_group .group-chevron').removeClass('fa-chevron-down').addClass('fa-chevron-right');
            $('.permission_group .btn-box-tool i').removeClass('fa-minus').addClass('fa-plus');
            $('#toggle_expand_text').text('Expand All');
            $(this).find('i').removeClass('fa-angle-double-up').addClass('fa-angle-double-down');
        }
    });

    // 5. Populate group filter dropdown dynamically
    function initPermissionGroupsDropdown() {
        var $groupSelect = $('#filter_permission_group');
        $groupSelect.empty().append('<option value="">-- All Groups --</option>');

        $('.permission_group').each(function(index) {
            var $groupRow = $(this);
            $groupRow.attr('data-group-index', index);

            var groupTitle = $groupRow.find('h3.box-title').contents().filter(function() {
                return this.nodeType === 3;
            }).text().trim();
            if (!groupTitle) {
                groupTitle = $groupRow.find('h3.box-title').text().trim();
            }

            if (groupTitle) {
                $groupSelect.append('<option value="' + index + '">' + groupTitle + '</option>');
            }
        });
    }

    initPermissionGroupsDropdown();

    // Helper functions for Edit / Delete permission checks
    function isDeletePermission($item) {
        var val = ($item.find('input').val() || '').toLowerCase();
        var text = $item.text().toLowerCase();
        return val.indexOf('delete') !== -1 || text.indexOf('delete') !== -1 || text.indexOf('ลบ') !== -1;
    }

    function isEditPermission($item) {
        var val = ($item.find('input').val() || '').toLowerCase();
        var text = $item.text().toLowerCase();
        return val.indexOf('update') !== -1 || val.indexOf('edit') !== -1 || text.indexOf('edit') !== -1 || text.indexOf('update') !== -1 || text.indexOf('แก้ไข') !== -1;
    }

    // 6. Main Filter Function (Search, Group Filter, Status Filter)
    function filterPermissions() {
        var query = $('#search_permissions').val().toLowerCase().trim();
        var selectedGroupIndex = $('#filter_permission_group').val();
        var selectedStatus = $('#filter_permission_status').val(); // 'all', 'checked', 'unchecked'

        if (query.length > 0) {
            $('#clear_permission_search').show();
        } else {
            $('#clear_permission_search').hide();
        }

        var totalMatchCount = 0;
        var isFiltered = (query.length > 0 || selectedGroupIndex !== '' || selectedStatus !== 'all');

        $('.permission_group').each(function() {
            var $groupRow = $(this);
            var groupIndex = $groupRow.attr('data-group-index');

            if (selectedGroupIndex !== '' && groupIndex !== selectedGroupIndex) {
                $groupRow.hide();
                return;
            }

            var groupTitle = $groupRow.find('h3.box-title').contents().filter(function() {
                return this.nodeType === 3;
            }).text().toLowerCase().trim();
            if (!groupTitle) {
                groupTitle = $groupRow.find('h3.box-title').text().toLowerCase().trim();
            }

            var groupMatchesTitle = (query.length > 0 && groupTitle.indexOf(query) !== -1);
            var groupVisibleCount = 0;

            $groupRow.find('.permission-item-wrapper').each(function() {
                var $item = $(this);
                var itemText = $item.text().toLowerCase();
                var inputValue = ($item.find('input').val() || '').toLowerCase();
                var combinedText = itemText + ' ' + inputValue;

                var textMatch = (query.length === 0 || groupMatchesTitle || combinedText.indexOf(query) !== -1);
                var isChecked = $item.find('input').is(':checked');

                var statusMatch = true;
                if (selectedStatus === 'checked') {
                    statusMatch = isChecked;
                } else if (selectedStatus === 'unchecked') {
                    statusMatch = !isChecked;
                }

                if (textMatch && statusMatch) {
                    $item.show();
                    groupVisibleCount++;
                    totalMatchCount++;
                } else {
                    $item.hide();
                }
            });

            if (groupVisibleCount > 0) {
                $groupRow.show();
                if (isFiltered) {
                    // Expand matching card automatically when filtering
                    $groupRow.find('.permission-body').slideDown(150);
                    $groupRow.find('.group-chevron').removeClass('fa-chevron-right').addClass('fa-chevron-down');
                    $groupRow.find('.btn-box-tool i').removeClass('fa-plus').addClass('fa-minus');
                }
            } else {
                $groupRow.hide();
            }
        });

        if (isFiltered) {
            $('#permission_search_count').text(totalMatchCount + ' found').show();
        } else {
            $('#permission_search_count').hide();
        }
    }

    $(document).on('keyup change input', '#search_permissions', filterPermissions);
    $(document).on('change', '#filter_permission_group', filterPermissions);
    $(document).on('change', '#filter_permission_status', filterPermissions);

    // 7. Event Handler for Item Checkbox / Radio Changes
    $(document).on('ifChanged', '.input-icheck:not(.check_all)', function() {
        if (isProgrammaticCheckAll) return;

        var $input = $(this);
        var $groupRow = $input.closest('.permission_group');

        updateCheckAllStatusForGroup($groupRow);
        updateSelectedBadges();

        if ($('#filter_permission_status').val() !== 'all') {
            filterPermissions();
        }
    });

    // Clear Search Input
    $(document).on('click', '#clear_permission_search', function() {
        $('#search_permissions').val('');
        filterPermissions();
        $('#search_permissions').focus();
    });

    // 8. Preset Handler
    $(document).on('click', '.preset-option', function(e) {
        e.preventDefault();
        var preset = $(this).data('preset');

        $('.permission_group').each(function() {
            var $groupRow = $(this);
            if ($groupRow.is(':hidden')) return;

            $groupRow.find('.permission-item-wrapper').each(function() {
                var $item = $(this);
                if ($item.css('display') === 'none') return;
                var $checkbox = $item.find('input[type="checkbox"].input-icheck, input[type="radio"].input-icheck');
                if (!$checkbox.length) return;

                var isDel = isDeletePermission($item);
                var isEd = isEditPermission($item);

                switch (preset) {
                    case 'all':
                        $checkbox.iCheck('check');
                        break;
                    case 'except_delete':
                        if (isDel) {
                            $checkbox.iCheck('uncheck');
                        } else {
                            $checkbox.iCheck('check');
                        }
                        break;
                    case 'except_edit':
                        if (isEd) {
                            $checkbox.iCheck('uncheck');
                        } else {
                            $checkbox.iCheck('check');
                        }
                        break;
                    case 'except_edit_delete':
                        if (isEd || isDel) {
                            $checkbox.iCheck('uncheck');
                        } else {
                            $checkbox.iCheck('check');
                        }
                        break;
                    case 'none':
                        $checkbox.iCheck('uncheck');
                        break;
                }
            });
        });

        updateAllCheckAllStatuses();
        updateSelectedBadges();
        if ($('#filter_permission_status').val() !== 'all') {
            filterPermissions();
        }
    });
});
</script>
@endsection