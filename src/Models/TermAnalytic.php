<?php

namespace EscolaLms\Recommender\Models;

use EscolaLms\Consultations\Models\Consultation;
use EscolaLms\Recommender\Database\Factories\TermAnalyticFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TermAnalytic extends Model
{
    use HasFactory;

    protected $fillable = [
        'model_type',
        'model_id',
        'term',
        'count',
        'sum_attention',
        'sum_emotions_angry',
        'sum_emotions_disgusted',
        'sum_emotions_fearful',
        'sum_emotions_happy',
        'sum_emotions_neutral',
        'sum_emotions_sad',
        'sum_emotions_surprised',
        'avg_attention',
        'avg_emotions_angry',
        'avg_emotions_disgusted',
        'avg_emotions_fearful',
        'avg_emotions_happy',
        'avg_emotions_neutral',
        'avg_emotions_sad',
        'avg_emotions_surprised',
        'max_emotion',
        'max_emotion_value',
        'aggregated_frames_count',
        'last_frame_at',
    ];

    protected $casts = [
        'term' => 'datetime',
        'last_frame_at' => 'datetime',
    ];

    protected static function newFactory(): TermAnalyticFactory
    {
        return TermAnalyticFactory::new();
    }

    public function consultation(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Consultation::class, 'model_id')
            ->where('model_type', 'consultation');
    }
}
