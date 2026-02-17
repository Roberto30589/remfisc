<?php

namespace App\Http\Requests\Project;

use App\Http\Requests\BaseRequest;

abstract class ProjectRequest extends BaseRequest
{
    public function attributes(): array
    {
        return [
            'name'              => 'nombre del proyecto',
            'internal_id'       => 'ID interno',
            'region'            => 'región',
            'comuna'            => 'comuna',
            'started_at'        => 'fecha de inicio',
            'planned_finish_at' => 'fecha de término planificada',
            'actual_finish_at'  => 'fecha de término real',
        ];
    }
}
