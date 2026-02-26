<?php

namespace App\Http\Requests\DailyReport;

class StoreDailyReportRequest extends DailyReportRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\DailyReport::class);
    }


    public function rules(): array
{
    return [
        'project_id' => ['required', 'exists:projects,id'],
        'machine_id' => ['required', 'exists:machines,id'],
        'date' => ['required', 'date'],

        'initial_km' => ['required', 'numeric'],
        'initial_hm' => ['required', 'numeric'],

        'final_km' => ['nullable', 'numeric', 'gte:initial_km'],
        'final_hm' => ['nullable', 'numeric', 'gte:initial_hm'],

        'work_description' => ['nullable', 'string'],

        // MANTENCIONES
        'maintenances' => ['nullable', 'array'],
        'maintenances.*.maintenance_type_id' => [
            'required',
            'exists:maintenance_types,id'
        ],
        'maintenances.*.quantity' => [
            'nullable',
            'numeric'
        ],
        'maintenances.*.observation' => [
            'nullable',
            'string'
        ],
        // ANOMALÍAS
        'anomalies' => ['nullable', 'array'],
        'anomalies.*.description' => ['nullable', 'string'],
        'anomalies.*.picture_id' => ['nullable', 'exists:pictures,id'],
        // 'anomalies.*.files' => ['nullable', 'array'], --- IGN
        'anomalies.*.severity' => ['nullable', 'string'],
        
    ];
}
}
