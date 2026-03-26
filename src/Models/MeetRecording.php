<?php

namespace EscolaLms\Recommender\Models;

use EscolaLms\Recommender\Database\Factories\MeetRecordingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="MeetRecording",
 *     required={"model_type", "model_id", "term"},
 *     @OA\Property(
 *           property="id",
 *           description="id",
 *           type="integer",
 *       ),
 *     @OA\Property(
 *           property="model_type",
 *           description="model_type",
 *           type="string",
 *       ),
 *     @OA\Property(
 *            property="model_id",
 *            description="model_id",
 *            type="integer",
 *        ),
 *     @OA\Property(
 *            property="term",
 *            description="term",
 *            type="timestamp",
 *        ),
 *     @OA\Property(
 *            property="start_at",
 *            description="start_at",
 *            type="datetime",
 *        ),
 *     @OA\Property(
 *             property="end_at",
 *             description="end_at",
 *             type="datetime",
 *         ),
 *     @OA\Property(
 *            property="url",
 *            description="url",
 *            type="string",
 *        ),
 *     @OA\Property(
 *            property="url_expiration_time_millis",
 *            description="url_expiration_time_millis",
 *            type="integer",
 *        ),
 * )
 */
class MeetRecording extends Model
{
    use HasFactory;

    protected $fillable = [
        'model_type',
        'model_id',
        'term',
        'start_at',
        'end_at',
        'url',
        'url_expiration_time_millis',
    ];

    protected $casts = [
        'term' => 'datetime',
        'time' => 'datetime',
    ];

    protected static function newFactory(): MeetRecordingFactory
    {
        return MeetRecordingFactory::new();
    }
}
