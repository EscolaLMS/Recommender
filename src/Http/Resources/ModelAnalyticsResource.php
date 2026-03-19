<?php

namespace EscolaLms\Recommender\Http\Resources;

use EscolaLms\Recommender\Enum\EmotionsEnum;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *      schema="ModelAnalytics",
 *      @OA\Property(
 *          property="term",
 *          description="term",
 *          type="datetime",
 *      ),
 *      @OA\Property(
 *          property="attention",
 *          description="attention",
 *          type="numeric",
 *      ),
 *      @OA\Property(
 *          property="max_emotion",
 *          description="max_emotion",
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="max_emotion_percentage",
 *          description="max_emotion_percentage",
 *          type="numeric",
 *      ),
 *      @OA\Property(
 *           property="avg_angry",
 *           description="avg_angry",
 *           type="numeric",
 *       ),
 *     @OA\Property(
 *            property="avg_disgusted",
 *            description="avg_disgusted",
 *            type="numeric",
 *        ),
 *     @OA\Property(
 *            property="avg_fearful",
 *            description="avg_fearful",
 *            type="numeric",
 *        ),
 *     @OA\Property(
 *            property="avg_happy",
 *            description="avg_happy",
 *            type="numeric",
 *        ),
 *     @OA\Property(
 *            property="avg_neutral",
 *            description="avg_neutral",
 *            type="numeric",
 *        ),
 *     @OA\Property(
 *            property="avg_sad",
 *            description="avg_sad",
 *            type="numeric",
 *        ),
 *     @OA\Property(
 *            property="avg_surprised",
 *            description="avg_surprised",
 *            type="numeric",
 *        ),
 * )
 */
class ModelAnalyticsResource extends JsonResource
{
    public function toArray($request): array
    {
        $emotions = [
            EmotionsEnum::ANGRY => $this->avg_emotions_angry,
            EmotionsEnum::DISGUSTED => $this->avg_emotions_disgusted,
            EmotionsEnum::FEARFUL => $this->avg_emotions_fearful,
            EmotionsEnum::HAPPY => $this->avg_emotions_happy,
            EmotionsEnum::NEUTRAL => $this->avg_emotions_neutral,
            EmotionsEnum::SAD => $this->avg_emotions_sad,
            EmotionsEnum::SURPRISED => $this->avg_emotions_surprised,
        ];

        $maxEmotion = null;
        $maxValue = -1;
        foreach ($emotions as $emotion => $value) {
            if ($value > $maxValue) {
                $maxValue = $value;
                $maxEmotion = $emotion;
            }
        }

        return array_merge([
            'attention' => $this->avg_attention,
            'max_emotion' => $maxEmotion,
            'max_emotion_percentage' => $maxValue,
            'term' => $this->term,
        ], $emotions);
    }
}
