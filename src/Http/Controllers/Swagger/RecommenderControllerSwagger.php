<?php

namespace EscolaLms\Recommender\Http\Controllers\Swagger;

use EscolaLms\Recommender\Http\Requests\CourseRecommendationRequest;
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
     *          name="id",
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
     *          name="id",
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
}
