<?php

namespace EscolaLms\Recommender\Http\Controllers;

use EscolaLms\Core\Http\Controllers\EscolaLmsBaseController;
use EscolaLms\Recommender\Http\Controllers\Swagger\TermAnalyticControllerContract;
use EscolaLms\Recommender\Http\Requests\TermAnalyticListRequest;
use EscolaLms\Recommender\Http\Requests\TermAnalyticRequest;
use EscolaLms\Recommender\Http\Resources\ModelAnalyticsResource;
use EscolaLms\Recommender\Http\Resources\TermAnalyticResource;
use EscolaLms\Recommender\Services\Contracts\TermAnalyticServiceContract;
use Illuminate\Http\JsonResponse;

class TermAnalyticController extends EscolaLmsBaseController implements TermAnalyticControllerContract
{
    public function __construct(
        private TermAnalyticServiceContract $termAnalyticService
    ) {}

    public function index(TermAnalyticListRequest $request, string $modelType): JsonResponse
    {
        $terms = $this->termAnalyticService->termAnalyticsList($modelType, $request->getCriteriaDto(), $request->getPaginationDto(), $request->getOrderDto());

        return $this->sendResponseForResource(
            TermAnalyticResource::collection($terms), __('Terms analytics retrieved successfully')
        );
    }

    public function modelAnalytics(string $modelType, int $modelId): JsonResponse
    {
        $data = $this->termAnalyticService->modelAnalytics($modelType, $modelId);

        return $this->sendResponseForResource(ModelAnalyticsResource::collection($data), __('Model analytics retrieved successfully'));
    }

    public function modelTermAnalytics(TermAnalyticRequest $request, string $modelType, int $modelId, int $term): JsonResponse
    {
        $data = $this->termAnalyticService->modelAnalyticsForTerm($term);

        return $this->sendResponseForResource(ModelAnalyticsResource::make($data), __('Term analytics retrieved successfully'));
    }
}
