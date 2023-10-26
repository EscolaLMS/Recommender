<?php

namespace EscolaLms\Recommender\Http\Requests;

use EscolaLms\Courses\Models\Lesson;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class TopicRecommendationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('topic', $this->getLesson());
    }

    public function rules(): array
    {
        return [];
    }

    private function getLesson(): Lesson
    {
        return Lesson::findOrFail($this->route('lessonId'));
    }
}
