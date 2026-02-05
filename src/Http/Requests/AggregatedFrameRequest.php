<?php

namespace EscolaLms\Recommender\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AggregatedFrameRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'model_type' => ['required'],
            'model_id' => ['required'],
            'term' => ['required'],
            'window_start' => ['required'],
            'window_end' => ['required'],
            'count' => ['required'],
            'sum_attention' => ['required'],
            'sum_emotions_angry' => ['required'],
            'sum_emotions_disgusted' => ['required'],
            'sum_emotions_fearful' => ['required'],
            'sum_emotions_happy' => ['required'],
            'sum_emotions_neutral' => ['required'],
            'sum_emotions_sad' => ['required'],
            'sum_emotions_surprised' => ['required'],
            'avg_attention' => ['required'],
            'avg_emotions_angry' => ['required'],
            'avg_emotions_disgusted' => ['required'],
            'avg_emotions_fearful' => ['required'],
            'avg_emotions_happy' => ['required'],
            'avg_emotions_neutral' => ['required'],
            'avg_emotions_sad' => ['required'],
            'avg_emotions_surprised' => ['required'],
            'median_attention' => ['required'],
            'median_emotions_angry' => ['required'],
            'median_emotions_disgusted' => ['required'],
            'median_emotions_fearful' => ['required'],
            'median_emotions_happy' => ['required'],
            'median_emotions_neutral' => ['required'],
            'median_emotions_sad' => ['required'],
            'median_emotions_surprised' => ['required'],
            'aggregated_at' => ['required'],
            'should_break' => ['required'],
            'break_confidence' => ['required'],
            'recommended_in_minutes' => ['nullable'],
            'reasoning' => ['required'],
            'algorithm' => ['required'],
            'processing_time_ms' => ['required'],
            'external_id' => ['required'],
        ];
    }
}
