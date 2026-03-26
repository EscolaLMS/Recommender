<?php

namespace EscolaLms\Recommender\Http\Requests;

use BenSampo\Enum\Rules\Enum;
use EscolaLms\Consultations\Models\Consultation;
use EscolaLms\Recommender\Enum\MeetRecordingEnum;
use EscolaLms\Webinar\Models\Webinar;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

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

        $model = $modelClass::query()->findOrFail($this->get('model_id'));

        return Gate::allows('update', $model);
    }

    public function rules(): array
    {
        return [
            'time' => ['required', 'date'],
            'type' => ['required', 'in:' . implode(',', MeetRecordingEnum::getValues())],
            'model_type' => ['required', 'in:consultation,webinar'],
            'model_id' => ['required'],
            'term' => ['required'],
        ];
    }
}
