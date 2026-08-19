<?php

namespace Modules\Manufacturing\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class MfgRecipe extends Model
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * Get the variations associated with the product.
     */
    public function variation()
    {
        return $this->belongsTo(\App\Variation::class, 'variation_id');
    }

    /**
     * Get all the ingredients for the recipe.
     */
    public function ingredients()
    {
        return $this->hasMany(\Modules\Manufacturing\Entities\MfgRecipeIngredient::class, 'mfg_recipe_id');
    }

    public static function forDropdown($business_id, $variation_id = true)
    {
        $recipes = MfgRecipe::join('variations as v', 'mfg_recipes.variation_id', '=', 'v.id')
                        ->join('products as p', 'v.product_id', '=', 'p.id')
                        ->join('product_variations as pv', 'v.product_variation_id', '=', 'pv.id')
                        ->where('p.business_id', $business_id)
                        ->select(
                            'p.name as product_name',
                            'p.secondary_name',
                            'p.type as product_type',
                            'pv.name as pv_name',
                            'v.name as v_name',
                            'v.sub_sku',
                            'mfg_recipes.variation_id',
                            'mfg_recipes.id'
                        )->get();

        $dropdown = [];
        foreach ($recipes as $recipe) {
            $p_name = \App\Utils\ProductUtil::getFormattedProductName($recipe->product_name, $recipe->secondary_name, false);
            if ($recipe->product_type == 'variable') {
                $recipe_name = $p_name . ' - ' . $recipe->pv_name . ' - ' . $recipe->v_name . ' (' . $recipe->sub_sku . ')';
            } else {
                $recipe_name = $p_name . ' (' . $recipe->sub_sku . ')';
            }

            if ($variation_id) {
                $dropdown[$recipe->variation_id] = $recipe_name;
            } else {
                $dropdown[$recipe->id] = $recipe_name;
            }
        }

        return collect($dropdown);
    }

    /**
     * Get the unit associated with the recipe.
     */
    public function sub_unit()
    {
        return $this->belongsTo(\App\Unit::class, 'sub_unit_id');
    }
}
