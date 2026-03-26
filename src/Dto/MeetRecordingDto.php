<?php

namespace EscolaLms\Recommender\Dto;

use EscolaLms\Recommender\Models\MeetRecording;

class MeetRecordingDto extends BaseDto
{
    protected string $time;
    protected string $type;
    protected string $modelType;
    protected string $modelId;
    protected string $term;

    public function model(): MeetRecording
    {
        // @phpstan-ignore-next-line
        return MeetRecording::newModelInstance();
    }

    public function toArray($filters = false): array
    {
        $result = $this->fillInArray($this->model()->getFillable());
        return $filters ? array_filter($result) : $result;
    }

    public function getTime(): string
    {
        return $this->time;
    }

    public function setTime(string $time): void
    {
        $this->time = $time;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getModelType(): string
    {
        return $this->modelType;
    }

    public function setModelType(string $modelType): void
    {
        $this->modelType = $modelType;
    }

    public function getModelId(): string
    {
        return $this->modelId;
    }

    public function setModelId(string $modelId): void
    {
        $this->modelId = $modelId;
    }

    public function getTerm(): string
    {
        return $this->term;
    }

    public function setTerm(string $term): void
    {
        $this->term = $term;
    }
}
