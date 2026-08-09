@if(count($module_permissions) > 0)
  @php
    $module_role_permissions = [];
    if(!empty($role_permissions)) {
      $module_role_permissions = $role_permissions;
    }
  @endphp
  @foreach($module_permissions as $key => $value)
  <div class="box box-solid check_group permission_group" style="border: 1px solid #d2d6de; border-radius: 4px; margin-bottom: 12px;">
    <div class="box-header with-border permission-header" style="background-color: #f9fafc; cursor: pointer; padding: 10px 15px;">
      <h3 class="box-title" style="font-size: 15px; font-weight: 600; color: #333;">
        <i class="fa fa-chevron-right text-muted group-chevron" style="margin-right: 8px; font-size: 13px; transition: transform 0.2s;"></i>
        {{$key}}
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
          @foreach($value as $module_permission)
          @php
            $isSelected = in_array($module_permission['value'], $module_role_permissions);
            if(empty($role_permissions) && !empty($module_permission['default'])) {
              $isSelected = true;
            }
          @endphp
          <div class="col-md-3 col-sm-6 permission-item-wrapper" style="margin-bottom: 8px;">
            <div class="checkbox" style="margin-top: 0;">
              <label>
                @if(!empty($module_permission['is_radio']))
                  {!! Form::radio('radio_option[' . $module_permission['radio_input_name'] . ']', $module_permission['value'], $isSelected, ['class' => 'input-icheck']); !!} {{ $module_permission['label'] }}
                @else
                  {!! Form::checkbox('permissions[]', $module_permission['value'], $isSelected, ['class' => 'input-icheck']); !!} {{ $module_permission['label'] }}
                @endif
              </label>
            </div>
          </div>
          @endforeach
        </div>
      </div>
    </div>
  </div>
  @endforeach
@endif