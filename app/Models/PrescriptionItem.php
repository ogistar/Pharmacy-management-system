<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrescriptionItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'prescription_id','product_id','compound_id','product_name','quantity','dosage',
        'is_controlled','is_compound','compound_note','label_note','is_full_pack'
    ];

    protected $casts = [
        'is_controlled' => 'boolean',
        'is_compound' => 'boolean',
        'is_full_pack' => 'boolean',
    ];

    public function prescription()
    {
        return $this->belongsTo(Prescription::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function components()
    {
        return $this->hasMany(PrescriptionItemComponent::class);
    }
}
