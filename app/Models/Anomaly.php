<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Anomaly extends Model
{
    protected $fillable = [
        'daily_report_id',
        'description',
        'severity',
    ];

    public function dailyReport()
    {
        return $this->belongsTo(DailyReport::class);
    }

    public function pictures()
    {
        return $this->hasMany(Picture::class);
    }
}