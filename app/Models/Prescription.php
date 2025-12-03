<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prescription extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id','doctor_name','diagnosis','status','prescribed_at',
        'approved_by','approved_at','dispensed_by','dispensed_at'
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'dispensed_at' => 'datetime',
        'prescribed_at' => 'datetime',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function items()
    {
        return $this->hasMany(PrescriptionItem::class);
    }
}
