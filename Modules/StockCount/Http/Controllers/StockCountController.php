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

        if (!auth()->user()->can('stock_count.view')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $sessions = StockCountSession::with(['location', 'creator'])
                ->where('business_id', $business_id)
                ->select('stock_count_sessions.*');

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
                                <ul class="dropdown-menu dropdown-menu-right" role="menu">';

                    if (auth()->user()->can('stock_count.view')) {
                        $html .= '<li><a href="' . action([\Modules\StockCount\Http\Controllers\StockCountController::class, 'show'], [$row->id]) . '"><i class="fa fa-eye"></i> ' . __('messages.view') . '</a></li>';
                    }

                    if ($row->status === 'active' && auth()->user()->can('stock_count.count')) {
                        $html .= '<li><a href="' . action([\Modules\StockCount\Http\Controllers\StockCountController::class, 'worksheet'], [$row->id]) . '"><i class="fa fa-edit"></i> Edit (worksheet)</a></li>';
                    }

                    $html .= '<li><a href="' . action([\Modules\StockCount\Http\Controllers\StockCountController::class, 'printWorksheet'], [$row->id]) . '" target="_blank"><i class="fa fa-print"></i> Print worksheet</a></li>';

                    if (auth()->user()->can('stock_count.create')) {
                        $html .= '<li><a href="' . action([\Modules\StockCount\Http\Controllers\StockCountController::class, 'duplicate'], [$row->id]) . '"><i class="fa fa-copy"></i> Duplicate</a></li>';
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
                    $color = 'bg-gray';
                    if ($row->status === 'active') {
                        $color = 'bg-blue';
                    } elseif ($row->status === 'completed') {
                        $color = 'bg-green';
                    } elseif ($row->status === 'cancelled') {
                        $color = 'bg-red';
                    }
                    return '<span class="label ' . $color . '">' . __('stockcount::lang.' . $row->status) . '</span>';
                })
                ->addColumn('total_items', function ($row) {
                    return $row->lines()->count();
                })
                ->addColumn('items_counted', function ($row) {
                    return $row->lines()->whereNotNull('counted_by')->count();
                })
                ->editColumn('created_at', '{{@format_datetime($created_at)}}')
                ->editColumn('blind_count', function ($row) {
                    return $row->blind_count ? __('messages.yes') : __('messages.no');
                })
                ->rawColumns(['action', 'status', 'created_at'])
                ->make(true);

            $data = $dt->getData(true);
            
            // Calculate stats based on the filtered query
            $filtered_sessions_query = clone $sessions;
            $session_ids = $filtered_sessions_query->pluck('id')->toArray();
            
            $active_sessions = $filtered_sessions_query->clone()->where('status', 'active')->count();
            $completed_sessions = $filtered_sessions_query->clone()->where('status', 'completed')->count();
            
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

        return view('stockcount::create', compact('business_locations', 'categories', 'brands', 'racks', 'products'));
    }

    public function store(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');

        if (!auth()->user()->can('stock_count.create')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $input = $request->only(['name', 'location_id', 'blind_count', 'reference_no']);
            $input['blind_count'] = !empty($input['blind_count']) ? true : false;
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

        if (!auth()->user()->can('stock_count.view')) {
            abort(403, 'Unauthorized action.');
        }

        $session = StockCountSession::with(['location', 'creator', 'completer'])
            ->where('business_id', $business_id)
            ->findOrFail($id);

        $query = StockCountLine::with(['product', 'variation', 'counter'])
            ->where('stock_count_session_id', $id)
            ->whereNotNull('counted_by');

        // Apply filters
        if (!empty(request()->get('category_id'))) {
            $query->whereHas('product', function($q) {
                $q->where('category_id', request()->get('category_id'));
            });
        }

        if (!empty(request()->get('brand_id'))) {
            $query->whereHas('product', function($q) {
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

        if ($session->status !== 'active') {
            return redirect()->action([\Modules\StockCount\Http\Controllers\StockCountController::class, 'show'], [$id]);
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

            $lines = $request->input('lines');
            if (!empty($lines) && is_array($lines)) {
                foreach ($lines as $line_data) {
                    $line = StockCountLine::whereHas('session', function ($query) use ($business_id, $id) {
                        $query->where('id', $id)->where('business_id', $business_id)->where('status', 'active');
                    })->find($line_data['line_id']);

                    if (!empty($line)) {
                        $line->counted_quantity = $line_data['quantity'] ?? 0;
                        $line->note = $line_data['note'] ?? '';
                        $line->counted_by = auth()->user()->id;
                        $line->counted_at = Carbon::now();
                        $line->save();
                    }
                }
            } else {
                $line_id = $request->input('line_id');
                $quantity = $request->input('quantity', 0);
                $note = $request->input('note', '');

                $line = StockCountLine::whereHas('session', function ($query) use ($business_id, $id) {
                    $query->where('id', $id)->where('business_id', $business_id)->where('status', 'active');
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
                ->where('status', 'active')
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
                ->where('status', 'active')
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

        if (!auth()->user()->can('stock_count.view')) {
            abort(403, 'Unauthorized action.');
        }

        $session = StockCountSession::with(['location', 'creator'])
            ->where('business_id', $business_id)
            ->findOrFail($id);

        $lines = StockCountLine::with(['product', 'variation'])
            ->where('stock_count_session_id', $id)
            ->get();

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
}
