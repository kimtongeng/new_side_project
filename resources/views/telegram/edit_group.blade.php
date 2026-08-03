<div class="modal-dialog" role="document">
    <div class="modal-content">
        {!! Form::open(['url' => action([\App\Http\Controllers\TelegramSettingController::class, 'updateGroup'], [$group->id]), 'method' => 'put', 'id' => 'edit_group_form']) !!}
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title">Edit Location Telegram Group</h4>
        </div>
        <div class="modal-body">
            <div class="form-group">
                {!! Form::label('telegram_bot_id', 'Assigned Telegram Bot:*') !!}
                {!! Form::select('telegram_bot_id', $bots, $group->telegram_bot_id, ['class' => 'form-control select2', 'required', 'placeholder' => 'Select Bot', 'style' => 'width:100%']) !!}
            </div>

            <div class="form-group">
                {!! Form::label('group_name', 'Group Title / Name:*') !!}
                {!! Form::text('group_name', $group->group_name, ['class' => 'form-control', 'required', 'placeholder' => 'e.g. PT1001 Main Group']) !!}
            </div>

            <div class="form-group">
                {!! Form::label('location_id', 'Business Location:*') !!}
                {!! Form::select('location_id', $locations, $group->location_id, ['class' => 'form-control select2', 'required', 'placeholder' => 'Select Business Location', 'style' => 'width:100%']) !!}
            </div>

            <div class="form-group">
                {!! Form::label('chat_id', 'Telegram Chat ID:*') !!}
                {!! Form::text('chat_id', $group->chat_id, ['class' => 'form-control', 'required', 'placeholder' => 'e.g. -1003663677436']) !!}
            </div>

            <div class="form-group">
                <div class="checkbox">
                    <label>
                        {!! Form::checkbox('is_active', 1, $group->is_active, ['class' => 'input-icheck']) !!} Is Active
                    </label>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="submit" class="btn btn-primary">Update Group</button>
            <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
        </div>
        {!! Form::close() !!}
    </div>
</div>
