<?php

namespace EscolaLms\Recommender\Services\Contracts;

interface RecommenderServiceContract
{
    public function makeCourseData(int $courseId): array;

    public function completionOfCourse(int $courseId): array;

    public function makeTopicData(int $lessonId): array;

    public function matchTopicType(int $lessonId): array;
}
