<?php

namespace Modules\ProductCatalogue\Http\Controllers;

use App\Business;
use App\BusinessLocation;
use App\Category;
use App\Discount;
use App\Product;
use App\SellingPriceGroup;
use App\Utils\ModuleUtil;
use App\Utils\ProductUtil;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;


class ProductCatalogueController extends Controller
{
    /**
     * All Utils instance.
     */
    protected $productUtil;

    protected $moduleUtil;

    /**
     * Constructor
     *
     * @param  ProductUtils  $product
     * @return void
     */
    public function __construct(ProductUtil $productUtil, ModuleUtil $moduleUtil)
    {
        $this->productUtil = $productUtil;
        $this->moduleUtil = $moduleUtil;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index($business_id, $location_id)
    {
        $business = Business::with(['currency'])->findOrFail($business_id);

        $settings = json_decode($business->productcatalogue_settings, true);

        $is_show = $settings['is_show'] ?? 1;

        $enable_whatsapp_ordering = $settings['enable_whatsapp_ordering'] ?? 0;

        $order_receiving_whatsapp_number = $settings['order_receiving_whatsapp_number'] ?? '';

        $customer_field_settings = $this->getCustomerFieldSettings($settings);

        $products = Product::where('business_id', $business_id)
            ->whereHas('product_locations', function ($q) use ($location_id) {
                $q->where('product_locations.location_id', $location_id);
            })
            ->ProductForSales()
            ->with(['variations', 'variations.product_variation', 'category']);
        if ($is_show == 0) {
            $products = $products->havingRaw('
                    (SELECT CASE WHEN enable_stock = 0 THEN 1 
                        ELSE SUM(variation_location_details.qty_available) END
                        FROM variation_location_details 
                        WHERE variation_location_details.product_id = products.id) > 0');
        }

        $products = $products->select('products.*', DB::raw('(SELECT SUM(variation_location_details.qty_available) FROM variation_location_details WHERE variation_location_details.product_id = products.id) as stock'))
            ->get()
            ->groupBy('category_id');

        $business = Business::with(['currency'])->findOrFail($business_id);
        $business_location = BusinessLocation::where('business_id', $business_id)->findOrFail($location_id);

        $now = \Carbon::now()->toDateTimeString();
        $discounts = Discount::where('business_id', $business_id)
            ->where('location_id', $location_id)
            ->where('is_active', 1)
            ->where('starts_at', '<=', $now)
            ->where('ends_at', '>=', $now)
            ->orderBy('priority', 'desc')
            ->get();
        foreach ($discounts as $key => $value) {
            $discounts[$key]->discount_amount = $this->productUtil->num_f($value->discount_amount, false, $business);
        }


        return view('productcatalogue::catalogue.index')->with(compact('products', 'business', 'discounts', 'business_location', 'enable_whatsapp_ordering', 'order_receiving_whatsapp_number', 'customer_field_settings'));
    }

    /**
     * Returns customer field settings with defaults applied (all fields required by default).
     */
    private function getCustomerFieldSettings($settings)
    {
        $defaults = [
            'name' => 'required',
            'email' => 'required',
            'phone' => 'required',
        ];

        $saved = $settings['customer_field_settings'] ?? [];
        $allowed = ['required', 'optional', 'remove'];
        $result = [];
        foreach ($defaults as $key => $default) {
            $result[$key] = (isset($saved[$key]) && in_array($saved[$key], $allowed, true))
                ? $saved[$key]
                : $default;
        }
        return $result;
    }

    /**
     * Show the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($business_id, $id)
    {


        $product = Product::with(['brand', 'unit', 'category', 'sub_category', 'product_tax', 'variations', 'variations.product_variation', 'variations.group_prices', 'variations.media', 'media', 'product_locations', 'warranty', 'variations.variation_location_details'])->where('business_id', $business_id)
            ->select('products.*', DB::raw('(SELECT SUM(variation_location_details.qty_available) FROM variation_location_details WHERE variation_location_details.product_id = products.id) as stock'))
            ->findOrFail($id);


        $price_groups = SellingPriceGroup::where('business_id', $product->business_id)->active()->pluck('name', 'id');

        $allowed_group_prices = [];
        foreach ($price_groups as $key => $value) {
            $allowed_group_prices[$key] = $value;
        }

        $group_price_details = [];
        $discounts = [];
        foreach ($product->variations as $variation) {
            foreach ($variation->group_prices as $group_price) {
                $group_price_details[$variation->id][$group_price->price_group_id] = $group_price->price_inc_tax;
            }

            $discounts[$variation->id] = $this->productUtil->getProductDiscount($product, $product->business_id, request()->input('location_id'), false, null, $variation->id);
        }

        $combo_variations = [];
        if ($product->type == 'combo') {
            $combo_variations = $this->productUtil->__getComboProductDetails($product['variations'][0]->combo_variations, $product->business_id);
        }

        $business = Business::findOrFail($business_id);

        $settings = json_decode($business->productcatalogue_settings, true);
        $enable_whatsapp_ordering = $settings['enable_whatsapp_ordering'] ?? 0;

        return view('productcatalogue::catalogue.show')->with(compact(
            'product',
            'allowed_group_prices',
            'group_price_details',
            'combo_variations',
            'discounts',
            'business',
            'enable_whatsapp_ordering'
        ));
    }

    public function generateQr()
    {
        $business_id = request()->session()->get('user.business_id');
        if (! (auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'productcatalogue_module'))) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $business_locations = BusinessLocation::forDropdown($business_id);
        $business = Business::findOrFail($business_id);

        return view('productcatalogue::catalogue.generate_qr')
            ->with(compact('business_locations', 'business'));
    }

    /**
     * update product Catalogue Setting
     * @param Request $request
     */

    public function productCatalogueSetting(Request $request)
    {

        $business_id = request()->session()->get('user.business_id');

        if (! (auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'productcatalogue_module'))) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $busines = Business::findOrFail($business_id);

            $settings = json_decode($busines->productcatalogue_settings, true);
            if (!is_array($settings)) {
                $settings = [];
            }

            // Update only the fields that are present in the request
            if ($request->has('is_show')) {
                $settings['is_show'] = $request->post('is_show');
            }
            if ($request->has('enable_whatsapp_ordering') || $request->has('order_receiving_whatsapp_number')) {
                $settings['enable_whatsapp_ordering'] = $request->post('enable_whatsapp_ordering', 0);
                $settings['order_receiving_whatsapp_number'] = $request->post('order_receiving_whatsapp_number', '');

                $submitted_field_settings = $request->post('customer_field_settings', []);
                $allowed_values = ['required', 'optional', 'remove'];
                $allowed_fields = ['name', 'email', 'phone'];
                $clean_field_settings = [];
                foreach ($allowed_fields as $field_key) {
                    $value = $submitted_field_settings[$field_key] ?? 'required';
                    $clean_field_settings[$field_key] = in_array($value, $allowed_values, true) ? $value : 'required';
                }
                $settings['customer_field_settings'] = $clean_field_settings;
            }

            $busines->productcatalogue_settings = json_encode($settings);

            $busines->update();

            $output = [
                'success' => 1,
                'msg' => __('lang_v1.success'),
            ];

            return redirect()
                ->action([\Modules\ProductCatalogue\Http\Controllers\ProductCatalogueController::class, 'generateQr'])
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
