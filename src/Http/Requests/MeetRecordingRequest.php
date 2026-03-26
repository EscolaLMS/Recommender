<?php

namespace EscolaLms\Recommender\Http\Requests;

use EscolaLms\Consultations\Models\Consultation;
use EscolaLms\Recommender\Enum\MeetRecordingEnum;
use EscolaLms\Webinar\Models\Webinar;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rules\Enum;

class MeetRecordingRequest extends FormRequest
{
    public function authorize(): bool
    {
        $modelType = $this->get('model_type');

        $modelClass = match ($modelType) {
            'consultation' => Consultation::class,
            'webinar' => Webinar::class,
            default => null,
        };

        if (!$modelClass) {
            return false;
        }

        return Gate::allows('update', $modelClass);
    }

    public function rules(): array
    {
        return [
            'time' => ['required', 'date'],
            'type' => ['required', new Enum(MeetRecordingEnum::class)],
            'model_type' => ['required', 'in:consultation,webinar'],
            'model_id' => ['required'],
            'term' => ['required'],
        ];
    }
}
