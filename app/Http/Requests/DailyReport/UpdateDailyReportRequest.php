<?php

namespace App\Http\Requests\DailyReport;

use Carbon\Carbon;
use App\Http\Requests\DailyReport\DailyReportRequest;

class UpdateDailyReportRequest extends DailyReportRequest
{
    public function authorize(): bool
    {
        $daily_report = $this->route('daily_report');
        return $this->user()->can('update', $daily_report);
    }

    public function rules(): array
{
    $isFinished = $this->boolean('is_finished');

    return [
        'project_id' => ['required', 'exists:projects,id'],
        'machine_id' => ['required', 'exists:machines,id'],
        'date' => ['required', 'date'],

        'initial_km' => ['required', 'numeric'],
        'initial_hm' => ['required', 'numeric'],

        'final_km' => [
            $isFinished ? 'required' : 'nullable',
            'numeric',
            'gte:initial_km'
        ],

        'final_hm' => [
            $isFinished ? 'required' : 'nullable',
            'numeric',
            'gte:initial_hm'
        ],

        'work_description' => [
            $isFinished ? 'required' : 'nullable',
            'string'
        ],

        //  MANTENCIONES
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
        'anomalies.*.severity' => ['nullable', 'string'],
        
    ];
}
}
