<?php

namespace EscolaLms\Recommender\Http\Controllers;

use EscolaLms\Core\Http\Controllers\EscolaLmsBaseController;
use EscolaLms\Recommender\Http\Controllers\Swagger\RecommenderControllerSwagger;
use EscolaLms\Recommender\Http\Requests\CourseRecommendationRequest;
use EscolaLms\Recommender\Http\Requests\TopicRecommendationRequest;
use EscolaLms\Recommender\Http\Resources\CourseRecommendationResource;
use EscolaLms\Recommender\Http\Resources\TopicRecommendationResource;
use EscolaLms\Recommender\Services\Contracts\RecommenderServiceContract;
use Illuminate\Http\JsonResponse;

class RecommenderController extends EscolaLmsBaseController implements RecommenderControllerSwagger
{
    private RecommenderServiceContract $recommenderService;

    public function __construct(RecommenderServiceContract $recommenderService)
    {
        $this->recommenderService = $recommenderService;
    }

    public function course(CourseRecommendationRequest $request, int $courseId): JsonResponse
    {
        $result = $this->recommenderService->completionOfCourse($courseId);

        return $this->sendResponseForResource(
            CourseRecommendationResource::make($result),
            __('Course recommendation retrieved successfully')
        );
    }

    public function topic(TopicRecommendationRequest $request, int $lessonId): JsonResponse
    {
        $result = $this->recommenderService->matchTopicType($lessonId);

        return $this->sendResponseForResource(
            TopicRecommendationResource::make($result),
            __('Topic recommendation retrieved successfully')
        );
    }
}
