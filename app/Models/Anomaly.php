<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Anomaly extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'daily_report_id',
        'description',
        'severity',
    ];

    public function dailyReport()
    {
        return $this->belongsTo(DailyReport::class);
    }
    // Define media collection for anomalies
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('anomalies')
            ->useDisk('public')
            ->acceptsMimeTypes(['image/jpeg','image/png','image/webp']);
    }
}