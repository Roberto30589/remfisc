<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MaintenanceType extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',// nombre  (ej: Cambio de aceite, Combustible, etc.)
        'slug', //para URLs amigables (ej: oil-change, fuel, etc.)
        'unit',              // litros, kg, unidad, etc.
        'requires_quantity', // si la cantidad es obligatoria
    ];

    protected $casts = [
        'requires_quantity' => 'boolean',
    ];

    public function maintenances()
    {
        return $this->hasMany(Maintenance::class, 'maintenance_type_id');
    }
    
}