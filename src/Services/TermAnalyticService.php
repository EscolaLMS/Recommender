<?php

namespace EscolaLms\Recommender\Services;

use EscolaLms\Core\Dtos\OrderDto;
use EscolaLms\Recommender\Dto\PageDto;
use EscolaLms\Recommender\Dto\TermAnalyticsFilterListDto;
use EscolaLms\Recommender\Enum\EmotionsEnum;
use EscolaLms\Recommender\Models\AggregatedFrame;
use EscolaLms\Recommender\Models\TermAnalytic;
use EscolaLms\Recommender\Repositories\Contracts\TermAnalyticsRepositoryContract;
use EscolaLms\Recommender\Services\Contracts\TermAnalyticServiceContract;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TermAnalyticService implements TermAnalyticServiceContract
{
    public function __construct(
        private TermAnalyticsRepositoryContract $termAnalyticsRepository,
    ) {}

    public function rebuildTermAnalytic(string $modelType, int $modelId, Carbon $term): void
    {
        DB::transaction(function () use ($modelType, $modelId, $term) {
            $query = AggregatedFrame::query()
                ->where('model_type', $modelType)
                ->where('model_id', $modelId)
                ->where('term', $term);

            $data = $query
                ->selectRaw("
                SUM(count) as count,
                COUNT(id) as aggregated_frames_count,
                MAX(updated_at) as last_frame_at,

                SUM(sum_attention) as sum_attention,
                SUM(sum_emotions_angry) as sum_emotions_angry,
                SUM(sum_emotions_disgusted) as sum_emotions_disgusted,
                SUM(sum_emotions_fearful) as sum_emotions_fearful,
                SUM(sum_emotions_happy) as sum_emotions_happy,
                SUM(sum_emotions_neutral) as sum_emotions_neutral,
                SUM(sum_emotions_sad) as sum_emotions_sad,
                SUM(sum_emotions_surprised) as sum_emotions_surprised
            ")
                ->first();

            if (!$data || $data->count == 0) {
                return;
            }

            $emotions = collect([
                EmotionsEnum::ANGRY => $data->sum_emotions_angry,
                EmotionsEnum::DISGUSTED => $data->sum_emotions_disgusted,
                EmotionsEnum::FEARFUL => $data->sum_emotions_fearful,
                EmotionsEnum::HAPPY => $data->sum_emotions_happy,
                EmotionsEnum::NEUTRAL => $data->sum_emotions_neutral,
                EmotionsEnum::SAD => $data->sum_emotions_sad,
                EmotionsEnum::SURPRISED => $data->sum_emotions_surprised,
            ]);

            $maxEmotion = $emotions->sortDesc()->keys()->first();

            $termAnalytic = TermAnalytic::updateOrCreate(
                ['model_type' => $modelType, 'model_id' => $modelId, 'term' => $term],
                [
                    'count' => $data->count,
                    'aggregated_frames_count' => $data->aggregated_frames_count,
                    'sum_attention' => $data->sum_attention,

                    'sum_emotions_angry' => $data->sum_emotions_angry,
                    'sum_emotions_disgusted' => $data->sum_emotions_disgusted,
                    'sum_emotions_fearful' => $data->sum_emotions_fearful,
                    'sum_emotions_happy' => $data->sum_emotions_happy,
                    'sum_emotions_neutral' => $data->sum_emotions_neutral,
                    'sum_emotions_sad' => $data->sum_emotions_sad,
                    'sum_emotions_surprised' => $data->sum_emotions_surprised,
                    'avg_attention' => $data->sum_attention / $data->count,
                    'avg_emotions_angry' => $data->sum_emotions_angry / $data->count,
                    'avg_emotions_disgusted' => $data->sum_emotions_disgusted / $data->count,
                    'avg_emotions_fearful' => $data->sum_emotions_fearful / $data->count,
                    'avg_emotions_happy' => $data->sum_emotions_happy / $data->count,
                    'avg_emotions_neutral' => $data->sum_emotions_neutral / $data->count,
                    'avg_emotions_sad' => $data->sum_emotions_sad / $data->count,
                    'avg_emotions_surprised' => $data->sum_emotions_surprised / $data->count,

                    'max_emotion' => $maxEmotion,
                    'max_emotion_value' => $emotions->get($maxEmotion) / $data->count,
                    'last_frame_at' => $data->last_frame_at,
                ]
            );

            AggregatedFrame::query()
                ->where('model_type', $modelType)
                ->where('model_id', $modelId)
                ->where('term', $term)
                ->whereNull('term_analytic_id')
                ->update([
                    'term_analytic_id' => $termAnalytic->getKey(),
                ]);
        });
    }

    public function termAnalyticsList(string $modelType, TermAnalyticsFilterListDto $criteriaDto, PageDto $pageDto, OrderDto $orderDto): LengthAwarePaginator
    {
        return $this->termAnalyticsRepository->findByCriteria($modelType, $criteriaDto, $pageDto->getPerPage(), $orderDto);
    }

    public function modelAnalyticsForTerm(int $termAnalyticId): AggregatedFrame
    {
        $pgsql = DB::connection()->getPdo()->getAttribute(\PDO::ATTR_DRIVER_NAME) === 'pgsql';

        if ($pgsql) {
            $selectRaw = "
                term,

                SUM(count) as total_count,

                SUM(sum_attention) / NULLIF(SUM(count)::numeric,0) as avg_attention,
                SUM(sum_emotions_angry) / NULLIF(SUM(count)::numeric,0) as avg_emotions_angry,
                SUM(sum_emotions_disgusted) / NULLIF(SUM(count)::numeric,0) as avg_emotions_disgusted,
                SUM(sum_emotions_fearful) / NULLIF(SUM(count)::numeric,0) as avg_emotions_fearful,
                SUM(sum_emotions_happy) / NULLIF(SUM(count)::numeric,0) as avg_emotions_happy,
                SUM(sum_emotions_neutral) / NULLIF(SUM(count)::numeric,0) as avg_emotions_neutral,
                SUM(sum_emotions_sad) / NULLIF(SUM(count)::numeric,0) as avg_emotions_sad,
                SUM(sum_emotions_surprised) / NULLIF(SUM(count)::numeric,0) as avg_emotions_surprised
            ";
        } else {
            $selectRaw = "
                term,

                SUM(count) as total_count,

                SUM(sum_attention) / NULLIF(SUM(count), 0) as avg_attention,
                SUM(sum_emotions_angry) / NULLIF(SUM(count), 0) as avg_emotions_angry,
                SUM(sum_emotions_disgusted) / NULLIF(SUM(count), 0) as avg_emotions_disgusted,
                SUM(sum_emotions_fearful) / NULLIF(SUM(count), 0) as avg_emotions_fearful,
                SUM(sum_emotions_happy) / NULLIF(SUM(count), 0) as avg_emotions_happy,
                SUM(sum_emotions_neutral) / NULLIF(SUM(count), 0) as avg_emotions_neutral,
                SUM(sum_emotions_sad) / NULLIF(SUM(count), 0) as avg_emotions_sad,
                SUM(sum_emotions_surprised) / NULLIF(SUM(count), 0) as avg_emotions_surprised
            ";
        }

        return AggregatedFrame::query()
            ->selectRaw($selectRaw)
            ->where('term_analytic_id', $termAnalyticId)
            ->groupBy('term')
            ->first();
    }

    public function modelAnalytics(string $modelType, int $modelId, ?int $term = null): Collection|AggregatedFrame
    {
        $pgsql = DB::connection()->getPdo()->getAttribute(\PDO::ATTR_DRIVER_NAME) === 'pgsql';

        if ($pgsql) {
            $selectRaw = "
                term,

                SUM(count) as total_count,

                SUM(sum_attention) / NULLIF(SUM(count)::numeric,0) as avg_attention,
                SUM(sum_emotions_angry) / NULLIF(SUM(count)::numeric,0) as avg_emotions_angry,
                SUM(sum_emotions_disgusted) / NULLIF(SUM(count)::numeric,0) as avg_emotions_disgusted,
                SUM(sum_emotions_fearful) / NULLIF(SUM(count)::numeric,0) as avg_emotions_fearful,
                SUM(sum_emotions_happy) / NULLIF(SUM(count)::numeric,0) as avg_emotions_happy,
                SUM(sum_emotions_neutral) / NULLIF(SUM(count)::numeric,0) as avg_emotions_neutral,
                SUM(sum_emotions_sad) / NULLIF(SUM(count)::numeric,0) as avg_emotions_sad,
                SUM(sum_emotions_surprised) / NULLIF(SUM(count)::numeric,0) as avg_emotions_surprised
            ";
        } else {
            $selectRaw = "
                term,

                SUM(count) as total_count,

                SUM(sum_attention) / NULLIF(SUM(count), 0) as avg_attention,
                SUM(sum_emotions_angry) / NULLIF(SUM(count), 0) as avg_emotions_angry,
                SUM(sum_emotions_disgusted) / NULLIF(SUM(count), 0) as avg_emotions_disgusted,
                SUM(sum_emotions_fearful) / NULLIF(SUM(count), 0) as avg_emotions_fearful,
                SUM(sum_emotions_happy) / NULLIF(SUM(count), 0) as avg_emotions_happy,
                SUM(sum_emotions_neutral) / NULLIF(SUM(count), 0) as avg_emotions_neutral,
                SUM(sum_emotions_sad) / NULLIF(SUM(count), 0) as avg_emotions_sad,
                SUM(sum_emotions_surprised) / NULLIF(SUM(count), 0) as avg_emotions_surprised
            ";
        }

        $query = AggregatedFrame::query()
            ->selectRaw($selectRaw)
            ->where('model_type', $modelType)
            ->where('model_id', $modelId);

        if ($term) {
            return $query->where(
                'term',
                Carbon::createFromTimestamp($term)
            )
                ->groupBy('term')
                ->orderBy('term')
                ->first();
        }

        return $query
            ->groupBy('term')
            ->orderBy('term')
            ->get();
    }
}
