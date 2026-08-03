<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        {!! Form::open(['url' => action([\App\Http\Controllers\TelegramSettingController::class, 'updateTopics'], [$group->id]), 'method' => 'put', 'id' => 'edit_topics_form']) !!}
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title">Manage Topics for: <strong>{{ $group->group_name }}</strong> ({{ $group->location_id }})</h4>
        </div>
        <div class="modal-body">
            <p class="text-muted">Configure the <code>message_thread_id</code> (Topic ID) for each notification category within this Telegram group. You can enter single Topic IDs (e.g. <code>16</code>) or multiple comma-separated IDs (e.g. <code>16, 25</code>).</p>
            
            <div class="table-responsive" style="max-height: 350px; overflow-y: auto;">
                <table class="table table-bordered table-striped" id="topics_table">
                    <thead>
                        <tr>
                            <th style="width: 25%;">Category Name</th>
                            <th style="width: 20%;">Key</th>
                            <th style="width: 30%;">Topic ID (message_thread_id)</th>
                            <th style="width: 15%;">Status</th>
                            <th style="width: 10%;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($group->topics as $topic)
                            <tr id="topic_row_{{ $topic->id }}">
                                <td><strong>{{ $topic->topic_name }}</strong></td>
                                <td><code>{{ $topic->topic_key }}</code></td>
                                <td>
                                    <input type="text"
                                        name="topics[{{ $topic->id }}][topic_id]"
                                        value="{{ $topic->topic_id }}"
                                        class="form-control input-sm"
                                        placeholder="e.g. 16 or 16, 25">
                                </td>
                                <td>
                                    <label>
                                        <input type="checkbox"
                                            name="topics[{{ $topic->id }}][is_active]"
                                            value="1"
                                            {{ $topic->is_active ? 'checked' : '' }}
                                            class="input-icheck"> Enable
                                    </label>
                                </td>
                                <td class="text-center">
                                    @if(!in_array($topic->topic_key, array_keys(\App\Http\Controllers\TelegramSettingController::$default_topic_keys)))
                                        <button type="button" class="btn btn-xs btn-danger mark_delete_topic_btn" data-id="{{ $topic->id }}">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    @else
                                        <span class="text-muted"><small>Default</small></span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Hidden container for marked deleted topic IDs -->
            <div id="deleted_topics_container"></div>

            <hr style="margin-top: 15px; margin-bottom: 15px;">

            <div class="row" style="margin-bottom: 10px;">
                <div class="col-md-8 col-xs-7">
                    <h4 style="margin: 0; padding-top: 5px;">Add Custom Categories / Topics</h4>
                </div>
                <div class="col-md-4 col-xs-5 text-right">
                    <button type="button" class="btn btn-sm btn-success" id="add_more_topic_row_btn">
                        <i class="fa fa-plus"></i> Add More Topic
                    </button>
                </div>
            </div>

            <div id="new_topics_container">
                <div class="row new-topic-row" style="margin-bottom: 10px;">
                    <div class="col-md-4">
                        <input type="text" name="new_topics[0][key]" class="form-control input-sm" placeholder="Category Key (e.g. custom_event)">
                    </div>
                    <div class="col-md-4">
                        <input type="text" name="new_topics[0][name]" class="form-control input-sm" placeholder="Display Name (e.g. Custom Event)">
                    </div>
                    <div class="col-md-3">
                        <input type="text" name="new_topics[0][id]" class="form-control input-sm" placeholder="Topic ID (e.g. 25 or 25,26)">
                    </div>
                    <div class="col-md-1 text-center">
                        <button type="button" class="btn btn-sm btn-danger remove_topic_row_btn"><i class="fa fa-trash"></i></button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="submit" class="btn btn-primary">Save All Topic Settings</button>
            <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
        </div>
        {!! Form::close() !!}
    </div>
</div>

<script>
    $(document).ready(function() {
        var topic_row_idx = 1;

        // Dynamic Add More Topic Row
        $('#add_more_topic_row_btn').on('click', function() {
            var html = '<div class="row new-topic-row" style="margin-bottom: 10px;">' +
                '<div class="col-md-4">' +
                    '<input type="text" name="new_topics[' + topic_row_idx + '][key]" class="form-control input-sm" placeholder="Category Key (e.g. custom_event)">' +
                '</div>' +
                '<div class="col-md-4">' +
                    '<input type="text" name="new_topics[' + topic_row_idx + '][name]" class="form-control input-sm" placeholder="Display Name (e.g. Custom Event)">' +
                '</div>' +
                '<div class="col-md-3">' +
                    '<input type="text" name="new_topics[' + topic_row_idx + '][id]" class="form-control input-sm" placeholder="Topic ID (e.g. 25 or 25,26)">' +
                '</div>' +
                '<div class="col-md-1 text-center">' +
                    '<button type="button" class="btn btn-sm btn-danger remove_topic_row_btn"><i class="fa fa-trash"></i></button>' +
                '</div>' +
            '</div>';
            $('#new_topics_container').append(html);
            topic_row_idx++;
        });

        // Remove new topic row
        $(document).on('click', '.remove_topic_row_btn', function() {
            $(this).closest('.new-topic-row').remove();
        });

        // Mark existing custom topic for deletion
        $(document).on('click', '.mark_delete_topic_btn', function() {
            var topicId = $(this).data('id');
            $('#deleted_topics_container').append('<input type="hidden" name="delete_topics[]" value="' + topicId + '">');
            $('#topic_row_' + topicId).fadeOut(300, function() { $(this).remove(); });
        });
    });
</script>
