<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubmissionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'pending' => $this->status->isPending(),
            'passed' => $this->passed(),
            'passed_count' => $this->passed_count,
            'total_count' => $this->total_count,
            'score' => $this->score,
            'attempt_number' => $this->attempt_number,
            'runtime_ms' => $this->runtime_ms,
            'output' => $this->output,
            'results' => $this->test_results ?? [],
            'code' => $this->code,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
