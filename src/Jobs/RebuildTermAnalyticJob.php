<?php

namespace EscolaLms\Recommender\Jobs;

use EscolaLms\Recommender\Models\AggregatedFrame;
use EscolaLms\Recommender\Services\Contracts\TermAnalyticServiceContract;
use Illuminate\Contracts\Queue\ShouldQueue;

class RebuildTermAnalyticJob implements ShouldQueue
{
    public function handle(TermAnalyticServiceContract $termAnalyticService): void
    {
        $terms = AggregatedFrame::query()
            ->select('model_type', 'model_id', 'term')
            ->whereNull('term_analytic_id')
            ->groupBy('model_type', 'model_id', 'term')
            ->get();

        foreach ($terms as $term) {
            $termAnalyticService->rebuildTermAnalytic($term->model_type, $term->model_id, $term->term);
        }
    }
}
