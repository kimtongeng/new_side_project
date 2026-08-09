@extends('layouts.app')
@section('title', __('role.edit_role'))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
  <h1>@lang( 'role.edit_role' )</h1>
</section>

<!-- Main content -->
<section class="content">
    @php
      $pos_settings = !empty(session('business.pos_settings')) ? json_decode(session('business.pos_settings'), true) : [];
      $role_permissions = isset($role_permissions) ? $role_permissions : [];
    @endphp
    @component('components.widget', ['class' => 'box-primary'])
        {!! Form::open(['url' => action([\App\Http\Controllers\RoleController::class, 'update'], [$role->id]), 'method' => 'PUT', 'id' => 'role_form' ]) !!}
        <div class="row" style="margin-bottom: 15px;">
          <div class="col-md-4">
            <div class="form-group">
              {!! Form::label('name', __( 'user.role_name' ) . ':*') !!}
              {!! Form::text('name', str_replace( '#' . auth()->user()->business_id, '', $role->name) , ['class' => 'form-control', 'required', 'placeholder' => __( 'user.role_name' ) ]); !!}
            </div>
          </div>
        </div>

        @include('role.partials.permission_form')

        <div class="row" style="margin-top: 20px;">
          <div class="col-md-12 text-center">
             <button type="submit" class="tw-dw-btn tw-dw-btn-primary tw-dw-btn-lg tw-text-white">@lang( 'messages.update' )</button>
          </div>
        </div>

        {!! Form::close() !!}
    @endcomponent
</section>
<!-- /.content -->
@endsection