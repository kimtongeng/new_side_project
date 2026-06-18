<?php

namespace Modules\Repair\Http\Controllers;

use App\Brands;
use App\Business;
use App\BusinessLocation;
use App\Category;
use App\Contact;
use App\CustomerGroup;
use App\Media;
use App\Notifications\TelegramNotification;
use App\Utils\CashRegisterUtil;
use App\Utils\ContactUtil;
use App\Utils\ModuleUtil;
use App\Utils\ProductUtil;
use App\Utils\Util;
use DB;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Modules\Repair\Entities\DeviceModel;
use Modules\Repair\Entities\JobSheet;
use Modules\Repair\Entities\RepairStatus;
use Modules\Repair\Utils\RepairUtil;
use Spatie\Activitylog\Models\Activity;
use Yajra\DataTables\Facades\DataTables;
use Modules\Repair\Notifications\RepairStatusUpdated;

class JobSheetController extends Controller
{
    /**
     * All Utils instance.
     */
    protected $repairUtil;

    protected $commonUtil;

    protected $cashRegisterUtil;

    protected $moduleUtil;

    protected $contactUtil;

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct(
        RepairUtil $repairUtil,
        Util $commonUtil,
        CashRegisterUtil $cashRegisterUtil,
        ModuleUtil $moduleUtil,
        ContactUtil $contactUtil,
        ProductUtil $productUtil
    ) {
        $this->repairUtil = $repairUtil;
        $this->commonUtil = $commonUtil;
        $this->cashRegisterUtil = $cashRegisterUtil;
        $this->moduleUtil = $moduleUtil;
        $this->contactUtil = $contactUtil;
        $this->productUtil = $productUtil;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $business_id = request()->session()->get('user.business_id');

        if (! (auth()->user()->can('superadmin') || ($this->moduleUtil->hasThePermissionInSubscription($business_id, 'repair_module') && (auth()->user()->can('job_sheet.view_assigned') || auth()->user()->can('job_sheet.view_all') || auth()->user()->can('job_sheet.create'))))) {
            abort(403, 'Unauthorized action.');
        }

        $is_user_admin = $this->commonUtil->is_admin(auth()->user(), $business_id);

        if (request()->ajax()) {
            $job_sheets = JobSheet::with('invoices')
                ->leftJoin('contacts', 'repair_job_sheets.contact_id', '=', 'contacts.id')
                ->leftJoin(
                    'repair_statuses AS rs',
                    'repair_job_sheets.status_id',
                    '=',
                    'rs.id'
                )
                ->leftJoin('users as technecian', 'repair_job_sheets.service_staff', '=', 'technecian.id')
                ->leftJoin(
                    'repair_device_models as rdm',
                    'rdm.id',
                    '=',
                    'repair_job_sheets.device_model_id'
                )
                ->leftJoin(
                    'brands AS b',
                    'repair_job_sheets.brand_id',
                    '=',
                    'b.id'
                )
                ->leftJoin(
                    'business_locations AS bl',
                    'repair_job_sheets.location_id',
                    '=',
                    'bl.id'
                )
                ->leftJoin(
                    'categories as device',
                    'device.id',
                    '=',
                    'repair_job_sheets.device_id'
                )
                ->leftJoin('users', 'repair_job_sheets.created_by', '=', 'users.id')
                ->where('repair_job_sheets.business_id', $business_id)

                ->select('repair_job_sheets.delivery_date', 'job_sheet_no', DB::raw("(
                    SELECT t.invoice_no
                    FROM transactions t
                    WHERE t.repair_job_sheet_id = repair_job_sheets.id
                    AND t.type = 'sell'
                    LIMIT 1
                ) as repair_no"), DB::raw("CONCAT(COALESCE(technecian.surname, ''),' ',COALESCE(technecian.first_name, ''),' ',COALESCE(technecian.last_name,'')) as technecian"), DB::raw("CONCAT(COALESCE(users.surname, ''),' ',COALESCE(users.first_name, ''),' ',COALESCE(users.last_name,'')) as added_by"), 'contacts.name as customer', 'b.name as brand', 'rdm.name as device_model', 'serial_no', 'estimated_cost', 'rs.name as status', 'repair_job_sheets.id as id', 'repair_job_sheets.created_at as created_at', 'service_type', 'rs.color as status_color', 'bl.name as location', 'rs.is_completed_status', 'device.name as device', 'repair_job_sheets.custom_field_1', 'repair_job_sheets.custom_field_2', 'repair_job_sheets.custom_field_3', 'repair_job_sheets.custom_field_4', 'repair_job_sheets.custom_field_5');

            //if user is not admin get only assgined/created_by job sheet
            if (! auth()->user()->can('job_sheet.view_all')) {
                if (! $is_user_admin) {
                    $user_id = auth()->user()->id;
                    $job_sheets->where(function ($query) use ($user_id) {
                        $query->where('repair_job_sheets.service_staff', $user_id)
                            ->orWhere('repair_job_sheets.created_by', $user_id);
                    });
                }
            }

            //if location is not all get only assgined location job sheet
            $permitted_locations = auth()->user()->permitted_locations();
            if ($permitted_locations != 'all') {
                $job_sheets->whereIn('repair_job_sheets.location_id', $permitted_locations);
            }

            //filter location
            if (! empty(request()->get('location_id'))) {
                $job_sheets->where('repair_job_sheets.location_id', request()->get('location_id'));
            }

            //filter by customer
            if (! empty(request()->contact_id)) {
                $job_sheets->where('repair_job_sheets.contact_id', request()->contact_id);
            }

            //filter by technecian
            if (! empty(request()->technician)) {
                $job_sheets->where('repair_job_sheets.service_staff', request()->technician);
            }

            //filter by status
            if (! empty(request()->status_id)) {
                $job_sheets->where('repair_job_sheets.status_id', request()->status_id);
            }

            //filter out mark as completed status
            if (request()->get('is_completed_status') === '1') {
                $job_sheets->where('rs.is_completed_status', 1);
            } else {
                $job_sheets->where(function ($q) {
                    $q->where('rs.is_completed_status', 0)
                        ->orWhereNull('rs.is_completed_status');
                });
            }

            return DataTables::of($job_sheets)
                ->addColumn('action', function ($row) {
                    $html = '<div class="btn-group">
                                <button class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline  tw-dw-btn-info tw-w-max dropdown-toggle" type="button"  data-toggle="dropdown" aria-expanded="false">
                                    ' . __('messages.action') . '
                                    <span class="caret"></span>
                                    <span class="sr-only">
                                    ' . __('messages.action') . '
                                    </span>
                                </button>';

                    $html .= '<ul class="dropdown-menu dropdown-menu-left" role="menu">';

                    if (auth()->user()->can('job_sheet.view_assigned') || auth()->user()->can('job_sheet.view_all') || auth()->user()->can('job_sheet.create')) {
                        $html .= '<li>
                                <a href="' . action([\Modules\Repair\Http\Controllers\JobSheetController::class, 'show'], [$row->id]) . '" class="cursor-pointer"><i class="fa fa-eye"></i> ' . __('messages.view') . '
                                </a>
                                </li>';
                    }
                    if (auth()->user()->can('repair.view_own_part') || auth()->user()->can('repair.view_all_part')) {
                        $html .= '<li>
                                    <a data-href="' . action([\Modules\Repair\Http\Controllers\JobSheetController::class, 'view_parts'], [$row->id]) . '" class="cursor-pointer view_part_sheet">
                                        <i class="fas fa-tasks"></i> View Parts
                                    </a>
                                </li>';
                    }

                    if (auth()->user()->can('repair.create')) {
                        $html .= '<li>
                                    <a href="' . action([\App\Http\Controllers\SellPosController::class, 'create']) . '?sub_type=repair&job_sheet_id=' . $row->id . '" class="cursor-pointer"><i class="fas fa-plus-circle"></i> ' . __('repair::lang.add_invoice') . '
                                    </a>
                                </li>';
                    }

                    if (auth()->user()->can('job_sheet.edit')) {
                        $html .= '<li>
                                    <a href="' . action([\Modules\Repair\Http\Controllers\JobSheetController::class, 'edit'], [$row->id]) . '" class="cursor-pointer edit_job_sheet"><i class="fa fa-edit"></i> ' . __('messages.edit') . '
                                    </a>
                                </li>';
                    }
                    if (auth()->user()->can('repair.request_and_save')) {
                        $html .= '<li>
                                    <a href="' . action([\Modules\Repair\Http\Controllers\JobSheetController::class, 'addParts'], [$row->id]) . '" class="cursor-pointer">
                                        <i class="fas fa-toolbox"></i>
                                        ' . __('repair::lang.add_parts') . '
                                    </a>
                                </li>';
                    }

                    if (auth()->user()->can('job_sheet.edit')) {

                        $html .= '<li>
                                    <a href="' . action([\Modules\Repair\Http\Controllers\JobSheetController::class, 'getUploadDocs'], [$row->id]) . '" class="cursor-pointer">
                                        <i class="fas fa-file-alt"></i>
                                        ' . __('repair::lang.upload_docs') . '
                                    </a>
                                </li>';
                    }


                    $html .= '<li>
                                    <a href="' . action([\Modules\Repair\Http\Controllers\JobSheetController::class, 'print'], [$row->id]) . '" target="_blank"><i class="fa fa-print"></i> ' . __('messages.print') . '
                                    </a>
                                </li>';

                    if (auth()->user()->can('job_sheet.create') || auth()->user()->can('job_sheet.edit')) {
                        $html .= '<li>
                                    <a data-href="' . action([\Modules\Repair\Http\Controllers\JobSheetController::class, 'editStatus'], [$row->id]) . '" class="cursor-pointer edit_job_sheet_status">
                                        <i class="fa fa-edit"></i>
                                        ' . __('repair::lang.change_status') . '
                                    </a>
                                </li>';
                    }

                    if (auth()->user()->can('job_sheet.delete')) {
                        $html .= '<li>
                                    <a data-href="' . action([\Modules\Repair\Http\Controllers\JobSheetController::class, 'destroy'], [$row->id]) . '"  id="delete_job_sheet" class="cursor-pointer">
                                        <i class="fas fa-trash"></i>
                                        ' . __('messages.delete') . '
                                    </a>
                                </li>';
                    }

                    $html .= '</ul>
                            </div>';

                    return $html;
                })
                ->editColumn(
                    'delivery_date',
                    '
                        @if($delivery_date)
                            {{@format_datetime($delivery_date)}}
                        @endif
                    '
                )
                ->editColumn(
                    'created_at',
                    '
                    {{@format_datetime($created_at)}}
                    '
                )
                ->editColumn('service_type', function ($row) {
                    return __('repair::lang.' . $row->service_type);
                })
                ->editColumn('estimated_cost', function ($row) {
                    $cost = '<span class="display_currency total-discount" data-currency_symbol="true" data-orig-value="' . $row->estimated_cost . '">' . $row->estimated_cost . '</span>';

                    return $cost;
                })
                ->editColumn('repair_no', function ($row) {
                    $invoice_no = [];
                    if ($row->invoices->count() > 0) {
                        foreach ($row->invoices as $key => $invoice) {
                            $invoice_no[] = $invoice->invoice_no;
                        }
                    }

                    $add_invoice = '';
                    if (auth()->user()->can('repair.create')) {

                        $add_invoice = '<br><a href="' . action([\App\Http\Controllers\SellPosController::class, 'create']) . '?sub_type=repair&job_sheet_id=' . $row->id . '" class="cursor-pointer" data-toggle="tooltip" title="' . __('repair::lang.add_invoice') . '">

                            <i class="fas fa-plus-circle"></i>
                            </a>';
                    }

                    return implode(', ', $invoice_no) . $add_invoice;
                })
                ->editColumn('status', function ($row) {
                    $html = '<a data-href="' . action([\Modules\Repair\Http\Controllers\JobSheetController::class, 'editStatus'], [$row->id]) . '" class="edit_job_sheet_status cursor-pointer" data-orig-value="' . $row->status . '" data-status-name="' . $row->status . '">
                                <span class="label " style="background-color:' . $row->status_color . ';" >
                                    ' . $row->status . '
                                </span>
                            </a>
                        ';

                    return $html;
                })
                ->removeColumn('id')
                ->rawColumns(['action', 'service_type', 'delivery_date', 'repair_no', 'status', 'estimated_cost', 'created_at'])
                ->make(true);
        }

        $business_locations = BusinessLocation::forDropdown($business_id, false);
        $customers = Contact::customersDropdown($business_id, false);
        $status_dropdown = RepairStatus::forDropdown($business_id);
        $service_staffs = $this->commonUtil->serviceStaffDropdown($business_id);

        $user_role_as_service_staff = auth()->user()->roles()
            ->where('is_service_staff', 1)
            ->get()
            ->toArray();
        $is_user_service_staff = false;
        if (! empty($user_role_as_service_staff) && ! $is_user_admin) {
            $is_user_service_staff = true;
        }

        $repair_settings = $this->repairUtil->getRepairSettings($business_id);

        return view('repair::job_sheet.index')
            ->with(compact('business_locations', 'customers', 'status_dropdown', 'service_staffs', 'is_user_service_staff', 'repair_settings'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $business_id = request()->session()->get('user.business_id');

        if (! (auth()->user()->can('superadmin') || ($this->moduleUtil->hasThePermissionInSubscription($business_id, 'repair_module') && auth()->user()->can('job_sheet.create')))) {
            abort(403, 'Unauthorized action.');
        }

        $repair_statuses = RepairStatus::getRepairSatuses($business_id);
        $device_models = DeviceModel::forDropdown($business_id);
        $brands = Brands::forDropdown($business_id, false, true);
        $devices = Category::forDropdown($business_id, 'device');
        $repair_settings = $this->repairUtil->getRepairSettings($business_id);
        $business_locations = BusinessLocation::forDropdown($business_id);
        $types = Contact::getContactTypes();
        $customer_groups = CustomerGroup::forDropdown($business_id);
        $walk_in_customer = $this->contactUtil->getWalkInCustomer($business_id);
        $default_status = '';
        if (! empty($repair_settings['default_status'])) {
            $default_status = $repair_settings['default_status'];
        }

        //get service staff(technecians)
        $technecians = [];
        if ($this->commonUtil->isModuleEnabled('service_staff')) {
            $technecians = $this->commonUtil->serviceStaffDropdown($business_id);
        }

        return view('repair::job_sheet.create')
            ->with(compact('repair_statuses', 'device_models', 'brands', 'devices', 'default_status', 'technecians', 'business_locations', 'types', 'customer_groups', 'walk_in_customer', 'repair_settings'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return Response
     */
    public function store(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');

        if (! (auth()->user()->can('superadmin') || ($this->moduleUtil->hasThePermissionInSubscription($business_id, 'repair_module') && auth()->user()->can('job_sheet.create')))) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $input = $request->only('contact_id', 'service_type', 'brand_id', 'device_id', 'device_model_id', 'security_pwd', 'security_pattern', 'serial_no', 'status_id', 'delivery_date', 'estimated_cost', 'product_configuration', 'defects', 'product_condition', 'service_staff', 'location_id', 'pick_up_on_site_addr', 'comment_by_ss', 'custom_field_1', 'custom_field_2', 'custom_field_3', 'custom_field_4', 'custom_field_5');

            if (! empty($input['delivery_date'])) {
                $input['delivery_date'] = $this->commonUtil->uf_date($input['delivery_date'], true);
            }

            if (! empty($input['estimated_cost'])) {
                $input['estimated_cost'] = $this->commonUtil->num_uf($input['estimated_cost']);
            }

            if (! empty($request->input('repair_checklist'))) {
                $input['checklist'] = $request->input('repair_checklist');
            }

            DB::beginTransaction();

            //Generate reference number
            $ref_count = $this->commonUtil->setAndGetReferenceCount('job_sheet', $business_id);
            $business = Business::find($business_id);
            $repair_settings = json_decode($business->repair_settings, true);

            $job_sheet_prefix = '';
            if (isset($repair_settings['job_sheet_prefix'])) {
                $job_sheet_prefix = $repair_settings['job_sheet_prefix'];
            }

            $input['job_sheet_no'] = $this->commonUtil->generateReferenceNumber('job_sheet', $ref_count, null, $job_sheet_prefix);

            $input['created_by'] = $request->user()->id;
            $input['business_id'] = $business_id;

            $job_sheet = JobSheet::create($input);

            //upload media
            Media::uploadMedia($business_id, $job_sheet, $request, 'images');

            if (! empty($request->input('send_notification')) && in_array('sms', $request->input('send_notification'))) {
                $status = RepairStatus::where('business_id', $business_id)
                    ->find($job_sheet->status_id);
                if (! empty($status->sms_template)) {
                    $this->repairUtil->sendJobSheetUpdateSmsNotification($status->sms_template, $job_sheet);
                }
            }

            if (! empty($request->input('send_notification')) && in_array('email', $request->input('send_notification'))) {
                $status = RepairStatus::where('business_id', $business_id)
                    ->find($job_sheet->status_id);
                $notification = [
                    'subject' => $status->email_subject,
                    'body' => $status->email_body,
                ];

                //Set email configuration
                $notificationUtil = new \App\Utils\NotificationUtil();
                $notificationUtil->configureEmail();

                if (! empty($status->email_subject) && ! empty($status->email_body)) {
                    $this->repairUtil->sendJobSheetUpdateEmailNotification($notification, $job_sheet);
                }
            }

            DB::commit();

            if (! empty($request->input('submit_type')) && $request->input('submit_type') == 'save_and_add_parts') {
                return redirect()
                    ->action([\Modules\Repair\Http\Controllers\JobSheetController::class, 'addParts'], [$job_sheet->id])
                    ->with('status', [
                        'success' => true,
                        'msg' => __('lang_v1.success'),
                    ]);
            } elseif (! empty($request->input('submit_type')) && $request->input('submit_type') == 'save_and_upload_docs') {
                return redirect()
                    ->action([\Modules\Repair\Http\Controllers\JobSheetController::class, 'getUploadDocs'], [$job_sheet->id])
                    ->with('status', ['success' => true, 'msg' => __('lang_v1.success')]);
            }

            // ── Telegram Notification ──────────────────────────────
            try {
                $job_sheet->load([
                    'customer',
                    'technician',
                    'status',
                    'Brand',
                    'Device',
                    'deviceModel',
                    'businessLocation',
                ]);

                $contact      = $job_sheet->customer;
                $location     = $job_sheet->businessLocation;
                $brand        = $job_sheet->Brand;
                $device       = $job_sheet->Device;
                $deviceModel  = $job_sheet->deviceModel;
                $status       = $job_sheet->status;
                $serviceStaff = $job_sheet->technician;

                $location_id = $location->location_id ?? 'PT1001';

                \App\Notifications\TelegramNotification::addJobSheetMessage(
                    $job_sheet,
                    $contact,
                    $location,
                    $brand,
                    $device,
                    $deviceModel,
                    $status,
                    $serviceStaff,
                    'repair',
                    $location_id
                );
            } catch (\Exception $te) {
                \Log::warning('Telegram job sheet notification failed: ' . $te->getMessage());
            }
            return redirect()
                ->action([\Modules\Repair\Http\Controllers\JobSheetController::class, 'show'], [$job_sheet->id])
                ->with('status', [
                    'success' => true,
                    'msg' => __('lang_v1.success'),
                ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('status', [
                    'success' => false,
                    'msg' => __('messages.something_went_wrong'),
                ]);
        }
    }

    /**
     * Show the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        $business_id = request()->session()->get('user.business_id');

        if (! (auth()->user()->can('superadmin') || ($this->moduleUtil->hasThePermissionInSubscription($business_id, 'repair_module') && (auth()->user()->can('job_sheet.view_assigned') || auth()->user()->can('job_sheet.view_all') || auth()->user()->can('job_sheet.create'))))) {
            abort(403, 'Unauthorized action.');
        }

        $query = JobSheet::with(
            'customer',
            'customer.business',
            'technician',
            'status',
            'Brand',
            'Device',
            'deviceModel',
            'businessLocation',
            'invoices',
            'media'
        )
            ->where('business_id', $business_id);

        //if user is not admin or didn't have permission `job_sheet.view_all` get only assgined/created_by job sheet
        if (! ($this->commonUtil->is_admin(auth()->user(), $business_id) || auth()->user()->can('job_sheet.view_all'))) {
            $user_id = auth()->user()->id;
            $query->where(function ($q) use ($user_id) {
                $q->where('repair_job_sheets.service_staff', $user_id)
                    ->orWhere('repair_job_sheets.created_by', $user_id);
            });
        }

        $job_sheet = $query->findOrFail($id);

        $parts = $job_sheet->getPartsUsed();

        $business = Business::find($business_id);
        $repair_settings = json_decode($business->repair_settings, true);
        $jobsheet_settings = ! empty($business->repair_jobsheet_settings) ?
            json_decode($business->repair_jobsheet_settings, true) : [];

        $activities = Activity::forSubject($job_sheet)
            ->with(['causer', 'subject'])
            ->latest()
            ->get();


        return view('repair::job_sheet.show')
            ->with(compact('job_sheet', 'repair_settings', 'parts', 'activities', 'jobsheet_settings'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        $business_id = request()->session()->get('user.business_id');

        if (! (auth()->user()->can('superadmin') || ($this->moduleUtil->hasThePermissionInSubscription($business_id, 'repair_module') && auth()->user()->can('job_sheet.edit')))) {
            abort(403, 'Unauthorized action.');
        }

        $job_sheet = JobSheet::where('business_id', $business_id)
            ->findOrFail($id);

        $repair_statuses = RepairStatus::getRepairSatuses($business_id);
        $device_models = DeviceModel::forDropdown($business_id);
        $brands = Brands::forDropdown($business_id, false, true);
        $devices = Category::forDropdown($business_id, 'device');
        $repair_settings = $this->repairUtil->getRepairSettings($business_id);
        $types = Contact::getContactTypes();
        $customer_groups = CustomerGroup::forDropdown($business_id);
        $default_status = '';
        if (! empty($repair_settings['default_status'])) {
            $default_status = $repair_settings['default_status'];
        }

        //get service staff(technecians)
        $technecians = [];
        if ($this->commonUtil->isModuleEnabled('service_staff')) {
            $technecians = $this->commonUtil->serviceStaffDropdown($business_id);
        }

        return view('repair::job_sheet.edit')
            ->with(compact('job_sheet', 'repair_statuses', 'device_models', 'brands', 'devices', 'default_status', 'technecians', 'types', 'customer_groups', 'repair_settings'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request  $request
     * @param  int  $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        $business_id = request()->session()->get('user.business_id');

        if (! (auth()->user()->can('superadmin') || ($this->moduleUtil->hasThePermissionInSubscription($business_id, 'repair_module') && auth()->user()->can('job_sheet.edit')))) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $input = $request->only('contact_id', 'service_type', 'brand_id', 'device_id', 'device_model_id', 'security_pwd', 'security_pattern', 'serial_no', 'status_id', 'delivery_date', 'estimated_cost', 'product_configuration', 'defects', 'product_condition', 'service_staff', 'pick_up_on_site_addr', 'comment_by_ss', 'custom_field_1', 'custom_field_2', 'custom_field_3', 'custom_field_4', 'custom_field_5');

            if (! empty($input['delivery_date'])) {
                $input['delivery_date'] = $this->commonUtil->uf_date($input['delivery_date'], true);
            }

            if (! empty($input['estimated_cost'])) {
                $input['estimated_cost'] = $this->commonUtil->num_uf($input['estimated_cost']);
            }

            if (! empty($request->input('repair_checklist'))) {
                $input['checklist'] = $request->input('repair_checklist');
            } else {
                $input['checklist'] = [];
            }

            DB::beginTransaction();

            $job_sheet = JobSheet::where('business_id', $business_id)
                ->findOrFail($id);

            // ── Snapshot BEFORE update ─────────────────────────
            $job_sheet->load(['customer', 'technician', 'status', 'Brand', 'Device', 'deviceModel', 'businessLocation']);

            $old_job_sheet    = clone $job_sheet;
            $old_status       = $job_sheet->status;
            $old_serviceStaff = $job_sheet->technician;
            $old_brand        = $job_sheet->Brand;
            $old_device       = $job_sheet->Device;
            $old_deviceModel  = $job_sheet->deviceModel;

            $job_sheet->update($input);

            //upload media
            Media::uploadMedia($business_id, $job_sheet, $request, 'images');

            if (! empty($request->input('send_notification')) && in_array('sms', $request->input('send_notification'))) {
                $status = RepairStatus::where('business_id', $business_id)
                    ->find($job_sheet->status_id);
                if (! empty($status->sms_template)) {
                    $this->repairUtil->sendJobSheetUpdateSmsNotification($status->sms_template, $job_sheet);
                }
            }

            if (! empty($request->input('send_notification')) && in_array('email', $request->input('send_notification'))) {
                $status = RepairStatus::where('business_id', $business_id)
                    ->find($job_sheet->status_id);
                $notification = [
                    'subject' => $status->email_subject,
                    'body' => $status->email_body,
                ];

                //Set email configuration
                $notificationUtil = new \App\Utils\NotificationUtil();
                $notificationUtil->configureEmail();

                if (! empty($status->email_subject) && ! empty($status->email_body)) {
                    $this->repairUtil->sendJobSheetUpdateEmailNotification($notification, $job_sheet);
                }
            }

            DB::commit();

            // ── Telegram Notification ──────────────────────────
            try {
                $job_sheet->load(['customer', 'technician', 'status', 'Brand', 'Device', 'deviceModel', 'businessLocation']);

                $location_id = $job_sheet->businessLocation->location_id ?? 'PT1001';

                \App\Notifications\TelegramNotification::updateJobSheetMessage(
                    $job_sheet,
                    $old_job_sheet,
                    $job_sheet->customer,
                    $job_sheet->businessLocation,
                    $job_sheet->Brand,
                    $job_sheet->Device,
                    $job_sheet->deviceModel,
                    $job_sheet->status,
                    $old_status,
                    $job_sheet->technician,
                    $old_serviceStaff,
                    $old_brand,
                    $old_device,
                    $old_deviceModel,
                    'repair',
                    $location_id
                );
            } catch (\Exception $te) {
                \Log::warning('Telegram update job sheet notification failed: ' . $te->getMessage());
            }

            if (! empty($request->input('submit_type')) && $request->input('submit_type') == 'save_and_add_parts') {
                return redirect()
                    ->action([\Modules\Repair\Http\Controllers\JobSheetController::class, 'addParts'], [$job_sheet->id])
                    ->with('status', [
                        'success' => true,
                        'msg' => __('lang_v1.success'),
                    ]);
            } elseif (! empty($request->input('submit_type')) && $request->input('submit_type') == 'save_and_upload_docs') {
                return redirect()
                    ->action([\Modules\Repair\Http\Controllers\JobSheetController::class, 'getUploadDocs'], [$job_sheet->id])
                    ->with('status', ['success' => true, 'msg' => __('lang_v1.success')]);
            }

            return redirect()
                ->action([\Modules\Repair\Http\Controllers\JobSheetController::class, 'show'], [$job_sheet->id])
                ->with('status', [
                    'success' => true,
                    'msg' => __('lang_v1.success'),
                ]);
        } catch (\Exception $e) {
            DB::rollBack();

            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            return redirect()->back()
                ->with('status', [
                    'success' => false,
                    'msg' => __('messages.something_went_wrong'),
                ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        $business_id = request()->session()->get('user.business_id');

        if (! (auth()->user()->can('superadmin') || ($this->moduleUtil->hasThePermissionInSubscription($business_id, 'repair_module') && auth()->user()->can('job_sheet.delete')))) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            try {
                $job_sheet = JobSheet::where('business_id', $business_id)
                    ->findOrFail($id);

                // ── Load relationships BEFORE delete ───────────────
                $job_sheet->load(['customer', 'technician', 'status', 'Brand', 'Device', 'deviceModel', 'businessLocation']);

                $location_id = $job_sheet->businessLocation->location_id ?? 'PT1001';

                $job_sheet->delete();
                $job_sheet->media()->delete();

                // ── Telegram Notification ──────────────────────────
                try {
                    \App\Notifications\TelegramNotification::deleteJobSheetMessage(
                        $job_sheet,
                        'repair',
                        $location_id
                    );
                } catch (\Exception $te) {
                    \Log::warning('Telegram delete job sheet notification failed: ' . $te->getMessage());
                }

                $output = [
                    'success' => true,
                    'msg' => __('lang_v1.success'),
                ];
            } catch (\Exception $e) {
                $output = [
                    'success' => false,
                    'msg' => __('messages.something_went_wrong'),
                ];
            }

            return $output;
        }
    }

    /**
     * Show the form for editing the status
     *
     * @param  int  $id
     * @return Response
     */
    public function editStatus($id)
    {
        $business_id = request()->session()->get('user.business_id');

        if (! (auth()->user()->can('superadmin') || ($this->moduleUtil->hasThePermissionInSubscription($business_id, 'repair_module') && (auth()->user()->can('job_sheet.create') || auth()->user()->can('job_sheet.edit'))))) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $job_sheet = JobSheet::where('business_id', $business_id)->with(['status'])->findOrFail($id);

            $status_dropdown = RepairStatus::forDropdown($business_id, true);
            $status_template_tags = $this->repairUtil->getRepairStatusTemplateTags();

            return view('repair::job_sheet.partials.edit_status')
                ->with(compact('job_sheet', 'status_dropdown', 'status_template_tags'));
        }
    }

    public function view_parts($id)
    {
        $business_id = request()->session()->get('user.business_id');

        if (! (auth()->user()->can('superadmin') || auth()->user()->can('repair.view_own_part') || auth()->user()->can('repair.view_all_part'))) {
            abort(403, 'Unauthorized action.');
        }
        $job_sheet = JobSheet::findOrFail($id);
        $parts = $job_sheet->getPartsUsed();
        if (auth()->user()->can('repair.view_own_part')) {
            $parts = array_filter($parts, function ($part) {
                return auth()->user()->can('superadmin')
                    || auth()->user()->can('repair.view_all_part')
                    || (
                        auth()->user()->can('repair.view_own_part')
                        && isset($part['user_id'])
                        && auth()->id() == $part['user_id']
                    );
            });
        }



        if (request()->ajax()) {
            return view('repair::job_sheet.partials.view_parts', compact('parts', "job_sheet"));
        }
    }
    public function editPartStatus($part_key, $job_sheet_id)
    {
        $job_sheet = JobSheet::findOrFail($job_sheet_id);
        $parts = $job_sheet->getPartsUsed();

        // Direct key lookup — no ambiguity
        $part = $parts[$part_key] ?? null;

        if (!$part) {
            abort(404, 'Part not found');
        }

        if (request()->ajax()) {
            return view('repair::job_sheet.partials.edit_part_status', compact('part', 'job_sheet'));
        }
    }
    public function updatePartStatus(Request $request, $part_key, $job_sheet_id)
    {
        $job_sheet = JobSheet::findOrFail($job_sheet_id);
        $parts     = $job_sheet->parts;

        if (!isset($parts[$part_key])) {
            return response()->json([
                'success' => false,
                'msg'     => 'Part not found',
            ], 404);
        }

        $old_parts  = $job_sheet->getPartsUsed();
        $old_status = $parts[$part_key]['status'] ?? null;
        $status     = $request->input('status_id');
        $note       = $request->input('Note');
        $variation_id = $parts[$part_key]['variation_id'] ?? $part_key;

        // Find another row with the same variation_id AND same target status
        $merge_key = null;
        foreach ($parts as $key => $value) {
            if ($key === $part_key) continue; // skip current row

            $vid = $value['variation_id'] ?? $key;
            if ((string)$vid === (string)$variation_id && ($value['status'] ?? null) === $status) {
                $merge_key = $key;
                break;
            }
        }

        if ($merge_key !== null) {
            // Merge qty into the existing same-status row
            $merged_qty = (float)($parts[$merge_key]['quantity'] ?? 0)
                + (float)($parts[$part_key]['quantity'] ?? 0);

            $parts[$merge_key]['quantity'] = number_format($merged_qty, 2, '.', '');
            $parts[$merge_key]['note']     = $note;

            // Remove the current row since it's been merged
            unset($parts[$part_key]);
        } else {
            // No existing row with same status — just update
            $parts[$part_key]['status'] = $status;
            $parts[$part_key]['note']   = $note;
        }

        $job_sheet->parts = $parts;
        $job_sheet->save();

        // ── Database Notification ──────────────────────────────
        try {
            if ($old_status != $status) {
                $part_name = $parts[$merge_key ?? $part_key]['variation_name'] ?? 'Part';

                $notification_data = [
                    'subject'   => 'Part status updated for Job Sheet #' . $job_sheet->id,
                    'body'      => $part_name . ' status changed to ' . ($status ?? 'no status'),
                    'repair_id' => $job_sheet->id,
                ];

                $users = \App\User::where('business_id', $job_sheet->business_id)->get();
                foreach ($users as $user) {
                    $user->notify(new RepairStatusUpdated($notification_data));
                }
            }
        } catch (\Exception $ne) {
            \Log::warning('Part status notification failed: ' . $ne->getMessage());
        }

        $response = response()->json([
            'success' => true,
            'data'    => $job_sheet,
            'msg'     => 'Part status updated successfully',
        ]);

        // ── Telegram Notification (After Response) ──────────────────────────────

        try {
            $new_parts   = $job_sheet->getPartsUsed();
            $location_id = \App\BusinessLocation::find($job_sheet->location_id)?->location_id ?? 'PT1001';

            TelegramNotification::updatePartsStatusJobSheetMessage(
                $job_sheet,
                $old_parts,
                $new_parts,
                'repair',
                $location_id
            );
        } catch (\Exception $te) {
            \Log::warning('Telegram part status notification failed: ' . $te->getMessage());
        }


        return $response;
    }

    public function deletePart($job_sheet_id, $part_key)
    {
        $business_id = request()->session()->get('user.business_id');

        if (!auth()->user()->can('superadmin') && !auth()->user()->can('repair.delete_part')) {
            return response()->json(['success' => false, 'msg' => 'Unauthorized action.'], 403);
        }

        try {
            $job_sheet = JobSheet::where('business_id', $business_id)->findOrFail($job_sheet_id);
            $parts     = $job_sheet->parts;
            $old_parts = $job_sheet->getPartsUsed();

            if (!isset($parts[$part_key])) {
                return response()->json(['success' => false, 'msg' => 'Part not found.'], 404);
            }

            $part_name = $parts[$part_key]['variation_name'] ?? 'Part';
            unset($parts[$part_key]);

            $job_sheet->parts = !empty($parts) ? $parts : null;
            $job_sheet->save();

            // ── Database Notification ──────────────────────────────
            try {
                $notification_data = [
                    'subject'   => 'Part deleted from Job Sheet #' . $job_sheet->id,
                    'body'      => $part_name . ' has been removed from this job sheet.',
                    'repair_id' => $job_sheet->id,
                ];
                $users = \App\User::where('business_id', $business_id)->get();
                foreach ($users as $user) {
                    $user->notify(new RepairStatusUpdated($notification_data));
                }
            } catch (\Exception $ne) {
                \Log::warning('Delete part notification failed: ' . $ne->getMessage());
            }

            // ── Telegram Notification ──────────────────────────────
            try {
                $new_parts   = $job_sheet->getPartsUsed();

                $location_id = \App\BusinessLocation::find($job_sheet->location_id)?->location_id ?? 'PT1001';
                TelegramNotification::updatePartsStatusJobSheetMessage(
                    $job_sheet,
                    $old_parts,
                    $new_parts,
                    'repair',
                    $location_id,
                    '🗑️ <b>PART DELETED FROM JOB SHEET</b>'
                );
            } catch (\Exception $te) {
                \Log::warning('Telegram delete part notification failed: ' . $te->getMessage());
            }

            return response()->json(['success' => true, 'msg' => 'Part deleted successfully.']);
        } catch (\Exception $e) {
            \Log::error('Delete part error: ' . $e->getMessage());
            return response()->json(['success' => false, 'msg' => __('messages.something_went_wrong')]);
        }
    }

    public function updatePartsStatus(Request $request, $id)
    {
        $job_sheet = JobSheet::findOrFail($id);

        $old_parts = $job_sheet->getPartsUsed(); // capture before change
        $parts     = $job_sheet->parts;

        foreach ($request->parts as $variation_id => $data) {
            if (isset($parts[$variation_id])) {
                if ($parts[$variation_id]['status'] !== null && $data['status'] !== null) {
                    $parts[$variation_id]['status'] = $data['status'];
                }
                $parts[$variation_id]['note'] = $data['note'] ?? null;
            }
        }

        $job_sheet->parts = $parts;
        $job_sheet->save();

        // ── Database Notification (Parts Status Updated) ─────────────
        // ── Database Notification (Parts Status Updated - Smart) ─────────────
        try {
            $new_parts = $job_sheet->getPartsUsed();

            $changes = [];

            foreach ($new_parts as $part) {
                $variation_id = $part['variation_id'];

                $old = collect($old_parts)->firstWhere('variation_id', $variation_id);

                $old_status = $old['status'] ?? null;
                $new_status = $part['status'] ?? null;

                if ($old_status != $new_status) {
                    $changes[] = $part['variation_name'] . ' (' . ($new_status ?? 'no status') . ')';
                }
            }

            // Only send notification if something actually changed
            if (!empty($changes)) {

                $notification_data = [
                    'subject' => 'Parts updated for Job Sheet #' . $job_sheet->id,
                    'body' => 'Updated: ' . implode(', ', $changes),
                    'repair_id' => $job_sheet->id,
                ];
                $business_id = $job_sheet->business_id;

                $users = \App\User::where('business_id', $business_id)->get();

                foreach ($users as $user) {
                    $user->notify(new RepairStatusUpdated($notification_data));
                }
            }
        } catch (\Exception $ne) {
            \Log::warning('Repair parts status notification failed: ' . $ne->getMessage());
        }
        // ── Telegram Notification ──────────────────────────────
        try {
            $location_id = \App\BusinessLocation::find($job_sheet->location_id)?->location_id ?? 'PT1001';
            TelegramNotification::updatePartsStatusJobSheetMessage(
                $job_sheet,
                $old_parts,
                $job_sheet->getPartsUsed(),
                'repair',
                $location_id
            );
        } catch (\Exception $e) {
            \Log::error('Telegram job sheet parts status notification failed: ' . $e->getMessage());
        }
        // ── End Telegram Notification ──────────────────────────

        return back()->with('success', 'Parts status updated successfully');
    }


    private function updateJobsheetStatus($input, $jobsheet_id)
    {
        $job_sheet = JobSheet::where('business_id', $input['business_id'])->findOrFail($jobsheet_id);
        $job_sheet->load([
            'customer',
            'technician',
            'status',
            'Brand',
            'Device',
            'deviceModel',
            'businessLocation',
        ]);
        $contact      = $job_sheet->customer;
        $location     = $job_sheet->businessLocation;
        $brand        = $job_sheet->Brand;
        $device       = $job_sheet->Device;
        $deviceModel  = $job_sheet->deviceModel;
        $old_status       = $job_sheet->status;
        $serviceStaff = $job_sheet->technician;

        $job_sheet->status_id = $input['status_id'];
        $job_sheet->save();



        $status = RepairStatus::where('business_id', $input['business_id'])->findOrFail($input['status_id']);

        //send job sheet updates
        if (! empty($input['send_sms'])) {
            $sms_body = $input['sms_body'];
            $response = $this->repairUtil->sendJobSheetUpdateSmsNotification($sms_body, $job_sheet);
        }

        if (! empty($input['send_email'])) {
            $subject = $input['email_subject'];
            $body = $input['email_body'];
            $notification = [
                'subject' => $subject,
                'body' => $body,
            ];

            //Set email configuration
            $notificationUtil = new \App\Utils\NotificationUtil();
            $notificationUtil->configureEmail();

            if (! empty($subject) && ! empty($body)) {
                $this->repairUtil->sendJobSheetUpdateEmailNotification($notification, $job_sheet);
            }
        }
        try {
            $location_id = $location->location_id ?? 'PT1001';
            \App\Notifications\TelegramNotification::updateStatusMessage(
                $job_sheet,
                $contact,
                $location,
                $brand,
                $device,
                $deviceModel,
                $status,
                $serviceStaff,
                $old_status,
                'repair',
                $location_id
            );
        } catch (\Exception $te) {
            \Log::warning('Telegram stastus notification failed: ' . $te->getMessage());
        }
        activity()
            ->performedOn($job_sheet)
            ->withProperties(['update_note' => $input['update_note'], 'updated_status' => $status->name])
            ->log('status_changed');
    }

    public function updateStatus(Request $request, $id)
    {
        $business_id = request()->session()->get('user.business_id');

        if (! (auth()->user()->can('superadmin') || ($this->moduleUtil->hasThePermissionInSubscription($business_id, 'repair_module') && (auth()->user()->can('job_sheet.create') || auth()->user()->can('job_sheet.edit'))))) {
            abort(403, 'Unauthorized action.');
        }

        if ($request->ajax()) {
            try {
                $input = $request->only([
                    'status_id',
                    'update_note',
                ]);

                $input['business_id'] = $business_id;

                if (! empty($request->input('send_sms'))) {
                    $input['send_sms'] = true;
                    $input['sms_body'] = $request->input('sms_body');
                }
                if (! empty($request->input('send_email'))) {
                    $input['send_email'] = true;
                    $input['email_body'] = $request->input('email_body');
                    $input['email_subject'] = $request->input('email_subject');
                }
                $status_id = $request->input('status_id');

                $status = RepairStatus::find($status_id);

                if ($status->is_completed_status == 1) {
                    $input['job_sheet_id'] = $id;
                    $request->session()->put('repair_status_update_data', $input);

                    return $output = ['success' => true];
                }

                $this->updateJobsheetStatus($input, $id);

                $output = [
                    'success' => true,
                    'msg' => __('lang_v1.success'),
                ];
            } catch (Exception $e) {
                $output = [
                    'success' => false,
                    'msg' => __('messages.something_went_wrong'),
                ];
            }

            return $output;
        }
    }

    public function deleteJobSheetImage(Request $request, $id)
    {
        $business_id = request()->session()->get('user.business_id');

        if (! (auth()->user()->can('superadmin') || ($this->moduleUtil->hasThePermissionInSubscription($business_id, 'repair_module') && (auth()->user()->can('job_sheet.view_assigned') || auth()->user()->can('job_sheet.view_all') || auth()->user()->can('job_sheet.create'))))) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            try {
                Media::deleteMedia($business_id, $id);

                $output = [
                    'success' => true,
                    'msg' => __('lang_v1.success'),
                ];
            } catch (\Exception $e) {
                $output = [
                    'success' => false,
                    'msg' => __('messages.something_went_wrong'),
                ];
            }

            return $output;
        }
    }

    public function addParts($id)
    {

        $business_id = request()->session()->get('user.business_id');

        if (! (auth()->user()->can('superadmin') || ($this->moduleUtil->hasThePermissionInSubscription($business_id, 'repair_module') && (auth()->user()->can('job_sheet.create') || auth()->user()->can('job_sheet.edit'))))) {
            abort(403, 'Unauthorized action.');
        }

        $status_update_data = request()->session()->get('repair_status_update_data');

        $job_sheet = JobSheet::where('business_id', $business_id)->findOrFail($id);

        $parts = $job_sheet->getPartsUsed();

        $status_dropdown = RepairStatus::forDropdown($business_id, true);
        $status_template_tags = $this->repairUtil->getRepairStatusTemplateTags();
        $business = Business::find($business_id);
        if (!empty($business->pos_settings) && !is_array($business->pos_settings)) {
            $business->pos_settings = json_decode($business->pos_settings, true);
        }
        $allow_overselling = !empty($business->pos_settings['allow_overselling']) ? true : false;

        return view('repair::job_sheet.add_parts')
            ->with(compact('job_sheet', 'parts', 'status_update_data', 'status_dropdown', 'status_template_tags', 'allow_overselling'));
    }
    public function saveParts(Request $request, $id)
    {
        $business_id = request()->session()->get('user.business_id');

        if (! (auth()->user()->can('superadmin') || ($this->moduleUtil->hasThePermissionInSubscription($business_id, 'repair_module') && (auth()->user()->can('job_sheet.create') || auth()->user()->can('job_sheet.edit'))))) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $parts = $request->input('parts');

            $job_sheet = JobSheet::where('business_id', $business_id)->findOrFail($id);

            // ── Snapshot old parts BEFORE save ────────────────────
            $old_parts_used = $job_sheet->getPartsUsed() ?? [];

            $normalized = [];
            foreach ($parts as $key => $data) {
                $variation_id = $data['variation_id'] ?? $key;

                if (empty($data['user_id'])) {
                    $data['user_id'] = auth()->user()->id;
                }

                $normalized[$key] = array_merge($data, ['variation_id' => $variation_id]);
            }

            $job_sheet->parts = !empty($normalized) ? $normalized : null;
            $job_sheet->save();

            if (! empty($request->session()->get('repair_status_update_data')) && ! empty($request->input('status_id'))) {
                $input = $request->only([
                    'status_id',
                    'update_note',
                ]);

                $input['business_id'] = $business_id;

                if (! empty($request->input('send_sms'))) {
                    $input['send_sms'] = true;
                    $input['sms_body'] = $request->input('sms_body');
                }
                if (! empty($request->input('send_email'))) {
                    $input['send_email'] = true;
                    $input['email_body'] = $request->input('email_body');
                    $input['email_subject'] = $request->input('email_subject');
                }

                $this->updateJobsheetStatus($input, $job_sheet->id);

                $request->session()->forget('repair_status_update_data');
            }

            // ── Telegram Notification ──────────────────────────────
            try {
                $partsUsed   = $job_sheet->getPartsUsed();
                $location    = \App\BusinessLocation::find($job_sheet->location_id);
                $location_id = $location->location_id ?? 'PT1001';

                \App\Notifications\TelegramNotification::addPartsJobSheetMessage(
                    $job_sheet,
                    $partsUsed      ?? [],
                    $old_parts_used,
                    'repair',
                    $location_id
                );
            } catch (\Exception $te) {
                \Log::warning('Telegram job sheet parts notification failed: ' . $te->getMessage());
            }

            // ── Database Notification (Repair) ────────────────────
            try {
                $notification_data = [
                    'subject'   => 'Repair updated for Job Sheet #' . $job_sheet->id,
                    'body'      => 'Parts have been updated for this repair.',
                    'repair_id' => $job_sheet->id,
                ];

                $users = \App\User::where('business_id', $business_id)->get();

                foreach ($users as $user) {
                    $user->notify(new RepairStatusUpdated($notification_data));
                }
            } catch (\Exception $ne) {
                \Log::warning('Repair notification failed: ' . $ne->getMessage());
            }

            $output = [
                'success' => true,
                'msg'     => __('lang_v1.success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency(
                'File:'    . $e->getFile() .
                    'Line:'    . $e->getLine() .
                    'Message:' . $e->getMessage()
            );

            $output = [
                'success' => false,
                'msg'     => __('messages.something_went_wrong'),
            ];
        }

        return redirect()
            ->action([\Modules\Repair\Http\Controllers\JobSheetController::class, 'show'], [$job_sheet->id])
            ->with('status', $output);
    }

    public function jobsheetPartRow(Request $request)
    {
        if (request()->ajax()) {
            $variation_id    = $request->input('variation_id');
            $part_key        = $request->input('part_key');
            $business_id     = $request->session()->get('user.business_id');
            $location_id     = $request->input('location_id');
            $already_used_qty = (float) $request->input('already_used_qty', 0); // ← from JS

            $product = $this->productUtil->getDetailsFromVariation($variation_id, $business_id);

            // ── Get Business & Check Allow Overselling ──
            $business = Business::find($business_id);
            if (!empty($business->pos_settings) && !is_array($business->pos_settings)) {
                $business->pos_settings = json_decode($business->pos_settings, true);
            }
            $allow_overselling = !empty($business->pos_settings['allow_overselling']) ? true : false;

            // ── Stock Check ──────────────────────────────
            if (!empty($product->enable_stock) && $product->enable_stock == 1 && !empty($location_id)) {
                $current_stock = $this->productUtil->getCurrentStock($product->variation_id, $location_id);

                // Subtract qty already in the table
                $available_stock = $current_stock - $already_used_qty;

                // Only block if overselling is not allowed and stock is insufficient
                if ($available_stock <= 0 && !$allow_overselling) {
                    return response()->json([
                        'success' => false,
                        'msg' => __('lang_v1.item_out_of_stock') .
                            ' (Available: ' . number_format($current_stock, 2) .
                            ', Already used: ' . number_format($already_used_qty, 2) . ')',
                    ]);
                }
            }
            // ─────────────────────────────────────────────

            $variation_name = $product->product_name . ' - ' . $product->sub_sku;
            $variation_id   = $product->variation_id;
            $current_stock  = (!empty($product->enable_stock) && $product->enable_stock == 1) ? $this->productUtil->getCurrentStock($product->variation_id, $location_id) : null;
            $quantity       = 1;
            $unit           = $product->unit;
            $user_id        = auth()->user()->id;
            $product_image = !empty($product->product_image)
                ? asset('/uploads/img/' . rawurlencode($product->product_image))
                : asset('/img/default.png');
            $can_not_edit = false;
            return response()->json([
                'success' => true,
                'html'    => view('repair::job_sheet.partials.job_sheet_part_row')
                    ->with(compact('variation_name', 'variation_id', 'quantity', 'unit', 'user_id', 'part_key', 'current_stock', 'product_image', 'allow_overselling', 'can_not_edit'))
                    ->render(),
            ]);
        }
    }

    /**
     * Show the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function print($id)
    {
        $business_id = request()->session()->get('user.business_id');

        if (! (auth()->user()->can('superadmin') || ($this->moduleUtil->hasThePermissionInSubscription($business_id, 'repair_module') && (auth()->user()->can('job_sheet.view_assigned') || auth()->user()->can('job_sheet.view_all') || auth()->user()->can('job_sheet.create'))))) {
            abort(403, 'Unauthorized action.');
        }

        $query = JobSheet::with(
            'customer',
            'customer.business',
            'technician',
            'status',
            'Brand',
            'Device',
            'deviceModel',
            'businessLocation',
            'invoices',
            'media'
        )
            ->where('business_id', $business_id);

        //if user is not admin or didn't have permission `job_sheet.view_all` get only assgined/created_by job sheet
        if (! ($this->commonUtil->is_admin(auth()->user(), $business_id) || auth()->user()->can('job_sheet.view_all'))) {
            $user_id = auth()->user()->id;
            $query->where(function ($q) use ($user_id) {
                $q->where('repair_job_sheets.service_staff', $user_id)
                    ->orWhere('repair_job_sheets.created_by', $user_id);
            });
        }

        $job_sheet = $query->findOrFail($id);

        $parts = $job_sheet->getPartsUsed();

        $business = Business::find($business_id);
        $repair_settings = json_decode($business->repair_settings, true);

        $jobsheet_settings = ! empty($business->repair_jobsheet_settings) ?
            json_decode($business->repair_jobsheet_settings, true) : [];

        $html = view('repair::job_sheet.print_pdf')
            ->with(compact('job_sheet', 'repair_settings', 'parts', 'jobsheet_settings'))->render();
        $mpdf = new \Mpdf\Mpdf([
            'tempDir' => public_path('uploads/temp'),
            'mode' => 'utf-8',
            'autoScriptToLang' => true,
            'autoLangToFont' => true,
            'autoVietnamese' => true,
            'autoArabic' => true,
            'margin_top' => 8,
            'margin_bottom' => 8,
        ]);
        $mpdf->useSubstitutions = true;
        $mpdf->SetTitle(__('repair::lang.job_sheet') . ' | ' . $job_sheet->job_sheet_no);
        $mpdf->WriteHTML($html);
        $mpdf->Output('job_sheet.pdf', 'I');

        return view('repair::job_sheet.print_pdf')
            ->with(compact('job_sheet', 'repair_settings', 'parts'));
    }

    /**
     * Print label.
     *
     * @param  int  $id
     * @return Response
     */
    public function printLabel($id)
    {
        $business_id = request()->session()->get('user.business_id');

        if (! (auth()->user()->can('superadmin') || ($this->moduleUtil->hasThePermissionInSubscription($business_id, 'repair_module') && (auth()->user()->can('job_sheet.view_assigned') || auth()->user()->can('job_sheet.view_all') || auth()->user()->can('job_sheet.create'))))) {
            abort(403, 'Unauthorized action.');
        }

        $query = JobSheet::with(
            'customer',
            'customer.business',
            'technician',
            'status',
            'Brand',
            'Device',
            'deviceModel',
            'businessLocation',
            'createdBy'
        )
            ->where('business_id', $business_id);

        //if user is not admin or didn't have permission `job_sheet.view_all` get only assgined/created_by job sheet
        if (! ($this->commonUtil->is_admin(auth()->user(), $business_id) || auth()->user()->can('job_sheet.view_all'))) {
            $user_id = auth()->user()->id;
            $query->where(function ($q) use ($user_id) {
                $q->where('repair_job_sheets.service_staff', $user_id)
                    ->orWhere('repair_job_sheets.created_by', $user_id);
            });
        }

        $job_sheet = $query->findOrFail($id);

        $business = Business::find($business_id);
        $repair_settings = json_decode($business->repair_settings, true);

        $jobsheet_settings = ! empty($business->repair_jobsheet_settings) ?
            json_decode($business->repair_jobsheet_settings, true) : [];

        $label_width = isset($jobsheet_settings['label_width']) ? $jobsheet_settings['label_width'] : 75;
        $label_height = isset($jobsheet_settings['label_height']) ? $jobsheet_settings['label_height'] : 50;

        $html = view('repair::job_sheet.print_label')
            ->with(compact('job_sheet', 'repair_settings', 'jobsheet_settings'))->render();
        $mpdf = new \Mpdf\Mpdf([
            'format' => [$label_width, $label_height],
            'tempDir' => public_path('uploads/temp'),
            'mode' => 'utf-8',
            'autoScriptToLang' => true,
            'autoLangToFont' => true,
            'autoVietnamese' => true,
            'autoArabic' => true,
            'margin_top' => 4,
            'margin_left' => 4,
            'margin_right' => 4,
            'margin_bottom' => 4,
        ]);
        $mpdf->useSubstitutions = true;
        $mpdf->SetTitle(__('repair::lang.job_sheet_label') . ' | ' . $job_sheet->job_sheet_no);
        $mpdf->WriteHTML($html);
        $mpdf->Output('job_sheet_label.pdf', 'I');
    }

    public function getUploadDocs($id)
    {
        $business_id = request()->session()->get('user.business_id');

        if (! (auth()->user()->can('superadmin') || ($this->moduleUtil->hasThePermissionInSubscription($business_id, 'repair_module') && (auth()->user()->can('job_sheet.create') || auth()->user()->can('job_sheet.edit'))))) {
            abort(403, 'Unauthorized action.');
        }

        $job_sheet = JobSheet::with(['media'])
            ->where('business_id', $business_id)
            ->findOrFail($id);

        return view('repair::job_sheet.upload_doc', compact('job_sheet'));
    }

    public function postUploadDocs(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');

        if (! (auth()->user()->can('superadmin') || ($this->moduleUtil->hasThePermissionInSubscription($business_id, 'repair_module') && (auth()->user()->can('job_sheet.create') || auth()->user()->can('job_sheet.edit'))))) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $images = json_decode($request->input('images'), true);

            $job_sheet = JobSheet::where('business_id', $business_id)
                ->findOrFail($request->input('job_sheet_id'));

            if (! empty($images) && ! empty($job_sheet)) {
                Media::attachMediaToModel($job_sheet, $business_id, $images);
            }

            $output = [
                'success' => true,
                'msg' => __('lang_v1.success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return redirect()
            ->action([\Modules\Repair\Http\Controllers\JobSheetController::class, 'show'], [$job_sheet->id])
            ->with('status', [
                'success' => true,
                'msg' => __('lang_v1.success'),
            ]);
    }
}
