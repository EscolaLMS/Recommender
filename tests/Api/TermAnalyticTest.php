<?php

namespace EscolaLms\Recommender\Tests\Api;

use EscolaLms\Core\Tests\CreatesUsers;
use EscolaLms\Recommender\Jobs\RebuildTermAnalyticJob;
use EscolaLms\Recommender\Models\AggregatedFrame;
use EscolaLms\Recommender\Models\TermAnalytic;
use EscolaLms\Recommender\Tests\CreatesCourse;
use EscolaLms\Recommender\Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Carbon;

class TermAnalyticTest extends TestCase
{
    use CreatesCourse, CreatesUsers, WithFaker;

    public function testTermAnalyticList(): void
    {
        $modelType = 'consultation';
        $modelId = 1;
        $term = Carbon::now();

        TermAnalytic::factory()->create([
            'model_type' => $modelType,
            'model_id' => $modelId,
            'term' => $term,
            'sum_attention' => 1,
            'count' => 1,
            'aggregated_frame_count' => 1,
            'sum_emotions_happy' => 0.6,
            'sum_emotions_sad' => 0.4,
        ]);

        $this
            ->actingAs($this->makeAdmin(), 'api')
            ->getJson("api/admin/recommender/terms/{$modelType}")
            ->assertOk();
    }

    public function testRebuildTermAnalytic(): void
    {
        $modelType = 'consultation';
        $modelId = 1;
        $term = Carbon::now();

        $aggregatedFrame = AggregatedFrame::factory()->count(5)->create([
            'model_type' => $modelType,
            'model_id' => $modelId,
            'term' => $term,
            'sum_attention' => 1,
            'count' => 1,
            'sum_emotions_happy' => 0.6,
            'sum_emotions_sad' => 0.4,
        ]);

        dispatch_sync(new RebuildTermAnalyticJob());

        $this->assertDatabaseHas('term_analytics', [
            'model_type' => $modelType,
            'model_id' => $modelId,
            'term' => $term,
            'sum_attention' => 1,
            'count' => 1,
            'sum_emotions_happy' => 0.6,
            'sum_emotions_sad' => 0.4,
            'aggregated_frame_count' => 1,
        ]);
    }

    public function testRebuildTermAnalyticExisting(): void
    {
        $modelType = 'consultation';
        $modelId = 1;
        $term = Carbon::now();

        $termAnalytic = TermAnalytic::factory()->create([
            'model_type' => $modelType,
            'model_id' => $modelId,
            'term' => $term,
            'sum_attention' => 1,
            'count' => 1,
            'sum_emotions_happy' => 0.6,
            'sum_emotions_sad' => 0.4,
        ]);

        $aggregatedFrame = AggregatedFrame::factory()->count(5)->create([
            'model_type' => $modelType,
            'model_id' => $modelId,
            'term' => $term,
            'sum_attention' => 1,
            'count' => 1,
            'sum_emotions_happy' => 0.6,
            'sum_emotions_sad' => 0.4,
            'term_analytic_id' => $termAnalytic->getKey(),
        ]);

        $aggregatedFrame = AggregatedFrame::factory()->count(5)->create([
            'model_type' => $modelType,
            'model_id' => $modelId,
            'term' => $term,
            'sum_attention' => 1,
            'count' => 1,
            'sum_emotions_happy' => 0.5,
            'sum_emotions_sad' => 0.5,
            'term_analytic_id' => $termAnalytic->getKey(),
        ]);

        dispatch_sync(new RebuildTermAnalyticJob());

        $this->assertDatabaseHas('term_analytics', [
            'id' => $termAnalytic->getKey(),
            'model_type' => $modelType,
            'model_id' => $modelId,
            'term' => $term,
            'sum_attention' => 2,
            'count' => 2,
            'sum_emotions_happy' => 1.1,
            'sum_emotions_sad' => 0.9,
            'aggregated_frame_count' => 2,
        ]);
    }
}
