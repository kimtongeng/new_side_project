@extends('layouts.app')
@section('title', 'Telegram Notification Settings')

@section('content')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">
            Telegram Notification Settings
            <small class="tw-text-sm md:tw-text-base tw-text-gray-700 tw-font-semibold">Manage Bots, Location-linked Groups & Topics</small>
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="nav-tabs-custom">
                    <ul class="nav nav-tabs">
                        <li class="active">
                            <a href="#groups_tab" data-toggle="tab">
                                <i class="fas fa-users"></i> <strong>Location Groups & Topics</strong>
                            </a>
                        </li>
                        <li>
                            <a href="#bots_tab" data-toggle="tab">
                                <i class="fas fa-robot"></i> <strong>Telegram Bots</strong>
                            </a>
                        </li>
                        <li>
                            <a href="#test_tab" data-toggle="tab">
                                <i class="fas fa-paper-plane"></i> <strong>Send Test Notification</strong>
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content">
                        <!-- 1. BOTS TAB -->
                        <div class="tab-pane" id="bots_tab">
                            <div class="row">
                                <div class="col-md-12">
                                    <button type="button"
                                        class="btn btn-primary pull-right btn-modal"
                                        data-href="{{ action([\App\Http\Controllers\TelegramSettingController::class, 'createBot']) }}"
                                        data-container=".telegram_modal">
                                        <i class="fa fa-plus"></i> Add Bot
                                    </button>
                                </div>
                            </div>
                            <br>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>Bot Name</th>
                                            <th>Bot Token</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($bots as $bot)
                                            <tr>
                                                <td><strong>{{ $bot->name }}</strong></td>
                                                <td><code>{{ Str::limit($bot->bot_token, 25, '...') }}</code></td>
                                                <td>
                                                    @if($bot->is_active)
                                                        <span class="label label-success">Active</span>
                                                    @else
                                                        <span class="label label-danger">Inactive</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <button type="button"
                                                        class="btn btn-xs btn-info test_bot_btn"
                                                        data-token="{{ $bot->bot_token }}">
                                                        <i class="fas fa-plug"></i> Test Connection
                                                    </button>
                                                    <button type="button"
                                                        class="btn btn-xs btn-primary btn-modal"
                                                        data-href="{{ action([\App\Http\Controllers\TelegramSettingController::class, 'editBot'], [$bot->id]) }}"
                                                        data-container=".telegram_modal">
                                                        <i class="glyphicon glyphicon-edit"></i> Edit
                                                    </button>
                                                    <a href="{{ action([\App\Http\Controllers\TelegramSettingController::class, 'destroyBot'], [$bot->id]) }}"
                                                        class="btn btn-xs btn-danger delete_button">
                                                        <i class="glyphicon glyphicon-trash"></i> Delete
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center">No Telegram Bots added yet. Click "Add Bot" to get started.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- 2. GROUPS TAB -->
                        <div class="tab-pane active" id="groups_tab">
                            <div class="row">
                                <div class="col-md-12">
                                    <button type="button"
                                        class="btn btn-primary pull-right btn-modal"
                                        data-href="{{ action([\App\Http\Controllers\TelegramSettingController::class, 'createGroup']) }}"
                                        data-container=".telegram_modal">
                                        <i class="fa fa-plus"></i> Add Group
                                    </button>
                                </div>
                            </div>
                            <br>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>Group Name</th>
                                            <th>Location ID</th>
                                            <th>Chat ID</th>
                                            <th>Bot Assigned</th>
                                            <th>Configured Topics</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($groups as $group)
                                            <tr>
                                                <td><strong>{{ $group->group_name }}</strong></td>
                                                <td><span class="label label-primary">{{ $group->location_id }}</span></td>
                                                <td><code>{{ $group->chat_id }}</code></td>
                                                <td>{{ $group->bot->name ?? 'None' }}</td>
                                                <td>
                                                    <span class="badge bg-green">{{ $group->topics->where('is_active', 1)->where('topic_id', '!=', '')->count() }} active topics</span>
                                                </td>
                                                <td>
                                                    @if($group->is_active)
                                                        <span class="label label-success">Active</span>
                                                    @else
                                                        <span class="label label-danger">Inactive</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <button type="button"
                                                        class="btn btn-xs btn-warning btn-modal"
                                                        data-href="{{ action([\App\Http\Controllers\TelegramSettingController::class, 'editTopics'], [$group->id]) }}"
                                                        data-container=".telegram_modal">
                                                        <i class="fas fa-list-alt"></i> Manage Topics
                                                    </button>
                                                    <button type="button"
                                                        class="btn btn-xs btn-primary btn-modal"
                                                        data-href="{{ action([\App\Http\Controllers\TelegramSettingController::class, 'editGroup'], [$group->id]) }}"
                                                        data-container=".telegram_modal">
                                                        <i class="glyphicon glyphicon-edit"></i> Edit
                                                    </button>
                                                    <a href="{{ action([\App\Http\Controllers\TelegramSettingController::class, 'destroyGroup'], [$group->id]) }}"
                                                        class="btn btn-xs btn-danger delete_button">
                                                        <i class="glyphicon glyphicon-trash"></i> Delete
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center">No Location Groups added yet. Click "Add Group" to link a chat with a Business Location.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- 3. TEST TAB -->
                        <div class="tab-pane" id="test_tab">
                            <form id="telegram_test_form">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            {!! Form::label('test_location_id', 'Location Code / ID:') !!}
                                            {!! Form::text('test_location_id', 'PT1001', ['class' => 'form-control', 'required', 'placeholder' => 'e.g. PT1001']) !!}
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            {!! Form::label('test_topic_key', 'Topic Key / Category:') !!}
                                            {!! Form::select('test_topic_key', \App\Http\Controllers\TelegramSettingController::$default_topic_keys, 'sell', ['class' => 'form-control select2', 'style' => 'width:100%']) !!}
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>&nbsp;</label>
                                            <button type="button" class="btn btn-success btn-block" id="send_test_msg_btn">
                                                <i class="fas fa-paper-plane"></i> Dispatch Test Notification
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                            <div id="test_result_alert" style="display:none;" class="alert"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade telegram_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel"></div>
    </section>
@endsection

@section('javascript')
<script>
    $(document).ready(function() {
        $(document).on('click', '.test_bot_btn', function() {
            var token = $(this).data('token');
            var btn = $(this);
            btn.button('loading');
            $.ajax({
                url: "{{ action([\App\Http\Controllers\TelegramSettingController::class, 'testBot']) }}",
                type: 'POST',
                data: { bot_token: token, _token: "{{ csrf_token() }}" },
                dataType: 'json',
                success: function(result) {
                    btn.button('reset');
                    if (result.success) {
                        toastr.success(result.msg);
                    } else {
                        toastr.error(result.msg);
                    }
                },
                error: function() {
                    btn.button('reset');
                    toastr.error('API call failed');
                }
            });
        });

        $(document).on('click', '#send_test_msg_btn', function() {
            var location_id = $('#test_location_id').val();
            var topic_key = $('#test_topic_key').val();
            var btn = $(this);
            btn.button('loading');
            $('#test_result_alert').hide();

            $.ajax({
                url: "{{ action([\App\Http\Controllers\TelegramSettingController::class, 'sendTestMessage']) }}",
                type: 'POST',
                data: { location_id: location_id, topic_key: topic_key, _token: "{{ csrf_token() }}" },
                dataType: 'json',
                success: function(result) {
                    btn.button('reset');
                    if (result.success) {
                        $('#test_result_alert').removeClass('alert-danger').addClass('alert-success').html(result.msg).show();
                        toastr.success(result.msg);
                    } else {
                        $('#test_result_alert').removeClass('alert-success').addClass('alert-danger').html(result.msg).show();
                        toastr.error(result.msg);
                    }
                },
                error: function() {
                    btn.button('reset');
                    toastr.error('Test notification dispatch failed');
                }
            });
        });
    });
</script>
@endsection
