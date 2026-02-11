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
        'initial_hm',
        'final_hm',
        'work_description',
        'fuel_quantity',
        'fuel_observation',
    ];

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
}
