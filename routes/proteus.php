<?php

use Illuminate\Support\Facades\Route;
use Ometra\Apollo\Proteus\Http\Controllers\DirectoryController;
use Ometra\Apollo\Proteus\Http\Controllers\FileController;

Route::post('directory-deleted', DirectoryController::class)->name('proteus.directory-deleted');
Route::post('file-deleted', FileController::class)->name('proteus.file-deleted');
