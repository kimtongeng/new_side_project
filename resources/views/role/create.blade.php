@extends('layouts.app')
@section('title', __('role.add_role'))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
  <h1>@lang( 'role.add_role' )</h1>
</section>

<!-- Main content -->
<section class="content">
    @php
      $pos_settings = !empty(session('business.pos_settings')) ? json_decode(session('business.pos_settings'), true) : [];
      $role_permissions = [];
    @endphp
    @component('components.widget', ['class' => 'box-primary'])
        {!! Form::open(['url' => action([\App\Http\Controllers\RoleController::class, 'store']), 'method' => 'post', 'id' => 'role_add_form' ]) !!}
        <div class="row" style="margin-bottom: 15px; display: flex; align-items: flex-end; justify-content: space-between;">
          <div class="col-md-4">
            <div class="form-group" style="margin-bottom: 0;">
              {!! Form::label('name', __( 'user.role_name' ) . ':*') !!}
              {!! Form::text('name', null, ['class' => 'form-control', 'required', 'placeholder' => __( 'user.role_name' ) ]); !!}
            </div>
          </div>
          <div class="col-md-8 text-right" style="display: flex; align-items: flex-end; justify-content: flex-end;">
            <button type="submit" class="tw-dw-btn tw-dw-btn-primary tw-dw-btn-md tw-text-white">@lang( 'messages.save' )</button>
          </div>
        </div>

        @include('role.partials.permission_form')

        <div class="row" style="margin-top: 20px;">
          <div class="col-md-12 text-center">
             <button type="submit" class="tw-dw-btn tw-dw-btn-primary tw-dw-btn-lg tw-text-white">@lang( 'messages.save' )</button>
          </div>
        </div>

        {!! Form::close() !!}
    @endcomponent
</section>
<!-- /.content -->
@endsection