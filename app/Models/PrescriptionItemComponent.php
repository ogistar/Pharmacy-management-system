<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrescriptionItemComponent extends Model
{
    use HasFactory;

    protected $fillable = [
        'prescription_item_id','product_id','product_name','quantity','is_full_pack'
    ];

    protected $casts = [
        'is_full_pack' => 'boolean',
    ];

    public function prescriptionItem()
    {
        return $this->belongsTo(PrescriptionItem::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
