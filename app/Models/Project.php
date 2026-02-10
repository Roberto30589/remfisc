<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'internal_id',
        'region',
        'comuna',
        'started_at',
        'planned_finish_at',
        'actual_finish_at',
    ];

    protected $dates = [
        'started_at',
        'planned_finish_at',
        'actual_finish_at',
        'deleted_at',
    ];
}

