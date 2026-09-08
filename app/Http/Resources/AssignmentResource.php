<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssignmentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'language' => $this->language,
            'difficulty' => $this->difficulty->value,
            'difficulty_label' => $this->difficulty->label(),
            'instructions' => $this->when($request->routeIs('api.assignments.show'), $this->instructions),
            'starter_code' => $this->when($request->routeIs('api.assignments.show'), $this->starter_code),
            'lesson' => $this->whenLoaded('lesson', fn (): array => [
                'id' => $this->lesson->id,
                'title' => $this->lesson->title,
            ]),
            // Скрытые кейсы наружу не отдаются вовсе: приложение получает ровно
            // то же, что видит ученик на сайте, иначе условие утекло бы через API.
            'tests' => $this->whenLoaded('tests', fn () => $this->tests
                ->where('is_hidden', false)
                ->map(fn ($test): array => [
                    'name' => $test->name,
                    'stdin' => $test->stdin,
                    'expected' => $test->expected_output,
                ])->values()),
            'hidden_tests' => $this->whenLoaded('tests', fn (): int => $this->tests->where('is_hidden', true)->count()),
        ];
    }
}
