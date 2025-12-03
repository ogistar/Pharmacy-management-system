<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory,SoftDeletes;
    protected $fillable = [
        'purchase_id','price',
        'price_retail','price_wholesale','price_insurance',
        'discount','description',
        'promo_name','promo_percent','bundle_qty','bundle_price',
    ];

    protected $casts = [
        'price' => 'float',
        'price_retail' => 'float',
        'price_wholesale' => 'float',
        'price_insurance' => 'float',
        'promo_percent' => 'float',
        'bundle_qty' => 'integer',
        'bundle_price' => 'float',
    ];

    public function purchase(){
        return $this->belongsTo(Purchase::class);
    }
}
