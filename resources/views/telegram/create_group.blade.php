<div class="modal-dialog" role="document">
    <div class="modal-content">
        {!! Form::open(['url' => action([\App\Http\Controllers\TelegramSettingController::class, 'storeGroup']), 'method' => 'post', 'id' => 'add_group_form']) !!}
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title">Add Location Telegram Group</h4>
        </div>
        <div class="modal-body">
            <div class="form-group">
                {!! Form::label('telegram_bot_id', 'Assigned Telegram Bot:*') !!}
                {!! Form::select('telegram_bot_id', $bots, null, ['class' => 'form-control select2', 'required', 'placeholder' => 'Select Bot', 'style' => 'width:100%']) !!}
            </div>

            <div class="form-group">
                {!! Form::label('group_name', 'Group Title / Name:*') !!}
                {!! Form::text('group_name', null, ['class' => 'form-control', 'required', 'placeholder' => 'e.g. PT1001 Main Group']) !!}
            </div>

            <div class="form-group">
                {!! Form::label('location_id', 'Business Location:*') !!}
                {!! Form::select('location_id', $locations, null, ['class' => 'form-control select2', 'required', 'placeholder' => 'Select Business Location', 'style' => 'width:100%']) !!}
                <p class="help-block">Select the Business Location linked to notification events.</p>
            </div>

            <div class="form-group">
                {!! Form::label('chat_id', 'Telegram Chat ID:*') !!}
                {!! Form::text('chat_id', null, ['class' => 'form-control', 'required', 'placeholder' => 'e.g. -1003663677436']) !!}
                <p class="help-block">Group chat ID (typically starting with -100).</p>
            </div>

            <div class="form-group">
                <div class="checkbox">
                    <label>
                        {!! Form::checkbox('is_active', 1, true, ['class' => 'input-icheck']) !!} Is Active
                    </label>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="submit" class="btn btn-primary">Save Group</button>
            <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
        </div>
        {!! Form::close() !!}
    </div>
</div>
