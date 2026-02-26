<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DailyReport extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'project_id',
        'machine_id',
        'date',
        'initial_km',
        'final_km',
        'total_km',
        'initial_hm',
        'final_hm',
        'total_hm',
        'work_description',
        'finished_at',
    ];

    protected $casts = [
        'date' => 'date',
        'finished_at' => 'datetime',
        'initial_km' => 'decimal:2',
        'final_km' => 'decimal:2',
        'total_km' => 'decimal:2',
        'initial_hm' => 'decimal:2',
        'final_hm' => 'decimal:2',
        'total_hm' => 'decimal:2',
    ];

    protected static function booted()
    {
        static::saving(function ($report) {

            if (!is_null($report->initial_km) && !is_null($report->final_km)) {
                $report->total_km = $report->final_km - $report->initial_km;
            }

            if (!is_null($report->initial_hm) && !is_null($report->final_hm)) {
                $report->total_hm = $report->final_hm - $report->initial_hm;
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function project()
    {
        return $this->belongsTo(Project::class)->withTrashed();
    }

    public function machine()
    {
        return $this->belongsTo(Machine::class)->withTrashed();
    }

    public function maintenances()
    {
        return $this->hasMany(Maintenance::class);
    }
    public function anomalies()
    {
        return $this->hasMany(Anomaly::class);
    }
}