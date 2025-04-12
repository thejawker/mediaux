<?php

use Illuminate\Support\Facades\Route;
use TheJawker\Mediaux\Http\Controllers\MediaItemFetchController;
use TheJawker\Mediaux\Http\Controllers\MediaUploadController;

Route::middleware('api')->prefix('media')->group(function () {
    Route::get('{mediaItem}/{filename}', MediaItemFetchController::class)
        ->name('media.fetch');

    Route::get('{mediaItem}/{options}/{filename}', [MediaItemFetchController::class, 'withOptions'])
        ->name('media.fetch.transformations');

    Route::post('', MediaUploadController::class);
});
