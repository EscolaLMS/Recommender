<?php

use EscolaLms\Recommender\Http\Controllers\RecommenderController;
use EscolaLms\Recommender\Http\Controllers\TermAnalyticController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/admin/recommender')
    ->middleware(['auth:api'])
    ->group(function () {
        Route::get('/course/{courseId}', [RecommenderController::class, 'course']);
        Route::get('/lesson/{lessonId}/topic', [RecommenderController::class, 'topic']);
        Route::get('/aggregated-frames/{modelType}/{modelId}/{term}', [RecommenderController::class, 'aggregateFrames']);
        Route::get('/analytics/{modelType}/{modelId}', [RecommenderController::class, 'modelAnalytics']);
        Route::get('/analytics/{modelType}/{modelId}/{term}', [RecommenderController::class, 'modelTermAnalytics']);
        Route::get('/terms/{modelType}', [TermAnalyticController::class, 'index']);
    });

Route::prefix('api/recommender')
    ->middleware('verifySignature')
    ->group(function () {
        Route::post('/aggregated-frames/save', [RecommenderController::class, 'aggregateFrameSave']);
    });
