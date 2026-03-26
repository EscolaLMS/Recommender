<?php

namespace EscolaLms\Recommender\Http\Controllers\Swagger;

use EscolaLms\Recommender\Http\Requests\AggregatedFrameListRequest;
use EscolaLms\Recommender\Http\Requests\AggregatedFrameRequest;
use EscolaLms\Recommender\Http\Requests\CourseRecommendationRequest;
use EscolaLms\Recommender\Http\Requests\MeetRecordingRequest;
use EscolaLms\Recommender\Http\Requests\TopicRecommendationRequest;
use Illuminate\Http\JsonResponse;

interface RecommenderControllerSwagger
{
    /**
     * @OA\Get(
     *      path="/api/admin/recommender/course/{courseId}",
     *      summary="Recommendation for completion of the course.",
     *      tags={"Admin Recommender"},
     *      description="Get recommendation for completion of the course.",
     *      security={
     *          {"passport": {}},
     *      },
     *      @OA\Parameter(
     *          name="courseId",
     *          description="ID of course",
     *          @OA\Schema(
     *             type="integer",
     *         ),
     *          required=true,
     *          in="path"
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Successfull operation",
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  type="object",
     *                  @OA\Property(
     *                      property="success",
     *                      type="boolean"
     *                  ),
     *                  @OA\Property(
     *                      property="data",
     *                      type="object"
     *                  ),
     *                  @OA\Property(
     *                      property="message",
     *                      type="string"
     *                  )
     *              )
     *          )
     *      )
     * )
     */
    public function course(CourseRecommendationRequest $request, int $courseId): JsonResponse;

    /**
     * @OA\Get(
     *      path="/api/admin/recommender/lesson/{lessonId}/topic",
     *      summary="Recommendation of the next exercise in the lesson.",
     *      tags={"Admin Recommender"},
     *      description="Get recommendation of the next exercise in the lesson..",
     *      security={
     *          {"passport": {}},
     *      },
     *      @OA\Parameter(
     *          name="lessonId",
     *          description="ID of lesson",
     *          @OA\Schema(
     *             type="integer",
     *         ),
     *          required=true,
     *          in="path"
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Successfull operation",
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  type="object",
     *                  @OA\Property(
     *                      property="success",
     *                      type="boolean"
     *                  ),
     *                  @OA\Property(
     *                      property="data",
     *                      type="object"
     *                  ),
     *                  @OA\Property(
     *                      property="message",
     *                      type="string"
     *                  )
     *              )
     *          )
     *      )
     * )
     */
    public function topic(TopicRecommendationRequest $request, int $lessonId): JsonResponse;

    public function aggregateFrameSave(AggregatedFrameRequest $request): \Illuminate\Http\Response;

    /**
     * @OA\Get(
     *      path="/api/admin/recommender/aggregated-frames/{modelType}/{modelId}/{term}",
     *      summary="Aggregated Frames with interval",
     *      tags={"Admin Recommender"},
     *      description="Get aggregated frames for model and term with interval",
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
     *     @OA\Parameter(
     *           name="interval",
     *           description="Interval",
     *           @OA\Schema(
     *              type="integer",
     *          ),
     *           required=true,
     *           in="query"
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
    public function aggregateFrames(AggregatedFrameListRequest $request, string $modelType, int $modelId, int $term): JsonResponse;

    /**
     * @OA\Post (
     *      path="/api/admin/recommender/meet-recordings",
     *      summary="Meet recordings",
     *      tags={"Admin Recommender"},
     *      description="Set meet recording time start or stop",
     *      security={
     *          {"passport": {}},
     *      },
     *     @OA\RequestBody(
     *           required=true,
     *           @OA\MediaType(
     *               mediaType="application/json",
     *               @OA\Schema(ref="#/components/schemas/MeetRecording")
     *           ),
     *       ),
     *      @OA\Response(
     *           response=201,
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
    public function meetRecordings(MeetRecordingRequest $request): JsonResponse;
}
