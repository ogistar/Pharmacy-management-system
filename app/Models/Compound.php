<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Compound extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'service_fee',
        'markup_percent',
        'price_override',
    ];

    protected $casts = [
        'service_fee' => 'float',
        'markup_percent' => 'float',
        'price_override' => 'float',
    ];

    public function items()
    {
        return $this->hasMany(CompoundItem::class);
    }
}
