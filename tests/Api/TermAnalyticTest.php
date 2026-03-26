<?php

namespace EscolaLms\Recommender\Tests\Api;

use App\Models\Consultation;
use EscolaLms\Consultations\Database\Seeders\ConsultationsPermissionSeeder;
use EscolaLms\Core\Tests\CreatesUsers;
use EscolaLms\Recommender\Jobs\RebuildTermAnalyticJob;
use EscolaLms\Recommender\Models\AggregatedFrame;
use EscolaLms\Recommender\Models\TermAnalytic;
use EscolaLms\Recommender\Services\Contracts\TermAnalyticServiceContract;
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

        $aggregatedFrame = AggregatedFrame::factory()->create([
            'model_type' => $modelType,
            'model_id' => $modelId,
            'term' => $term,
            'sum_attention' => 1,
            'count' => 1,
            'sum_emotions_happy' => 0.6,
            'sum_emotions_sad' => 0.4,
        ]);

        $job = new RebuildTermAnalyticJob();
        $service = app(TermAnalyticServiceContract::class);
        $job->handle($service);

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
            'aggregated_frames_count' => 1,
        ]);

        $aggregatedFrame = AggregatedFrame::factory()->create([
            'model_type' => $modelType,
            'model_id' => $modelId,
            'term' => $term,
            'sum_attention' => 1,
            'count' => 1,
            'sum_emotions_happy' => 0.6,
            'sum_emotions_sad' => 0.4,
            'term_analytic_id' => $termAnalytic->getKey(),
        ]);

        $aggregatedFrame = AggregatedFrame::factory()->create([
            'model_type' => $modelType,
            'model_id' => $modelId,
            'term' => $term,
            'sum_attention' => 1,
            'count' => 1,
            'sum_emotions_happy' => 0.5,
            'sum_emotions_sad' => 0.5,
        ]);

        $job = new RebuildTermAnalyticJob();
        $service = app(TermAnalyticServiceContract::class);
        $job->handle($service);

        sleep(1);

        $aggregatedFrame = AggregatedFrame::factory()->create([
            'model_type' => $modelType,
            'model_id' => $modelId,
            'term' => $term,
            'sum_attention' => 1,
            'count' => 3,
            'sum_emotions_happy' => 0.5,
            'sum_emotions_sad' => 0.5,
        ]);

        $job = new RebuildTermAnalyticJob();
        $service = app(TermAnalyticServiceContract::class);
        $job->handle($service);

        $this->assertDatabaseHas('term_analytics', [
            'id' => $termAnalytic->getKey(),
            'model_type' => $modelType,
            'model_id' => $modelId,
            'term' => $term,
            'count' => 5,
            'sum_attention' => '3.0000000000000000000000000',
            'sum_emotions_happy' => '1.6000000000000000000000000',
            'sum_emotions_sad' => '1.4000000000000000000000000',
            'aggregated_frames_count' => 3,
        ]);
    }

    public function testModelAnalyticsReturnsAllTerms(): void
    {
        $modelType = 'consultation';
        $modelId = 1;

        $term1 = Carbon::now()->subDay();
        $term2 = Carbon::now();

        AggregatedFrame::factory()->count(5)->create([
            'model_type' => $modelType,
            'model_id' => $modelId,
            'term' => $term1,
            'sum_attention' => 1,
            'count' => 1,
            'sum_emotions_happy' => 0.5,
            'sum_emotions_neutral' => 0.5,
        ]);

        AggregatedFrame::factory()->count(7)->create([
            'model_type' => $modelType,
            'model_id' => $modelId,
            'term' => $term2,
            'sum_attention' => 2,
            'count' => 2,
            'sum_emotions_happy' => 0.7,
            'sum_emotions_neutral' => 0.3,
        ]);

        $response = $this->actingAs($this->makeAdmin(), 'api')->getJson("api/admin/recommender/analytics/{$modelType}/{$modelId}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'term',
                        'attention',
                        'max_emotion',
                        'max_emotion_percentage',
                    ],
                ]
            ]);

        $json = $response->json('data');

        $this->assertCount(2, $json);
    }

    public function testModelTermAnalyticsReturnsSingleTerm(): void
    {
        $consultation = Consultation::factory()->create();
        $modelType = 'consultation';
        $modelId = $consultation->getKey();
        $term = Carbon::now();

        $termAnalytic = TermAnalytic::factory()->create([
            'model_type' => $modelType,
            'model_id' => $modelId,
            'term' => $term,
            'sum_attention' => 1,
            'count' => 1,
            'sum_emotions_happy' => 0.6,
            'sum_emotions_sad' => 0.4,
            'aggregated_frames_count' => 1,
        ]);

        AggregatedFrame::factory()->create([
            'model_type' => $modelType,
            'model_id' => $modelId,
            'term' => $term,
            'sum_attention' => 1,
            'count' => 1,
            'sum_emotions_happy' => 0.6,
            'sum_emotions_sad' => 0.4,
            'term_analytic_id' => $termAnalytic->getKey(),
        ]);

        $response = $this->actingAs($this->makeAdmin(), 'api')->getJson("api/admin/recommender/analytics/{$modelType}/{$modelId}/" . $termAnalytic->getKey());

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'term',
                    'attention',
                    'max_emotion',
                    'max_emotion_percentage',
                ],
            ]);
    }
}
