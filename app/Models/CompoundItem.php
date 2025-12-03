<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompoundItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'compound_id',
        'product_id',
        'quantity',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    public function compound()
    {
        return $this->belongsTo(Compound::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
