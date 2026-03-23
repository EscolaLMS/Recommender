<?php

namespace EscolaLms\Recommender\Repositories;

use EscolaLms\Core\Dtos\OrderDto;
use EscolaLms\Core\Repositories\BaseRepository;
use EscolaLms\Recommender\Dto\TermAnalyticsFilterListDto;
use EscolaLms\Recommender\Models\TermAnalytic;
use EscolaLms\Recommender\Repositories\Contracts\TermAnalyticsRepositoryContract;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class TermAnalyticsRepository extends BaseRepository implements TermAnalyticsRepositoryContract
{

    public function getFieldsSearchable()
    {
        return [
            //
        ];
    }

    public function model()
    {
        return TermAnalytic::class;
    }

    public function findByCriteria(
        string $modelType,
        TermAnalyticsFilterListDto $criteriaDto,
        int $perPage,
        ?OrderDto $orderDto = null
    ): LengthAwarePaginator
    {
        $modelTable = $this->resolveModelTable($modelType);

        $query = TermAnalytic::query()
            ->from('term_analytics as ta')
            ->join("$modelTable as m", 'm.id', '=', 'ta.model_id')
            ->where('ta.model_type', $modelType)
            ->select([
                'ta.*',
                'm.name as model_name',
            ]);

        $query = $this->applyCriteria($query, $criteriaDto->toArray());

        if ($orderDto) {
            $query = $this->orderBy($query, $orderDto);
        }

        return $query->paginate($perPage);
    }

    private function resolveModelTable(string $modelType): string
    {
        return match ($modelType) {
            'consultation' => 'consultations',
            'webinar' => 'webinars',
            default => throw new \RuntimeException('Invalid model type'),
        };
    }

    private function orderBy(Builder $query, ?OrderDto $orderDto): Builder
    {
        if (!$orderDto || !$orderDto->getOrderBy()) {
            return $query->orderBy('ta.model_id', 'desc');
        }

        $column = match ($orderDto->getOrderBy()) {
            'name' => 'm.name',
            'term' => 'ta.term',
            default => 'ta.model_id',
        };

        return $query->orderBy(
            $column,
            $orderDto->getOrder() ?? 'asc'
        );
    }
}
