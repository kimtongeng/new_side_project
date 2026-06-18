<?php

namespace Modules\Repair\Entities;

use App\Utils\ProductUtil;
use App\Variation;
use Illuminate\Database\Eloquent\Model;

class JobSheet extends Model
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'checklist' => 'array',
        'parts' => 'array',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'repair_job_sheets';

    /**
     * Return the customer for the project.
     */
    public function customer()
    {
        return $this->belongsTo(\App\Contact::class, 'contact_id');
    }

    /**
     * user added job sheet.
     */
    public function createdBy()
    {
        return $this->belongsTo(\App\User::class, 'created_by');
    }

    /**
     * technecian for job sheet.
     */
    public function technician()
    {
        return $this->belongsTo(\App\User::class, 'service_staff');
    }

    /**
     * status of job sheet.
     */
    public function status()
    {
        return $this->belongsTo('Modules\Repair\Entities\RepairStatus', 'status_id');
    }

    /**
     * get device for job sheet
     */
    public function Device()
    {
        return $this->belongsTo(\App\Category::class, 'device_id');
    }

    /**
     * get Brand for job sheet
     */
    public function Brand()
    {
        return $this->belongsTo(\App\Brands::class, 'brand_id');
    }

    /**
     * get device model for job sheet
     */
    public function deviceModel()
    {
        return $this->belongsTo('Modules\Repair\Entities\DeviceModel', 'device_model_id');
    }

    /**
     * get business location for job sheet
     */
    public function businessLocation()
    {
        return $this->belongsTo(\App\BusinessLocation::class, 'location_id');
    }

    /**
     * Get the repair for the job sheet
     */
    public function invoices()
    {
        return $this->hasMany(\App\Transaction::class, 'repair_job_sheet_id');
    }

    public function media()
    {
        return $this->morphMany(\App\Media::class, 'model');
    }

    public function getPartsUsed()
    {
        $parts = [];
        if (!empty($this->parts)) {
            $job_sheet_parts = $this->parts;

            $variation_ids = [];
            foreach ($job_sheet_parts as $key => $value) {
                $vid = $value['variation_id'] ?? $key;
                $variation_ids[$key] = $vid;
            }

            $variations = Variation::whereIn('id', array_values($variation_ids))
                ->with(['product_variation', 'product', 'product.unit'])
                ->get()
                ->keyBy('id');

            $productUtil = app(ProductUtil::class);
            $location_id = $this->location_id ?? null;

            foreach ($job_sheet_parts as $key => $value) {
                $vid = $value['variation_id'] ?? $key;
                $variation = $variations->get($vid);

                if (!$variation) continue;
                $current_stock = null;
                if (!empty($variation->product->enable_stock) && !empty($location_id)) {
                    $current_stock = $productUtil->getCurrentStock($variation->id, $location_id);
                }

                $parts[$key]['part_key']      = $key; // ← the storage key
                $parts[$key]['variation_id']  = $variation->id;
                $parts[$key]['variation_name'] = $variation->full_name;
                $parts[$key]['unit']          = $variation->product->unit->short_name;
                $parts[$key]['unit_id']       = $variation->product->unit->id;
                $parts[$key]['quantity']      = $value['quantity'];
                $parts[$key]['status']        = $value['status'] ?? null;
                $parts[$key]['user_id']       = $value['user_id'] ?? null;
                $parts[$key]['note']          = $value['note'] ?? '';
                $parts[$key]['product_image'] = !empty($variation->product->image)
                    ? asset('/uploads/img/' . rawurlencode($variation->product->image))
                    : asset('/img/default.png');
                $parts[$key]['current_stock'] = $current_stock;
                $parts[$key]['enable_stock']  = !empty($variation->product->enable_stock) ? 1 : 0;
            }
        }

        return $parts;
    }
}
