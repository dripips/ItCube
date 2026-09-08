<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LessonResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'summary' => $this->summary,
            'content' => $this->when($request->routeIs('api.lessons.show'), $this->content),
            'subject' => $this->subject->name,
            'direction' => $this->subject->direction->name,
            'assignments' => AssignmentResource::collection($this->whenLoaded('assignments')),
            'quizzes' => $this->whenLoaded('quizzes', fn () => $this->quizzes
                ->where('published', true)
                ->map(fn ($quiz): array => [
                    'id' => $quiz->id,
                    'title' => $quiz->title,
                    'time_limit_minutes' => $quiz->time_limit_minutes,
                ])->values()),
        ];
    }
}
