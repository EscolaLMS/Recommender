<?php

namespace EscolaLms\Recommender\Services\Contracts;

use EscolaLms\Core\Dtos\OrderDto;
use EscolaLms\Recommender\Dto\PageDto;
use EscolaLms\Recommender\Dto\TermAnalyticsFilterListDto;
use EscolaLms\Recommender\Models\AggregatedFrame;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

interface TermAnalyticServiceContract
{
    public function rebuildTermAnalytic(string $modelType, int $modelId, Carbon $term): void;
    public function termAnalyticsList(string $modelType, TermAnalyticsFilterListDto $criteriaDto, PageDto $pageDto, OrderDto $orderDto): LengthAwarePaginator;
    public function modelAnalytics(string $modelType, int $modelId, ?int $term = null): Collection|AggregatedFrame;
    public function modelAnalyticsForTerm(int $termAnalyticId): AggregatedFrame;
}
