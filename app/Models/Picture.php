<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Picture extends Model
{
    /** @use HasFactory<\Database\Factories\PictureFactory> */
    use HasFactory;
    protected $fillable = [
        'path',
        'anomaly_id', // Relación con Anomaly
    ];
    public function anomaly()
    {
        return $this->belongsTo(Anomaly::class);
    }
}