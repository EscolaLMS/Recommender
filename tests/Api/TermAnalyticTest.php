<?php

namespace EscolaLms\Recommender\Tests\Api;

use EscolaLms\Consultations\Database\Seeders\ConsultationsPermissionSeeder;
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

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(ConsultationsPermissionSeeder::class);
    }

    public function testTermAnalyticList(): void
    {
        $modelType = 'consultation';
        $modelId = 21;
        $term = Carbon::now();

        TermAnalytic::factory()->create([
            'model_type' => $modelType,
            'model_id' => $modelId,
            'term' => $term,
            'sum_attention' => 1,
            'count' => 1,
            'aggregated_frames_count' => 1,
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
        $modelId = 37;
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
            'sum_attention' => '1.0000000000000000000000000',
            'count' => 1,
            'sum_emotions_happy' => '0.6000000000000000000000000',
            'sum_emotions_sad' => '0.4000000000000000000000000',
            'aggregated_frames_count' => 1,
        ]);
    }

    public function testRebuildTermAnalyticExisting(): void
    {
        $modelType = 'consultation';
        $modelId = 2137;
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
            'count' => 2,
            'sum_attention' => '2.0000000000000000000000000',
            'sum_emotions_happy' => '1.1000000000000000000000000',
            'sum_emotions_sad' => '0.9000000000000000000000000',
            'aggregated_frames_count' => 2,
        ]);
    }
}
