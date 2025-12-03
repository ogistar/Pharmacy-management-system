<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ControlledDrugLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'prescription_item_id',
        'user_id',
        'action',
        'note',
    ];

    public function prescriptionItem()
    {
        return $this->belongsTo(PrescriptionItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
