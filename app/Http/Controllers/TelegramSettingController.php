<?php

namespace App\Http\Controllers;

use App\BusinessLocation;
use App\Notifications\TelegramNotification;
use App\TelegramBot;
use App\TelegramGroup;
use App\TelegramTopic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramSettingController extends Controller
{
    public static array $default_topic_keys = [
        'sell'             => 'Sales',
        'draft'            => 'Drafts',
        'quotation'        => 'Quotations',
        'product'          => 'Products',
        'purchase'         => 'Purchases',
        'expense'          => 'Expenses',
        'transfer'         => 'Stock Transfers',
        'stock_adjustment' => 'Stock Adjustments',
        'stock_count'      => 'Stock Counts',
        'payment_account'  => 'Payment Accounts',
        'repair'           => 'Repairs',
        'home'             => 'General / Home',
    ];

    private function getLocationDropdown($business_id)
    {
        $loc_models = BusinessLocation::where('business_id', $business_id)->get();
        $locations = [];
        foreach ($loc_models as $loc) {
            $code = !empty($loc->location_id) ? $loc->location_id : (string)$loc->id;
            $locations[$code] = $loc->name . ' (' . $code . ')';
        }
        if (empty($locations)) {
            $locations['PT1001'] = 'Default Location (PT1001)';
        }
        return $locations;
    }

    public function index()
    {
        if (! auth()->user()->can('business_settings.access')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');

        $bots = TelegramBot::where('business_id', $business_id)->with('groups')->get();
        $groups = TelegramGroup::where('business_id', $business_id)->with(['bot', 'topics'])->get();
        $locations = $this->getLocationDropdown($business_id);

        return view('telegram.index', compact('bots', 'groups', 'locations'));
    }

    public function createBot()
    {
        return view('telegram.create_bot');
    }

    public function storeBot(Request $request)
    {
        if (! auth()->user()->can('business_settings.access')) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'name'      => 'required|string|max:255',
            'bot_token' => 'required|string|max:255',
        ]);

        try {
            $business_id = request()->session()->get('user.business_id');

            TelegramBot::create([
                'business_id' => $business_id,
                'name'        => $request->input('name'),
                'bot_token'   => trim($request->input('bot_token')),
                'is_active'   => $request->has('is_active') ? 1 : 0,
            ]);

            $output = ['success' => true, 'msg' => __('lang_v1.added_success')];
        } catch (\Exception $e) {
            Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            $output = ['success' => false, 'msg' => __('messages.something_went_wrong')];
        }

        return redirect()->action([\App\Http\Controllers\TelegramSettingController::class, 'index'])->with('status', $output);
    }

    public function editBot($id)
    {
        $business_id = request()->session()->get('user.business_id');
        $bot = TelegramBot::where('business_id', $business_id)->findOrFail($id);

        return view('telegram.edit_bot', compact('bot'));
    }

    public function updateBot(Request $request, $id)
    {
        if (! auth()->user()->can('business_settings.access')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $business_id = request()->session()->get('user.business_id');
            $bot = TelegramBot::where('business_id', $business_id)->findOrFail($id);

            $bot->update([
                'name'      => $request->input('name'),
                'bot_token' => trim($request->input('bot_token')),
                'is_active' => $request->has('is_active') ? 1 : 0,
            ]);

            $output = ['success' => true, 'msg' => __('lang_v1.updated_success')];
        } catch (\Exception $e) {
            Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            $output = ['success' => false, 'msg' => __('messages.something_went_wrong')];
        }

        return redirect()->action([\App\Http\Controllers\TelegramSettingController::class, 'index'])->with('status', $output);
    }

    public function destroyBot($id)
    {
        if (! auth()->user()->can('business_settings.access')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $business_id = request()->session()->get('user.business_id');
            $bot = TelegramBot::where('business_id', $business_id)->findOrFail($id);
            $bot->delete();

            $output = ['success' => true, 'msg' => __('lang_v1.deleted_success')];
        } catch (\Exception $e) {
            Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            $output = ['success' => false, 'msg' => __('messages.something_went_wrong')];
        }

        return redirect()->action([\App\Http\Controllers\TelegramSettingController::class, 'index'])->with('status', $output);
    }

    public function testBot(Request $request)
    {
        $bot_token = trim($request->input('bot_token'));
        if (empty($bot_token)) {
            return response()->json(['success' => false, 'msg' => 'Bot Token is required.']);
        }

        try {
            $response = Http::timeout(5)->get("https://api.telegram.org/bot{$bot_token}/getMe");
            if ($response->successful() && $response->json('ok')) {
                $bot_info = $response->json('result');
                $bot_username = $bot_info['username'] ?? '';
                $first_name   = $bot_info['first_name'] ?? '';
                return response()->json(['success' => true, 'msg' => "Bot Connected! Name: {$first_name} (@{$bot_username})"]);
            } else {
                return response()->json(['success' => false, 'msg' => 'Invalid Bot Token or Telegram API error.']);
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'msg' => 'Connection failed: ' . $e->getMessage()]);
        }
    }

    public function createGroup()
    {
        $business_id = request()->session()->get('user.business_id');
        $bots = TelegramBot::where('business_id', $business_id)->where('is_active', 1)->pluck('name', 'id');
        $locations = $this->getLocationDropdown($business_id);

        return view('telegram.create_group', compact('bots', 'locations'));
    }

    public function storeGroup(Request $request)
    {
        if (! auth()->user()->can('business_settings.access')) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'telegram_bot_id' => 'required',
            'group_name'      => 'required|string|max:255',
            'chat_id'         => 'required|string|max:255',
            'location_id'     => 'required|string|max:255',
        ]);

        try {
            $business_id = request()->session()->get('user.business_id');

            $group = TelegramGroup::create([
                'business_id'     => $business_id,
                'telegram_bot_id' => $request->input('telegram_bot_id'),
                'group_name'      => $request->input('group_name'),
                'chat_id'         => trim($request->input('chat_id')),
                'location_id'     => trim($request->input('location_id')),
                'is_active'       => $request->has('is_active') ? 1 : 0,
            ]);

            // Seed default topics
            foreach (self::$default_topic_keys as $key => $name) {
                TelegramTopic::create([
                    'telegram_group_id' => $group->id,
                    'topic_key'         => $key,
                    'topic_name'        => $name,
                    'topic_id'          => '',
                    'is_active'         => 1,
                ]);
            }

            $output = ['success' => true, 'msg' => __('lang_v1.added_success')];
        } catch (\Exception $e) {
            Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            $output = ['success' => false, 'msg' => __('messages.something_went_wrong')];
        }

        return redirect()->action([\App\Http\Controllers\TelegramSettingController::class, 'index'])->with('status', $output);
    }

    public function editGroup($id)
    {
        $business_id = request()->session()->get('user.business_id');
        $group = TelegramGroup::where('business_id', $business_id)->findOrFail($id);
        $bots = TelegramBot::where('business_id', $business_id)->where('is_active', 1)->pluck('name', 'id');
        $locations = $this->getLocationDropdown($business_id);

        return view('telegram.edit_group', compact('group', 'bots', 'locations'));
    }

    public function updateGroup(Request $request, $id)
    {
        if (! auth()->user()->can('business_settings.access')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $business_id = request()->session()->get('user.business_id');
            $group = TelegramGroup::where('business_id', $business_id)->findOrFail($id);

            $group->update([
                'telegram_bot_id' => $request->input('telegram_bot_id'),
                'group_name'      => $request->input('group_name'),
                'chat_id'         => trim($request->input('chat_id')),
                'location_id'     => trim($request->input('location_id')),
                'is_active'       => $request->has('is_active') ? 1 : 0,
            ]);

            $output = ['success' => true, 'msg' => __('lang_v1.updated_success')];
        } catch (\Exception $e) {
            Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            $output = ['success' => false, 'msg' => __('messages.something_went_wrong')];
        }

        return redirect()->action([\App\Http\Controllers\TelegramSettingController::class, 'index'])->with('status', $output);
    }

    public function destroyGroup($id)
    {
        if (! auth()->user()->can('business_settings.access')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $business_id = request()->session()->get('user.business_id');
            $group = TelegramGroup::where('business_id', $business_id)->findOrFail($id);
            $group->delete();

            $output = ['success' => true, 'msg' => __('lang_v1.deleted_success')];
        } catch (\Exception $e) {
            Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            $output = ['success' => false, 'msg' => __('messages.something_went_wrong')];
        }

        return redirect()->action([\App\Http\Controllers\TelegramSettingController::class, 'index'])->with('status', $output);
    }

    public function editTopics($group_id)
    {
        $business_id = request()->session()->get('user.business_id');
        $group = TelegramGroup::where('business_id', $business_id)->with('topics')->findOrFail($group_id);

        $existing_keys = $group->topics->pluck('topic_key')->toArray();

        // Ensure all default topic keys exist
        foreach (self::$default_topic_keys as $key => $name) {
            if (! in_array($key, $existing_keys)) {
                TelegramTopic::create([
                    'telegram_group_id' => $group->id,
                    'topic_key'         => $key,
                    'topic_name'        => $name,
                    'topic_id'          => '',
                    'is_active'         => 1,
                ]);
            }
        }

        $group->load('topics');

        return view('telegram.edit_topics', compact('group'));
    }

    public function updateTopics(Request $request, $group_id)
    {
        if (! auth()->user()->can('business_settings.access')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $business_id = request()->session()->get('user.business_id');
            $group = TelegramGroup::where('business_id', $business_id)->findOrFail($group_id);

            // Update existing topics
            $topics_input = $request->input('topics', []);
            foreach ($topics_input as $topic_id => $data) {
                $topic = TelegramTopic::where('telegram_group_id', $group->id)->find($topic_id);
                if ($topic) {
                    $topic->update([
                        'topic_id'  => trim($data['topic_id'] ?? ''),
                        'is_active' => !empty($data['is_active']) ? 1 : 0,
                    ]);
                }
            }

            // Handle deleted topics
            $deleted_ids = $request->input('delete_topics', []);
            if (! empty($deleted_ids) && is_array($deleted_ids)) {
                TelegramTopic::where('telegram_group_id', $group->id)
                    ->whereIn('id', $deleted_ids)
                    ->delete();
            }

            // Handle multiple new topics from + Add More Topic
            $new_topics = $request->input('new_topics', []);
            if (is_array($new_topics)) {
                foreach ($new_topics as $new_t) {
                    $key  = trim($new_t['key'] ?? '');
                    $name = trim($new_t['name'] ?? '');
                    $t_id = trim($new_t['id'] ?? '');

                    if (! empty($key) && ! empty($name)) {
                        TelegramTopic::create([
                            'telegram_group_id' => $group->id,
                            'topic_key'         => $key,
                            'topic_name'        => $name,
                            'topic_id'          => $t_id,
                            'is_active'         => 1,
                        ]);
                    }
                }
            }

            // Legacy single new topic fallback
            if (! empty($request->input('new_topic_key')) && ! empty($request->input('new_topic_name'))) {
                TelegramTopic::create([
                    'telegram_group_id' => $group->id,
                    'topic_key'         => trim($request->input('new_topic_key')),
                    'topic_name'        => trim($request->input('new_topic_name')),
                    'topic_id'          => trim($request->input('new_topic_id') ?? ''),
                    'is_active'         => 1,
                ]);
            }

            $output = ['success' => true, 'msg' => __('lang_v1.updated_success')];
        } catch (\Exception $e) {
            Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            $output = ['success' => false, 'msg' => __('messages.something_went_wrong')];
        }

        return redirect()->action([\App\Http\Controllers\TelegramSettingController::class, 'index'])->with('status', $output);
    }

    public function sendTestMessage(Request $request)
    {
        $request->validate([
            'location_id' => 'required',
            'topic_key'   => 'required',
        ]);

        $location_id = $request->input('location_id');
        $topic_key   = $request->input('topic_key');

        $time = date('Y-m-d H:i:s');
        $test_msg = "<b>🧪 TEST TELEGRAM NOTIFICATION</b>\n\n";
        $test_msg .= "📍 <b>Location Code:</b> {$location_id}\n";
        $test_msg .= "🏷️ <b>Topic Key:</b> {$topic_key}\n";
        $test_msg .= "⏰ <b>Sent At:</b> {$time}\n\n";
        $test_msg .= "✅ <i>If you received this message in the correct topic, your Telegram module configuration is working perfectly!</i>";

        try {
            TelegramNotification::sendMessage($test_msg, $topic_key, $location_id);
            return response()->json(['success' => true, 'msg' => 'Test message request dispatched successfully!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'msg' => 'Failed to send test message: ' . $e->getMessage()]);
        }
    }
}
