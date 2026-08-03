<div class="modal-dialog" role="document">
    <div class="modal-content">
        {!! Form::open(['url' => action([\App\Http\Controllers\TelegramSettingController::class, 'updateBot'], [$bot->id]), 'method' => 'put', 'id' => 'edit_bot_form']) !!}
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title">Edit Telegram Bot</h4>
        </div>
        <div class="modal-body">
            <div class="form-group">
                {!! Form::label('name', 'Bot Name:*') !!}
                {!! Form::text('name', $bot->name, ['class' => 'form-control', 'required', 'placeholder' => 'e.g. Main Production Bot']) !!}
            </div>

            <div class="form-group">
                {!! Form::label('bot_token', 'Bot Token:*') !!}
                {!! Form::text('bot_token', $bot->bot_token, ['class' => 'form-control', 'required', 'placeholder' => 'e.g. 8841464720:AAGLHIGlPDTUhUP52LnAh_0XKfO6qDk8rKo']) !!}
            </div>

            <div class="form-group">
                <div class="checkbox">
                    <label>
                        {!! Form::checkbox('is_active', 1, $bot->is_active, ['class' => 'input-icheck']) !!} Is Active
                    </label>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="submit" class="btn btn-primary">Update Bot</button>
            <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
        </div>
        {!! Form::close() !!}
    </div>
</div>
