<?php

namespace Modules\StockCount\Http\Controllers;

use App\BusinessLocation;
use App\Category;
use App\Brands;
use App\Product;
use App\Variation;
use App\ProductRack;
use App\Utils\ProductUtil;
use App\Utils\ModuleUtil;
use App\Utils\Util;
use App\Utils\TransactionUtil;
use App\Transaction;
use App\StockAdjustmentLine;
use Modules\StockCount\Entities\StockCountSession;
use Modules\StockCount\Entities\StockCountLine;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use DB;

class StockCountController extends Controller
{
    protected $productUtil;
    protected $moduleUtil;
    protected $commonUtil;
    protected $transactionUtil;

    public function __construct(
        ProductUtil $productUtil,
        ModuleUtil $moduleUtil,
        Util $commonUtil,
        TransactionUtil $transactionUtil
    ) {
        $this->productUtil = $productUtil;
        $this->moduleUtil = $moduleUtil;
        $this->commonUtil = $commonUtil;
        $this->transactionUtil = $transactionUtil;
    }

    public function index()
    {
        $business_id = request()->session()->get('user.business_id');

        if (!auth()->user()->can('stock_count.view_all') && !auth()->user()->can('stock_count.view_own')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $sessions = StockCountSession::with(['location', 'creator'])
                ->where('business_id', $business_id)
                ->select('stock_count_sessions.*');

            if (!auth()->user()->can('stock_count.view_all') && auth()->user()->can('stock_count.view_own')) {
                $sessions->where('stock_count_sessions.created_by', auth()->user()->id);
            }

            if (!empty(request()->get('location_id'))) {
                $sessions->where('location_id', request()->get('location_id'));
            }
            if (!empty(request()->get('status'))) {
                $sessions->where('status', request()->get('status'));
            }
            if (!empty(request()->get('created_by'))) {
                $sessions->where('created_by', request()->get('created_by'));
            }
            if (!empty(request()->get('start_date')) && !empty(request()->get('end_date'))) {
                $sessions->whereDate('created_at', '>=', request()->get('start_date'))
                    ->whereDate('created_at', '<=', request()->get('end_date'));
            }

            $dt = DataTables::of($sessions)
                ->addColumn('action', function ($row) {
                    $html = '<div class="btn-group">
                                <button class="btn btn-info btn-xs dropdown-toggle" type="button" data-toggle="dropdown" aria-expanded="false">
                                    ' . __('messages.action') . '
                                    <span class="caret"></span>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-left" role="menu">';

                    if (auth()->user()->can('stock_count.view_all') || (auth()->user()->can('stock_count.view_own') && $row->created_by == auth()->user()->id)) {
                        $html .= '<li><a href="' . action([\Modules\StockCount\Http\Controllers\StockCountController::class, 'show'], [$row->id]) . '"><i class="fa fa-eye"></i> ' . __('messages.view') . '</a></li>';
                    }

                    if (in_array($row->status, ['active', 'in_progress']) && auth()->user()->can('stock_count.count')) {
                        $html .= '<li><a href="' . action([\Modules\StockCount\Http\Controllers\StockCountController::class, 'worksheet'], [$row->id]) . '"><i class="fa fa-edit"></i> Edit (worksheet)</a></li>';
                    }

                    $html .= '<li><a href="#" class="print-invoice" data-href="' . action([\Modules\StockCount\Http\Controllers\StockCountController::class, 'printWorksheet'], [$row->id]) . '"><i class="fa fa-print"></i> Print worksheet</a></li>';

                    if (auth()->user()->can('stock_count.create')) {
                        $html .= '<li><a href="' . action([\Modules\StockCount\Http\Controllers\StockCountController::class, 'duplicate'], [$row->id]) . '"><i class="fa fa-copy"></i> Duplicate</a></li>';
                    }

                    $is_admin = auth()->user()->hasRole('Admin#' . $business_id) || auth()->user()->can('superadmin');
                    $can_update_status = $is_admin || auth()->user()->can('stock_count.update_status') || auth()->user()->can('stock_count.edit') || auth()->user()->can('stock_count.create');

                    if ($can_update_status) {
                        $html .= '<li>
                                    <a data-href="' . action([\Modules\StockCount\Http\Controllers\StockCountController::class, 'updateStatus']) . '" data-session_id="' . $row->id . '" data-status="' . $row->status . '" class="btn_update_status cursor-pointer">
                                        <i class="fa fa-sync"></i> Update Status
                                    </a>
                                 </li>';
                    }

                    if (auth()->user()->can('stock_count.view_all') || (auth()->user()->can('stock_count.view_own') && $row->created_by == auth()->user()->id)) {
                        $html .= '<li>
                                    <a data-session_id="' . $row->id . '" data-session_name="' . $row->name . '" class="btn_compare_worksheet cursor-pointer">
                                        <i class="fa fa-balance-scale"></i> Compare worksheet
                                    </a>
                                 </li>';
                    }

                    if ($row->status !== 'completed' && auth()->user()->can('stock_count.delete')) {
                        $html .= '<li>
                                    <a data-href="' . action([\Modules\StockCount\Http\Controllers\StockCountController::class, 'destroy'], [$row->id]) . '" class="delete_stock_count text-danger cursor-pointer">
                                        <i class="fa fa-trash"></i> ' . __('messages.delete') . '
                                    </a>
                                 </li>';
                    }

                    $html .= '</ul></div>';
                    return $html;
                })
                ->editColumn('status', function ($row) {
                    $color = 'bg-yellow';
                    $status_name = 'Pending';

                    if ($row->status === 'completed' || $row->status === 'approved') {
                        $color = 'bg-green';
                        $status_name = 'Completed';
                    } elseif ($row->status === 'in_progress') {
                        $color = 'bg-blue';
                        $status_name = 'In Progress';
                    } elseif ($row->status === 'pending' || $row->status === 'draft' || $row->status === 'active') {
                        $color = 'bg-yellow';
                        $status_name = 'Pending';
                    } else {
                        $color = 'bg-gray';
                        $status_name = ucfirst(str_replace('_', ' ', $row->status));
                    }

                    return '<span class="label ' . $color . ' btn_update_status" style="cursor: pointer;" data-href="' . action([\Modules\StockCount\Http\Controllers\StockCountController::class, 'updateStatus']) . '" data-session_id="' . $row->id . '" data-status="' . $row->status . '">' . $status_name . '</span>';
                })
                ->addColumn('total_items', function ($row) {
                    return $row->lines()->count();
                })
                ->addColumn('items_counted', function ($row) {
                    return $row->lines()->whereNotNull('counted_by')->count();
                })
                ->addColumn('completion', function ($row) {
                    $total = $row->lines()->count();
                    $counted = $row->lines()->whereNotNull('counted_by')->count();
                    $percent = $total > 0 ? round(($counted / $total) * 100) : 0;

                    $bar_color = 'progress-bar-primary';
                    if ($percent == 100) {
                        $bar_color = 'progress-bar-success';
                    } elseif ($percent > 0) {
                        $bar_color = 'progress-bar-info';
                    }

                    $html = '<div class="progress progress-xs" style="margin-bottom: 3px; background-color: #d2d6de; height: 6px; border-radius: 3px; overflow: hidden;">
                                <div class="progress-bar ' . $bar_color . '" role="progressbar" style="width: ' . $percent . '%; height: 6px;"></div>
                             </div>
                             <small class="text-muted" style="font-size: 11px; font-weight: bold;">' . $percent . '%</small>';
                    return $html;
                })
                ->editColumn('created_at', '{{@format_datetime($created_at)}}')
                ->editColumn('blind_count', function ($row) {
                    return $row->blind_count ? __('messages.yes') : __('messages.no');
                })
                ->rawColumns(['action', 'status', 'created_at', 'completion'])
                ->make(true);

            $data = $dt->getData(true);

            // Calculate stats based on the filtered query
            $filtered_sessions_query = clone $sessions;
            $session_ids = $filtered_sessions_query->pluck('id')->toArray();

            $active_sessions = $filtered_sessions_query->clone()->whereIn('status', ['active', 'pending', 'in_progress', 'draft'])->count();
            $completed_sessions = $filtered_sessions_query->clone()->whereIn('status', ['completed', 'approved'])->count();

            $total_items = 0;
            $total_counted = 0;
            if (!empty($session_ids)) {
                $total_items = DB::table('stock_count_lines')
                    ->whereIn('stock_count_session_id', $session_ids)
                    ->count();
                $total_counted = DB::table('stock_count_lines')
                    ->whereIn('stock_count_session_id', $session_ids)
                    ->whereNotNull('counted_by')
                    ->count();
            }

            $progress_percent = $total_items > 0 ? round(($total_counted / $total_items) * 100, 1) : 0;

            $data['stats'] = [
                'active_sessions' => $active_sessions,
                'completed_sessions' => $completed_sessions,
                'total_counted' => $total_counted,
                'total_items' => $total_items,
                'progress_percent' => $progress_percent
            ];

            return response()->json($data);
        }

        $business_locations = BusinessLocation::forDropdown($business_id);
        $users = \App\User::forDropdown($business_id, false);
        return view('stockcount::index', compact('business_locations', 'users'));
    }

    public function create()
    {
        $business_id = request()->session()->get('user.business_id');

        if (!auth()->user()->can('stock_count.create')) {
            abort(403, 'Unauthorized action.');
        }

        $business_locations = BusinessLocation::forDropdown($business_id);
        $categories = Category::forDropdown($business_id, 'product');
        $brands = Brands::forDropdown($business_id);
        $products = Product::where('business_id', $business_id)
            ->where('is_inactive', 0)
            ->where('type', '!=', 'modifier')
            ->pluck('name', 'id');

        // Fetch distinct racks
        $racks = ProductRack::where('business_id', $business_id)
            ->whereNotNull('rack')
            ->distinct()
            ->pluck('rack', 'rack');

        $business = \App\Business::where('id', $business_id)->first();
        $settings = $business->common_settings ?? [];

        return view('stockcount::create', compact('business_locations', 'categories', 'brands', 'racks', 'products', 'settings'));
    }

    public function store(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');

        if (!auth()->user()->can('stock_count.create')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $business = \App\Business::where('id', $business_id)->first();
            $settings = $business->common_settings ?? [];
            $default_blind_count = isset($settings['stock_count_default_blind_count'])
                ? $settings['stock_count_default_blind_count']
                : (isset($settings['stock_count_show_expected_qty']) ? !$settings['stock_count_show_expected_qty'] : false);

            $input = $request->only(['name', 'location_id', 'reference_no']);
            $input['blind_count'] = $default_blind_count ? true : false;
            $input['business_id'] = $business_id;
            $input['created_by'] = auth()->user()->id;
            $input['status'] = 'active';

            if (empty($input['reference_no'])) {
                $count = StockCountSession::where('business_id', $business_id)->count() + 1;
                $input['reference_no'] = 'SC' . date('Y') . sprintf('%04d', $count);
            }

            $filters = $request->only(['categories', 'brands', 'racks', 'products']);
            $input['filters'] = $filters;

            DB::beginTransaction();

            $session = StockCountSession::create($input);

            // Fetch matching variations
            $query = Variation::join('products as p', 'variations.product_id', '=', 'p.id')
                ->join('product_locations as pl', 'pl.product_id', '=', 'p.id')
                ->leftJoin('variation_location_details as vld', function ($join) use ($input) {
                    $join->on('variations.id', '=', 'vld.variation_id')
                        ->where('vld.location_id', '=', $input['location_id']);
                })
                ->where('p.business_id', $business_id)
                ->where('p.is_inactive', 0)
                ->where('p.type', '!=', 'modifier')
                ->where('pl.location_id', $input['location_id'])
                ->select(
                    'variations.id as variation_id',
                    'p.id as product_id',
                    'variations.default_purchase_price',
                    'vld.qty_available'
                );

            if (!empty($filters['categories'])) {
                $query->whereIn('p.category_id', $filters['categories']);
            }
            if (!empty($filters['brands'])) {
                $query->whereIn('p.brand_id', $filters['brands']);
            }
            if (!empty($filters['racks'])) {
                $query->join('product_racks as pr', 'pr.product_id', '=', 'p.id')
                    ->where('pr.location_id', $input['location_id'])
                    ->whereIn('pr.rack', $filters['racks']);
            }
            if (!empty($filters['products'])) {
                $query->whereIn('p.id', $filters['products']);
            }

            $business = \App\Business::where('id', $business_id)->first();
            $settings = $business->common_settings ?? [];
            $skip_zero = isset($settings['stock_count_skip_zero_stock']) ? $settings['stock_count_skip_zero_stock'] : false;
            
            if ($skip_zero) {
                $query->whereNotNull('vld.qty_available')
                    ->where('vld.qty_available', '!=', 0);
            }

            $variations = $query->get();

            $lines = [];
            foreach ($variations as $var) {
                $lines[] = [
                    'stock_count_session_id' => $session->id,
                    'product_id' => $var->product_id,
                    'variation_id' => $var->variation_id,
                    'book_quantity' => $var->qty_available ?? 0.0000,
                    'counted_quantity' => 0.0000,
                    'unit_price' => $var->default_purchase_price ?? 0.0000,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ];
            }

            if (!empty($lines)) {
                StockCountLine::insert($lines);
            }

            DB::commit();

            return redirect()
                ->action([\Modules\StockCount\Http\Controllers\StockCountController::class, 'index'])
                ->with('status', [
                    'success' => true,
                    'msg' => __('lang_v1.success')
                ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            return redirect()->back()
                ->with('status', [
                    'success' => false,
                    'msg' => __('messages.something_went_wrong')
                ]);
        }
    }

    public function show($id)
    {
        $business_id = request()->session()->get('user.business_id');

        $can_view_all = auth()->user()->can('stock_count.view_all');
        $can_view_own = auth()->user()->can('stock_count.view_own');

        if (!$can_view_all && !$can_view_own) {
            abort(403, 'Unauthorized action.');
        }

        $session = StockCountSession::with(['location', 'creator', 'completer'])
            ->where('business_id', $business_id)
            ->when(!$can_view_all && $can_view_own, function ($q) {
                return $q->where('created_by', auth()->user()->id);
            })
            ->findOrFail($id);

        $query = StockCountLine::with(['product', 'variation', 'counter'])
            ->where('stock_count_session_id', $id)
            ->whereNotNull('counted_by');

        // Apply filters
        if (!empty(request()->get('category_id'))) {
            $query->whereHas('product', function ($q) {
                $q->where('category_id', request()->get('category_id'));
            });
        }

        if (!empty(request()->get('brand_id'))) {
            $query->whereHas('product', function ($q) {
                $q->where('brand_id', request()->get('brand_id'));
            });
        }

        $variance_type = request()->get('variance_type');
        if (!empty($variance_type) && $variance_type != 'all') {
            if ($variance_type == 'shortage') {
                $query->whereColumn('counted_quantity', '<', 'book_quantity');
            } elseif ($variance_type == 'surplus') {
                $query->whereColumn('counted_quantity', '>', 'book_quantity');
            } elseif ($variance_type == 'no_variance') {
                $query->whereColumn('counted_quantity', '=', 'book_quantity');
            } elseif ($variance_type == 'variance') {
                $query->whereColumn('counted_quantity', '!=', 'book_quantity');
            }
        }

        $lines = $query->get();

        $summary = [
            'total_items' => count($lines),
            'shortage_qty' => 0,
            'shortage_value' => 0,
            'surplus_qty' => 0,
            'surplus_value' => 0,
            'exact_qty' => 0,
        ];

        foreach ($lines as $line) {
            $diff = $line->counted_quantity - $line->book_quantity;
            if ($diff < 0) {
                $summary['shortage_qty'] += abs($diff);
                $summary['shortage_value'] += abs($diff) * $line->unit_price;
            } elseif ($diff > 0) {
                $summary['surplus_qty'] += $diff;
                $summary['surplus_value'] += $diff * $line->unit_price;
            } else {
                $summary['exact_qty']++;
            }
        }

        $categories = Category::forDropdown($business_id, 'product');
        $brands = Brands::forDropdown($business_id);

        return view('stockcount::show', compact('session', 'lines', 'summary', 'categories', 'brands'));
    }

    public function worksheet($id)
    {
        $business_id = request()->session()->get('user.business_id');

        if (!auth()->user()->can('stock_count.count')) {
            abort(403, 'Unauthorized action.');
        }

        $session = StockCountSession::where('business_id', $business_id)
            ->findOrFail($id);

        if (!in_array($session->status, ['active', 'pending', 'draft', 'in_progress'])) {
            return redirect()->action([\Modules\StockCount\Http\Controllers\StockCountController::class, 'show'], [$id]);
        }

        // Auto transition status to 'in_progress' when user opens/starts counting on worksheet
        if (in_array($session->status, ['active', 'pending', 'draft'])) {
            $session->status = 'in_progress';
            $session->save();
        }

        $lines = StockCountLine::with(['product', 'variation'])
            ->where('stock_count_session_id', $id)
            ->get();

        return view('stockcount::worksheet', compact('session', 'lines'));
    }

    public function saveWorksheetProgress(Request $request, $id)
    {
        $business_id = $request->session()->get('user.business_id');

        if (!auth()->user()->can('stock_count.count')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        try {
            DB::beginTransaction();

            $line_id = $request->input('line_id');
            $is_pending = $request->input('is_pending', false);

            if ($is_pending && !empty($line_id)) {
                $line = StockCountLine::whereHas('session', function ($query) use ($business_id, $id) {
                    $query->where('id', $id)->where('business_id', $business_id)->whereIn('status', ['active', 'in_progress']);
                })->find($line_id);

                if (!empty($line)) {
                    $line->counted_quantity = 0;
                    $line->counted_by = null;
                    $line->counted_at = null;
                    $line->save();
                }
                DB::commit();
                return response()->json(['success' => true]);
            }

            $lines = $request->input('lines');
            if (!empty($lines) && is_array($lines)) {
                foreach ($lines as $line_data) {
                    $line = StockCountLine::whereHas('session', function ($query) use ($business_id, $id) {
                        $query->where('id', $id)->where('business_id', $business_id)->whereIn('status', ['active', 'in_progress']);
                    })->find($line_data['line_id']);

                    if (!empty($line)) {
                        if (!empty($line_data['is_pending'])) {
                            $line->counted_quantity = 0;
                            $line->counted_by = null;
                            $line->counted_at = null;
                        } else {
                            $line->counted_quantity = $line_data['quantity'] ?? 0;
                            $line->note = $line_data['note'] ?? '';
                            $line->counted_by = auth()->user()->id;
                            $line->counted_at = Carbon::now();
                        }
                        $line->save();
                    }
                }
            } else {
                $quantity = $request->input('quantity', 0);
                $note = $request->input('note', '');

                $line = StockCountLine::whereHas('session', function ($query) use ($business_id, $id) {
                    $query->where('id', $id)->where('business_id', $business_id)->whereIn('status', ['active', 'in_progress']);
                })->findOrFail($line_id);

                $line->counted_quantity = $quantity;
                $line->note = $note;
                $line->counted_by = auth()->user()->id;
                $line->counted_at = Carbon::now();
                $line->save();
            }

            DB::commit();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function scanBarcode(Request $request, $id)
    {
        $business_id = $request->session()->get('user.business_id');

        if (!auth()->user()->can('stock_count.scan_barcode')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        try {
            $barcode = trim($request->input('barcode'));

            $session = StockCountSession::where('business_id', $business_id)
                ->whereIn('status', ['active', 'in_progress'])
                ->findOrFail($id);

            // Find variation matching barcode (SKU or Sub-SKU)
            $variation = Variation::join('products as p', 'variations.product_id', '=', 'p.id')
                ->where('p.business_id', $business_id)
                ->where(function ($q) use ($barcode) {
                    $q->where('variations.sub_sku', $barcode)
                        ->orWhere('p.sku', $barcode);
                })
                ->select('variations.*')
                ->first();

            if (empty($variation)) {
                return response()->json(['success' => false, 'message' => 'Product not found'], 404);
            }

            // Check if there is an existing line for this variation in session
            $line = StockCountLine::where('stock_count_session_id', $id)
                ->where('variation_id', $variation->id)
                ->first();

            if (!empty($line)) {
                $line->counted_quantity += 1;
                $line->counted_by = auth()->user()->id;
                $line->counted_at = Carbon::now();
                $line->save();

                return response()->json([
                    'success' => true,
                    'line_id' => $line->id,
                    'new_qty' => (float)$line->counted_quantity
                ]);
            } else {
                // If the variation is not in the worksheet, let's append it
                // First get live stock of this variation at session location
                $qty = DB::table('variation_location_details')
                    ->where('variation_id', $variation->id)
                    ->where('location_id', $session->location_id)
                    ->value('qty_available') ?? 0.0000;

                $line = StockCountLine::create([
                    'stock_count_session_id' => $session->id,
                    'product_id' => $variation->product_id,
                    'variation_id' => $variation->id,
                    'book_quantity' => $qty,
                    'counted_quantity' => 1.0000,
                    'unit_price' => $variation->default_purchase_price ?? 0.0000,
                    'counted_by' => auth()->user()->id,
                    'counted_at' => Carbon::now()
                ]);

                // Render single line row to append to HTML table
                $row_html = view('stockcount::partials.worksheet_row', [
                    'line' => $line->load(['product', 'variation']),
                    'session' => $session
                ])->render();

                return response()->json([
                    'success' => true,
                    'appended' => true,
                    'row_html' => $row_html,
                    'line_id' => $line->id,
                    'new_qty' => 1
                ]);
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function reconcile(Request $request, $id)
    {
        $business_id = $request->session()->get('user.business_id');

        if (!auth()->user()->can('stock_count.reconcile')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $session = StockCountSession::where('business_id', $business_id)
                ->whereIn('status', ['active', 'in_progress'])
                ->findOrFail($id);

            $lines = StockCountLine::where('stock_count_session_id', $id)->get();

            DB::beginTransaction();

            $shortage_items = [];
            $total_shortage_value = 0;

            foreach ($lines as $line) {
                $qty_difference = $line->counted_quantity - $line->book_quantity;

                if ($qty_difference != 0) {
                    // Update current stock to counted quantity
                    $this->productUtil->updateProductQuantity(
                        $session->location_id,
                        $line->product_id,
                        $line->variation_id,
                        $line->counted_quantity,
                        $line->book_quantity,
                        null,
                        false
                    );

                    if ($qty_difference < 0) {
                        // Shortage: Add to standard stock adjustment list
                        $shortage_items[] = [
                            'product_id' => $line->product_id,
                            'variation_id' => $line->variation_id,
                            'quantity' => abs($qty_difference),
                            'unit_price' => $line->unit_price,
                        ];
                        $total_shortage_value += abs($qty_difference) * $line->unit_price;
                    }
                }
            }

            // If there are shortages, create a stock adjustment transaction
            if (!empty($shortage_items)) {
                $transaction_data = [
                    'type' => 'stock_adjustment',
                    'business_id' => $business_id,
                    'location_id' => $session->location_id,
                    'transaction_date' => Carbon::now()->toDateTimeString(),
                    'adjustment_type' => 'normal',
                    'additional_notes' => 'Reconciliation for count session: ' . $session->name,
                    'final_total' => $total_shortage_value,
                    'created_by' => auth()->user()->id,
                ];

                $ref_count = $this->productUtil->setAndGetReferenceCount('stock_adjustment');
                $transaction_data['ref_no'] = $this->productUtil->generateReferenceNumber('stock_adjustment', $ref_count);

                $stock_adjustment = Transaction::create($transaction_data);

                foreach ($shortage_items as $item) {
                    StockAdjustmentLine::create([
                        'transaction_id' => $stock_adjustment->id,
                        'product_id' => $item['product_id'],
                        'variation_id' => $item['variation_id'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                    ]);
                }

                $this->transactionUtil->activityLog($stock_adjustment, 'added', null, [], false);
            }

            // Complete the session
            $session->status = 'completed';
            $session->completed_by = auth()->user()->id;
            $session->completed_at = Carbon::now();
            $session->save();

            DB::commit();

            return redirect()
                ->action([\Modules\StockCount\Http\Controllers\StockCountController::class, 'show'], [$id])
                ->with('status', [
                    'success' => true,
                    'msg' => __('stockcount::lang.reconciled_successfully')
                ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            return redirect()->back()
                ->with('status', [
                    'success' => false,
                    'msg' => __('messages.something_went_wrong')
                ]);
        }
    }

    public function destroy($id)
    {
        $business_id = request()->session()->get('user.business_id');

        if (!auth()->user()->can('stock_count.delete')) {
            return response()->json([
                'success' => false,
                'msg' => __('messages.unauthorized')
            ], 403);
        }

        try {
            $session = StockCountSession::where('business_id', $business_id)
                ->where('status', '!=', 'completed')
                ->findOrFail($id);

            $session->delete();

            return response()->json([
                'success' => true,
                'msg' => __('lang_v1.success')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'msg' => __('messages.something_went_wrong')
            ], 500);
        }
    }

    public function export($id)
    {
        $business_id = request()->session()->get('user.business_id');

        if (!auth()->user()->can('stock_count.export')) {
            abort(403, 'Unauthorized action.');
        }

        $session = StockCountSession::where('business_id', $business_id)->findOrFail($id);

        if (ob_get_level() > 0) {
            ob_end_clean();
        }

        return \Excel::download(
            new \Modules\StockCount\Exports\StockCountExport($session->id),
            'stock_count_variance_report_' . $session->id . '.xlsx'
        );
    }

    public function downloadTemplate($id)
    {
        $business_id = request()->session()->get('user.business_id');

        if (!auth()->user()->can('stock_count.count')) {
            abort(403, 'Unauthorized action.');
        }

        $session = StockCountSession::where('business_id', $business_id)->findOrFail($id);

        if (ob_get_level() > 0) {
            ob_end_clean();
        }

        return \Excel::download(
            new \Modules\StockCount\Exports\StockCountTemplateExport($session->id),
            'stock_count_template_' . $session->id . '.xlsx'
        );
    }

    public function importExcel(Request $request, $id)
    {
        $business_id = $request->session()->get('user.business_id');

        if (!auth()->user()->can('stock_count.count')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $session = StockCountSession::where('business_id', $business_id)
                ->where('status', 'active')
                ->findOrFail($id);

            if (!$request->hasFile('file')) {
                $output = [
                    'success' => false,
                    'msg' => 'Please select an Excel or CSV file to upload.'
                ];
                return redirect()->back()->with('status', $output);
            }

            $file = $request->file('file');
            $parsed_array = \Excel::toArray([], $file);

            if (empty($parsed_array) || empty($parsed_array[0])) {
                $output = [
                    'success' => false,
                    'msg' => 'The uploaded file is empty.'
                ];
                return redirect()->back()->with('status', $output);
            }

            $rows = $parsed_array[0];
            $headers = array_map('strtolower', array_map('trim', $rows[0]));

            // Search for column indexes
            $sku_idx = -1;
            $qty_idx = -1;
            $note_idx = -1;

            foreach ($headers as $idx => $header) {
                if (in_array($header, ['sku', 'variation sku', 'product sku'])) {
                    $sku_idx = $idx;
                } elseif (in_array($header, ['counted qty', 'counted quantity', 'quantity', 'qty'])) {
                    $qty_idx = $idx;
                } elseif (in_array($header, ['note', 'notes', 'comment'])) {
                    $note_idx = $idx;
                }
            }

            // Fallback to column index offsets if headers are not found or matched
            if ($sku_idx === -1) {
                // If it looks like the exported variance report format:
                // Column 2 (0-indexed) is SKU
                if (count($headers) > 2) {
                    $sku_idx = 2;
                } else {
                    $sku_idx = 0; // Default first column
                }
            }
            if ($qty_idx === -1) {
                if (count($headers) > 4) {
                    $qty_idx = 4; // Exported sheet: counted qty is 5th column
                } else {
                    $qty_idx = 1; // Default second column
                }
            }

            $success_count = 0;
            $failed_count = 0;

            \DB::beginTransaction();

            // Iterate starting from row 1 (skipping headers)
            for ($i = 1; $i < count($rows); $i++) {
                $row = $rows[$i];
                if (!isset($row[$sku_idx])) {
                    continue;
                }

                $sku = trim($row[$sku_idx]);
                if (empty($sku)) {
                    continue;
                }

                $qty = isset($row[$qty_idx]) ? floatval($row[$qty_idx]) : 0;
                $note = $note_idx !== -1 && isset($row[$note_idx]) ? trim($row[$note_idx]) : '';

                // Find variation
                $variation = Variation::join('products as p', 'variations.product_id', '=', 'p.id')
                    ->where('p.business_id', $business_id)
                    ->where(function ($q) use ($sku) {
                        $q->where('variations.sub_sku', $sku)
                            ->orWhere('p.sku', $sku);
                    })
                    ->select('variations.*')
                    ->first();

                if (empty($variation)) {
                    $failed_count++;
                    continue;
                }

                // Check if variation line already exists in the session
                $line = StockCountLine::where('stock_count_session_id', $session->id)
                    ->where('variation_id', $variation->id)
                    ->first();

                if (!empty($line)) {
                    $line->counted_quantity = $qty;
                    $line->counted_by = auth()->user()->id;
                    $line->counted_at = Carbon::now();
                    if ($note_idx !== -1) {
                        $line->note = $note;
                    }
                    $line->save();
                } else {
                    // Create line
                    $book_qty = \DB::table('variation_location_details')
                        ->where('variation_id', $variation->id)
                        ->where('location_id', $session->location_id)
                        ->value('qty_available') ?? 0.0000;

                    StockCountLine::create([
                        'stock_count_session_id' => $session->id,
                        'product_id' => $variation->product_id,
                        'variation_id' => $variation->id,
                        'book_quantity' => $book_qty,
                        'counted_quantity' => $qty,
                        'unit_price' => $variation->default_purchase_price ?? 0.0000,
                        'counted_by' => auth()->user()->id,
                        'counted_at' => Carbon::now(),
                        'note' => $note
                    ]);
                }

                $success_count++;
            }

            \DB::commit();

            $msg = "Excel imported successfully! $success_count items updated.";
            if ($failed_count > 0) {
                $msg .= " $failed_count items skipped (SKUs not found in system).";
            }

            $output = [
                'success' => true,
                'msg' => $msg
            ];

            return redirect()->back()->with('status', $output);
        } catch (\Exception $e) {
            \DB::rollBack();
            $output = [
                'success' => false,
                'msg' => 'Error: ' . $e->getMessage()
            ];
            return redirect()->back()->with('status', $output);
        }
    }

    public function printWorksheet($id)
    {
        $business_id = request()->session()->get('user.business_id');

        $can_view_all = auth()->user()->can('stock_count.view_all');
        $can_view_own = auth()->user()->can('stock_count.view_own');

        if (!$can_view_all && !$can_view_own) {
            abort(403, 'Unauthorized action.');
        }

        $session = StockCountSession::with(['location', 'creator'])
            ->where('business_id', $business_id)
            ->when(!$can_view_all && $can_view_own, function ($q) {
                return $q->where('created_by', auth()->user()->id);
            })
            ->findOrFail($id);

        $lines = StockCountLine::with(['product', 'variation'])
            ->where('stock_count_session_id', $id)
            ->get();

        if (request()->ajax()) {
            $html_content = view('stockcount::print_worksheet', compact('session', 'lines'))->render();
            return [
                'success' => 1,
                'receipt' => [
                    'html_content' => $html_content
                ],
                'print_title' => 'Stock Count Worksheet - ' . $session->name
            ];
        }

        return view('stockcount::print_worksheet', compact('session', 'lines'));
    }

    public function duplicate($id)
    {
        $business_id = request()->session()->get('user.business_id');

        if (!auth()->user()->can('stock_count.create')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $session = StockCountSession::where('business_id', $business_id)
                ->findOrFail($id);

            DB::beginTransaction();

            // Create duplicated session
            $count = StockCountSession::where('business_id', $business_id)->count() + 1;
            $new_session = $session->replicate();
            $new_session->name = $session->name . ' - Copy';
            $new_session->reference_no = 'SC' . date('Y') . sprintf('%04d', $count);
            $new_session->status = 'active';
            $new_session->created_by = auth()->user()->id;
            $new_session->completed_by = null;
            $new_session->completed_at = null;
            $new_session->save();

            // Duplicate lines
            $lines = StockCountLine::where('stock_count_session_id', $id)->get();
            $new_lines = [];
            foreach ($lines as $line) {
                // Fetch current QOH for the location to start fresh
                $qty = DB::table('variation_location_details')
                    ->where('variation_id', $line->variation_id)
                    ->where('location_id', $new_session->location_id)
                    ->value('qty_available') ?? 0.0000;

                $new_lines[] = [
                    'stock_count_session_id' => $new_session->id,
                    'product_id' => $line->product_id,
                    'variation_id' => $line->variation_id,
                    'book_quantity' => $qty,
                    'counted_quantity' => 0.0000,
                    'unit_price' => $line->unit_price,
                    'counted_by' => null,
                    'counted_at' => null,
                    'note' => null,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ];
            }

            if (!empty($new_lines)) {
                StockCountLine::insert($new_lines);
            }

            DB::commit();

            return redirect()
                ->action([\Modules\StockCount\Http\Controllers\StockCountController::class, 'index'])
                ->with('status', [
                    'success' => true,
                    'msg' => 'Count session duplicated successfully.'
                ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            return redirect()->back()
                ->with('status', [
                    'success' => false,
                    'msg' => __('messages.something_went_wrong')
                ]);
        }
    }

    public function updateStatus(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#' . $business_id) || auth()->user()->can('superadmin');
        $can_update_status = $is_admin || auth()->user()->can('stock_count.update_status') || auth()->user()->can('stock_count.edit') || auth()->user()->can('stock_count.create');

        if (!$can_update_status) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $session_id = $request->input('session_id');
            $status = $request->input('status');

            $session = StockCountSession::where('business_id', $business_id)
                ->findOrFail($session_id);

            // Prevent reverting status if it has already been reconciled/completed
            if (!empty($session->completed_at) && in_array($status, ['draft', 'active', 'in_progress', 'cancelled'])) {
                $output = [
                    'success' => false,
                    'msg' => 'Cannot revert status after stock has been reconciled.'
                ];
                if ($request->ajax()) {
                    return response()->json($output);
                }
                return redirect()->back()->with('status', $output);
            }

            // Fetch business settings
            $business = \App\Business::where('id', $business_id)->first();
            $settings = $business->common_settings ?? [];
            $auto_adjust = isset($settings['stock_count_auto_adjust_stock']) ? $settings['stock_count_auto_adjust_stock'] : false;
            $require_approval = isset($settings['stock_count_require_approval']) ? $settings['stock_count_require_approval'] : true;

            // Determine if we should perform inventory reconciliation / stock adjustments
            $should_reconcile = false;
            $should_lock = false;

            if (empty($session->completed_at)) {
                if ($require_approval) {
                    if ($status === 'approved') {
                        $should_lock = true;
                        $should_reconcile = $auto_adjust;
                    }
                } else {
                    if ($status === 'completed' || $status === 'approved') {
                        $should_lock = true;
                        $should_reconcile = $auto_adjust;
                    }
                }
            }

            if ($should_lock) {
                DB::beginTransaction();

                if ($should_reconcile) {
                    $lines = StockCountLine::where('stock_count_session_id', $session->id)->get();
                    $shortage_items = [];
                    $total_shortage_value = 0;

                    foreach ($lines as $line) {
                        if ($line->counted_quantity === null) {
                            continue;
                        }

                        $qty_difference = (float)$line->counted_quantity - (float)$line->book_quantity;

                        if ($qty_difference != 0) {
                            // Update product quantity in database
                            $this->productUtil->updateProductQuantity(
                                $session->location_id,
                                $line->product_id,
                                $line->variation_id,
                                (float)$line->counted_quantity,
                                (float)$line->book_quantity,
                                null,
                                false
                            );

                            if ($qty_difference < 0) {
                                // Add shortages to stock adjustment collection
                                $shortage_items[] = [
                                    'product_id' => $line->product_id,
                                    'variation_id' => $line->variation_id,
                                    'quantity' => abs($qty_difference),
                                    'unit_price' => $line->unit_price,
                                ];
                                $total_shortage_value += abs($qty_difference) * $line->unit_price;
                            }
                        }
                    }

                    // Create stock adjustment transaction if shortages exist
                    if (!empty($shortage_items)) {
                        $transaction_data = [
                            'type' => 'stock_adjustment',
                            'business_id' => $business_id,
                            'location_id' => $session->location_id,
                            'transaction_date' => Carbon::now()->toDateTimeString(),
                            'adjustment_type' => 'normal',
                            'additional_notes' => 'Reconciliation for count session: ' . $session->name,
                            'final_total' => $total_shortage_value,
                            'created_by' => auth()->user()->id,
                        ];

                        $ref_count = $this->productUtil->setAndGetReferenceCount('stock_adjustment');
                        $transaction_data['ref_no'] = $this->productUtil->generateReferenceNumber('stock_adjustment', $ref_count);

                        $stock_adjustment = Transaction::create($transaction_data);

                        foreach ($shortage_items as $item) {
                            StockAdjustmentLine::create([
                                'transaction_id' => $stock_adjustment->id,
                                'product_id' => $item['product_id'],
                                'variation_id' => $item['variation_id'],
                                'quantity' => $item['quantity'],
                                'unit_price' => $item['unit_price'],
                            ]);
                        }

                    $this->transactionUtil->activityLog($stock_adjustment, 'added', null, [], false);
                    }
                }

                DB::commit();
            }

            // If status is set to completed or approved, fill uncounted lines so completion reaches 100%
            if ($status === 'completed' || $status === 'approved') {
                StockCountLine::where('stock_count_session_id', $session->id)
                    ->whereNull('counted_by')
                    ->update([
                        'counted_quantity' => DB::raw('COALESCE(counted_quantity, book_quantity)'),
                        'counted_by' => auth()->user()->id,
                        'counted_at' => Carbon::now()
                    ]);

                if (empty($session->completed_at)) {
                    $session->completed_by = auth()->user()->id;
                    $session->completed_at = Carbon::now();
                }
            } elseif (in_array($status, ['pending', 'in_progress', 'draft'])) {
                $session->completed_by = null;
                $session->completed_at = null;
            }

            $session->status = $status;
            $session->save();

            $output = [
                'success' => true,
                'msg' => 'Status updated successfully.'
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            $output = [
                'success' => false,
                'msg' => 'Error: ' . $e->getMessage()
            ];
        }

        if ($request->ajax()) {
            return response()->json($output);
        }

        return redirect()->back()->with('status', $output);
    }

    public function getSettings()
    {
        $business_id = request()->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#' . $business_id) || auth()->user()->can('superadmin');

        $can_access = $is_admin
            || auth()->user()->can('stock_count.settings')
            || auth()->user()->can('stock_count.settings_auto_adjust')
            || auth()->user()->can('stock_count.settings_approval')
            || auth()->user()->can('stock_count.settings_counting')
            || auth()->user()->can('stock_count.settings_notifications');

        if (!$can_access) {
            abort(403, 'Unauthorized action.');
        }

        $business = \App\Business::where('id', $business_id)->first();
        
        $settings = $business->common_settings ?? [];

        return view('stockcount::settings', compact('settings'));
    }

    public function postSettings(\Illuminate\Http\Request $request)
    {
        $business_id = request()->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#' . $business_id) || auth()->user()->can('superadmin');

        $can_save = $is_admin
            || auth()->user()->can('stock_count.settings_auto_adjust')
            || auth()->user()->can('stock_count.settings_approval')
            || auth()->user()->can('stock_count.settings_counting')
            || auth()->user()->can('stock_count.settings_notifications');

        if (!$can_save) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $business = \App\Business::where('id', $business_id)->first();
            
            $common_settings = $business->common_settings ?? [];
            
            if ($is_admin || auth()->user()->can('stock_count.settings_auto_adjust')) {
                $common_settings['stock_count_auto_adjust_stock'] = $request->has('stock_count_auto_adjust_stock');
            }

            if ($is_admin || auth()->user()->can('stock_count.settings_approval')) {
                $common_settings['stock_count_require_approval'] = $request->has('stock_count_require_approval');
                $common_settings['stock_count_lock_after_approval'] = $request->has('stock_count_lock_after_approval');
            }

            if ($is_admin || auth()->user()->can('stock_count.settings_counting')) {
                $common_settings['stock_count_allow_recount'] = $request->has('stock_count_allow_recount');
                $common_settings['stock_count_show_expected_qty'] = $request->has('stock_count_show_expected_qty');
                $common_settings['stock_count_default_blind_count'] = $request->has('stock_count_default_blind_count');
                $common_settings['stock_count_default_count_type'] = $request->input('stock_count_default_count_type', 'full_count');
                $common_settings['stock_count_skip_zero_stock'] = $request->has('stock_count_skip_zero_stock');
            }

            if ($is_admin || auth()->user()->can('stock_count.settings_notifications')) {
                $common_settings['stock_count_notify_on_completion'] = $request->has('stock_count_notify_on_completion');
                $common_settings['stock_count_notify_on_large_discrepancies'] = $request->has('stock_count_notify_on_large_discrepancies');
                $common_settings['stock_count_discrepancy_threshold'] = (float)$request->input('stock_count_discrepancy_threshold', 0);
            }
            
            $business->common_settings = $common_settings;
            $business->save();
            
            // Update session
            request()->session()->put('business.common_settings', $common_settings);

            $output = [
                'success' => true,
                'msg' => 'Settings saved successfully.'
            ];
        } catch (\Exception $e) {
            $output = [
                'success' => false,
                'msg' => 'Error: ' . $e->getMessage()
            ];
        }

        return redirect()->route('stock-counts.index')->with('status', $output);
    }

    public function compare(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');

        $can_view_all = auth()->user()->can('stock_count.view_all');
        $can_view_own = auth()->user()->can('stock_count.view_own');

        if (!$can_view_all && !$can_view_own) {
            abort(403, 'Unauthorized action.');
        }

        $session_1_id = $request->get('session_1');
        $session_2_id = $request->get('session_2');

        if (empty($session_1_id) || empty($session_2_id)) {
            $output = [
                'success' => false,
                'msg' => 'Please select two sessions to compare.'
            ];
            return redirect()->route('stock-counts.index')->with('status', $output);
        }

        $session_1 = StockCountSession::with(['location', 'creator'])
            ->where('business_id', $business_id)
            ->when(!$can_view_all && $can_view_own, function ($q) {
                return $q->where('created_by', auth()->user()->id);
            })
            ->findOrFail($session_1_id);

        $session_2 = StockCountSession::with(['location', 'creator'])
            ->where('business_id', $business_id)
            ->when(!$can_view_all && $can_view_own, function ($q) {
                return $q->where('created_by', auth()->user()->id);
            })
            ->findOrFail($session_2_id);

        // Load lines keying by variation_id for direct alignment
        $lines_1 = StockCountLine::with(['product', 'variation'])
            ->where('stock_count_session_id', $session_1->id)
            ->get()
            ->keyBy('variation_id');

        $lines_2 = StockCountLine::with(['product', 'variation'])
            ->where('stock_count_session_id', $session_2->id)
            ->get()
            ->keyBy('variation_id');

        // Union of all variation IDs
        $variation_ids = $lines_1->keys()->merge($lines_2->keys())->unique();

        $comparison = [];
        foreach ($variation_ids as $var_id) {
            $line_1 = $lines_1->get($var_id);
            $line_2 = $lines_2->get($var_id);

            // Fetch info from whichever is present
            $product_name = '';
            $sub_sku = '';
            $unit = '';
            $unit_price = 0;

            if (!empty($line_1)) {
                $product_name = ($line_1->product->name ?? '') . (!empty($line_1->variation->name) && $line_1->variation->name !== 'DUMMY' ? ' (' . $line_1->variation->name . ')' : '');
                $sub_sku = $line_1->variation->sub_sku ?? '';
                $unit = $line_1->product->unit->short_name ?? '';
                $unit_price = $line_1->unit_price;
            } elseif (!empty($line_2)) {
                $product_name = ($line_2->product->name ?? '') . (!empty($line_2->variation->name) && $line_2->variation->name !== 'DUMMY' ? ' (' . $line_2->variation->name . ')' : '');
                $sub_sku = $line_2->variation->sub_sku ?? '';
                $unit = $line_2->product->unit->short_name ?? '';
                $unit_price = $line_2->unit_price;
            }

            $qty_1 = !empty($line_1) ? (float)$line_1->counted_quantity : 0.0;
            $qty_2 = !empty($line_2) ? (float)$line_2->counted_quantity : 0.0;
            $diff = $qty_1 - $qty_2;

            $comparison[] = [
                'product_name' => $product_name,
                'sub_sku' => $sub_sku,
                'unit' => $unit,
                'unit_price' => $unit_price,
                'qty_1' => $qty_1,
                'qty_2' => $qty_2,
                'diff' => $diff
            ];
        }

        return view('stockcount::compare', compact('session_1', 'session_2', 'comparison'));
    }

    public function getAllSessionsJson(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        $exclude_id = $request->get('exclude_id');

        $query = StockCountSession::where('business_id', $business_id);
        if (!empty($exclude_id)) {
            $query->where('id', '!=', $exclude_id);
        }

        $sessions = $query->select('id', 'name', 'reference_no')->get();

        return response()->json($sessions);
    }

    public function getFilteredData(Request $request, $id)
    {
        $business_id = $request->session()->get('user.business_id');

        $can_view_all = auth()->user()->can('stock_count.view_all');
        $can_view_own = auth()->user()->can('stock_count.view_own');

        if (!$can_view_all && !$can_view_own) {
            abort(403, 'Unauthorized action.');
        }

        // Verify session belongs to this business
        StockCountSession::where('business_id', $business_id)
            ->when(!$can_view_all && $can_view_own, function ($q) {
                return $q->where('created_by', auth()->user()->id);
            })
            ->findOrFail($id);

        $query = StockCountLine::with(['product', 'variation', 'counter'])
            ->where('stock_count_session_id', $id)
            ->whereNotNull('counted_by');

        if (!empty($request->get('category_id'))) {
            $query->whereHas('product', function ($q) use ($request) {
                $q->where('category_id', $request->get('category_id'));
            });
        }

        if (!empty($request->get('brand_id'))) {
            $query->whereHas('product', function ($q) use ($request) {
                $q->where('brand_id', $request->get('brand_id'));
            });
        }

        $variance_type = $request->get('variance_type');
        if (!empty($variance_type) && $variance_type != 'all') {
            if ($variance_type == 'shortage') {
                $query->whereColumn('counted_quantity', '<', 'book_quantity');
            } elseif ($variance_type == 'surplus') {
                $query->whereColumn('counted_quantity', '>', 'book_quantity');
            } elseif ($variance_type == 'no_variance') {
                $query->whereColumn('counted_quantity', '=', 'book_quantity');
            } elseif ($variance_type == 'variance') {
                $query->whereColumn('counted_quantity', '!=', 'book_quantity');
            }
        }

        $lines = $query->get();

        $summary = [
            'total_items'    => count($lines),
            'shortage_qty'   => 0,
            'shortage_value' => 0,
            'surplus_qty'    => 0,
            'surplus_value'  => 0,
            'exact_qty'      => 0,
        ];

        foreach ($lines as $line) {
            $diff = $line->counted_quantity - $line->book_quantity;
            if ($diff < 0) {
                $summary['shortage_qty']   += abs($diff);
                $summary['shortage_value'] += abs($diff) * $line->unit_price;
            } elseif ($diff > 0) {
                $summary['surplus_qty']    += $diff;
                $summary['surplus_value']  += $diff * $line->unit_price;
            } else {
                $summary['exact_qty']++;
            }
        }

        // Build rows
        $rows = $lines->map(function ($line, $index) {
            $variance      = $line->counted_quantity - $line->book_quantity;
            $financial_diff = $variance * $line->unit_price;
            $variationName = (!empty($line->variation->name) && $line->variation->name !== 'DUMMY')
                ? $line->variation->name : null;

            return [
                'index'           => $index + 1,
                'product_name'    => $line->product->name ?? '',
                'variation_name'  => $variationName,
                'sku'             => $line->variation->sub_sku ?? '',
                'book_quantity'   => $line->book_quantity,
                'counted_quantity'=> $line->counted_quantity,
                'variance'        => $variance,
                'unit_price'      => $line->unit_price,
                'financial_diff'  => $financial_diff,
                'counter_name'    => $line->counter->user_full_name ?? '',
                'counted_at'      => $line->counted_at ? \Carbon\Carbon::parse($line->counted_at)->format('d M Y H:i') : '',
                'note'            => $line->note ?? '',
            ];
        });

        return response()->json([
            'rows'    => $rows,
            'summary' => $summary,
        ]);
    }

    /**
     * Reset and seed all Stock Count permissions, assigning them to Admin roles.
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function resetPermissions()
    {
        try {
            $dataController = new DataController();
            $permissions_data = $dataController->user_permissions();

            $permission_names = [];
            foreach ($permissions_data as $p) {
                $permission_names[] = $p['value'];
                \Spatie\Permission\Models\Permission::firstOrCreate([
                    'name' => $p['value'],
                    'guard_name' => 'web'
                ]);
            }

            // Assign all stock_count permissions to Admin role for current business and Superadmin
            $business_id = session()->get('user.business_id');
            $admin_role_name = 'Admin#' . $business_id;

            $roles = \Spatie\Permission\Models\Role::whereIn('name', [$admin_role_name, 'Admin', 'Superadmin'])
                ->orWhere('name', 'like', 'Admin#%')
                ->get();

            foreach ($roles as $role) {
                $role->givePermissionTo($permission_names);
            }

            // Forget cached permissions for Spatie
            app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

            $output = [
                'success' => true,
                'msg' => 'All ' . count($permission_names) . ' Stock Count permissions have been reset and assigned to Admin roles successfully!',
                'permissions' => $permission_names
            ];

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json($output);
            }

            return redirect()->back()->with('status', $output);
        } catch (\Exception $e) {
            $output = [
                'success' => false,
                'msg' => 'Error: ' . $e->getMessage()
            ];

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json($output, 500);
            }

            return redirect()->back()->with('status', $output);
        }
    }
}
