<?php

namespace EscolaLms\Recommender\Tests\Api;

use EscolaLms\Core\Tests\CreatesUsers;
use EscolaLms\Courses\Database\Seeders\CoursesPermissionSeeder;
use EscolaLms\Recommender\EscolaLmsRecommenderServiceProvider;
use EscolaLms\Recommender\Tests\CreatesCourse;
use EscolaLms\Recommender\Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

class RecommenderControllerTest extends TestCase
{
    use CreatesCourse, CreatesUsers, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CoursesPermissionSeeder::class);

        Config::set(EscolaLmsRecommenderServiceProvider::CONFIG_KEY . '.course_model', '{"model": "course"}');
        Config::set(EscolaLmsRecommenderServiceProvider::CONFIG_KEY . '.exercise_model', '{"model": "exercise"}');
        Config::set(EscolaLmsRecommenderServiceProvider::CONFIG_KEY . '.enabled', true);
    }

    public function testCourseRecommendation(): void
    {
        Http::fakeSequence()
            ->push(['data' => [
                'value' => $this->faker->randomFloat()]
            ]);

        $this
            ->actingAs($this->makeAdmin(), 'api')
            ->getJson('api/admin/recommender/course/' . $this->createCourseWithStructure()->getKey())
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'probability'
                ]
            ]);
    }

    public function testCourseRecommendationApiDisabled(): void
    {
        Config::set(EscolaLmsRecommenderServiceProvider::CONFIG_KEY . '.api_url');

        $this
            ->actingAs($this->makeAdmin(), 'api')
            ->getJson('api/admin/recommender/course/' . $this->createCourseWithStructure()->getKey())
            ->assertUnprocessable()
            ->assertJson([
                'message' => 'Recommender API URL is not set!'
            ]);
    }

    public function testCourseRecommendationCourseNotFound(): void
    {
        $this
            ->actingAs($this->makeAdmin(), 'api')
            ->getJson('api/admin/recommender/course/1')
            ->assertNotFound();
    }

    public function testCourseRecommendationForbidden(): void
    {
        $this
            ->actingAs($this->makeStudent(), 'api')
            ->getJson('api/admin/recommender/course/' . $this->createCourse()->getKey())
            ->assertForbidden();
    }

    public function testCourseRecommendationUnauthorized(): void
    {
        $this
            ->getJson('api/admin/recommender/course/' . $this->createCourse()->getKey())
            ->assertUnauthorized();
    }

    public function testTopicRecommendation(): void
    {
        Http::fakeSequence()
            ->push([
                'data' => [
                    'key' => $this->faker->word,
                    'value' => $this->faker->randomFloat()
                ]
            ]);

        $this
            ->actingAs($this->makeAdmin(), 'api')
            ->getJson('api/admin/recommender/lesson/' . $this->createCourseWithStructure()->lessons->first()->getKey() . '/topic')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'topic_type',
                    'probability',
                ]
            ]);
    }

    public function testTopicRecommendationApiDisabled(): void
    {
        Config::set(EscolaLmsRecommenderServiceProvider::CONFIG_KEY . '.api_url');

        $this
            ->actingAs($this->makeAdmin(), 'api')
            ->getJson('api/admin/recommender/lesson/' . $this->createCourseWithStructure()->lessons->first()->getKey() . '/topic')
            ->assertUnprocessable()
            ->assertJson([
                'message' => 'Recommender API URL is not set!'
            ]);
    }

    public function testTopicRecommendationLessonNotFoundForbidden(): void
    {
        $this
            ->actingAs($this->makeAdmin(), 'api')
            ->getJson('api/admin/recommender/lesson/1/topic')
            ->assertNotFound();
    }

    public function testTopicRecommendationForbidden(): void
    {
        $this
            ->actingAs($this->makeStudent(), 'api')
            ->getJson('api/admin/recommender/lesson/' . $this->createCourseWithStructure()->lessons->first()->getKey() . '/topic')
            ->assertForbidden();
    }

    public function testTopicRecommendationUnauthorized(): void
    {
        $this
            ->getJson('api/admin/recommender/lesson/' . $this->createCourseWithStructure()->lessons->first()->getKey() . '/topic')
            ->assertUnauthorized();
    }

    public function testRecommenderDisabled(): void
    {
        Config::set(EscolaLmsRecommenderServiceProvider::CONFIG_KEY . '.enabled', false);

        $this
            ->actingAs($this->makeAdmin(), 'api')
            ->getJson('api/admin/recommender/course/' . $this->createCourseWithStructure()->getKey())
            ->assertUnprocessable()
            ->assertJson([
                'message' => 'Recommender is disabled.'
            ]);

        $this
            ->actingAs($this->makeAdmin(), 'api')
            ->getJson('api/admin/recommender/lesson/' . $this->createCourseWithStructure()->lessons->first()->getKey() . '/topic')
            ->assertUnprocessable()
            ->assertJson([
                'message' => 'Recommender is disabled.'
            ]);
    }
}
