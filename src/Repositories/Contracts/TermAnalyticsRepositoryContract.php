<?php

namespace EscolaLms\Recommender\Repositories\Contracts;

use EscolaLms\Core\Dtos\OrderDto;
use EscolaLms\Recommender\Dto\TermAnalyticsFilterListDto;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface TermAnalyticsRepositoryContract
{
    public function findByCriteria(
        string $modelType,
        TermAnalyticsFilterListDto $criteriaDto,
        int $perPage,
        ?OrderDto $orderDto = null
    ): LengthAwarePaginator;
}
