<?php

namespace EscolaLms\Recommender\Repositories\Contracts;

use EscolaLms\Core\Repositories\Contracts\BaseRepositoryContract;
use Illuminate\Support\Collection;

interface TopicRepositoryContract extends BaseRepositoryContract
{
    public function getAllByLessonId(int $lessonId, ?string $orderDir = 'desc', ?int $limit = null): Collection;

    public function getAllByCourseId(int $courseId, ?string $orderDir = 'desc'): Collection;
}
