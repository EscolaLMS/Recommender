<?php

namespace EscolaLms\Recommender\Http\Controllers\Swagger;

use EscolaLms\Recommender\Http\Requests\TermAnalyticListRequest;
use Illuminate\Http\JsonResponse;

interface TermAnalyticControllerContract
{

    /**
     * @OA\Get(
     *      path="/api/admin/recommender/terms/{modelType}",
     *      summary="Model type terms analytics",
     *      tags={"Admin Recommender"},
     *      description="Get Model type terms analytics",
     *      security={
     *          {"passport": {}},
     *      },
     *     @OA\Parameter(
     *           name="modelType",
     *           description="Model type",
     *           @OA\Schema(
     *              type="string",
     *          ),
     *           required=true,
     *           in="path"
     *       ),
     *     @OA\Parameter(
     *            name="name",
     *            description="Model name",
     *            @OA\Schema(
     *               type="string",
     *           ),
     *            required=false,
     *            in="query"
     *        ),
     *     @OA\Parameter(
     *             name="term_from",
     *             description="Term date from",
     *             @OA\Schema(
     *                type="string",
     *            ),
     *             required=false,
     *             in="query"
     *         ),
     *     @OA\Parameter(
     *             name="term_to",
     *             description="Term date to",
     *             @OA\Schema(
     *                type="string",
     *            ),
     *             required=false,
     *             in="query"
     *         ),
     *      @OA\Response(
     *           response=200,
     *           description="successful operation",
     *           @OA\MediaType(
     *               mediaType="application/json"
     *           ),
     *           @OA\Schema(
     *               type="object",
     *               @OA\Property(
     *                   property="success",
     *                   type="boolean"
     *               ),
     *               @OA\Property(
     *                   property="data",
     *                   type="array",
     *                   @OA\Items(ref="#/components/schemas/TermAnalytic")
     *               ),
     *               @OA\Property(
     *                   property="message",
     *                   type="string"
     *               )
     *           )
     *       )
     * )
     */
    public function index(TermAnalyticListRequest $request, string $modelType): JsonResponse;

    /**
     * @OA\Get(
     *      path="/api/admin/recommender/analytics/{modelType}/{modelId}",
     *      summary="Model analytics",
     *      tags={"Admin Recommender"},
     *      description="Get model analytics",
     *      security={
     *          {"passport": {}},
     *      },
     *     @OA\Parameter(
     *           name="modelType",
     *           description="Model type",
     *           @OA\Schema(
     *              type="string",
     *          ),
     *           required=true,
     *           in="path"
     *       ),
     *     @OA\Parameter(
     *           name="modelId",
     *           description="ID of model",
     *           @OA\Schema(
     *              type="integer",
     *          ),
     *           required=true,
     *           in="path"
     *       ),
     *      @OA\Response(
     *           response=200,
     *           description="successful operation",
     *           @OA\MediaType(
     *               mediaType="application/json"
     *           ),
     *           @OA\Schema(
     *               type="object",
     *               @OA\Property(
     *                   property="success",
     *                   type="boolean"
     *               ),
     *               @OA\Property(
     *                   property="data",
     *                   type="array",
     *                   @OA\Items(ref="#/components/schemas/AggregatedFrame")
     *               ),
     *               @OA\Property(
     *                   property="message",
     *                   type="string"
     *               )
     *           )
     *       )
     * )
     */
    public function modelAnalytics(string $modelType, int $modelId): JsonResponse;

    /**
     * @OA\Get(
     *      path="/api/admin/recommender/analytics/{modelType}/{modelId}/{term}",
     *      summary="Model term analytics",
     *      tags={"Admin Recommender"},
     *      description="Get Model term analytics",
     *      security={
     *          {"passport": {}},
     *      },
     *     @OA\Parameter(
     *           name="modelType",
     *           description="Model type",
     *           @OA\Schema(
     *              type="string",
     *          ),
     *           required=true,
     *           in="path"
     *       ),
     *     @OA\Parameter(
     *           name="modelId",
     *           description="ID of model",
     *           @OA\Schema(
     *              type="integer",
     *          ),
     *           required=true,
     *           in="path"
     *       ),
     *      @OA\Parameter(
     *          name="term",
     *          description="Model term",
     *          @OA\Schema(
     *             type="integer",
     *         ),
     *          required=true,
     *          in="path"
     *      ),
     *      @OA\Response(
     *           response=200,
     *           description="successful operation",
     *           @OA\MediaType(
     *               mediaType="application/json"
     *           ),
     *           @OA\Schema(
     *               type="object",
     *               @OA\Property(
     *                   property="success",
     *                   type="boolean"
     *               ),
     *               @OA\Property(
     *                   property="data",
     *                   type="array",
     *                   @OA\Items(ref="#/components/schemas/AggregatedFrame")
     *               ),
     *               @OA\Property(
     *                   property="message",
     *                   type="string"
     *               )
     *           )
     *       )
     * )
     */
    public function modelTermAnalytics(string $modelType, int $modelId, int $term): JsonResponse;
}
