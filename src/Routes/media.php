<?php

use Illuminate\Support\Facades\Route;
use TheJawker\Mediaux\Http\Controllers\MediaItemFetchController;

Route::prefix('media')->group(function () {
    Route::get('{mediaItem}/{filename}', MediaItemFetchController::class)
        ->name('media.fetch');

    Route::get('{mediaItem}/{options}/{filename}', [MediaItemFetchController::class, 'withOptions'])
        ->name('media.fetch.transformations');
});
