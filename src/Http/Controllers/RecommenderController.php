<?php

namespace EscolaLms\Recommender\Http\Controllers;

use EscolaLms\Core\Http\Controllers\EscolaLmsBaseController;
use EscolaLms\Recommender\Http\Controllers\Swagger\RecommenderControllerSwagger;
use EscolaLms\Recommender\Http\Requests\AggregatedFrameListRequest;
use EscolaLms\Recommender\Http\Requests\AggregatedFrameRequest;
use EscolaLms\Recommender\Http\Requests\CourseRecommendationRequest;
use EscolaLms\Recommender\Http\Requests\TopicRecommendationRequest;
use EscolaLms\Recommender\Http\Resources\AggregatedFrameResource;
use EscolaLms\Recommender\Http\Resources\CourseRecommendationResource;
use EscolaLms\Recommender\Http\Resources\ModelAnalyticsResource;
use EscolaLms\Recommender\Http\Resources\TopicRecommendationResource;
use EscolaLms\Recommender\Services\Contracts\RecommenderServiceContract;
use EscolaLms\Recommender\Dto\AggregatedFrameDto;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;

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

    public function aggregateFrameSave(AggregatedFrameRequest $request): \Illuminate\Http\Response
    {
        $dto = new AggregatedFrameDto($request->all());
        $this->recommenderService->aggregatedFrameSave($dto);

        return Response::noContent();
    }

    public function aggregateFrames(AggregatedFrameListRequest $request, string $modelType, int $modelId, int $term): JsonResponse
    {
        $data = $this->recommenderService->aggregatedFrames($modelType, $modelId, $term, $request->get('interval'));

        return $this->sendResponseForResource(AggregatedFrameResource::collection($data), __('Aggregated Frames retrieved successfully'));
    }

    public function modelAnalytics(string $modelType, int $modelId): JsonResponse
    {
        $data = $this->recommenderService->modelAnalytics($modelType, $modelId);

        return $this->sendResponseForResource(ModelAnalyticsResource::collection($data), __('Model analytics retrieved successfully'));
    }

    public function modelTermAnalytics(string $modelType, int $modelId, int $term): JsonResponse
    {
        $data = $this->recommenderService->modelAnalytics($modelType, $modelId, $term);

        return $this->sendResponseForResource(ModelAnalyticsResource::collection($data), __('Term analytics retrieved successfully'));
    }
}
