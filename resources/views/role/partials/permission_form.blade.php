@php
  $role_permissions = isset($role_permissions) ? $role_permissions : [];
  $pos_settings = !empty(session('business.pos_settings')) ? json_decode(session('business.pos_settings'), true) : [];
  $is_service_staff = isset($role->is_service_staff) ? $role->is_service_staff : 0;
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

<!-- 7. Purchase -->
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

<!-- 8. POS Sale -->
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

<!-- 9. Sale -->
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
      </div>
    </div>
  </div>
</div>

<!-- 10. Selling Price Groups -->
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

<!-- 11. Restaurant -->
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

<!-- Additional Module Permissions -->
@include('role.partials.module_permissions')
