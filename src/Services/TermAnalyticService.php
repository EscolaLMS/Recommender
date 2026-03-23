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
        $termAnalytic = TermAnalytic::query()->where('model_type', $modelType)
            ->where('model_id', $modelId)
            ->where('term', $term)
            ->first();
        $query = AggregatedFrame::query()->where('model_type', $modelType)->where('model_id', $modelId)->where('term', $term)->whereNull('term_analytic_id');

        $framesQuery = AggregatedFrame::query()
            ->whereNull('term_analytic_id')
            ->where('model_type', $modelType)
            ->where('model_id', $modelId)
            ->where('term', $term);

        $data = $query
            ->selectRaw("
                SUM(count) as count,
                COUNT(*) as aggregated_frames_count,
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

        if (!$data) {
            return;
        }

        if (!$termAnalytic) {
            $termAnalytic = new TermAnalytic();

            $termAnalytic->model_type = $modelType;
            $termAnalytic->model_id = $modelId;
            $termAnalytic->term = $term;
            $termAnalytic->count = 0;
            $termAnalytic->aggregated_frames_count = 0;
            $termAnalytic->sum_attention = 0;
            $termAnalytic->sum_emotions_angry = 0;
            $termAnalytic->sum_emotions_disgusted = 0;
            $termAnalytic->sum_emotions_fearful = 0;
            $termAnalytic->sum_emotions_happy = 0;
            $termAnalytic->sum_emotions_neutral = 0;
            $termAnalytic->sum_emotions_sad = 0;
            $termAnalytic->sum_emotions_surprised = 0;
        }

        $termAnalytic->count += $data->count;
        $termAnalytic->aggregated_frames_count += $data->aggregated_frames_count;
        $termAnalytic->sum_attention += $data->sum_attention;
        $termAnalytic->sum_emotions_angry += $data->sum_emotions_angry;
        $termAnalytic->sum_emotions_disgusted += $data->sum_emotions_disgusted;
        $termAnalytic->sum_emotions_fearful += $data->sum_emotions_fearful;
        $termAnalytic->sum_emotions_happy += $data->sum_emotions_happy;
        $termAnalytic->sum_emotions_neutral += $data->sum_emotions_neutral;
        $termAnalytic->sum_emotions_sad += $data->sum_emotions_sad;
        $termAnalytic->sum_emotions_surprised += $data->sum_emotions_surprised;
        $termAnalytic->last_frame_at = $data->last_frame_at;
        $termAnalytic->avg_attention = $termAnalytic->sum_attention / $termAnalytic->count;
        $termAnalytic->avg_emotions_angry = $termAnalytic->sum_emotions_angry / $termAnalytic->count;
        $termAnalytic->avg_emotions_disgusted = $termAnalytic->sum_emotions_disgusted / $termAnalytic->count;
        $termAnalytic->avg_emotions_fearful = $termAnalytic->sum_emotions_fearful / $termAnalytic->count;
        $termAnalytic->avg_emotions_happy = $termAnalytic->sum_emotions_happy / $termAnalytic->count;
        $termAnalytic->avg_emotions_neutral = $termAnalytic->sum_emotions_neutral / $termAnalytic->count;
        $termAnalytic->avg_emotions_sad = $termAnalytic->sum_emotions_sad / $termAnalytic->count;
        $termAnalytic->avg_emotions_surprised = $termAnalytic->sum_emotions_surprised / $termAnalytic->count;

        $emotions = collect([
            EmotionsEnum::ANGRY => $termAnalytic->avg_emotions_angry,
            EmotionsEnum::DISGUSTED => $termAnalytic->avg_emotions_,
            EmotionsEnum::FEARFUL => $termAnalytic->avg_emotions_fearful,
            EmotionsEnum::HAPPY => $termAnalytic->avg_emotions_happy,
            EmotionsEnum::NEUTRAL => $termAnalytic->avg_emotions_neutral,
            EmotionsEnum::SAD => $termAnalytic->avg_emotions_sad,
            EmotionsEnum::SURPRISED => $termAnalytic->avg_emotions_surprised,
        ]);

        $maxEmotion = $emotions->sortDesc()->keys()->first();
        $maxEmotionValue = $emotions->get($maxEmotion);

        $termAnalytic->max_emotion = $maxEmotion;
        $termAnalytic->max_emotion_value = $maxEmotionValue;

        $termAnalytic->save();

        $framesQuery->update([
            'term_analytic_id' => $termAnalytic->getKey(),
        ]);
    }

    public function termAnalyticsList(string $modelType, TermAnalyticsFilterListDto $criteriaDto, PageDto $pageDto, OrderDto $orderDto): LengthAwarePaginator
    {
        return $this->termAnalyticsRepository->findByCriteria($modelType, $criteriaDto, $pageDto->getPerPage(), $orderDto);
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
