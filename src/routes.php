<?php

use EscolaLms\Recommender\Http\Controllers\RecommenderController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/admin/recommender')
    ->middleware(['auth:api'])
    ->group(function () {
        Route::get('/course/{courseId}', [RecommenderController::class, 'course']);
        Route::get('/lesson/{lessonId}/topic', [RecommenderController::class, 'topic']);
    });
