<?php

namespace App\Http\Requests\DailyReport;
use App\Http\Requests\BaseRequest;

class DailyReportRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true; 
    }

    public function rules(): array
    {
        $isFinished = $this->boolean('is_finished');

        return [
            'project_id' => ['required', 'exists:projects,id'],
            'machine_id' => ['required', 'exists:machines,id'],
            'date'       => ['required', 'date'],

            'initial_km' => ['required', 'numeric'],
            'initial_hm' => ['required', 'numeric'],

            'final_km' => [
                'nullable',
                $isFinished ? 'required' : 'nullable',
                'numeric',
                'gte:initial_km'
            ],

            'final_hm' => [
                'nullable',
                $isFinished ? 'required' : 'nullable',
                'numeric',
                'gte:initial_hm'
            ],

            'work_description' => [
                'nullable',
                $isFinished ? 'required' : 'nullable',
                'string'
            ],

            'fuel_quantity'    => ['nullable', 'numeric'],
            'fuel_observation' => ['nullable', 'string'],

            'finished_at' => [
                'nullable',
                $isFinished ? 'required' : 'nullable',
                'date_format:Y-m-d H:i:s'
            ],
        ];
    }
}
