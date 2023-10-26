<?php

namespace EscolaLms\Recommender\Tests\Feature;

use EscolaLms\Recommender\Services\Contracts\RecommenderServiceContract;
use EscolaLms\Recommender\Tests\CreatesCourse;
use EscolaLms\Recommender\Tests\TestCase;
use EscolaLms\TopicTypes\Models\TopicContent\Image;
use EscolaLms\TopicTypes\Models\TopicContent\OEmbed;
use EscolaLms\TopicTypes\Models\TopicContent\PDF;
use EscolaLms\TopicTypes\Models\TopicContent\RichText;
use EscolaLms\TopicTypes\Models\TopicContent\Video;
class RecommenderServiceTest extends TestCase
{
    use CreatesCourse;

    private RecommenderServiceContract $recommenderService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->recommenderService = app()->make(RecommenderServiceContract::class);
    }

    public function testCourseMakeData(): void
    {
        $course = $this->createCourseWithTopicTypes([
            PDF::class,
            OEmbed::class,
            Video::class,
            RichText::class,
            Image::class,
            PDF::class,
            Video::class,
        ]);

        $result = $this->recommenderService->makeCourseData($course->getKey());

        $this->assertEquals(2, $result['PDF']);
        $this->assertEquals(1, $result['OEmbed']);
        $this->assertEquals(2, $result['Video']);
        $this->assertEquals(1, $result['RichText']);
        $this->assertEquals(1, $result['Image']);
        $this->assertArrayHasKey('PDF_length', $result);
        $this->assertArrayHasKey('PDF_page', $result);
        $this->assertArrayHasKey('Video_length', $result);
        $this->assertArrayHasKey('RichText_length', $result);
        $this->assertArrayNotHasKey('Image_length', $result);
        $this->assertArrayNotHasKey('OEmbed_length', $result);
    }

    public function testTopicMakeData(): void
    {
        $topicTypes = [PDF::class, OEmbed::class, Video::class, RichText::class, Image::class, PDF::class, Video::class];
        $course = $this->createCourseWithTopicTypes($topicTypes);

        $result = $this->recommenderService->makeTopicData($course->lessons->first()->getKey());

        $this->assertEquals([
            'question_number' => count($topicTypes) + 1,
            'PDF_5' => 1.0,
            'OEmbed_4' => 1.0,
            'Video_3' => 1.0,
            'RichText_2' => 1.0,
            'Image_1' => 1.0,
        ], $result);
    }
}
