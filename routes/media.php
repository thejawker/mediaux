<?php

use Illuminate\Support\Facades\Route;
use TheJawker\Mediaux\Http\Controllers\MediaItemFetchController;
use TheJawker\Mediaux\Http\Controllers\MediaUploadController;

Route::middleware('api')->prefix('media')->group(function () {
    Route::get('{mediaItem}/{options}/{filename}', MediaItemFetchController::class)
        ->name('media.fetch.with_options');

    Route::get('{mediaItem}/{filename}', MediaItemFetchController::class)
        ->name('media.fetch');

    Route::post('', MediaUploadController::class);
});
