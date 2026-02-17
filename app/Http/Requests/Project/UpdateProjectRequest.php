<?php

namespace App\Http\Requests\Project;

use Illuminate\Validation\Rule;

class UpdateProjectRequest extends ProjectRequest
{
    public function rules(): array
    {
        $project = $this->route('project');

        return [
            'name' => 'required|string|max:255',
            'internal_id' => [
                'required',
                'string',
                'max:100',
                Rule::unique('projects', 'internal_id')->ignore($project->id),
            ],
            'region'            => 'required|string|max:150',
            'comuna'            => 'required|string|max:150',
            'started_at'        => 'nullable|date',
            'planned_finish_at' => 'nullable|date',
            'actual_finish_at'  => 'nullable|date',
        ];
    }
}
