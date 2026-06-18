<div class="row">
    <div class="col-md-3">
        <div class="form-group">
            {!! Form::label('location_id', __('business.business_location') . ':') !!}
            {!! Form::select('location_id', $business_locations, null, [
                'class' => 'form-control select2',
                'placeholder' => __('messages.all'),
            ]) !!}
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            {!! Form::label('customer_id', __('contact.customer') . ':') !!}
            {!! Form::select('customer_id', $customers, null, [
                'class' => 'form-control select2',
                'placeholder' => __('messages.all'),
            ]) !!}
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            {!! Form::label('payment_status', __('lang_v1.payment_status') . ':') !!}
            {!! Form::select('payment_status', [
                '' => __('messages.all'),
                'paid' => __('lang_v1.paid'),
                'partial' => __('lang_v1.partial'),
                'due' => __('lang_v1.due'),
                'overdue' => __('lang_v1.overdue'),
            ], null, ['class' => 'form-control select2']) !!}
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            {!! Form::label('sales_representative', __('lang_v1.sales_representative') . ':') !!}
            {!! Form::select('sales_representative', $sales_representative, null, [
                'class' => 'form-control select2',
                'placeholder' => __('messages.all'),
            ]) !!}
        </div>
    </div>
    @if ($is_cmsn_agent_enabled)
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('commission_agent', __('lang_v1.commission_agent') . ':') !!}
                {!! Form::select('commission_agent', $commission_agents, null, [
                    'class' => 'form-control select2',
                    'placeholder' => __('messages.all'),
                ]) !!}
            </div>
        </div>
    @endif
    <div class="col-md-3">
        <div class="form-group">
            {!! Form::label('date_range', __('report.date_range') . ':') !!}
            {!! Form::text('date_range', null, [
                'placeholder' => __('lang_v1.select_a_range'),
                'class' => 'form-control',
                'id' => 'date_range',
                'readonly',
            ]) !!}
        </div>
    </div>
</div>
@stop