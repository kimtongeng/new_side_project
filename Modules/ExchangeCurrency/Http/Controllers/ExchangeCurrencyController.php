<?php

namespace Modules\ExchangeCurrency\Http\Controllers;

use App\Utils\ModuleUtil;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\ExchangeCurrency\Entities\ExchangeCurrency;
use Yajra\DataTables\Facades\DataTables;

class ExchangeCurrencyController extends Controller
{
    protected $moduleUtil;

    /**
     * Constructor
     *
     * @param  ProductUtils  $product
     * @return void
     */
    public function __construct(ModuleUtil $moduleUtil)
    {
        $this->moduleUtil = $moduleUtil;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        if (!auth()->user()->can('exchange_currency.view') && !auth()->user()->can('exchange_currency.create')) {
            abort(403, 'Unauthorized action.');
        }

        // dump(request()->ajax());
        if (request()->ajax()) {
            
            $business_id = auth()->user()->business_id;

            $exchange_currency = ExchangeCurrency::where(ExchangeCurrency::BUSINESS_ID,$business_id)->get();
            return DataTables::of($exchange_currency)

                ->addColumn('is_use', function ($row) {
                    $buttonClass = $row->is_use == 1 ? 'btn-success' : 'btn-danger';
                    $buttonText = $row->is_use == 1 ? 'Enable' : 'Disable';

                    return '<button data-href="' . action('\Modules\ExchangeCurrency\Http\Controllers\ExchangeCurrencyController@update_status', [$row->id]) . '" class="toggle-status btn ' . $buttonClass . ' update_status" data-id="' . $row->id . '" style="display: inline-block; padding: 0.25rem 0.5rem; font-size: 0.75rem; font-weight: 600; color: white; border-radius: 9999px;">' . $buttonText . '</button>';
                })
                ->addColumn('exchange_rate', function ($row) {
                    $value = floatval($row->exchange_rate);
                    
                    // Separate integer and decimal parts
                    $parts = explode('.', number_format($value, 8, '.', ''));
                
                    $integer = number_format($parts[0]); // adds comma
                    $decimal = isset($parts[1]) ? rtrim($parts[1], '0') : '';
                
                    $formatted = $decimal !== '' ? $integer . '.' . $decimal : $integer;
                
                    return '<span>' . $formatted . '</span>';
                })

                ->addColumn('action', function ($row) {
                    $html = '';
                    if (auth()->user()->can('exchange_currency.update')) {
                        $html .= '<button data-href="' . action('\Modules\ExchangeCurrency\Http\Controllers\ExchangeCurrencyController@edit', [$row->id]) . '" 
                        class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline tw-dw-btn-primary edit_exchange_currency">
                        <i class="glyphicon glyphicon-edit"></i> ' . __('messages.edit') . '</button>&nbsp;';
                    }

                    if (auth()->user()->can('exchange_currency.delete')) {
                        $html .= '<button data-href="' . action('\Modules\ExchangeCurrency\Http\Controllers\ExchangeCurrencyController@destroy', [$row->id]) . '" 
                        class="tw-dw-btn tw-dw-btn-outline tw-dw-btn-xs tw-dw-btn-error delete_exchange_currency">
                        <i class="glyphicon glyphicon-trash"></i> ' . __('messages.delete') . '</button>';
                    }

                    return $html;
                })
                ->removeColumn('id', 'created_at', 'updated_at')
                ->rawColumns([
                    'country',
                    'currency',
                    'code',
                    'symbol',
                    'exchange_rate',
                    'is_use',
                    'action'
                ]) // ✅ fixed here
                ->make(true);
        }

        return view('ExchangeCurrency::exchange_currency.index');

    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        
        if (! auth()->user()->can('exchange_currency.create')) {
            abort(403, 'Unauthorized action.');
        }
        $quick_add = false;

        if (! empty(request()->input('quick_add'))) {
            $quick_add = true;
        }
        $is_repair_installed = $this->moduleUtil->isModuleInstalled('Repair');
        return view('ExchangeCurrency::exchange_currency.create')
            ->with(compact('quick_add', 'is_repair_installed'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        if (! auth()->user()->can('exchange_currency.create')) {
            abort(403, 'Unauthorized action.');
        }
        DB::beginTransaction();
        try {

            $business_id = auth()->user()->business_id;
            $input = $request->only(['country', 'currency', "code", "symbol",'exchange_rate', 'is_use']);
            $exchange_currency = new ExchangeCurrency();
            $exchange_currency->setData($input);
            $exchange_currency->{ExchangeCurrency::BUSINESS_ID} = $business_id;
            $exchange_currency->save();
            DB::commit();
            $output = [
                'success' => true,
                'data' => $exchange_currency,
                'msg' => __('brand.added_success'),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }
        return $output;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {

        if (! auth()->user()->can('exchange_currency.update')) {
            abort(403, 'Unauthorized action.');
        }
        if (request()->ajax()) {

            $exchange_currency = ExchangeCurrency::find($id);
            return view('ExchangeCurrency::exchange_currency.edit')->with(compact("exchange_currency"));
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if (! auth()->user()->can('exchange_currency.update')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            try {
                $input = $request->only(['country', 'currency', "code", "symbol", 'exchange_rate','is_use']);

                $exchange_currency = ExchangeCurrency::findOrFail($id);
                $exchange_currency->setData(
                    [
                        ExchangeCurrency::COUNTRY => $input[ExchangeCurrency::COUNTRY],
                        ExchangeCurrency::CURRENCY => $input[ExchangeCurrency::CURRENCY],
                        ExchangeCurrency::CODE => $input[ExchangeCurrency::CODE],
                        ExchangeCurrency::SYMBOL => $input[ExchangeCurrency::SYMBOL],
                        ExchangeCurrency::EXCHANGE_RATE => $input[ExchangeCurrency::EXCHANGE_RATE],
                        ExchangeCurrency::IS_USE => $input[ExchangeCurrency::IS_USE],
                    ]
                );


                $exchange_currency->save();

                $output = [
                    'success' => true,
                    'msg' => __('brand.updated_success'),
                ];
            } catch (\Exception $e) {
                \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

                $output = [
                    'success' => false,
                    'msg' => __('messages.something_went_wrong'),
                ];
            }

            return $output;
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (! auth()->user()->can('exchange_currency.delete')) {
            abort(403, 'Unauthorized action.');
        }
        if (request()->ajax()) {
            try {

                $exchange_currency = ExchangeCurrency::find($id);
                $exchange_currency->delete();



                $output = [
                    'success' => true,
                    'msg' => __('exchange_currency.deleted_success'),
                ];
            } catch (\Exception $e) {
                \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

                $output = [
                    'success' => false,
                    'msg' => __('messages.something_went_wrong'),
                ];
            }

            return $output;
        }
    }
    public function update_status($id)
    {
        if (! auth()->user()->can('exchange_currency.update')) {
            abort(403, 'Unauthorized action.');
        }
        if (request()->ajax()) {
            try {

                $exchange_currency = ExchangeCurrency::find($id);

                $exchange_currency->{ExchangeCurrency::IS_USE} = !$exchange_currency->{ExchangeCurrency::IS_USE};
                $exchange_currency->save();

                

                $output = [
                    'success' => true,
                    'msg' => __('exchange_currency.deleted_success'),
                ];
            } catch (\Exception $e) {
                \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

                $output = [
                    'success' => false,
                    'msg' => __('messages.something_went_wrong'),
                ];
            }

            return $output;
        }
    }
}
