<?php

namespace App\Http\Requests\Project;

class UpdateProjectRequest extends ProjectRequest
{
    public function rules(): array
    {
        $projectId = $this->route('project');

        return [
            'name'              => 'required|string|max:255',
            'internal_id'       => "required|string|max:100|unique:projects,internal_id,{$projectId}",
            'region'            => 'required|string|max:150',
            'comuna'            => 'required|string|max:150',
            'started_at'        => 'nullable|date',
            'planned_finish_at' => 'nullable|date',
            'actual_finish_at'  => 'nullable|date',
        ];
    }
}
