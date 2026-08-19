@php
  $role_permissions = isset($role_permissions) ? $role_permissions : [];
  $pos_settings = !empty(session('business.pos_settings')) ? json_decode(session('business.pos_settings'), true) : [];
  $common_settings = !empty(session('business.common_settings')) ? session('business.common_settings') : [];
  $is_service_staff = isset($role->is_service_staff) ? $role->is_service_staff : 0;
  $enabled_modules = !empty($enabled_modules) ? $enabled_modules : [];
@endphp

@include('role.partials.permission_search')

<!-- 1. Others -->
<div class="box box-solid check_group permission_group" style="border: 1px solid #d2d6de; border-radius: 4px; margin-bottom: 12px;">
  <div class="box-header with-border permission-header" style="background-color: #f9fafc; cursor: pointer; padding: 10px 15px;">
    <h3 class="box-title" style="font-size: 15px; font-weight: 600; color: #333;">
      <i class="fa fa-chevron-right text-muted group-chevron" style="margin-right: 8px; font-size: 13px; transition: transform 0.2s;"></i>
      @lang('lang_v1.others')
    </h3>
    <div class="box-tools pull-right">
      <span class="badge bg-gray selected-count-badge" style="font-weight: normal; margin-right: 5px;">0 selected</span>
      <button type="button" class="btn btn-box-tool"><i class="fa fa-plus"></i></button>
    </div>
  </div>
  <div class="box-body permission-body" style="padding: 15px 20px; display: none;">
    <div class="row">
      <div class="col-md-2" style="border-right: 1px solid #eee;">
        <div class="checkbox" style="margin-top: 0;">
          <label style="font-weight: bold; color: #3c8dbc;">
            <input type="checkbox" class="check_all input-icheck"> {{ __('role.select_all') }}
          </label>
        </div>
      </div>
      <div class="col-md-10">
        @if(in_array('service_staff', $enabled_modules))
          <div class="col-md-4 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
            <div class="checkbox" style="margin-top: 0;">
              <label>
                {!! Form::checkbox('is_service_staff', 1, $is_service_staff, ['class' => 'input-icheck']); !!} {{ __('restaurant.service_staff') }}
              </label>
              @show_tooltip(__('restaurant.tooltip_service_staff'))
            </div>
          </div>
        @endif
        <div class="col-md-4 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>
              {!! Form::checkbox('permissions[]', 'view_export_buttons', in_array('view_export_buttons', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('lang_v1.view_export_buttons') }}
            </label>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- 2. User -->
<div class="box box-solid check_group permission_group" style="border: 1px solid #d2d6de; border-radius: 4px; margin-bottom: 12px;">
  <div class="box-header with-border permission-header" style="background-color: #f9fafc; cursor: pointer; padding: 10px 15px;">
    <h3 class="box-title" style="font-size: 15px; font-weight: 600; color: #333;">
      <i class="fa fa-chevron-right text-muted group-chevron" style="margin-right: 8px; font-size: 13px; transition: transform 0.2s;"></i>
      @lang('role.user')
    </h3>
    <div class="box-tools pull-right">
      <span class="badge bg-gray selected-count-badge" style="font-weight: normal; margin-right: 5px;">0 selected</span>
      <button type="button" class="btn btn-box-tool"><i class="fa fa-plus"></i></button>
    </div>
  </div>
  <div class="box-body permission-body" style="padding: 15px 20px; display: none;">
    <div class="row">
      <div class="col-md-2" style="border-right: 1px solid #eee;">
        <div class="checkbox" style="margin-top: 0;">
          <label style="font-weight: bold; color: #3c8dbc;">
            <input type="checkbox" class="check_all input-icheck"> {{ __('role.select_all') }}
          </label>
        </div>
      </div>
      <div class="col-md-10">
        <div class="col-md-3 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::checkbox('permissions[]', 'user.view', in_array('user.view', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('role.user.view') }}</label>
          </div>
        </div>
        <div class="col-md-3 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::checkbox('permissions[]', 'user.create', in_array('user.create', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('role.user.create') }}</label>
          </div>
        </div>
        <div class="col-md-3 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::checkbox('permissions[]', 'user.update', in_array('user.update', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('role.user.update') }}</label>
          </div>
        </div>
        <div class="col-md-3 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::checkbox('permissions[]', 'user.delete', in_array('user.delete', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('role.user.delete') }}</label>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- 3. Roles -->
<div class="box box-solid check_group permission_group" style="border: 1px solid #d2d6de; border-radius: 4px; margin-bottom: 12px;">
  <div class="box-header with-border permission-header" style="background-color: #f9fafc; cursor: pointer; padding: 10px 15px;">
    <h3 class="box-title" style="font-size: 15px; font-weight: 600; color: #333;">
      <i class="fa fa-chevron-right text-muted group-chevron" style="margin-right: 8px; font-size: 13px; transition: transform 0.2s;"></i>
      @lang('user.roles')
    </h3>
    <div class="box-tools pull-right">
      <span class="badge bg-gray selected-count-badge" style="font-weight: normal; margin-right: 5px;">0 selected</span>
      <button type="button" class="btn btn-box-tool"><i class="fa fa-plus"></i></button>
    </div>
  </div>
  <div class="box-body permission-body" style="padding: 15px 20px; display: none;">
    <div class="row">
      <div class="col-md-2" style="border-right: 1px solid #eee;">
        <div class="checkbox" style="margin-top: 0;">
          <label style="font-weight: bold; color: #3c8dbc;">
            <input type="checkbox" class="check_all input-icheck"> {{ __('role.select_all') }}
          </label>
        </div>
      </div>
      <div class="col-md-10">
        <div class="col-md-3 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::checkbox('permissions[]', 'roles.view', in_array('roles.view', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('lang_v1.view_role') }}</label>
          </div>
        </div>
        <div class="col-md-3 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::checkbox('permissions[]', 'roles.create', in_array('roles.create', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('role.add_role') }}</label>
          </div>
        </div>
        <div class="col-md-3 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::checkbox('permissions[]', 'roles.update', in_array('roles.update', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('role.edit_role') }}</label>
          </div>
        </div>
        <div class="col-md-3 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::checkbox('permissions[]', 'roles.delete', in_array('roles.delete', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('lang_v1.delete_role') }}</label>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- 4. Supplier -->
<div class="box box-solid check_group permission_group" style="border: 1px solid #d2d6de; border-radius: 4px; margin-bottom: 12px;">
  <div class="box-header with-border permission-header" style="background-color: #f9fafc; cursor: pointer; padding: 10px 15px;">
    <h3 class="box-title" style="font-size: 15px; font-weight: 600; color: #333;">
      <i class="fa fa-chevron-right text-muted group-chevron" style="margin-right: 8px; font-size: 13px; transition: transform 0.2s;"></i>
      @lang('role.supplier')
    </h3>
    <div class="box-tools pull-right">
      <span class="badge bg-gray selected-count-badge" style="font-weight: normal; margin-right: 5px;">0 selected</span>
      <button type="button" class="btn btn-box-tool"><i class="fa fa-plus"></i></button>
    </div>
  </div>
  <div class="box-body permission-body" style="padding: 15px 20px; display: none;">
    <div class="row">
      <div class="col-md-2" style="border-right: 1px solid #eee;">
        <div class="checkbox" style="margin-top: 0;">
          <label style="font-weight: bold; color: #3c8dbc;">
            <input type="checkbox" class="check_all input-icheck"> {{ __('role.select_all') }}
          </label>
        </div>
      </div>
      <div class="col-md-10">
        <div class="col-md-3 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::radio('radio_option[supplier_view]', 'supplier.view', in_array('supplier.view', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('lang_v1.view_all_supplier') }}</label>
          </div>
        </div>
        <div class="col-md-3 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::radio('radio_option[supplier_view]', 'supplier.view_own', in_array('supplier.view_own', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('lang_v1.view_own_supplier') }}</label>
          </div>
        </div>
        <div class="col-md-2 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::checkbox('permissions[]', 'supplier.create', in_array('supplier.create', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('role.supplier.create') }}</label>
          </div>
        </div>
        <div class="col-md-2 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::checkbox('permissions[]', 'supplier.update', in_array('supplier.update', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('role.supplier.update') }}</label>
          </div>
        </div>
        <div class="col-md-2 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::checkbox('permissions[]', 'supplier.delete', in_array('supplier.delete', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('role.supplier.delete') }}</label>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- 5. Customer -->
<div class="box box-solid check_group permission_group" style="border: 1px solid #d2d6de; border-radius: 4px; margin-bottom: 12px;">
  <div class="box-header with-border permission-header" style="background-color: #f9fafc; cursor: pointer; padding: 10px 15px;">
    <h3 class="box-title" style="font-size: 15px; font-weight: 600; color: #333;">
      <i class="fa fa-chevron-right text-muted group-chevron" style="margin-right: 8px; font-size: 13px; transition: transform 0.2s;"></i>
      @lang('role.customer') @show_tooltip(__('lang_v1.customer_permissions_tooltip'))
    </h3>
    <div class="box-tools pull-right">
      <span class="badge bg-gray selected-count-badge" style="font-weight: normal; margin-right: 5px;">0 selected</span>
      <button type="button" class="btn btn-box-tool"><i class="fa fa-plus"></i></button>
    </div>
  </div>
  <div class="box-body permission-body" style="padding: 15px 20px; display: none;">
    <div class="row">
      <div class="col-md-2" style="border-right: 1px solid #eee;">
        <div class="checkbox" style="margin-top: 0;">
          <label style="font-weight: bold; color: #3c8dbc;">
            <input type="checkbox" class="check_all input-icheck"> {{ __('role.select_all') }}
          </label>
        </div>
      </div>
      <div class="col-md-10">
        <div class="row" style="margin-bottom: 5px;">
          <div class="col-md-4 col-sm-6 permission-item-wrapper">
            <div class="checkbox" style="margin-top: 0;">
              <label>{!! Form::radio('radio_option[customer_view]', 'customer.view', in_array('customer.view', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('lang_v1.view_all_customer') }}</label>
            </div>
          </div>
          <div class="col-md-4 col-sm-6 permission-item-wrapper">
            <div class="checkbox" style="margin-top: 0;">
              <label>{!! Form::radio('radio_option[customer_view]', 'customer.view_own', in_array('customer.view_own', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('lang_v1.view_own_customer') }}</label>
            </div>
          </div>
        </div>
        <hr style="margin: 8px 0;">
        <div class="row" style="margin-bottom: 5px;">
          <div class="col-md-4 col-sm-6 permission-item-wrapper">
            <div class="checkbox" style="margin-top: 0;">
              <label>{!! Form::radio('radio_option[customer_view_by_sell]', 'customer_with_no_sell_one_month', in_array('customer_with_no_sell_one_month', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('lang_v1.customer_with_no_sell_one_month') }}</label>
            </div>
          </div>
          <div class="col-md-4 col-sm-6 permission-item-wrapper">
            <div class="checkbox" style="margin-top: 0;">
              <label>{!! Form::radio('radio_option[customer_view_by_sell]', 'customer_with_no_sell_three_month', in_array('customer_with_no_sell_three_month', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('lang_v1.customer_with_no_sell_three_month') }}</label>
            </div>
          </div>
          <div class="col-md-4 col-sm-6 permission-item-wrapper">
            <div class="checkbox" style="margin-top: 0;">
              <label>{!! Form::radio('radio_option[customer_view_by_sell]', 'customer_with_no_sell_six_month', in_array('customer_with_no_sell_six_month', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('lang_v1.customer_with_no_sell_six_month') }}</label>
            </div>
          </div>
          <div class="col-md-4 col-sm-6 permission-item-wrapper">
            <div class="checkbox" style="margin-top: 0;">
              <label>{!! Form::radio('radio_option[customer_view_by_sell]', 'customer_with_no_sell_one_year', in_array('customer_with_no_sell_one_year', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('lang_v1.customer_with_no_sell_one_year') }}</label>
            </div>
          </div>
          <div class="col-md-4 col-sm-6 permission-item-wrapper">
            <div class="checkbox" style="margin-top: 0;">
              <label>{!! Form::radio('radio_option[customer_view_by_sell]', 'customer_irrespective_of_sell', in_array('customer_irrespective_of_sell', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('lang_v1.customer_irrespective_of_sell') }}</label>
            </div>
          </div>
        </div>
        <hr style="margin: 8px 0;">
        <div class="row">
          <div class="col-md-4 col-sm-6 permission-item-wrapper">
            <div class="checkbox" style="margin-top: 0;">
              <label>{!! Form::checkbox('permissions[]', 'customer.create', in_array('customer.create', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('role.customer.create') }}</label>
            </div>
          </div>
          <div class="col-md-4 col-sm-6 permission-item-wrapper">
            <div class="checkbox" style="margin-top: 0;">
              <label>{!! Form::checkbox('permissions[]', 'customer.update', in_array('customer.update', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('role.customer.update') }}</label>
            </div>
          </div>
          <div class="col-md-4 col-sm-6 permission-item-wrapper">
            <div class="checkbox" style="margin-top: 0;">
              <label>{!! Form::checkbox('permissions[]', 'customer.delete', in_array('customer.delete', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('role.customer.delete') }}</label>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- 6. Product -->
<div class="box box-solid check_group permission_group" style="border: 1px solid #d2d6de; border-radius: 4px; margin-bottom: 12px;">
  <div class="box-header with-border permission-header" style="background-color: #f9fafc; cursor: pointer; padding: 10px 15px;">
    <h3 class="box-title" style="font-size: 15px; font-weight: 600; color: #333;">
      <i class="fa fa-chevron-right text-muted group-chevron" style="margin-right: 8px; font-size: 13px; transition: transform 0.2s;"></i>
      @lang('business.product')
    </h3>
    <div class="box-tools pull-right">
      <span class="badge bg-gray selected-count-badge" style="font-weight: normal; margin-right: 5px;">0 selected</span>
      <button type="button" class="btn btn-box-tool"><i class="fa fa-plus"></i></button>
    </div>
  </div>
  <div class="box-body permission-body" style="padding: 15px 20px; display: none;">
    <div class="row">
      <div class="col-md-2" style="border-right: 1px solid #eee;">
        <div class="checkbox" style="margin-top: 0;">
          <label style="font-weight: bold; color: #3c8dbc;">
            <input type="checkbox" class="check_all input-icheck"> {{ __('role.select_all') }}
          </label>
        </div>
      </div>
      <div class="col-md-10">
        <div class="col-md-3 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::checkbox('permissions[]', 'product.view', in_array('product.view', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('role.product.view') }}</label>
          </div>
        </div>
        <div class="col-md-3 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::checkbox('permissions[]', 'product.create', in_array('product.create', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('role.product.create') }}</label>
          </div>
        </div>
        <div class="col-md-3 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::checkbox('permissions[]', 'product.update', in_array('product.update', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('role.product.update') }}</label>
          </div>
        </div>
        <div class="col-md-3 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::checkbox('permissions[]', 'product.delete', in_array('product.delete', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('role.product.delete') }}</label>
          </div>
        </div>
        <div class="col-md-3 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::checkbox('permissions[]', 'product.upload_image', in_array('product.upload_image', $role_permissions), ['class' => 'input-icheck']); !!} Upload image</label>
          </div>
        </div>
        <div class="col-md-3 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::checkbox('permissions[]', 'product.secondary_name', in_array('product.secondary_name', $role_permissions), ['class' => 'input-icheck']); !!} View & Edit Product Second Language</label>
          </div>
        </div>
        <div class="col-md-3 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::checkbox('permissions[]', 'product.opening_stock', in_array('product.opening_stock', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('lang_v1.add_opening_stock') }}</label>
          </div>
        </div>
        <div class="col-md-3 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::checkbox('permissions[]', 'view_purchase_price', in_array('view_purchase_price', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('lang_v1.view_purchase_price') }}</label>
            @show_tooltip(__('lang_v1.view_purchase_price_tooltip'))
          </div>
        </div>
        <div class="col-md-3 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::checkbox('permissions[]', 'product.rename_product', in_array('product.rename_product', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('lang_v1.rename_product') }}</label>
          </div>
        </div>
        <div class="col-md-3 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::checkbox('permissions[]', 'product.rename_sku', in_array('product.rename_sku', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('lang_v1.rename_sku') }}</label>
          </div>
        </div>
        <div class="col-md-3 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::checkbox('permissions[]', 'product.update_price', in_array('product.update_price', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('lang_v1.update_price') }}</label>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- 7. Purchase & Stock Adjustment -->
@if(in_array('purchases', $enabled_modules) || in_array('stock_adjustment', $enabled_modules))
<div class="box box-solid check_group permission_group" style="border: 1px solid #d2d6de; border-radius: 4px; margin-bottom: 12px;">
  <div class="box-header with-border permission-header" style="background-color: #f9fafc; cursor: pointer; padding: 10px 15px;">
    <h3 class="box-title" style="font-size: 15px; font-weight: 600; color: #333;">
      <i class="fa fa-chevron-right text-muted group-chevron" style="margin-right: 8px; font-size: 13px; transition: transform 0.2s;"></i>
      @lang('role.purchase')
    </h3>
    <div class="box-tools pull-right">
      <span class="badge bg-gray selected-count-badge" style="font-weight: normal; margin-right: 5px;">0 selected</span>
      <button type="button" class="btn btn-box-tool"><i class="fa fa-plus"></i></button>
    </div>
  </div>
  <div class="box-body permission-body" style="padding: 15px 20px; display: none;">
    <div class="row">
      <div class="col-md-2" style="border-right: 1px solid #eee;">
        <div class="checkbox" style="margin-top: 0;">
          <label style="font-weight: bold; color: #3c8dbc;">
            <input type="checkbox" class="check_all input-icheck"> {{ __('role.select_all') }}
          </label>
        </div>
      </div>
      <div class="col-md-10">
        <div class="col-md-4 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::radio('radio_option[purchase_view]', 'purchase.view', in_array('purchase.view', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('lang_v1.view_all_purchase_n_stock_adjustment') }}</label>
          </div>
        </div>
        <div class="col-md-4 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::radio('radio_option[purchase_view]', 'view_own_purchase', in_array('view_own_purchase', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('lang_v1.view_own_purchase_n_stock_adjustment') }}</label>
          </div>
        </div>
        <div class="col-md-4 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::checkbox('permissions[]', 'purchase.create', in_array('purchase.create', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('role.purchase.create') }}</label>
          </div>
        </div>
        <div class="col-md-4 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::checkbox('permissions[]', 'purchase.update', in_array('purchase.update', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('role.purchase.update') }}</label>
          </div>
        </div>
        <div class="col-md-4 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::checkbox('permissions[]', 'purchase.delete', in_array('purchase.delete', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('role.purchase.delete') }}</label>
          </div>
        </div>
        <div class="col-md-4 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::checkbox('permissions[]', 'purchase.payments', in_array('purchase.payments', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('lang_v1.add_purchase_payment') }}</label>
          </div>
        </div>
        <div class="col-md-4 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::checkbox('permissions[]', 'edit_purchase_payment', in_array('edit_purchase_payment', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('lang_v1.edit_purchase_payment') }}</label>
          </div>
        </div>
        <div class="col-md-4 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::checkbox('permissions[]', 'delete_purchase_payment', in_array('delete_purchase_payment', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('lang_v1.delete_purchase_payment') }}</label>
          </div>
        </div>
        <div class="col-md-4 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::checkbox('permissions[]', 'purchase.update_status', in_array('purchase.update_status', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('lang_v1.update_status') }}</label>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endif

<!-- 8. Purchase Requisition -->
@if(!empty($common_settings['enable_purchase_requisition']))
<div class="box box-solid check_group permission_group" style="border: 1px solid #d2d6de; border-radius: 4px; margin-bottom: 12px;">
  <div class="box-header with-border permission-header" style="background-color: #f9fafc; cursor: pointer; padding: 10px 15px;">
    <h3 class="box-title" style="font-size: 15px; font-weight: 600; color: #333;">
      <i class="fa fa-chevron-right text-muted group-chevron" style="margin-right: 8px; font-size: 13px; transition: transform 0.2s;"></i>
      @lang('lang_v1.purchase_requisition')
    </h3>
    <div class="box-tools pull-right">
      <span class="badge bg-gray selected-count-badge" style="font-weight: normal; margin-right: 5px;">0 selected</span>
      <button type="button" class="btn btn-box-tool"><i class="fa fa-plus"></i></button>
    </div>
  </div>
  <div class="box-body permission-body" style="padding: 15px 20px; display: none;">
    <div class="row">
      <div class="col-md-2" style="border-right: 1px solid #eee;">
        <div class="checkbox" style="margin-top: 0;">
          <label style="font-weight: bold; color: #3c8dbc;">
            <input type="checkbox" class="check_all input-icheck"> {{ __('role.select_all') }}
          </label>
        </div>
      </div>
      <div class="col-md-10">
        <div class="col-md-3 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::radio('radio_option[purchase_requisition_view]', 'purchase_requisition.view_all', in_array('purchase_requisition.view_all', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('lang_v1.view_all_purchase_requisition') }}</label>
          </div>
        </div>
        <div class="col-md-3 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::radio('radio_option[purchase_requisition_view]', 'purchase_requisition.view_own', in_array('purchase_requisition.view_own', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('lang_v1.view_own_purchase_requisition') }}</label>
          </div>
        </div>
        <div class="col-md-3 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::checkbox('permissions[]', 'purchase_requisition.create', in_array('purchase_requisition.create', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('lang_v1.create_purchase_requisition') }}</label>
          </div>
        </div>
        <div class="col-md-3 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::checkbox('permissions[]', 'purchase_requisition.delete', in_array('purchase_requisition.delete', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('lang_v1.delete_purchase_requisition') }}</label>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endif

<!-- 9. Purchase Order -->
@if(!empty($common_settings['enable_purchase_order']))
<div class="box box-solid check_group permission_group" style="border: 1px solid #d2d6de; border-radius: 4px; margin-bottom: 12px;">
  <div class="box-header with-border permission-header" style="background-color: #f9fafc; cursor: pointer; padding: 10px 15px;">
    <h3 class="box-title" style="font-size: 15px; font-weight: 600; color: #333;">
      <i class="fa fa-chevron-right text-muted group-chevron" style="margin-right: 8px; font-size: 13px; transition: transform 0.2s;"></i>
      @lang('lang_v1.purchase_order')
    </h3>
    <div class="box-tools pull-right">
      <span class="badge bg-gray selected-count-badge" style="font-weight: normal; margin-right: 5px;">0 selected</span>
      <button type="button" class="btn btn-box-tool"><i class="fa fa-plus"></i></button>
    </div>
  </div>
  <div class="box-body permission-body" style="padding: 15px 20px; display: none;">
    <div class="row">
      <div class="col-md-2" style="border-right: 1px solid #eee;">
        <div class="checkbox" style="margin-top: 0;">
          <label style="font-weight: bold; color: #3c8dbc;">
            <input type="checkbox" class="check_all input-icheck"> {{ __('role.select_all') }}
          </label>
        </div>
      </div>
      <div class="col-md-10">
        <div class="col-md-3 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::radio('radio_option[purchase_order_view]', 'purchase_order.view_all', in_array('purchase_order.view_all', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('lang_v1.view_all_purchase_order') }}</label>
          </div>
        </div>
        <div class="col-md-3 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::radio('radio_option[purchase_order_view]', 'purchase_order.view_own', in_array('purchase_order.view_own', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('lang_v1.view_own_purchase_order') }}</label>
          </div>
        </div>
        <div class="col-md-2 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::checkbox('permissions[]', 'purchase_order.create', in_array('purchase_order.create', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('lang_v1.create_purchase_order') }}</label>
          </div>
        </div>
        <div class="col-md-2 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::checkbox('permissions[]', 'purchase_order.update', in_array('purchase_order.update', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('lang_v1.edit_purchase_order') }}</label>
          </div>
        </div>
        <div class="col-md-2 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::checkbox('permissions[]', 'purchase_order.delete', in_array('purchase_order.delete', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('lang_v1.delete_purchase_order') }}</label>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endif

<!-- 10. POS Sale -->
<div class="box box-solid check_group permission_group" style="border: 1px solid #d2d6de; border-radius: 4px; margin-bottom: 12px;">
  <div class="box-header with-border permission-header" style="background-color: #f9fafc; cursor: pointer; padding: 10px 15px;">
    <h3 class="box-title" style="font-size: 15px; font-weight: 600; color: #333;">
      <i class="fa fa-chevron-right text-muted group-chevron" style="margin-right: 8px; font-size: 13px; transition: transform 0.2s;"></i>
      @lang('sale.pos_sale')
    </h3>
    <div class="box-tools pull-right">
      <span class="badge bg-gray selected-count-badge" style="font-weight: normal; margin-right: 5px;">0 selected</span>
      <button type="button" class="btn btn-box-tool"><i class="fa fa-plus"></i></button>
    </div>
  </div>
  <div class="box-body permission-body" style="padding: 15px 20px; display: none;">
    <div class="row">
      <div class="col-md-2" style="border-right: 1px solid #eee;">
        <div class="checkbox" style="margin-top: 0;">
          <label style="font-weight: bold; color: #3c8dbc;">
            <input type="checkbox" class="check_all input-icheck"> {{ __('role.select_all') }}
          </label>
        </div>
      </div>
      <div class="col-md-10">
        @if(in_array('pos_sale', $enabled_modules))
          <div class="col-md-3 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
            <div class="checkbox" style="margin-top: 0;">
              <label>{!! Form::checkbox('permissions[]', 'sell.view', in_array('sell.view', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('role.sell.view') }}</label>
            </div>
          </div>
          <div class="col-md-3 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
            <div class="checkbox" style="margin-top: 0;">
              <label>{!! Form::checkbox('permissions[]', 'sell.create', in_array('sell.create', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('role.sell.create') }}</label>
            </div>
          </div>
        @endif
        <div class="col-md-3 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::checkbox('permissions[]', 'sell.update', in_array('sell.update', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('role.sell.update') }}</label>
          </div>
        </div>
        <div class="col-md-3 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::checkbox('permissions[]', 'sell.delete', in_array('sell.delete', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('role.sell.delete') }}</label>
          </div>
        </div>
        <div class="col-md-4 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::checkbox('permissions[]', 'edit_product_price_from_pos_screen', in_array('edit_product_price_from_pos_screen', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('lang_v1.edit_product_price_from_pos_screen') }}</label>
          </div>
        </div>
        <div class="col-md-4 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::checkbox('permissions[]', 'edit_product_discount_from_pos_screen', in_array('edit_product_discount_from_pos_screen', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('lang_v1.edit_product_discount_from_pos_screen') }}</label>
          </div>
        </div>
        <div class="col-md-4 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::checkbox('permissions[]', 'edit_pos_payment', in_array('edit_pos_payment', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('lang_v1.add_edit_payment') }}</label>
          </div>
        </div>
        <div class="col-md-3 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::checkbox('permissions[]', 'print_invoice', in_array('print_invoice', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('lang_v1.print_invoice') }}</label>
          </div>
        </div>
        <div class="col-md-3 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::checkbox('permissions[]', 'disable_pay_checkout', in_array('disable_pay_checkout', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('lang_v1.disable_pay_checkout') }}</label>
          </div>
        </div>
        <div class="col-md-3 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::checkbox('permissions[]', 'disable_draft', in_array('disable_draft', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('lang_v1.disable_draft') }}</label>
          </div>
        </div>
        <div class="col-md-3 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::checkbox('permissions[]', 'disable_express_checkout', in_array('disable_express_checkout', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('lang_v1.disable_express_checkout') }}</label>
          </div>
        </div>
        <div class="col-md-3 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::checkbox('permissions[]', 'disable_discount', in_array('disable_discount', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('lang_v1.disable_discount') }}</label>
          </div>
        </div>
        <div class="col-md-3 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::checkbox('permissions[]', 'disable_suspend_sale', in_array('disable_suspend_sale', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('lang_v1.disable_suspend_sale') }}</label>
          </div>
        </div>
        <div class="col-md-3 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::checkbox('permissions[]', 'disable_credit_sale', in_array('disable_credit_sale', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('lang_v1.disable_credit_sale_button') }}</label>
          </div>
        </div>
        <div class="col-md-3 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::checkbox('permissions[]', 'disable_quotation', in_array('disable_quotation', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('lang_v1.disable_quotation') }}</label>
          </div>
        </div>
        <div class="col-md-3 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::checkbox('permissions[]', 'disable_card', in_array('disable_card', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('lang_v1.disable_card') }}</label>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- 11. Sale -->
<div class="box box-solid check_group permission_group" style="border: 1px solid #d2d6de; border-radius: 4px; margin-bottom: 12px;">
  <div class="box-header with-border permission-header" style="background-color: #f9fafc; cursor: pointer; padding: 10px 15px;">
    <h3 class="box-title" style="font-size: 15px; font-weight: 600; color: #333;">
      <i class="fa fa-chevron-right text-muted group-chevron" style="margin-right: 8px; font-size: 13px; transition: transform 0.2s;"></i>
      @lang('sale.sale') @show_tooltip(__('lang_v1.sell_permissions_tooltip'))
    </h3>
    <div class="box-tools pull-right">
      <span class="badge bg-gray selected-count-badge" style="font-weight: normal; margin-right: 5px;">0 selected</span>
      <button type="button" class="btn btn-box-tool"><i class="fa fa-plus"></i></button>
    </div>
  </div>
  <div class="box-body permission-body" style="padding: 15px 20px; display: none;">
    <div class="row">
      <div class="col-md-2" style="border-right: 1px solid #eee;">
        <div class="checkbox" style="margin-top: 0;">
          <label style="font-weight: bold; color: #3c8dbc;">
            <input type="checkbox" class="check_all input-icheck"> {{ __('role.select_all') }}
          </label>
        </div>
      </div>
      <div class="col-md-10">
        @if(in_array('add_sale', $enabled_modules))
          <div class="col-md-4 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
            <div class="checkbox" style="margin-top: 0;">
              <label>{!! Form::radio('radio_option[sell_view]', 'direct_sell.view', in_array('direct_sell.view', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('lang_v1.view_all_sale') }}</label>
            </div>
          </div>
          <div class="col-md-4 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
            <div class="checkbox" style="margin-top: 0;">
              <label>{!! Form::radio('radio_option[sell_view]', 'view_own_sell_only', in_array('view_own_sell_only', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('lang_v1.view_own_sell_only') }}</label>
            </div>
          </div>
        @endif
        <div class="col-md-4 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::checkbox('permissions[]', 'view_paid_sells_only', in_array('view_paid_sells_only', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('lang_v1.view_paid_sells_only') }}</label>
          </div>
        </div>
        <div class="col-md-4 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::checkbox('permissions[]', 'view_due_sells_only', in_array('view_due_sells_only', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('lang_v1.view_due_sells_only') }}</label>
          </div>
        </div>
        <div class="col-md-4 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::checkbox('permissions[]', 'view_partial_sells_only', in_array('view_partial_sells_only', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('lang_v1.view_partially_paid_sells_only') }}</label>
          </div>
        </div>
        <div class="col-md-4 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::checkbox('permissions[]', 'view_overdue_sells_only', in_array('view_overdue_sells_only', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('lang_v1.view_overdue_sells_only') }}</label>
          </div>
        </div>
        @if(in_array('add_sale', $enabled_modules))
          <div class="col-md-4 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
            <div class="checkbox" style="margin-top: 0;">
              <label>{!! Form::checkbox('permissions[]', 'direct_sell.access', in_array('direct_sell.access', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('lang_v1.add_sell') }}</label>
            </div>
          </div>
          <div class="col-md-4 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
            <div class="checkbox" style="margin-top: 0;">
              <label>{!! Form::checkbox('permissions[]', 'direct_sell.update', in_array('direct_sell.update', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('lang_v1.update_sale') }}</label>
            </div>
          </div>
          <div class="col-md-4 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
            <div class="checkbox" style="margin-top: 0;">
              <label>{!! Form::checkbox('permissions[]', 'direct_sell.delete', in_array('direct_sell.delete', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('lang_v1.delete_sell') }}</label>
            </div>
          </div>
        @endif
        <div class="col-md-4 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::checkbox('permissions[]', 'view_commission_agent_sell', in_array('view_commission_agent_sell', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('lang_v1.view_commission_agent_sell') }}</label>
          </div>
        </div>
        <div class="col-md-4 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::checkbox('permissions[]', 'sell.payments', in_array('sell.payments', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('lang_v1.add_sell_payment') }}</label>
          </div>
        </div>
        <div class="col-md-4 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::checkbox('permissions[]', 'edit_sell_payment', in_array('edit_sell_payment', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('lang_v1.edit_sell_payment') }}</label>
          </div>
        </div>
        <div class="col-md-4 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::checkbox('permissions[]', 'delete_sell_payment', in_array('delete_sell_payment', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('lang_v1.delete_sell_payment') }}</label>
          </div>
        </div>
        <div class="col-md-4 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::checkbox('permissions[]', 'edit_product_price_from_sale_screen', in_array('edit_product_price_from_sale_screen', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('lang_v1.edit_product_price_from_sale_screen') }}</label>
          </div>
        </div>
        <div class="col-md-4 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::checkbox('permissions[]', 'edit_product_discount_from_sale_screen', in_array('edit_product_discount_from_sale_screen', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('lang_v1.edit_product_discount_from_sale_screen') }}</label>
          </div>
        </div>
        <div class="col-md-4 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::checkbox('permissions[]', 'discount.access', in_array('discount.access', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('lang_v1.discount.access') }}</label>
          </div>
        </div>
        @if(in_array('types_of_service', $enabled_modules))
          <div class="col-md-4 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
            <div class="checkbox" style="margin-top: 0;">
              <label>{!! Form::checkbox('permissions[]', 'access_types_of_service', in_array('access_types_of_service', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('lang_v1.access_types_of_service') }}</label>
            </div>
          </div>
        @endif
        <div class="col-md-4 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::checkbox('permissions[]', 'access_sell_return', in_array('access_sell_return', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('lang_v1.access_all_sell_return') }}</label>
          </div>
        </div>
        <div class="col-md-4 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::checkbox('permissions[]', 'access_own_sell_return', in_array('access_own_sell_return', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('lang_v1.access_own_sell_return') }}</label>
          </div>
        </div>
        <div class="col-md-4 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::checkbox('permissions[]', 'edit_invoice_number', in_array('edit_invoice_number', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('lang_v1.add_edit_invoice_number') }}</label>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- 12. Sales Order -->
@if(!empty($pos_settings['enable_sales_order']))
<div class="box box-solid check_group permission_group" style="border: 1px solid #d2d6de; border-radius: 4px; margin-bottom: 12px;">
  <div class="box-header with-border permission-header" style="background-color: #f9fafc; cursor: pointer; padding: 10px 15px;">
    <h3 class="box-title" style="font-size: 15px; font-weight: 600; color: #333;">
      <i class="fa fa-chevron-right text-muted group-chevron" style="margin-right: 8px; font-size: 13px; transition: transform 0.2s;"></i>
      @lang('lang_v1.sales_order')
    </h3>
    <div class="box-tools pull-right">
      <span class="badge bg-gray selected-count-badge" style="font-weight: normal; margin-right: 5px;">0 selected</span>
      <button type="button" class="btn btn-box-tool"><i class="fa fa-plus"></i></button>
    </div>
  </div>
  <div class="box-body permission-body" style="padding: 15px 20px; display: none;">
    <div class="row">
      <div class="col-md-2" style="border-right: 1px solid #eee;">
        <div class="checkbox" style="margin-top: 0;">
          <label style="font-weight: bold; color: #3c8dbc;">
            <input type="checkbox" class="check_all input-icheck"> {{ __('role.select_all') }}
          </label>
        </div>
      </div>
      <div class="col-md-10">
        <div class="col-md-3 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::radio('radio_option[so_view]', 'so.view_all', in_array('so.view_all', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('lang_v1.view_all_so') }}</label>
          </div>
        </div>
        <div class="col-md-3 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::radio('radio_option[so_view]', 'so.view_own', in_array('so.view_own', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('lang_v1.view_own_so') }}</label>
          </div>
        </div>
        <div class="col-md-2 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::checkbox('permissions[]', 'so.create', in_array('so.create', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('lang_v1.create_so') }}</label>
          </div>
        </div>
        <div class="col-md-2 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::checkbox('permissions[]', 'so.update', in_array('so.update', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('lang_v1.edit_so') }}</label>
          </div>
        </div>
        <div class="col-md-2 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::checkbox('permissions[]', 'so.delete', in_array('so.delete', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('lang_v1.delete_so') }}</label>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endif

<!-- 13. Draft -->
<div class="box box-solid check_group permission_group" style="border: 1px solid #d2d6de; border-radius: 4px; margin-bottom: 12px;">
  <div class="box-header with-border permission-header" style="background-color: #f9fafc; cursor: pointer; padding: 10px 15px;">
    <h3 class="box-title" style="font-size: 15px; font-weight: 600; color: #333;">
      <i class="fa fa-chevron-right text-muted group-chevron" style="margin-right: 8px; font-size: 13px; transition: transform 0.2s;"></i>
      @lang('sale.draft')
    </h3>
    <div class="box-tools pull-right">
      <span class="badge bg-gray selected-count-badge" style="font-weight: normal; margin-right: 5px;">0 selected</span>
      <button type="button" class="btn btn-box-tool"><i class="fa fa-plus"></i></button>
    </div>
  </div>
  <div class="box-body permission-body" style="padding: 15px 20px; display: none;">
    <div class="row">
      <div class="col-md-2" style="border-right: 1px solid #eee;">
        <div class="checkbox" style="margin-top: 0;">
          <label style="font-weight: bold; color: #3c8dbc;">
            <input type="checkbox" class="check_all input-icheck"> {{ __('role.select_all') }}
          </label>
        </div>
      </div>
      <div class="col-md-10">
        <div class="col-md-3 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::radio('radio_option[draft_view]', 'draft.view_all', in_array('draft.view_all', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('lang_v1.view_all_drafts') }}</label>
          </div>
        </div>
        <div class="col-md-3 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::radio('radio_option[draft_view]', 'draft.view_own', in_array('draft.view_own', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('lang_v1.view_own_drafts') }}</label>
          </div>
        </div>
        <div class="col-md-3 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::checkbox('permissions[]', 'draft.update', in_array('draft.update', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('lang_v1.edit_draft') }}</label>
          </div>
        </div>
        <div class="col-md-3 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::checkbox('permissions[]', 'draft.delete', in_array('draft.delete', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('lang_v1.delete_draft') }}</label>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- 14. Quotation -->
<div class="box box-solid check_group permission_group" style="border: 1px solid #d2d6de; border-radius: 4px; margin-bottom: 12px;">
  <div class="box-header with-border permission-header" style="background-color: #f9fafc; cursor: pointer; padding: 10px 15px;">
    <h3 class="box-title" style="font-size: 15px; font-weight: 600; color: #333;">
      <i class="fa fa-chevron-right text-muted group-chevron" style="margin-right: 8px; font-size: 13px; transition: transform 0.2s;"></i>
      @lang('lang_v1.quotation')
    </h3>
    <div class="box-tools pull-right">
      <span class="badge bg-gray selected-count-badge" style="font-weight: normal; margin-right: 5px;">0 selected</span>
      <button type="button" class="btn btn-box-tool"><i class="fa fa-plus"></i></button>
    </div>
  </div>
  <div class="box-body permission-body" style="padding: 15px 20px; display: none;">
    <div class="row">
      <div class="col-md-2" style="border-right: 1px solid #eee;">
        <div class="checkbox" style="margin-top: 0;">
          <label style="font-weight: bold; color: #3c8dbc;">
            <input type="checkbox" class="check_all input-icheck"> {{ __('role.select_all') }}
          </label>
        </div>
      </div>
      <div class="col-md-10">
        <div class="col-md-3 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::radio('radio_option[quotation_view]', 'quotation.view_all', in_array('quotation.view_all', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('lang_v1.view_all_quotations') }}</label>
          </div>
        </div>
        <div class="col-md-3 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::radio('radio_option[quotation_view]', 'quotation.view_own', in_array('quotation.view_own', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('lang_v1.view_own_quotations') }}</label>
          </div>
        </div>
        <div class="col-md-3 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::checkbox('permissions[]', 'quotation.update', in_array('quotation.update', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('lang_v1.edit_quotation') }}</label>
          </div>
        </div>
        <div class="col-md-3 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::checkbox('permissions[]', 'quotation.delete', in_array('quotation.delete', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('lang_v1.delete_quotation') }}</label>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- 15. Shipments -->
<div class="box box-solid check_group permission_group" style="border: 1px solid #d2d6de; border-radius: 4px; margin-bottom: 12px;">
  <div class="box-header with-border permission-header" style="background-color: #f9fafc; cursor: pointer; padding: 10px 15px;">
    <h3 class="box-title" style="font-size: 15px; font-weight: 600; color: #333;">
      <i class="fa fa-chevron-right text-muted group-chevron" style="margin-right: 8px; font-size: 13px; transition: transform 0.2s;"></i>
      @lang('lang_v1.shipments')
    </h3>
    <div class="box-tools pull-right">
      <span class="badge bg-gray selected-count-badge" style="font-weight: normal; margin-right: 5px;">0 selected</span>
      <button type="button" class="btn btn-box-tool"><i class="fa fa-plus"></i></button>
    </div>
  </div>
  <div class="box-body permission-body" style="padding: 15px 20px; display: none;">
    <div class="row">
      <div class="col-md-2" style="border-right: 1px solid #eee;">
        <div class="checkbox" style="margin-top: 0;">
          <label style="font-weight: bold; color: #3c8dbc;">
            <input type="checkbox" class="check_all input-icheck"> {{ __('role.select_all') }}
          </label>
        </div>
      </div>
      <div class="col-md-10">
        <div class="col-md-3 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::radio('radio_option[shipping_view]', 'access_shipping', in_array('access_shipping', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('lang_v1.access_all_shipments') }}</label>
          </div>
        </div>
        <div class="col-md-3 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::radio('radio_option[shipping_view]', 'access_own_shipping', in_array('access_own_shipping', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('lang_v1.access_own_shipping') }}</label>
          </div>
        </div>
        <div class="col-md-3 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::checkbox('permissions[]', 'access_pending_shipments_only', in_array('access_pending_shipments_only', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('lang_v1.access_pending_shipments_only') }}</label>
          </div>
        </div>
        <div class="col-md-3 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::checkbox('permissions[]', 'access_commission_agent_shipping', in_array('access_commission_agent_shipping', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('lang_v1.access_commission_agent_shipping') }}</label>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- 16. Cash Register -->
<div class="box box-solid check_group permission_group" style="border: 1px solid #d2d6de; border-radius: 4px; margin-bottom: 12px;">
  <div class="box-header with-border permission-header" style="background-color: #f9fafc; cursor: pointer; padding: 10px 15px;">
    <h3 class="box-title" style="font-size: 15px; font-weight: 600; color: #333;">
      <i class="fa fa-chevron-right text-muted group-chevron" style="margin-right: 8px; font-size: 13px; transition: transform 0.2s;"></i>
      @lang('cash_register.cash_register')
    </h3>
    <div class="box-tools pull-right">
      <span class="badge bg-gray selected-count-badge" style="font-weight: normal; margin-right: 5px;">0 selected</span>
      <button type="button" class="btn btn-box-tool"><i class="fa fa-plus"></i></button>
    </div>
  </div>
  <div class="box-body permission-body" style="padding: 15px 20px; display: none;">
    <div class="row">
      <div class="col-md-2" style="border-right: 1px solid #eee;">
        <div class="checkbox" style="margin-top: 0;">
          <label style="font-weight: bold; color: #3c8dbc;">
            <input type="checkbox" class="check_all input-icheck"> {{ __('role.select_all') }}
          </label>
        </div>
      </div>
      <div class="col-md-10">
        <div class="col-md-4 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::checkbox('permissions[]', 'view_cash_register', in_array('view_cash_register', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('lang_v1.view_cash_register') }}</label>
          </div>
        </div>
        <div class="col-md-4 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::checkbox('permissions[]', 'close_cash_register', in_array('close_cash_register', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('lang_v1.close_cash_register') }}</label>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- 17. Brand -->
<div class="box box-solid check_group permission_group" style="border: 1px solid #d2d6de; border-radius: 4px; margin-bottom: 12px;">
  <div class="box-header with-border permission-header" style="background-color: #f9fafc; cursor: pointer; padding: 10px 15px;">
    <h3 class="box-title" style="font-size: 15px; font-weight: 600; color: #333;">
      <i class="fa fa-chevron-right text-muted group-chevron" style="margin-right: 8px; font-size: 13px; transition: transform 0.2s;"></i>
      @lang('role.brand')
    </h3>
    <div class="box-tools pull-right">
      <span class="badge bg-gray selected-count-badge" style="font-weight: normal; margin-right: 5px;">0 selected</span>
      <button type="button" class="btn btn-box-tool"><i class="fa fa-plus"></i></button>
    </div>
  </div>
  <div class="box-body permission-body" style="padding: 15px 20px; display: none;">
    <div class="row">
      <div class="col-md-2" style="border-right: 1px solid #eee;">
        <div class="checkbox" style="margin-top: 0;">
          <label style="font-weight: bold; color: #3c8dbc;">
            <input type="checkbox" class="check_all input-icheck"> {{ __('role.select_all') }}
          </label>
        </div>
      </div>
      <div class="col-md-10">
        <div class="col-md-3 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::checkbox('permissions[]', 'brand.view', in_array('brand.view', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('role.brand.view') }}</label>
          </div>
        </div>
        <div class="col-md-3 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::checkbox('permissions[]', 'brand.create', in_array('brand.create', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('role.brand.create') }}</label>
          </div>
        </div>
        <div class="col-md-3 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::checkbox('permissions[]', 'brand.update', in_array('brand.update', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('role.brand.update') }}</label>
          </div>
        </div>
        <div class="col-md-3 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::checkbox('permissions[]', 'brand.delete', in_array('brand.delete', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('role.brand.delete') }}</label>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- 18. Tax Rate -->
<div class="box box-solid check_group permission_group" style="border: 1px solid #d2d6de; border-radius: 4px; margin-bottom: 12px;">
  <div class="box-header with-border permission-header" style="background-color: #f9fafc; cursor: pointer; padding: 10px 15px;">
    <h3 class="box-title" style="font-size: 15px; font-weight: 600; color: #333;">
      <i class="fa fa-chevron-right text-muted group-chevron" style="margin-right: 8px; font-size: 13px; transition: transform 0.2s;"></i>
      @lang('role.tax_rate')
    </h3>
    <div class="box-tools pull-right">
      <span class="badge bg-gray selected-count-badge" style="font-weight: normal; margin-right: 5px;">0 selected</span>
      <button type="button" class="btn btn-box-tool"><i class="fa fa-plus"></i></button>
    </div>
  </div>
  <div class="box-body permission-body" style="padding: 15px 20px; display: none;">
    <div class="row">
      <div class="col-md-2" style="border-right: 1px solid #eee;">
        <div class="checkbox" style="margin-top: 0;">
          <label style="font-weight: bold; color: #3c8dbc;">
            <input type="checkbox" class="check_all input-icheck"> {{ __('role.select_all') }}
          </label>
        </div>
      </div>
      <div class="col-md-10">
        <div class="col-md-3 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::checkbox('permissions[]', 'tax_rate.view', in_array('tax_rate.view', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('role.tax_rate.view') }}</label>
          </div>
        </div>
        <div class="col-md-3 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::checkbox('permissions[]', 'tax_rate.create', in_array('tax_rate.create', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('role.tax_rate.create') }}</label>
          </div>
        </div>
        <div class="col-md-3 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::checkbox('permissions[]', 'tax_rate.update', in_array('tax_rate.update', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('role.tax_rate.update') }}</label>
          </div>
        </div>
        <div class="col-md-3 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::checkbox('permissions[]', 'tax_rate.delete', in_array('tax_rate.delete', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('role.tax_rate.delete') }}</label>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- 19. Unit -->
<div class="box box-solid check_group permission_group" style="border: 1px solid #d2d6de; border-radius: 4px; margin-bottom: 12px;">
  <div class="box-header with-border permission-header" style="background-color: #f9fafc; cursor: pointer; padding: 10px 15px;">
    <h3 class="box-title" style="font-size: 15px; font-weight: 600; color: #333;">
      <i class="fa fa-chevron-right text-muted group-chevron" style="margin-right: 8px; font-size: 13px; transition: transform 0.2s;"></i>
      @lang('role.unit')
    </h3>
    <div class="box-tools pull-right">
      <span class="badge bg-gray selected-count-badge" style="font-weight: normal; margin-right: 5px;">0 selected</span>
      <button type="button" class="btn btn-box-tool"><i class="fa fa-plus"></i></button>
    </div>
  </div>
  <div class="box-body permission-body" style="padding: 15px 20px; display: none;">
    <div class="row">
      <div class="col-md-2" style="border-right: 1px solid #eee;">
        <div class="checkbox" style="margin-top: 0;">
          <label style="font-weight: bold; color: #3c8dbc;">
            <input type="checkbox" class="check_all input-icheck"> {{ __('role.select_all') }}
          </label>
        </div>
      </div>
      <div class="col-md-10">
        <div class="col-md-3 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::checkbox('permissions[]', 'unit.view', in_array('unit.view', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('role.unit.view') }}</label>
          </div>
        </div>
        <div class="col-md-3 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::checkbox('permissions[]', 'unit.create', in_array('unit.create', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('role.unit.create') }}</label>
          </div>
        </div>
        <div class="col-md-3 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::checkbox('permissions[]', 'unit.update', in_array('unit.update', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('role.unit.update') }}</label>
          </div>
        </div>
        <div class="col-md-3 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::checkbox('permissions[]', 'unit.delete', in_array('unit.delete', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('role.unit.delete') }}</label>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- 20. Category -->
<div class="box box-solid check_group permission_group" style="border: 1px solid #d2d6de; border-radius: 4px; margin-bottom: 12px;">
  <div class="box-header with-border permission-header" style="background-color: #f9fafc; cursor: pointer; padding: 10px 15px;">
    <h3 class="box-title" style="font-size: 15px; font-weight: 600; color: #333;">
      <i class="fa fa-chevron-right text-muted group-chevron" style="margin-right: 8px; font-size: 13px; transition: transform 0.2s;"></i>
      @lang('category.category')
    </h3>
    <div class="box-tools pull-right">
      <span class="badge bg-gray selected-count-badge" style="font-weight: normal; margin-right: 5px;">0 selected</span>
      <button type="button" class="btn btn-box-tool"><i class="fa fa-plus"></i></button>
    </div>
  </div>
  <div class="box-body permission-body" style="padding: 15px 20px; display: none;">
    <div class="row">
      <div class="col-md-2" style="border-right: 1px solid #eee;">
        <div class="checkbox" style="margin-top: 0;">
          <label style="font-weight: bold; color: #3c8dbc;">
            <input type="checkbox" class="check_all input-icheck"> {{ __('role.select_all') }}
          </label>
        </div>
      </div>
      <div class="col-md-10">
        <div class="col-md-3 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::checkbox('permissions[]', 'category.view', in_array('category.view', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('role.category.view') }}</label>
          </div>
        </div>
        <div class="col-md-3 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::checkbox('permissions[]', 'category.create', in_array('category.create', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('role.category.create') }}</label>
          </div>
        </div>
        <div class="col-md-3 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::checkbox('permissions[]', 'category.update', in_array('category.update', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('role.category.update') }}</label>
          </div>
        </div>
        <div class="col-md-3 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::checkbox('permissions[]', 'category.delete', in_array('category.delete', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('role.category.delete') }}</label>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- 21. Report -->
<div class="box box-solid check_group permission_group" style="border: 1px solid #d2d6de; border-radius: 4px; margin-bottom: 12px;">
  <div class="box-header with-border permission-header" style="background-color: #f9fafc; cursor: pointer; padding: 10px 15px;">
    <h3 class="box-title" style="font-size: 15px; font-weight: 600; color: #333;">
      <i class="fa fa-chevron-right text-muted group-chevron" style="margin-right: 8px; font-size: 13px; transition: transform 0.2s;"></i>
      @lang('role.report')
    </h3>
    <div class="box-tools pull-right">
      <span class="badge bg-gray selected-count-badge" style="font-weight: normal; margin-right: 5px;">0 selected</span>
      <button type="button" class="btn btn-box-tool"><i class="fa fa-plus"></i></button>
    </div>
  </div>
  <div class="box-body permission-body" style="padding: 15px 20px; display: none;">
    <div class="row">
      <div class="col-md-2" style="border-right: 1px solid #eee;">
        <div class="checkbox" style="margin-top: 0;">
          <label style="font-weight: bold; color: #3c8dbc;">
            <input type="checkbox" class="check_all input-icheck"> {{ __('role.select_all') }}
          </label>
        </div>
      </div>
      <div class="col-md-10">
        @if(in_array('purchases', $enabled_modules) || in_array('add_sale', $enabled_modules) || in_array('pos_sale', $enabled_modules))
          <div class="col-md-4 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
            <div class="checkbox" style="margin-top: 0;">
              <label>{!! Form::checkbox('permissions[]', 'purchase_n_sell_report.view', in_array('purchase_n_sell_report.view', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('role.purchase_n_sell_report.view') }}</label>
            </div>
          </div>
        @endif
        <div class="col-md-4 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::checkbox('permissions[]', 'tax_report.view', in_array('tax_report.view', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('role.tax_report.view') }}</label>
          </div>
        </div>
        <div class="col-md-4 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::checkbox('permissions[]', 'contacts_report.view', in_array('contacts_report.view', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('role.contacts_report.view') }}</label>
          </div>
        </div>
        @if(in_array('expenses', $enabled_modules))
          <div class="col-md-4 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
            <div class="checkbox" style="margin-top: 0;">
              <label>{!! Form::checkbox('permissions[]', 'expense_report.view', in_array('expense_report.view', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('role.expense_report.view') }}</label>
            </div>
          </div>
        @endif
        <div class="col-md-4 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::checkbox('permissions[]', 'profit_loss_report.view', in_array('profit_loss_report.view', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('role.profit_loss_report.view') }}</label>
          </div>
        </div>
        <div class="col-md-4 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::checkbox('permissions[]', 'stock_report.view', in_array('stock_report.view', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('role.stock_report.view') }}</label>
          </div>
        </div>
        <div class="col-md-4 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::checkbox('permissions[]', 'trending_product_report.view', in_array('trending_product_report.view', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('role.trending_product_report.view') }}</label>
          </div>
        </div>
        <div class="col-md-4 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::checkbox('permissions[]', 'register_report.view', in_array('register_report.view', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('role.register_report.view') }}</label>
          </div>
        </div>
        <div class="col-md-4 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::checkbox('permissions[]', 'sales_representative.view', in_array('sales_representative.view', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('role.sales_representative.view') }}</label>
          </div>
        </div>
        <div class="col-md-4 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::checkbox('permissions[]', 'view_product_stock_value', in_array('view_product_stock_value', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('lang_v1.view_product_stock_value') }}</label>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- 22. Settings -->
<div class="box box-solid check_group permission_group" style="border: 1px solid #d2d6de; border-radius: 4px; margin-bottom: 12px;">
  <div class="box-header with-border permission-header" style="background-color: #f9fafc; cursor: pointer; padding: 10px 15px;">
    <h3 class="box-title" style="font-size: 15px; font-weight: 600; color: #333;">
      <i class="fa fa-chevron-right text-muted group-chevron" style="margin-right: 8px; font-size: 13px; transition: transform 0.2s;"></i>
      @lang('role.settings')
    </h3>
    <div class="box-tools pull-right">
      <span class="badge bg-gray selected-count-badge" style="font-weight: normal; margin-right: 5px;">0 selected</span>
      <button type="button" class="btn btn-box-tool"><i class="fa fa-plus"></i></button>
    </div>
  </div>
  <div class="box-body permission-body" style="padding: 15px 20px; display: none;">
    <div class="row">
      <div class="col-md-2" style="border-right: 1px solid #eee;">
        <div class="checkbox" style="margin-top: 0;">
          <label style="font-weight: bold; color: #3c8dbc;">
            <input type="checkbox" class="check_all input-icheck"> {{ __('role.select_all') }}
          </label>
        </div>
      </div>
      <div class="col-md-10">
        <div class="col-md-3 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::checkbox('permissions[]', 'business_settings.access', in_array('business_settings.access', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('role.business_settings.access') }}</label>
          </div>
        </div>
        <div class="col-md-3 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::checkbox('permissions[]', 'barcode_settings.access', in_array('barcode_settings.access', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('role.barcode_settings.access') }}</label>
          </div>
        </div>
        <div class="col-md-3 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::checkbox('permissions[]', 'invoice_settings.access', in_array('invoice_settings.access', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('role.invoice_settings.access') }}</label>
          </div>
        </div>
        <div class="col-md-3 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::checkbox('permissions[]', 'access_printers', in_array('access_printers', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('lang_v1.access_printers') }}</label>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- 23. Expense -->
@if(in_array('expenses', $enabled_modules))
<div class="box box-solid check_group permission_group" style="border: 1px solid #d2d6de; border-radius: 4px; margin-bottom: 12px;">
  <div class="box-header with-border permission-header" style="background-color: #f9fafc; cursor: pointer; padding: 10px 15px;">
    <h3 class="box-title" style="font-size: 15px; font-weight: 600; color: #333;">
      <i class="fa fa-chevron-right text-muted group-chevron" style="margin-right: 8px; font-size: 13px; transition: transform 0.2s;"></i>
      @lang('lang_v1.expense')
    </h3>
    <div class="box-tools pull-right">
      <span class="badge bg-gray selected-count-badge" style="font-weight: normal; margin-right: 5px;">0 selected</span>
      <button type="button" class="btn btn-box-tool"><i class="fa fa-plus"></i></button>
    </div>
  </div>
  <div class="box-body permission-body" style="padding: 15px 20px; display: none;">
    <div class="row">
      <div class="col-md-2" style="border-right: 1px solid #eee;">
        <div class="checkbox" style="margin-top: 0;">
          <label style="font-weight: bold; color: #3c8dbc;">
            <input type="checkbox" class="check_all input-icheck"> {{ __('role.select_all') }}
          </label>
        </div>
      </div>
      <div class="col-md-10">
        <div class="col-md-3 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::radio('radio_option[expense_view]', 'all_expense.access', in_array('all_expense.access', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('lang_v1.access_all_expense') }}</label>
          </div>
        </div>
        <div class="col-md-3 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::radio('radio_option[expense_view]', 'view_own_expense', in_array('view_own_expense', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('lang_v1.view_own_expense') }}</label>
          </div>
        </div>
        <div class="col-md-2 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::checkbox('permissions[]', 'expense.add', in_array('expense.add', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('expense.add_expense') }}</label>
          </div>
        </div>
        <div class="col-md-2 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::checkbox('permissions[]', 'expense.edit', in_array('expense.edit', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('expense.edit_expense') }}</label>
          </div>
        </div>
        <div class="col-md-2 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::checkbox('permissions[]', 'expense.delete', in_array('expense.delete', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('lang_v1.delete_expense') }}</label>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endif

<!-- 24. Dashboard -->
<div class="box box-solid check_group permission_group" style="border: 1px solid #d2d6de; border-radius: 4px; margin-bottom: 12px;">
  <div class="box-header with-border permission-header" style="background-color: #f9fafc; cursor: pointer; padding: 10px 15px;">
    <h3 class="box-title" style="font-size: 15px; font-weight: 600; color: #333;">
      <i class="fa fa-chevron-right text-muted group-chevron" style="margin-right: 8px; font-size: 13px; transition: transform 0.2s;"></i>
      @lang('role.dashboard') @show_tooltip(__('tooltip.dashboard_permission'))
    </h3>
    <div class="box-tools pull-right">
      <span class="badge bg-gray selected-count-badge" style="font-weight: normal; margin-right: 5px;">0 selected</span>
      <button type="button" class="btn btn-box-tool"><i class="fa fa-plus"></i></button>
    </div>
  </div>
  <div class="box-body permission-body" style="padding: 15px 20px; display: none;">
    <div class="row">
      <div class="col-md-2" style="border-right: 1px solid #eee;">
        <div class="checkbox" style="margin-top: 0;">
          <label style="font-weight: bold; color: #3c8dbc;">
            <input type="checkbox" class="check_all input-icheck"> {{ __('role.select_all') }}
          </label>
        </div>
      </div>
      <div class="col-md-10">
        <div class="col-md-4 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::checkbox('permissions[]', 'dashboard.data', (empty($role_permissions) || in_array('dashboard.data', $role_permissions)), ['class' => 'input-icheck']); !!} {{ __('role.dashboard.data') }}</label>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- 25. Account -->
<div class="box box-solid check_group permission_group" style="border: 1px solid #d2d6de; border-radius: 4px; margin-bottom: 12px;">
  <div class="box-header with-border permission-header" style="background-color: #f9fafc; cursor: pointer; padding: 10px 15px;">
    <h3 class="box-title" style="font-size: 15px; font-weight: 600; color: #333;">
      <i class="fa fa-chevron-right text-muted group-chevron" style="margin-right: 8px; font-size: 13px; transition: transform 0.2s;"></i>
      @lang('account.account')
    </h3>
    <div class="box-tools pull-right">
      <span class="badge bg-gray selected-count-badge" style="font-weight: normal; margin-right: 5px;">0 selected</span>
      <button type="button" class="btn btn-box-tool"><i class="fa fa-plus"></i></button>
    </div>
  </div>
  <div class="box-body permission-body" style="padding: 15px 20px; display: none;">
    <div class="row">
      <div class="col-md-2" style="border-right: 1px solid #eee;">
        <div class="checkbox" style="margin-top: 0;">
          <label style="font-weight: bold; color: #3c8dbc;">
            <input type="checkbox" class="check_all input-icheck"> {{ __('role.select_all') }}
          </label>
        </div>
      </div>
      <div class="col-md-10">
        <div class="col-md-4 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::checkbox('permissions[]', 'account.access', in_array('account.access', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('lang_v1.access_accounts') }}</label>
          </div>
        </div>
        <div class="col-md-4 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::checkbox('permissions[]', 'account.create', in_array('account.create', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('account.add_account') }}</label>
          </div>
        </div>
        <div class="col-md-4 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::checkbox('permissions[]', 'account.edit', in_array('account.edit', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('account.edit_account') }}</label>
          </div>
        </div>
        <div class="col-md-4 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::checkbox('permissions[]', 'account.show', in_array('account.show', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('account.account_book') }}</label>
          </div>
        </div>
        <div class="col-md-4 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::checkbox('permissions[]', 'account.fund_transfer', in_array('account.fund_transfer', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('account.fund_transfer') }}</label>
          </div>
        </div>
        <div class="col-md-4 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::checkbox('permissions[]', 'account.deposit', in_array('account.deposit', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('account.deposit') }}</label>
          </div>
        </div>
        <div class="col-md-4 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::checkbox('permissions[]', 'account.close', in_array('account.close', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('messages.close') }} / {{ __('messages.activate') }} @lang('account.account')</label>
          </div>
        </div>
        <div class="col-md-4 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::checkbox('permissions[]', 'edit_account_transaction', in_array('edit_account_transaction', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('lang_v1.edit_account_transaction') }}</label>
          </div>
        </div>
        <div class="col-md-4 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::checkbox('permissions[]', 'delete_account_transaction', in_array('delete_account_transaction', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('lang_v1.delete_account_transaction') }}</label>
          </div>
        </div>
        <div class="col-md-4 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::radio('radio_option[account_pending_transfer]', 'account.enable_pending_transfer', in_array('account.enable_pending_transfer', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('account.enable_pending_transfer') }}</label>
          </div>
        </div>
        <div class="col-md-4 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::radio('radio_option[account_pending_transfer]', 'account.enable_immediate_credit_pending_transfer', in_array('account.enable_immediate_credit_pending_transfer', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('account.enable_immediate_credit_pending_transfer') }}</label>
          </div>
        </div>
        <div class="col-md-4 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::checkbox('permissions[]', 'account.accept_pending_transfer', in_array('account.accept_pending_transfer', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('account.accept_pending_transfer') }}</label>
          </div>
        </div>
        <div class="col-md-4 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::checkbox('permissions[]', 'account.reject_pending_transfer', in_array('account.reject_pending_transfer', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('account.reject_pending_transfer') }}</label>
          </div>
        </div>
        <div class="col-md-4 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::checkbox('permissions[]', 'account.edit_pending_transfer', in_array('account.edit_pending_transfer', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('account.edit_pending_transfer') }}</label>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- 26. Bookings -->
@if(in_array('booking', $enabled_modules))
<div class="box box-solid check_group permission_group" style="border: 1px solid #d2d6de; border-radius: 4px; margin-bottom: 12px;">
  <div class="box-header with-border permission-header" style="background-color: #f9fafc; cursor: pointer; padding: 10px 15px;">
    <h3 class="box-title" style="font-size: 15px; font-weight: 600; color: #333;">
      <i class="fa fa-chevron-right text-muted group-chevron" style="margin-right: 8px; font-size: 13px; transition: transform 0.2s;"></i>
      @lang('restaurant.bookings')
    </h3>
    <div class="box-tools pull-right">
      <span class="badge bg-gray selected-count-badge" style="font-weight: normal; margin-right: 5px;">0 selected</span>
      <button type="button" class="btn btn-box-tool"><i class="fa fa-plus"></i></button>
    </div>
  </div>
  <div class="box-body permission-body" style="padding: 15px 20px; display: none;">
    <div class="row">
      <div class="col-md-2" style="border-right: 1px solid #eee;">
        <div class="checkbox" style="margin-top: 0;">
          <label style="font-weight: bold; color: #3c8dbc;">
            <input type="checkbox" class="check_all input-icheck"> {{ __('role.select_all') }}
          </label>
        </div>
      </div>
      <div class="col-md-10">
        <div class="col-md-6 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::radio('radio_option[bookings_view]', 'crud_all_bookings', in_array('crud_all_bookings', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('restaurant.add_edit_view_all_booking') }}</label>
          </div>
        </div>
        <div class="col-md-6 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::radio('radio_option[bookings_view]', 'crud_own_bookings', in_array('crud_own_bookings', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('restaurant.add_edit_view_own_booking') }}</label>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endif

<!-- 27. Selling Price Groups -->
<div class="box box-solid check_group permission_group" style="border: 1px solid #d2d6de; border-radius: 4px; margin-bottom: 12px;">
  <div class="box-header with-border permission-header" style="background-color: #f9fafc; cursor: pointer; padding: 10px 15px;">
    <h3 class="box-title" style="font-size: 15px; font-weight: 600; color: #333;">
      <i class="fa fa-chevron-right text-muted group-chevron" style="margin-right: 8px; font-size: 13px; transition: transform 0.2s;"></i>
      @lang('lang_v1.access_selling_price_groups')
    </h3>
    <div class="box-tools pull-right">
      <span class="badge bg-gray selected-count-badge" style="font-weight: normal; margin-right: 5px;">0 selected</span>
      <button type="button" class="btn btn-box-tool"><i class="fa fa-plus"></i></button>
    </div>
  </div>
  <div class="box-body permission-body" style="padding: 15px 20px; display: none;">
    <div class="row">
      <div class="col-md-2" style="border-right: 1px solid #eee;">
        <div class="checkbox" style="margin-top: 0;">
          <label style="font-weight: bold; color: #3c8dbc;">
            <input type="checkbox" class="check_all input-icheck"> {{ __('role.select_all') }}
          </label>
        </div>
      </div>
      <div class="col-md-10">
        <div class="col-md-4 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::checkbox('permissions[]', 'access_default_selling_price', (empty($role_permissions) || in_array('access_default_selling_price', $role_permissions)), ['class' => 'input-icheck']); !!} {{ __('lang_v1.default_selling_price') }}</label>
          </div>
        </div>
        @if(isset($selling_price_groups) && count($selling_price_groups) > 0)
          @foreach($selling_price_groups as $selling_price_group)
            <div class="col-md-4 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
              <div class="checkbox" style="margin-top: 0;">
                <label>{!! Form::checkbox('spg_permissions[]', 'selling_price_group.' . $selling_price_group->id, in_array('selling_price_group.' . $selling_price_group->id, $role_permissions), ['class' => 'input-icheck']); !!} {{ $selling_price_group->name }}</label>
              </div>
            </div>
          @endforeach
        @endif
      </div>
    </div>
  </div>
</div>

<!-- 28. Restaurant -->
@if(in_array('tables', $enabled_modules))
<div class="box box-solid check_group permission_group" style="border: 1px solid #d2d6de; border-radius: 4px; margin-bottom: 12px;">
  <div class="box-header with-border permission-header" style="background-color: #f9fafc; cursor: pointer; padding: 10px 15px;">
    <h3 class="box-title" style="font-size: 15px; font-weight: 600; color: #333;">
      <i class="fa fa-chevron-right text-muted group-chevron" style="margin-right: 8px; font-size: 13px; transition: transform 0.2s;"></i>
      @lang('restaurant.restaurant')
    </h3>
    <div class="box-tools pull-right">
      <span class="badge bg-gray selected-count-badge" style="font-weight: normal; margin-right: 5px;">0 selected</span>
      <button type="button" class="btn btn-box-tool"><i class="fa fa-plus"></i></button>
    </div>
  </div>
  <div class="box-body permission-body" style="padding: 15px 20px; display: none;">
    <div class="row">
      <div class="col-md-2" style="border-right: 1px solid #eee;">
        <div class="checkbox" style="margin-top: 0;">
          <label style="font-weight: bold; color: #3c8dbc;">
            <input type="checkbox" class="check_all input-icheck"> {{ __('role.select_all') }}
          </label>
        </div>
      </div>
      <div class="col-md-10">
        <div class="col-md-4 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
          <div class="checkbox" style="margin-top: 0;">
            <label>{!! Form::checkbox('permissions[]', 'access_tables', in_array('access_tables', $role_permissions), ['class' => 'input-icheck']); !!} {{ __('lang_v1.access_tables') }}</label>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endif

<!-- 29. Additional Module Permissions -->
@include('role.partials.module_permissions')
