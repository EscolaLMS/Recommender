<?php

namespace EscolaLms\Recommender\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MeetRecordingResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->resource->getKey(),
            'time' => $this->resource->time,
            'type' => $this->resource->type,
            'model_type' => $this->resource->model_type,
            'model_id' => $this->resource->model_id,
            'term' => $this->resource->term,
            'url' => $this->resource->recording_url,
            'url_expiration_time_millis' => $this->resource->url_expiration_time_millis,
        ];
    }
}
