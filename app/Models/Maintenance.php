<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Maintenance extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'daily_report_id',
        'maintenance_type_id',
        'quantity',
        'observation',


    ];
    protected $casts = [
    'quantity' => 'decimal:2',
    ];
    public function maintenanceType()
    {
        return $this->belongsTo(MaintenanceType::class, 'maintenance_type_id');
    }

    public function dailyReport()
    {
        return $this->belongsTo(DailyReport::class);
    }
}