<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Machine extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'internal_id',
        'plate',
        'name',
        'machine_type_id',
        'fuel_type',
        'fuel_capacity',
    ];

    public function type()
    {
        return $this->belongsTo(MachineType::class, 'machine_type_id');
    }
}

