<?php

namespace Modules\Hms\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Contact;
use App\Transaction;
use App\TransactionPayment;
use App\User;
use App\Utils\TransactionUtil;
use App\Utils\Util;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Hms\Entities\HmsRoomType;
use Modules\Hms\Entities\HmsBookingLine;
use App\Utils\ModuleUtil;
use Modules\Hms\Entities\HmsTransactionClass;
use Yajra\DataTables\Facades\DataTables;


class HmsReportController extends Controller
{
    protected $commonUtil;
    protected $moduleUtil;
    protected $transactionUtil;

    public function __construct(
        Util $commonUtil, ModuleUtil $moduleUtil, TransactionUtil $transactionUtil

    ) {
        $this->commonUtil = $commonUtil;
        $this->moduleUtil = $moduleUtil;
        $this->transactionUtil = $transactionUtil;
    }
    /**
     * Reports landing page with cards linking to each report.
     * @return Renderable
     */
    public function index()
    {
        $business_id = request()->session()->get('user.business_id');

        if (! (auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'hms_module'))) {
            abort(403, 'Unauthorized action.');
        }

        return view('hms::report.index');
    }

    /**
     * Booking statistics report (formerly the index page).
     * @return Renderable
     */
    public function bookingStatsReport(Request $request)
    {

        $business_id = request()->session()->get('user.business_id');

        if (! (auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'hms_module'))) {
            abort(403, 'Unauthorized action.');
        }

        if ($request->date_to && $request->date_from) {

            $business_id = request()->session()->get('user.business_id');

            $date_to= $this->commonUtil->uf_date($request->date_to) . ' 00:00:00';
            $date_from = $this->commonUtil->uf_date($request->date_from) . ' 23:59:59';

            // all booking report
            $total_booking = $this->count_booking_by_status($date_to, $date_from, ['confirmed', 'cancelled', 'pending']);
            // all confirmed booking 
            $total_confirmed_booking =  $this->count_booking_by_status($date_to, $date_from, ['confirmed']);

            //all cancelled booking\\
            $total_cancelled_booking =  $this->count_booking_by_status($date_to, $date_from, ['cancelled']); 

            // all pending booking
            $total_pending_booking =  $this->count_booking_by_status($date_to, $date_from, ['pending']); 
  
            // booking count by room
            $transactions_with_room = Transaction::where('status', 'confirmed')
                ->where('type', 'hms_booking')
                ->where('transactions.business_id', $business_id)
                ->select('transactions.id', DB::raw('COUNT(hms_booking_lines.id) as line_count'))
                ->leftJoin('hms_booking_lines', 'transactions.id', '=', 'hms_booking_lines.transaction_id')
                ->groupBy('transactions.id')
                ->whereBetween('hms_booking_arrival_date_time', [$date_to, $date_from])
                ->get();

            $rooms_by_booking_count = $this->count_booking_by_room($transactions_with_room);
            
            $count_booking_by_night = Transaction::where('status', 'confirmed')
                ->where('transactions.business_id', $business_id)
                ->where('type', 'hms_booking')
                ->whereBetween('hms_booking_arrival_date_time', [$date_to, $date_from])
                ->get();

            $count_by_night = $this->count_booking_by_night($count_booking_by_night);

            // booking count by night 
            $transactions_adult_counts = Transaction::where('status', 'confirmed')
            ->where('transactions.business_id', $business_id)
            ->select('transactions.id')
            ->addSelect(DB::raw('SUM(hms_booking_lines.adults) as total_adults'))
            ->leftJoin('hms_booking_lines', 'transactions.id', '=', 'hms_booking_lines.transaction_id')
            ->groupBy('transactions.id')
            ->whereBetween('hms_booking_arrival_date_time', [$date_to, $date_from])
            ->get();

            $count_by_adults = $this->transactions_adult_counts($transactions_adult_counts);

            // count by room type 
            $pending_room_types = $this->room_type_count('pending', $date_to, $date_from);
            $cancelled_room_types = $this->room_type_count('cancelled', $date_to, $date_from);  
            $confirmed_room_types = $this->room_type_count('confirmed', $date_to, $date_from);
            
            $all_room_types = HmsRoomType::select(
                'hms_room_types.type',
                DB::raw('(SELECT COUNT(DISTINCT transactions.id) FROM hms_booking_lines
                            LEFT JOIN transactions ON hms_booking_lines.transaction_id = transactions.id
                            WHERE hms_booking_lines.hms_room_type_id = hms_room_types.id
                                AND transactions.hms_booking_arrival_date_time BETWEEN ? AND ? ) as transactions_count'),
                DB::raw('(SELECT SUM(hms_booking_lines.total_price) FROM hms_booking_lines
                            LEFT JOIN transactions ON hms_booking_lines.transaction_id = transactions.id
                            WHERE hms_booking_lines.hms_room_type_id = hms_room_types.id
                                AND transactions.hms_booking_arrival_date_time BETWEEN ? AND ? ) as total_price'),
                DB::raw('(SELECT SUM( DATEDIFF(transactions.hms_booking_departure_date_time, transactions.hms_booking_arrival_date_time)) FROM hms_booking_lines
                            LEFT JOIN transactions ON hms_booking_lines.transaction_id = transactions.id
                            WHERE hms_booking_lines.hms_room_type_id = hms_room_types.id
                                AND transactions.hms_booking_arrival_date_time BETWEEN ? AND ? ) as total_days'),
                DB::raw('(SELECT SUM(hms_booking_lines.adults + hms_booking_lines.childrens) FROM hms_booking_lines
                            LEFT JOIN transactions ON hms_booking_lines.transaction_id = transactions.id
                            WHERE hms_booking_lines.hms_room_type_id = hms_room_types.id
                                AND transactions.hms_booking_arrival_date_time BETWEEN ? AND ?) as no_of_guest'),
            )
            ->setBindings([$date_to, $date_from, $date_to, $date_from, $date_to, $date_from, $date_to, $date_from])
            ->where('hms_room_types.business_id', $business_id)
            ->get();

            return view('hms::report.booking_stats', compact('total_booking', 'total_confirmed_booking', 'total_cancelled_booking', 'total_pending_booking', 'rooms_by_booking_count', 'count_by_night', 'count_by_adults', 'all_room_types', 'confirmed_room_types', 'cancelled_room_types', 'pending_room_types'));
        }
        return view('hms::report.booking_stats');
    }


    public function count_booking_by_status($date_to, $date_from, $status){ 
        // all confirmed booking report
        $business_id = session()->get('user.business_id');

        $bookings = HmsTransactionClass::where('transactions.business_id', $business_id)->whereBetween('hms_booking_arrival_date_time', [$date_to, $date_from])->where('type', 'hms_booking')->whereIn('status', $status)->get();

        $count = (object) [
            'total_guest' => 0,
            'total_adult_guest' => 0, 
            'total_childs_guest' => 0,
            'total_amount' => 0,
            'total_nights' => 0,
            'total_count' => 0,
        ];

        $count->total_count = count($bookings);

        foreach ($bookings as $booking) {
            $count->total_guest = $count->total_guest + $booking->hms_booking_lines->sum('childrens') + $booking->hms_booking_lines->sum('adults');

            $count->total_adult_guest = $count->total_adult_guest + $booking->hms_booking_lines->sum('adults');

            $count->total_childs_guest = $count->total_childs_guest + $booking->hms_booking_lines->sum('childrens');

            $count->total_amount += $booking->final_total;  

            $start = Carbon::parse($booking->hms_booking_arrival_date_time);
            $end = Carbon::parse($booking->hms_booking_departure_date_time);
            // Calculate the difference in days
            $difference_in_days = $end->diffInDays($start);

            $count->total_nights += $difference_in_days;  
        }

        return $count;
    }

    public function room_type_count($status, $date_to, $date_from){
        $business_id = session()->get('user.business_id');
        
        return HmsRoomType::select(
            'hms_room_types.type',
            DB::raw('(SELECT COUNT(DISTINCT transactions.id) FROM hms_booking_lines
                       LEFT JOIN transactions ON hms_booking_lines.transaction_id = transactions.id
                       WHERE hms_booking_lines.hms_room_type_id = hms_room_types.id
                         AND transactions.hms_booking_arrival_date_time BETWEEN ? AND ?
                         AND transactions.status = "'.$status.'") as transactions_count'),
            DB::raw('(SELECT SUM(hms_booking_lines.total_price) FROM hms_booking_lines
                       LEFT JOIN transactions ON hms_booking_lines.transaction_id = transactions.id
                       WHERE hms_booking_lines.hms_room_type_id = hms_room_types.id
                         AND transactions.hms_booking_arrival_date_time BETWEEN ? AND ?
                         AND transactions.status = "'.$status.'") as total_price'),
            DB::raw('(SELECT SUM( DATEDIFF(transactions.hms_booking_departure_date_time, transactions.hms_booking_arrival_date_time)) FROM hms_booking_lines
                       LEFT JOIN transactions ON hms_booking_lines.transaction_id = transactions.id
                       WHERE hms_booking_lines.hms_room_type_id = hms_room_types.id
                         AND transactions.hms_booking_arrival_date_time BETWEEN ? AND ?
                         AND transactions.status = "'.$status.'") as total_days'),
            DB::raw('(SELECT SUM(hms_booking_lines.adults + hms_booking_lines.childrens) FROM hms_booking_lines
                       LEFT JOIN transactions ON hms_booking_lines.transaction_id = transactions.id
                       WHERE hms_booking_lines.hms_room_type_id = hms_room_types.id
                         AND transactions.hms_booking_arrival_date_time BETWEEN ? AND ?
                         AND transactions.status = "'.$status.'") as no_of_guest'),
        )
        ->setBindings([$date_to, $date_from, $date_to, $date_from, $date_to, $date_from, $date_to, $date_from])
        ->where('hms_room_types.business_id', $business_id)
        ->get();
    }

    public function transactions_adult_counts($transactions){
        $count = (object) [
            'one_adult_count' => 0,
            'two_adults_count' => 0,
            'three_adults_count' => 0,
            'four_adults_count' => 0,
            'five_adults_count' => 0,
            'six_adults_count' => 0,
            'more_than_six_adults_count' => 0,
        ];
        
        foreach ($transactions as $transaction) {
            $totalAdults = $transaction->total_adults;
            switch ($totalAdults) {
                case 1:
                    $count->one_adult_count++;
                    break;
                case 2:
                    $count->two_adults_count++;
                    break;
                case 3:
                    $count->three_adults_count++;
                    break;
                case 4:
                    $count->four_adults_count++;
                    break;
                case 5:
                    $count->five_adults_count++;
                    break;
                case 6:
                    $count->six_adults_count++;
                    break;
                default:
                    $count->more_than_six_adults_count++;
                    break;
            }
        }

        return $count;
    }

    public function count_booking_by_night($bookings){
        $counts = (object) [
            'one_night_count' => 0,
            'two_night_count' => 0,
            'three_night_count' => 0,
            'four_night_count' => 0,
            'five_night_count' => 0,
            'six_night_count' => 0,
            'more_than_six_night_count' => 0,
        ];
        
        foreach ($bookings as $booking) {
            $start = Carbon::parse($booking->hms_booking_arrival_date_time);
            $end = Carbon::parse($booking->hms_booking_departure_date_time);
            // Calculate the difference in days
            $nights = $end->diffInDays($start);

            // echo $nights;

            switch ($nights) {
                case 0:
                    $counts->one_night_count++;
                    break;
                case 1:
                    $counts->one_night_count++;
                    break;
                case 2:
                    $counts->two_night_count++;
                    break;
                case 3:
                    $counts->three_night_count++;
                    break;
                case 4:
                    $counts->four_night_count++;
                    break;
                case 5:
                    $counts->five_night_count++;
                    break;
                case 6:
                    $counts->six_night_count++;
                    break;
                default:
                    if ($nights > 6) {
                        $counts->more_than_six_night_count++;
                    }
            }
        }  
        
        return $counts;
    }

    public function count_booking_by_room($transactions){
        
        $lineCounts = (object) [
            'one_line_count' => 0,
            'two_lines_count' => 0,
            'more_than_two_lines_count' => 0,
        ];
        
        foreach ($transactions as $transaction) {
            $lineCount = $transaction->line_count;
        
            switch ($lineCount) {
                case 1:
                    $lineCounts->one_line_count++;
                    break;
                case 2:
                    $lineCounts->two_lines_count++;
                    break;
                default:
                    $lineCounts->more_than_two_lines_count++;
                    break;
            }
        }

        return $lineCounts;
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
        //
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

    /**
     * Booking Payment Report - lists transaction_payments for HMS bookings.
     *
     * @return Renderable
     */
    public function bookingPaymentReport(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');

        if (! (auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'hms_module'))) {
            abort(403, 'Unauthorized action.');
        }

        $payment_types = $this->transactionUtil->payment_types(null, true, $business_id);

        if ($request->ajax()) {
            $query = TransactionPayment::leftjoin('transactions as t', function ($join) use ($business_id) {
                $join->on('transaction_payments.transaction_id', '=', 't.id')
                    ->where('t.business_id', $business_id)
                    ->where('t.type', 'hms_booking');
            })
                ->leftjoin('contacts as c', 't.contact_id', '=', 'c.id')
                ->leftjoin('users as u', 'u.id', '=', 'transaction_payments.created_by')
                ->where('transaction_payments.business_id', $business_id)
                ->whereNotNull('transaction_payments.transaction_id')
                ->where('t.type', 'hms_booking')
                ->whereNull('transaction_payments.parent_id')
                ->select(
                    'transaction_payments.id as DT_RowId',
                    'transaction_payments.payment_ref_no',
                    'transaction_payments.paid_on',
                    'transaction_payments.amount',
                    'transaction_payments.is_return',
                    'transaction_payments.method',
                    'transaction_payments.document',
                    'transaction_payments.transaction_no',
                    'transaction_payments.cheque_number',
                    'transaction_payments.card_transaction_number',
                    'transaction_payments.bank_account_number',
                    't.id as transaction_id',
                    't.ref_no',
                    'c.name as customer',
                    'c.contact_id',
                    DB::raw("CONCAT(COALESCE(u.surname, ''), ' ', COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, '')) as user_name")
                );

            // Customer filter
            if (! empty($request->get('customer_id'))) {
                $query->where('t.contact_id', $request->get('customer_id'));
            }

            // User filter (payment recorder)
            if (! empty($request->get('user_id'))) {
                $query->where('transaction_payments.created_by', $request->get('user_id'));
            }

            // Date range filter
            $start_date = $request->get('start_date');
            $end_date = $request->get('end_date');
            if (! empty($start_date) && ! empty($end_date)) {
                $query->whereBetween(DB::raw('date(transaction_payments.paid_on)'), [$start_date, $end_date]);
            }

            // Room type filter (avoid duplicates by using whereExists on booking lines)
            if (! empty($request->get('room_type_id'))) {
                $room_type_id = $request->get('room_type_id');
                $query->whereExists(function ($q) use ($room_type_id) {
                    $q->select(DB::raw(1))
                        ->from('hms_booking_lines')
                        ->whereColumn('hms_booking_lines.transaction_id', 't.id')
                        ->where('hms_booking_lines.hms_room_type_id', $room_type_id);
                });
            }

            // Restrict by permitted locations
            $permitted_locations = auth()->user()->permitted_locations();
            if ($permitted_locations != 'all') {
                $query->whereIn('t.location_id', $permitted_locations);
            }

            return DataTables::of($query)
                ->editColumn('paid_on', '{{@format_datetime($paid_on)}}')
                ->editColumn('amount', function ($row) {
                    $amount = $row->is_return == 1 ? -1 * $row->amount : $row->amount;

                    return '<span class="paid-amount" data-orig-value="'.$amount.'">'.$this->transactionUtil->num_f($amount, true).'</span>';
                })
                ->editColumn('method', function ($row) use ($payment_types) {
                    $method = ! empty($payment_types[$row->method]) ? $payment_types[$row->method] : '';
                    if ($row->method == 'cheque') {
                        $method .= '<br>('.__('lang_v1.cheque_no').': '.$row->cheque_number.')';
                    } elseif ($row->method == 'card') {
                        $method .= '<br>('.__('lang_v1.card_transaction_no').': '.$row->card_transaction_number.')';
                    } elseif ($row->method == 'bank_transfer') {
                        $method .= '<br>('.__('lang_v1.bank_account_no').': '.$row->bank_account_number.')';
                    } elseif (in_array($row->method, ['custom_pay_1', 'custom_pay_2', 'custom_pay_3'])) {
                        $method .= '<br>('.__('lang_v1.transaction_no').': '.$row->transaction_no.')';
                    }
                    if ($row->is_return == 1) {
                        $method .= '<br><small>('.__('lang_v1.change_return').')</small>';
                    }

                    return $method;
                })
                ->editColumn('ref_no', function ($row) {
                    if (! empty($row->transaction_id)) {
                        return '<a href="'.action([\Modules\Hms\Http\Controllers\HmsBookingController::class, 'show'], [$row->transaction_id]).'">'.$row->ref_no.'</a>';
                    }

                    return '';
                })
                ->addColumn('action', function ($row) {
                    $html = '<button type="button" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline tw-dw-btn-primary view_payment" data-href="'.action([\App\Http\Controllers\TransactionPaymentController::class, 'viewPayment'], [$row->DT_RowId]).'">'.__('messages.view').'</button>';
                   
                    if (! empty($row->document)) {
                        $html .= ' <a href="'.asset('/uploads/documents/'.$row->document).'" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline tw-dw-btn-accent" download><i class="fa fa-download"></i> '.__('purchase.download_document').'</a>';
                    }

                    return $html;
                })
                ->filterColumn('user_name', function ($q, $keyword) {
                    $q->whereRaw("CONCAT(COALESCE(u.surname, ''), ' ', COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, '')) like ?", ["%{$keyword}%"]);
                })
                ->rawColumns(['amount', 'method', 'ref_no', 'action'])
                ->make(true);
        }

        $customers = Contact::customersDropdown($business_id, false);
        $users = User::forDropdown($business_id, false);
        $room_types = HmsRoomType::where('business_id', $business_id)->pluck('type', 'id');

        return view('hms::report.booking_payment_report', compact('customers', 'users', 'room_types', 'payment_types'));
    }
}
  