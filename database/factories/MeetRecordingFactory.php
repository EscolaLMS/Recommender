<?php

namespace EscolaLms\Recommender\Database\Factories;

use EscolaLms\Recommender\Enum\MeetRecordingEnum;
use EscolaLms\Recommender\Models\MeetRecording;
use Illuminate\Database\Eloquent\Factories\Factory;

class MeetRecordingFactory extends Factory
{
    protected $model = MeetRecording::class;

    public function definition()
    {
        return [
            'time' => $this->faker->dateTime(),
            'type' => $this->faker->randomElement(MeetRecordingEnum::getValues()),
            'model_type' => $this->faker->randomElement(['consultation', 'webinar']),
            'model_id' => $this->faker->randomNumber(),
            'term' => $this->faker->dateTime(),
            'recording_url' => $this->faker->url(),
            'url_expiration_time_millis' => $this->faker->dateTime()->getTimestamp(),
        ];
    }
}
