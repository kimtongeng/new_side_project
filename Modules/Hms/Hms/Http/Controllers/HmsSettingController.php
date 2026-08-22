<?php

namespace Modules\Hms\Http\Controllers;

use App\Business;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\NotificationTemplate;
use App\Utils\ModuleUtil;
use App\Utils\BusinessUtil;
use App\System;

class HmsSettingController extends Controller
{
    protected $moduleUtil;
    protected $businessUtil;


    public function __construct(ModuleUtil $moduleUtil, BusinessUtil $businessUtil)
    {
        $this->moduleUtil = $moduleUtil;
        $this->businessUtil = $businessUtil;
    }

    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        $business_id = request()->session()->get('user.business_id');
        
        if (! (auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'hms_module'))) {
            abort(403, 'Unauthorized action.');
        }

        $busines = Business::findOrFail($business_id);

        $tags = [
            '{business_name}, {business_logo}, {customer_name}, {booking_id}, {booking_status}, {adults}, {childrens}, {booking_details}, {additional_services}', '{arrival_date}', '{departure_date}'
        ];

        $template = NotificationTemplate::where('template_for', 'hms_new_booking')->where('business_id', $business_id)->first();

        $module_version = System::getProperty('hms_version');

        return view('hms::settings.index', compact('busines', 'tags', 'template', 'module_version'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('hms::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');
        
        if (! (auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'hms_module'))) {
            abort(403, 'Unauthorized action.');
        }
        
        try {
            $setting = $request->post('hms');
            $business_id = session()->get('user.business_id');
    
            $busines = Business::findOrFail($business_id);


            $hms_settings = json_decode($busines->hms_settings, true);

            $hms_settings['prefix'] = $request->booking_prefix;
            // Update custom field labels and requirement flags directly
            $hms_settings['id_proof_label_1'] = $request->input('id_proof_label_1');
            $hms_settings['is_id_proof_1_required'] = $request->has('is_id_proof_1_required');

            $hms_settings['id_proof_label_2'] = $request->input('id_proof_label_2');
            $hms_settings['is_id_proof_2_required'] = $request->has('is_id_proof_2_required');

            $hms_settings['id_proof_label_3'] = $request->input('id_proof_label_3');
            $hms_settings['is_id_proof_3_required'] = $request->has('is_id_proof_3_required');

            $busines->hms_settings = json_encode($hms_settings);
            $busines->update();
    
            $output = [
                'success' => 1,
                'msg' => __('lang_v1.success'),
            ];
    
            return redirect()
                ->action([\Modules\Hms\Http\Controllers\HmsSettingController::class, 'index'])
                ->with('status', $output);
                
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = [
                'success' => 0,
                'msg' => __('messages.something_went_wrong'),
            ];

            return back()->with('status', $output)->withInput();
        }

    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return view('hms::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return view('hms::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        //
    }

    public function store_email_template(Request $request){
    $business_id = session()->get('user.business_id');

        try{
            NotificationTemplate::updateOrCreate(
                ['template_for' => 'hms_new_booking', 'business_id' => $business_id],

                    [
                        'subject' => $request->post('subject'),
                        'bcc' => $request->post('bcc'),
                        'cc' => $request->post('cc'),
                        'auto_send' => ! empty($request->post('auto_send')) ? 1 : 0,
                        'email_body' => $request->post('email_body'),
                    ]
                );

                $output = [
                    'success' => 1,
                    'msg' => __('lang_v1.success'),
                ];

                return back()->with('status', $output);
                
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = [
                'success' => 0,
                'msg' => __('messages.something_went_wrong'),
            ];
            return back()->with('status', $output)->withInput();

        }
    }

    public function post_pdf(Request $request){
        $business_id = request()->session()->get('user.business_id');
        
        if (! (auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'hms_module'))) {
            abort(403, 'Unauthorized action.');
        }
        
        try {
            $setting = $request->post('hms');
            $business_id = session()->get('user.business_id');
    
            $busines = Business::findOrFail($business_id);

            $hms_settings = json_decode($busines->hms_settings, true);

            $hms_settings['booking_pdf']['footer_text'] = $request->footer_text;
            $hms_settings['booking_pdf']['address'] = $request->address;
            $hms_settings['booking_pdf']['text_after_table'] = $request->text_after_table;
            $hms_settings['booking_pdf']['phone'] = $request->phone;
            $hms_settings['booking_pdf']['email'] = $request->email;
            $hms_settings['booking_pdf']['website'] = $request->website;

            //  //upload logo
            // if ($request->hasFile('logo')) {
            //     $logo_name = $this->businessUtil->uploadFile($request, 'logo', 'hms_logos', 'image');
            //     if (! empty($logo_name)) {
            //         $hms_settings['logo'] = $logo_name;
            //     }
            // }

            $busines->hms_settings = json_encode($hms_settings);
  
            $busines->update();
    
            $output = [
                'success' => 1,
                'msg' => __('lang_v1.success'),
            ];
    
            return redirect()
                ->action([\Modules\Hms\Http\Controllers\HmsSettingController::class, 'index'])
                ->with('status', $output);
                
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = [
                'success' => 0,
                'msg' => __('messages.something_went_wrong'),
            ];

            return back()->with('status', $output)->withInput();
        }

    }
}
