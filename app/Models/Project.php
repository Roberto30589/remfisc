<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'internal_id',
        'region',
        'comuna',
        'started_at',
        'planned_finish_at',
        'actual_finish_at',
    ];
}

